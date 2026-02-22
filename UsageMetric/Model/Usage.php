<?php

declare(strict_types=1);

use Exception\InvalidQuantityException;

final readonly class Usage implements CollectionItem
{
    public function __construct(
        public UsageId $id,
        public FeatureType $featureType,
        public Quantity $quantity,
        public DateTimeImmutable $consumedAt,
        public UsageSource $source,
        public ?BucketId $bucketId = null,
        public ?string $reference = null,
        public string $status = 'ALLOWED',
    ) {
        if ($quantity->value <= 0) {
            throw new InvalidQuantityException('Usage quantity must be greater than 0');
        }
    }

    public static function fromPlanQuota(
        FeatureType $featureType,
        Quantity $quantity,
        ?string $reference = null,
    ): self {
        return new self(
            UsageId::create(),
            $featureType,
            $quantity,
            new DateTimeImmutable(),
            UsageSource::PLAN_QUOTA,
            null,
            $reference,
            'ALLOWED',
        );
    }

    public static function fromBucket(
        FeatureType $featureType,
        Quantity $quantity,
        BucketId $bucketId,
        ?string $reference = null,
    ): self {
        return new self(
            UsageId::create(),
            $featureType,
            $quantity,
            new DateTimeImmutable(),
            UsageSource::BUCKET,
            $bucketId,
            $reference,
            'ALLOWED',
        );
    }

    public static function refused(
        FeatureType $featureType,
        Quantity $quantity,
        string $reason = 'Insufficient quota',
    ): self {
        return new self(
            UsageId::create(),
            $featureType,
            $quantity,
            new DateTimeImmutable(),
            UsageSource::MANUAL_ADJUSTMENT,
            null,
            $reason,
            'REFUSED',
        );
    }

    public function identity(): string
    {
        return $this->id->value;
    }

    public function equals(CollectionItem $item): bool
    {
        return $item->identity() === $this->identity();
    }

    public function isAllowed(): bool
    {
        return $this->status === 'ALLOWED';
    }

    public function isRefused(): bool
    {
        return $this->status === 'REFUSED';
    }
}