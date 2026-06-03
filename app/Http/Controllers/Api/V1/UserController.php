<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\User\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        $this->authorize('manageUsers', $restaurant);

        $users = User::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get();

        return UserResource::collection($users);
    }

    public function store(Request $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('manageUsers', $restaurant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'unique:users,login'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:owner,manager'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'login' => $validated['login'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'restaurant_id' => $restaurant->id,
        ]);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Restaurant $restaurant, User $user): JsonResponse
    {
        $this->authorize('manageUsers', $restaurant);

        abort_if(
            $user->restaurant_id !== $restaurant->id,
            403,
            'Пользователь не принадлежит этому ресторану.'
        );

        $user->delete();

        return response()->json(['message' => 'Пользователь удалён.']);
    }
}
