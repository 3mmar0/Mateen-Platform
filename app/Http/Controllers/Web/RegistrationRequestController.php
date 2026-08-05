<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:190'],
            'age' => ['nullable', 'integer', 'min:5', 'max:120'],
            'level' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:120'],
        ]);

        $row = RegistrationRequest::query()->create($data);

        return response()->json(['data' => $row], 201);
    }
}
