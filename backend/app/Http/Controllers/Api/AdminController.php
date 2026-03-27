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
    }
}
