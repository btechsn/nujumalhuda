<?php

namespace Modules\Education\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Education\Models\Enrollment;

class EnrollmentApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Enrollment $enrollment
    ) {}
}
