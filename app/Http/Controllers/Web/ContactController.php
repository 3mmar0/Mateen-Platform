<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function recipients(): JsonResponse
    {
        $users = User::query()
            ->whereIn('role', ['admin', 'supervisor', 'teacher', 'support'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return response()->json(['data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'recipient' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:5000'],
            'email' => ['nullable', 'email', 'max:190'],
        ]);

        $message = ContactMessage::query()->create($data);

        return response()->json(['data' => $message], 201);
    }
}
