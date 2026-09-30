<?php

namespace Modules\Resources\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Resources\Models\AudioRecitation;

class RecitationController extends Controller
{
    public function index(Request $request)
    {
        $query = AudioRecitation::query()->where('is_public', true)->orderBy('surah_number');

        if ($request->filled('reciter')) {
            $query->where('reciter', $request->string('reciter'));
        }

        if ($request->filled('surah')) {
            $query->where('surah_number', $request->integer('surah'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('reciter', 'like', "%{$search}%")
                    ->orWhere('surah_name_i18n->fr', 'like', "%{$search}%")
                    ->orWhere('surah_name_i18n->en', 'like', "%{$search}%")
                    ->orWhere('surah_name_i18n->ar', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->get()->map(fn (AudioRecitation $item) => [
                'id' => $item->id,
                'surah_number' => $item->surah_number,
                'surah_name' => $item->surah_name_i18n,
                'reciter' => $item->reciter,
                'audio_url' => $item->audio_url,
                'duration_seconds' => $item->duration_seconds,
                'play_count' => $item->play_count,
            ]),
        ]);
    }

    public function play(string $id)
    {
        $recitation = AudioRecitation::query()->where('is_public', true)->findOrFail($id);
        $recitation->increment('play_count');

        return response()->json([
            'audio_url' => $recitation->audio_url,
        ]);
    }
}
