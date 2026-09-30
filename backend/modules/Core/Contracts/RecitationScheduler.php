<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

interface RecitationScheduler
{
    public function onMilestoneCompleted(string $studentId, string $milestoneId, ?string $teacherId, string $title): void;
}
