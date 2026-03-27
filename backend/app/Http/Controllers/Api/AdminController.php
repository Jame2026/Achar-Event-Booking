<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private const SERVICE_FEE_RATE = 0.035;

    public function dashboard(): JsonResponse
    {
        $adminsTotal = User::where('role', 'admin')->count();
        $vendorsTotal = User::where('role', 'vendor')->count();
        $customersTotal = User::where('role', 'user')->count();

        $confirmedBookingsQuery = Booking::query()
            ->where(function ($query) {
                $query
                    ->where('status', 'confirmed')
                    ->orWhere('payment_status', 'confirmed');
            });

        $confirmedBookingsTotal = (clone $confirmedBookingsQuery)->count();
        $grossRevenueTotal = round((float) ((clone $confirmedBookingsQuery)->sum('total_amount') ?: 0), 2);
        $serviceFeeTotal = round($grossRevenueTotal * self::SERVICE_FEE_RATE, 2);

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

    public function exportReport(): StreamedResponse
    {
        $filename = 'admin-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Achar Admin Report']);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Users Total', 'Events Total', 'Bookings Total']);
            fputcsv($handle, [User::count(), Event::count(), Booking::count()]);
            fputcsv($handle, []);

            fputcsv($handle, ['Events']);
            fputcsv($handle, ['ID', 'Title', 'Vendor', 'Starts At', 'Location', 'Price', 'Capacity', 'Active', 'Bookings']);

            $events = Event::with('vendor')
                ->withCount('bookings')
                ->orderByDesc('created_at')
                ->get();

            foreach ($events as $event) {
                fputcsv($handle, [
                    $event->id,
                    $event->title,
                    $event->vendor?->name,
                    optional($event->starts_at)->toDateTimeString(),
                    $event->location,
                    $event->price,
                    $event->capacity,
                    $event->is_active ? 'Yes' : 'No',
                    $event->bookings_count,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Bookings']);
            fputcsv($handle, ['ID', 'Event', 'Customer', 'Email', 'Status', 'Payment', 'Total', 'Created At', 'Event Date']);

            $bookings = Booking::with(['event', 'user'])
                ->orderByDesc('created_at')
                ->get();

            foreach ($bookings as $booking) {
                $eventTitle = $booking->event?->title ?: $booking->service_name;
                $customerName = $booking->customer_name ?: $booking->user?->name;
                $customerEmail = $booking->customer_email ?: $booking->user?->email;
                $eventDate = $booking->requested_event_date
                    ? $booking->requested_event_date->toDateString()
                    : optional($booking->event?->starts_at)->toDateTimeString();

                fputcsv($handle, [
                    $booking->id,
                    $eventTitle,
                    $customerName,
                    $customerEmail,
                    $booking->status,
                    $booking->payment_status,
                    $booking->total_amount,
                    optional($booking->created_at)->toDateTimeString(),
                    $eventDate,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Users']);
            fputcsv($handle, ['ID', 'Name', 'Email', 'Role', 'Created At']);

            $users = User::query()
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->orderByDesc('created_at')
                ->get();

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    optional($user->created_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportUsers(): StreamedResponse
    {
        $filename = 'admin-users-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ID', 'Name', 'Email', 'Role', 'Created At']);

            $users = User::query()
                ->select('id', 'name', 'email', 'role', 'created_at')
                ->orderByDesc('created_at')
                ->get();

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    optional($user->created_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
