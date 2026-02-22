<?php

declare(strict_types=1);

final class UsageLog extends Collection
{
    public function getForFeature(FeatureType $featureType): self
    {
        $filtered = array_filter(
            $this->values,
            static fn (CollectionItem $item) => $item instanceof Usage && $item->featureType === $featureType
        );

        $new = new self();
        foreach ($filtered as $item) {
            $new->add($item);
        }

        return $new;
    }

    public function getAllowedForFeature(FeatureType $featureType): self
    {
        $filtered = array_filter(
            $this->values,
            static fn (CollectionItem $item) => $item instanceof Usage
                && $item->featureType === $featureType
                && $item->isAllowed()
        );

        $new = new self();
        foreach ($filtered as $item) {
            $new->add($item);
        }

        return $new;
    }

    public function getTotalConsumedForFeature(FeatureType $featureType): int
    {
        $total = 0;
        foreach ($this->getAllowedForFeature($featureType)->values as $usage) {
            if ($usage instanceof Usage) {
                $total += $usage->quantity->value;
            }
        }

        return $total;
    }

    public function getRefused(): self
    {
        $filtered = array_filter(
            $this->values,
            static fn (CollectionItem $item) => $item instanceof Usage && $item->isRefused()
        );

        $new = new self();
        foreach ($filtered as $item) {
            $new->add($item);
        }

        return $new;
    }

    public function getInPeriod(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        $filtered = array_filter(
            $this->values,
            static fn (CollectionItem $item) => $item instanceof Usage
                && $item->consumedAt >= $from
                && $item->consumedAt <= $to
        );

        $new = new self();
        foreach ($filtered as $item) {
            $new->add($item);
        }

        return $new;
    }
}