<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $client = Client::where('email', $validated['email'])->first();

        if ($client && $client->password) {
            throw ValidationException::withMessages([
                'email' => 'An account with this email already exists.',
            ]);
        }

        // A guest Client (password null) from a prior booking/enrollment
        // upgrades into a full account instead of erroring as a duplicate
        // email — see App\Models\Client's docblock.
        $client ??= new Client(['email' => $validated['email']]);
        $client->fill([
            'name' => $validated['name'],
            // phone is optional on RegisterInput but NOT NULL in the
            // clients table; fall back to the existing value (guest
            // upgrade) or an empty string rather than erroring.
            'phone' => $validated['phone'] ?? $client->phone ?? '',
            'password' => $validated['password'],
        ])->save();

        $token = $client->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => new ClientResource($client),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $client = Client::where('email', $validated['email'])->first();

        if (! $client || ! $client->password || ! Hash::check($validated['password'], $client->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $token = $client->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => new ClientResource($client),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('sanctum')->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'client' => new ClientResource($request->user('sanctum')),
        ]);
    }
}
