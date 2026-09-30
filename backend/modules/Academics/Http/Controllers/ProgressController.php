<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academics\Models\HifzMilestone;
use Modules\Academics\Models\StudentProgress;
use Modules\Academics\Services\ProgressAccess;
use Modules\Core\Models\Guardianship;

class ProgressController extends Controller
{
    public function milestones()
    {
        return HifzMilestone::with('badge')->orderBy('display_order')->get();
    }

    public function portal(Request $request)
    {
        $wardIds = Guardianship::query()
            ->where('guardian_id', $request->user()->id)
            ->where('can_view_progress', true)
            ->pluck('ward_id');

        $ids = $wardIds->push($request->user()->id)->unique();

        $rows = StudentProgress::with(['milestone.badge', 'certificate'])
            ->whereIn('student_id', $ids)
            ->get()
            ->groupBy('student_id');

        return response()->json([
            'students' => $rows->map(fn ($items, $studentId) => [
                'student_id' => $studentId,
                'is_self' => $studentId === $request->user()->id,
                'steps' => $items->sortBy(fn ($item) => $item->milestone->display_order)->values()->map(fn ($item) => [
                    'milestone' => $item->milestone->title_i18n,
                    'status' => $item->status,
                    'completed_at' => optional($item->completed_at)->toISOString(),
                    'badge' => $item->status === 'completed' ? $item->milestone->badge?->name_i18n : null,
                    'certificate_code' => $item->certificate?->verification_code,
                    'next' => null,
                ]),
            ])->values(),
        ]);
    }

    public function show(Request $request, string $studentId, ProgressAccess $access)
    {
        if (!$access->canView($request->user(), $studentId)) {
            abort(403);
        }

        $milestones = HifzMilestone::with('badge')->orderBy('display_order')->get();
        $progress = StudentProgress::with('certificate')
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('milestone_id');

        $next = null;

        $steps = $milestones->map(function ($milestone) use ($progress, &$next) {
            $row = $progress->get($milestone->id);
            $status = $row->status ?? 'not_started';
            if ($next === null && $status !== 'completed') {
                $next = $milestone->title_i18n;
            }

            return [
                'milestone' => $milestone->title_i18n,
                'from_juz' => $milestone->from_juz,
                'to_juz' => $milestone->to_juz,
                'status' => $status,
                'badge' => $status === 'completed' ? $milestone->badge?->name_i18n : null,
                'certificate_code' => $row?->certificate?->verification_code,
            ];
        });

        return response()->json([
            'student_id' => $studentId,
            'steps' => $steps,
            'next_step' => $next,
        ]);
    }
}
