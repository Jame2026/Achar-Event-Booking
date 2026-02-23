<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'users_total' => User::count(),
            'users_by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'vendor' => User::where('role', 'vendor')->count(),
                'user' => User::where('role', 'user')->count(),
            ],
            'events_total' => Event::count(),
            'bookings_total' => Booking::count(),
        ]);
    }

    public function users(): JsonResponse
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'vendor', 'admin'])],
        ]);

        $user->update($validated);

        return response()->json($user->only(['id', 'name', 'email', 'role']));
    }
}
