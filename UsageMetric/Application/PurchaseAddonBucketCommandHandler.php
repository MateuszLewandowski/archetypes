<?php

declare(strict_types=1);

namespace Application\CommandHandler;

use Application\Command\PurchaseAddonBucketCommand;
use Application\DTO\PurchaseAddonBucketResponse;
use Application\Exception\InvalidBucketQuantityException;
use Application\Service\EventPublisher;
use Application\Service\IdempotencyStore;
use DateTimeImmutable;
use EntitlementRepository;
use Exception\InvalidBucketExpirationException;
use FeatureType;
use InvalidArgumentException;
use Quantity;

final readonly class PurchaseAddonBucketCommandHandler
{
    public function __construct(
        private EntitlementRepository $repository,
        private EventPublisher $eventPublisher,
        private IdempotencyStore $idempotencyStore,
    ) {
    }

    public function handle(PurchaseAddonBucketCommand $command): PurchaseAddonBucketResponse
    {
        if ($command->idempotencyKey) {
            $cached = $this->idempotencyStore->get($command->idempotencyKey);
            if ($cached) {
                return $cached;
            }
        }

        $feature = $this->parseFeature($command->feature);

        $entitlement = $this->repository->findByCustomerAndFeature(
            $command->customerId,
            $feature
        );

        $expirationDate = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $command->expirationDate
        )->setTime(23, 59, 59);

        try {
            $bucketId = $entitlement->addBucket(
                new Quantity($command->quantity),
                $expirationDate,
                $command->orderId,
            );
        } catch (InvalidBucketExpirationException $e) {
            throw new InvalidBucketQuantityException($e->getMessage());
        }

        $this->repository->save($entitlement);

        foreach ($entitlement->pullEvents() as $event) {
            $this->eventPublisher->publish($event);
        }

        $state = $entitlement->getState();

        $response = new PurchaseAddonBucketResponse(
            bucketId: $bucketId->value,
            customerId: $command->customerId,
            feature: $command->feature,
            quantity: $command->quantity,
            expiresAt: $expirationDate->format('Y-m-d H:i:s'),
            totalRemainingInBuckets: $state['buckets']['totalRemaining'],
            totalRemainingInQuota: $state['planQuota']['available'],
            message: sprintf(
                'Successfully purchased %d %s. Total remaining: %d',
                $command->quantity,
                $command->feature,
                $state['buckets']['totalRemaining'] + $state['planQuota']['available']
            ),
        );

        if ($command->idempotencyKey) {
            $this->idempotencyStore->store($command->idempotencyKey, $response, 3600);
        }

        return $response;
    }

    private function parseFeature(string $featureString): FeatureType
    {
        return match (strtolower($featureString)) {
            'sms' => FeatureType::SMS,
            default => throw new InvalidArgumentException(
                sprintf('Unknown feature: %s', $featureString)
            ),
        };
    }
}