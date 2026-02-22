<?php

declare(strict_types=1);

final class Buckets extends Collection
{
    public function findActive(DateTimeImmutable $now): self
    {
        $filtered = array_filter(
            $this->values,
            static fn(CollectionItem $item) => $item instanceof Bucket
                && $item->status === BucketStatus::ACTIVE
                && !$item->isExpired($now)
        );

        $new = new self();
        foreach ($filtered as $item) {
            $new->add($item);
        }

        return $new;
    }

    public function findExpired(DateTimeImmutable $now): self
    {
        $filtered = array_filter(
            $this->values,
            static fn (CollectionItem $item) => $item instanceof Bucket
                && $item->isExpired($now)
                && $item->status !== BucketStatus::EXPIRED
        );

        $new = new self();
        foreach ($filtered as $item) {
            $new->add($item);
        }

        return $new;
    }

    public function getTotalRemaining(DateTimeImmutable $now): int
    {
        $total = 0;
        foreach ($this->findActive($now)->values as $bucket) {
            $total += $bucket->getRemaining();
        }

        return $total;
    }

    public function getTotalAvailable(): int
    {
        $total = 0;
        foreach ($this->values as $bucket) {
            if ($bucket instanceof Bucket) {
                $total += $bucket->available->value;
            }
        }

        return $total;
    }

    /** Find bucket suitable for consumption (using EXPIRY_FIRST strategy) */
    public function findBucketForConsumption(DateTimeImmutable $now): ?Bucket
    {
        $activeBuckets = $this->findActive($now)->values;

        if (empty($activeBuckets)) {
            return null;
        }

        // soonest first
        usort(
            $activeBuckets,
            static fn (CollectionItem $a, CollectionItem $b) => $a instanceof Bucket && $b instanceof Bucket
                ? $a->expiresAt->getTimestamp() <=> $b->expiresAt->getTimestamp()
                : 0
        );

        return reset($activeBuckets);
    }

    public function count(): int
    {
        return count($this->values);
    }
}