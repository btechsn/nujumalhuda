<?php

declare(strict_types=1);

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Lesson;

class CurrentLessonController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['approved', 'active'])
            ->with('promotion')
            ->latest('updated_at')
            ->first();

        $programId = $enrollment?->promotion?->program_id;
        if (!$programId) {
            return ApiResponse::success(null);
        }

        $lesson = Lesson::query()
            ->select('lessons.*')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('courses.program_id', $programId)
            ->where('courses.is_active', true)
            ->whereNull('courses.deleted_at')
            ->where('lessons.is_active', true)
            ->orderBy('courses.sequence')
            ->orderBy('lessons.sequence')
            ->first();

        if (!$lesson) {
            return ApiResponse::success(null);
        }

        $lesson->load('course');

        return ApiResponse::success([
            'id' => $lesson->id,
            'title' => $lesson->title_i18n,
            'content' => $lesson->content_i18n,
            'course' => $lesson->course?->name_i18n,
            'materials' => $this->materials($lesson),
        ]);
    }

    /**
     * @return list<array{kind: string, url: string, name: string}>
     */
    private function materials(Lesson $lesson): array
    {
        $items = [];
        foreach ($lesson->materials ?? [] as $material) {
            if (is_string($material) && $material !== '') {
                $items[] = [
                    'kind' => $this->kind($material),
                    'url' => $material,
                    'name' => $this->name($material),
                ];
                continue;
            }
            if (!is_array($material)) {
                continue;
            }
            $url = $material['url'] ?? $material['path'] ?? null;
            if (!is_string($url) || $url === '') {
                continue;
            }
            $kind = $material['type'] ?? $material['kind'] ?? $this->kind($url);
            $items[] = [
                'kind' => in_array($kind, ['audio', 'pdf'], true) ? $kind : $this->kind($url),
                'url' => $url,
                'name' => is_string($material['name'] ?? null) ? $material['name'] : $this->name($url),
            ];
        }

        return $items;
    }

    private function kind(string $url): string
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if (preg_match('/\.(mp3|m4a|ogg|wav|aac)$/', $path)) {
            return 'audio';
        }
        if (str_ends_with($path, '.pdf')) {
            return 'pdf';
        }

        return 'file';
    }

    private function name(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $base = basename($path);

        return $base !== '' ? $base : $url;
    }
}
