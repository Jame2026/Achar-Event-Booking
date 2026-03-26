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

    public function users(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', Rule::in(['user', 'vendor', 'admin'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = max(1, min((int) ($validated['per_page'] ?? 20), 100));

        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'phone', 'location', 'profile_image_url', 'created_at')
            ->withCount('events')
            ->when(
                $validated['role'] ?? null,
                fn ($query, $role) => $query->where('role', $role)
            )
            ->latest()
            ->paginate($perPage);

        return response()->json($users);
    }

    public function customerDirectory(Request $request): JsonResponse
    {
        $admin = $this->resolveAdminFromRequest($request);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = max(1, min((int) ($validated['per_page'] ?? 20), 100));

        $customers = User::query()
            ->select('id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'created_at')
            ->where('role', 'user')
            ->withCount([
                'bookings',
                'bookings as confirmed_bookings_count' => fn ($query) => $query->where('status', 'confirmed'),
                'bookings as pending_bookings_count' => fn ($query) => $query->where('status', 'pending'),
                'bookings as cancelled_bookings_count' => fn ($query) => $query->where('status', 'cancelled'),
            ])
            ->with([
                'bookings' => fn ($query) => $query
                    ->select([
                        'id',
                        'user_id',
                        'event_id',
                        'quantity',
                        'total_amount',
                        'status',
                        'payment_status',
                        'service_name',
                        'requested_event_date',
                        'booked_items',
                        'created_at',
                    ])
                    ->with([
                        'event:id,title,event_type,starts_at,location,vendor_id,service_mode',
                        'event.vendor:id,name,email,profile_image_url',
                    ])
                    ->latest(),
            ])
            ->latest()
            ->paginate($perPage);

        return response()->json($customers);
    }

    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'vendor', 'admin'])],
        ]);

        $user->update($validated);

        return response()->json($user->only(['id', 'name', 'email', 'role']));
    }

    private function resolveAdminFromRequest(Request $request): User|JsonResponse
    {
        $validated = $request->validate([
            'admin_user_id' => ['required', 'integer', 'min:1'],
        ]);

        $admin = User::query()
            ->where('role', 'admin')
            ->find((int) $validated['admin_user_id']);

        if (! $admin) {
            return response()->json(['message' => 'Admin account not found.'], 403);
        }

        return $admin;
    }
}
