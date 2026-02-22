<?php

declare(strict_types=1);

namespace Application\DTO;

final readonly class ConsumeFeatureResponse
{
    public function __construct(
        public bool $allowed,
        public string $feature,
        public int $quantity,
        public string $consumedFrom,
        public int $remainingInQuota,
        public int $remainingInBuckets,
        public ?string $bucketId = null,
        public ?string $message = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed,
            'feature' => $this->feature,
            'quantity' => $this->quantity,
            'consumedFrom' => $this->consumedFrom,
            'remaining' => [
                'total' => $this->remainingInQuota + $this->remainingInBuckets,
                'planQuota' => $this->remainingInQuota,
                'buckets' => $this->remainingInBuckets,
            ],
            'bucketId' => $this->bucketId,
            'message' => $this->message,
        ];
    }
}