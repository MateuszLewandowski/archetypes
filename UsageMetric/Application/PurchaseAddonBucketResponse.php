<?php

declare(strict_types=1);

namespace Application\DTO;

final readonly class PurchaseAddonBucketResponse
{
    public function __construct(
        public string $bucketId,
        public string $customerId,
        public string $feature,
        public int $quantity,
        public string $expiresAt,
        public int $totalRemainingInBuckets,
        public int $totalRemainingInQuota,
        public string $message,
    ) {
    }

    public function toArray(): array
    {
        return [
            'success' => true,
            'bucketId' => $this->bucketId,
            'customerId' => $this->customerId,
            'feature' => $this->feature,
            'quantity' => $this->quantity,
            'expiresAt' => $this->expiresAt,
            'totalRemaining' => $this->totalRemainingInBuckets + $this->totalRemainingInQuota,
            'breakdown' => [
                'buckets' => $this->totalRemainingInBuckets,
                'planQuota' => $this->totalRemainingInQuota,
            ],
            'message' => $this->message,
        ];
    }
}