<?php

namespace App\Services;

use App\Models\Certificate;

final class CourseCompletionResult
{
    public function __construct(
        private readonly int $requiredCount,
        private readonly int $satisfiedCount,
        private readonly int $percent,
        private readonly bool $complete,
        private readonly ?Certificate $certificate,
    ) {
    }

    public function requiredCount(): int
    {
        return $this->requiredCount;
    }

    public function satisfiedCount(): int
    {
        return $this->satisfiedCount;
    }

    public function progressPercent(): int
    {
        return $this->percent;
    }

    public function isComplete(): bool
    {
        return $this->complete;
    }

    public function certificate(): ?Certificate
    {
        return $this->certificate;
    }
}
