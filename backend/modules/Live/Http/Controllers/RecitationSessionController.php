<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Live\Models\RecitationSession;

class RecitationSessionController extends Controller
{
    public function index()
    {
        return RecitationSession::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('starts_at')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'starts_at' => 'required|date',
            'student_id' => 'nullable|string|size:26',
            'live_stream_id' => 'nullable|string|size:26',
        ]);

        $session = RecitationSession::create([
            ...$data,
            'trigger' => 'scheduled',
            'teacher_id' => $request->user()->id,
            'status' => 'scheduled',
        ]);

        return response()->json($session, 201);
    }
}
