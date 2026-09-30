<?php

namespace Modules\Resources\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Resources\Models\LibraryResource;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = LibraryResource::query()
            ->where('is_public', true)
            ->orderBy('display_order')
            ->orderByDesc('download_count');

        if ($request->filled('tradition')) {
            $query->where('tradition', $request->string('tradition'));
        }

        if ($request->filled('kind')) {
            $query->where('kind', $request->string('kind'));
        }

        if ($request->filled('q')) {
            $raw = trim((string) $request->string('q'));
            $term = '%'.$raw.'%';
            $normalized = '%'.$this->normalizeSearch($raw).'%';

            $query->where(function ($builder) use ($term, $normalized) {
                $builder->where('title_i18n->fr', 'ilike', $term)
                    ->orWhere('title_i18n->en', 'ilike', $term)
                    ->orWhere('title_i18n->ar', 'ilike', $term)
                    ->orWhere('author', 'ilike', $term)
                    ->orWhere('description_i18n->fr', 'ilike', $term)
                    ->orWhere('description_i18n->en', 'ilike', $term)
                    ->orWhere('slug', 'ilike', $term)
                    ->orWhereRaw(
                        "regexp_replace(lower(coalesce(title_i18n->>'fr','') || ' ' || coalesce(title_i18n->>'en','') || ' ' || coalesce(title_i18n->>'ar','') || ' ' || coalesce(author,'') || ' ' || coalesce(slug,'')), E'[''‘’‛ʼʻ`]', '', 'g') like ?",
                        [$normalized]
                    );
            });
        }

        $paginator = $query->paginate(min(40, max(1, $request->integer('per_page', 24))));

        return response()->json([
            'data' => collect($paginator->items())->map(fn (LibraryResource $item) => $this->payload($item)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $item = LibraryResource::query()
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return response()->json(['data' => $this->payload($item)]);
    }

    public function download(string $slug)
    {
        $item = LibraryResource::query()
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $item->increment('download_count');

        return response()->json([
            'url' => $item->external_url ?: ($item->file_path ? url('storage/'.$item->file_path) : null),
        ]);
    }

    private function normalizeSearch(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return str_replace(
            ["'", '‘', '’', '‛', 'ʼ', 'ʻ', '`', '´'],
            '',
            $value
        );
    }

    private function payload(LibraryResource $item): array
    {
        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $item->title_i18n,
            'description' => $item->description_i18n,
            'author' => $item->author,
            'tradition' => $item->tradition,
            'kind' => $item->kind,
            'language' => $item->language,
            'external_url' => $item->external_url,
            'file_path' => $item->file_path,
            'download_count' => $item->download_count,
            'display_order' => $item->display_order,
            'cover_tone' => match ($item->tradition) {
                'baye_niasse' => 'gold',
                'sunnite' => 'green',
                default => 'cream',
            },
        ];
    }
}
