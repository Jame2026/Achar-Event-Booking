<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('bookings.event:id,title,starts_at,location,vendor_id', 'bookings.event.vendor:id,name');

        return response()->json($user);
    }

    public function myBookings(Request $request): JsonResponse
    {
        $bookings = $request->user()
            ->bookings()
            ->with('event:id,title,starts_at,location,vendor_id', 'event.vendor:id,name')
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }
}
