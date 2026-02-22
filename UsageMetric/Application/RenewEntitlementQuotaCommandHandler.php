<?php

declare(strict_types=1);

namespace Application\CommandHandler;

use Application\Command\RenewEntitlementQuotaCommand;
use Application\Exception\EntitlementNotFoundException;
use Application\Service\EventPublisher;
use Bucket;
use DateTimeImmutable;
use Entitlement;
use EntitlementId;
use EntitlementRepository;

final readonly class RenewEntitlementQuotaCommandHandler
{
    public function __construct(
        private EntitlementRepository $repository,
        private EventPublisher $eventPublisher,
        private Logger $logger,
        private NotificationService $notificationService,
    ) {
    }

    public function handle(RenewEntitlementQuotaCommand $command): array
    {
        $now = $command->renewalDateTime
            ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $command->renewalDateTime)
            : new DateTimeImmutable();

        $entitlementId = new EntitlementId($command->entitlementId);
        $entitlement = $this->repository->findById($entitlementId);

        $entitlement->renewQuota($now);

        $this->repository->save($entitlement);

        foreach ($entitlement->pullEvents() as $event) {
            $this->eventPublisher->publish($event);
        }

        $state = $entitlement->getState();
        $this->checkAndAlertExpiringBuckets($entitlement, $now);

        $this->logger->info(
            'Entitlement quota renewed',
            [
                'entitlementId' => $command->entitlementId,
                'feature' => $state['feature'],
                'newQuota' => $state['planQuota']['limit'],
                'nextRenewal' => $state['renewal']['nextRenewalAt'],
            ]
        );

        return [
            'success' => true,
            'entitlementId' => $command->entitlementId,
            'message' => 'Quota renewed successfully',
            'nextRenewalAt' => $state['renewal']['nextRenewalAt'],
        ];
    }

    private function checkAndAlertExpiringBuckets(Entitlement $entitlement, DateTimeImmutable $now): void
    {
        $buckets = $entitlement->getBuckets();
        $warningThreshold = $now->modify('+7 days');

        foreach ($buckets->list() as $bucket) {
            if (
                $bucket instanceof Bucket
                && $bucket->expiresAt <= $warningThreshold
                && !$bucket->isExpired($now)
            ) {
                $this->notificationService->notifyBucketExpiringWarning(
                    $entitlement->customerId,
                    $bucket,
                );
            }
        }
    }
}