<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $adminsTotal = User::where('role', 'admin')->count();
        $vendorsTotal = User::where('role', 'vendor')->count();
        $customersTotal = User::where('role', 'user')->count();

        $confirmedBookingsQuery = Booking::query()
            ->where('status', 'confirmed');

        $confirmedBookingsTotal = (clone $confirmedBookingsQuery)->count();
        $grossRevenueTotal = round((float) ((clone $confirmedBookingsQuery)->sum('total_amount') ?: 0), 2);
        $serviceFeeTotal = round((float) ((clone $confirmedBookingsQuery)->sum('service_fee_amount') ?: 0), 2);
        $vendorSubscriptionRevenueTotal = round((float) VendorSetting::query()
            ->whereNull('event_id')
            ->whereNotNull('subscription_paid_at')
            ->sum('subscription_price_amount'), 2);

        return response()->json([
            'users_total' => User::count(),
            'users_by_role' => [
                'admin' => $adminsTotal,
                'vendor' => $vendorsTotal,
                'user' => $customersTotal,
            ],
            'admins_total' => $adminsTotal,
            'vendors_total' => $vendorsTotal,
            'customers_total' => $customersTotal,
            'accounts_total' => $vendorsTotal + $customersTotal,
            'events_total' => Event::count(),
            'bookings_total' => Booking::count(),
            'confirmed_bookings_total' => $confirmedBookingsTotal,
            'gross_revenue_total' => $grossRevenueTotal,
            'service_fee_total' => $serviceFeeTotal,
            'vendor_subscription_revenue_total' => $vendorSubscriptionRevenueTotal,
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
            ->select('id', 'name', 'email', 'role', 'phone', 'location', 'profile_image_url', 'created_at', 'updated_at')
            ->with([
                'vendorSetting:user_id,subscription_plan_code,subscription_plan_name,subscription_price_amount,subscription_duration_months,subscription_service_limit,subscription_package_limit,subscription_status,subscription_started_at,subscription_paid_at,subscription_expires_at',
            ])
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
            ->select('id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'created_at', 'updated_at')
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

    public function activateVendorSubscription(Request $request, User $user): JsonResponse
    {
        $authenticatedAdmin = $request->user();
        if (! ($authenticatedAdmin instanceof User && $authenticatedAdmin->isAdmin())) {
            $admin = $this->resolveAdminFromRequest($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }
        }

        if (! $user->isVendor()) {
            return response()->json(['message' => 'This account is not a vendor.'], 422);
        }

        $settings = VendorSetting::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'event_id' => null,
            ],
            VendorSetting::defaultGlobalAttributes(),
        );

        $planCode = (string) ($settings->subscription_plan_code ?? '');
        if (! VendorSetting::resolveSubscriptionPlan($planCode)) {
            return response()->json(['message' => 'This vendor does not have a selected listing plan yet.'], 422);
        }

        $status = $settings->resolvedSubscriptionStatus();
        if ($status === 'active') {
            return response()->json(['message' => 'This vendor listing plan is already active.'], 422);
        }

        if ($status !== 'pending_approval') {
            return response()->json([
                'message' => 'This vendor has not clicked Complete Payment yet.',
            ], 422);
        }

        $payload = VendorSetting::subscriptionPayloadForPlan($planCode);
        if ($settings->subscription_paid_at) {
            $payload['subscription_paid_at'] = $settings->subscription_paid_at;
        }

        $settings->fill($payload);
        $settings->save();

        return response()->json([
            'message' => 'Vendor listing plan activated.',
            'user' => $user->fresh()->load([
                'vendorSetting:user_id,subscription_plan_code,subscription_plan_name,subscription_price_amount,subscription_duration_months,subscription_service_limit,subscription_package_limit,subscription_status,subscription_started_at,subscription_paid_at,subscription_expires_at',
            ]),
        ]);
    }

    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'vendor', 'admin'])],
        ]);

        $user->update($validated);

        if ($user->isVendor()) {
            VendorSetting::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'event_id' => null,
                ],
                VendorSetting::defaultGlobalAttributes(),
            );
        }

        return response()->json(
            $user->fresh()->load([
                'vendorSetting:user_id,subscription_plan_code,subscription_plan_name,subscription_price_amount,subscription_duration_months,subscription_service_limit,subscription_package_limit,subscription_status,subscription_started_at,subscription_paid_at,subscription_expires_at',
            ])
        );
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
