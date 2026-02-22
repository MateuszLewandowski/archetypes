<?php

declare(strict_types=1);

namespace Application\CommandHandler;

use Application\Command\ConsumeFeatureCommand;
use Application\DTO\ConsumeFeatureResponse;
use Application\Exception\EntitlementNotFoundException;
use Application\Service\EventPublisher;
use EntitlementRepository;
use FeatureType;
use InvalidArgumentException;
use Quantity;

final readonly class ConsumeFeatureCommandHandler
{
    public function __construct(
        private EntitlementRepository $repository,
        private EventPublisher $eventPublisher,
        private UsageMetricsService $metricsService,
        private Logger $logger,
    ) {
    }

    public function handle(ConsumeFeatureCommand $command): ConsumeFeatureResponse
    {
        $startTime = microtime(true);

        $feature = $this->parseFeature($command->feature);

        $entitlement = $this->repository->findByCustomerAndFeature(
            $command->customerId,
            $feature
        );

        $result = $entitlement->consume(
            new Quantity($command->quantity),
            $command->reference,
        );

        $this->repository->save($entitlement);

        foreach ($entitlement->pullEvents() as $event) {
            $this->eventPublisher->publish($event);
        }

        $duration = microtime(true) - $startTime;
        $this->metricsService->recordConsumption(
            $command->customerId,
            $command->feature,
            $result->allowed,
            $duration
        );

        if (!$result->allowed) {
            $this->logger->info(
                'Consumption refused',
                [
                    'customerId' => $command->customerId,
                    'feature' => $command->feature,
                    'quantity' => $command->quantity,
                    'remaining' => $result->getRemaining(),
                ]
            );
        }

        return new ConsumeFeatureResponse(
            allowed: $result->allowed,
            feature: $command->feature,
            quantity: $command->quantity,
            consumedFrom: $result->consumedFromSource ?? 'refused',
            remainingInQuota: $result->remainingInQuota,
            remainingInBuckets: $result->remainingInBuckets,
            bucketId: $result->usage->bucketId?->value,
            message: $result->allowed
                ? sprintf(
                    'Consumed %d %s from %s. Remaining: %d',
                    $command->quantity,
                    $command->feature,
                    $result->consumedFromSource,
                    $result->getRemaining()
                )
                : sprintf(
                    'Insufficient quota. Requested: %d, Available: %d',
                    $command->quantity,
                    $result->getRemaining()
                ),
        );
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