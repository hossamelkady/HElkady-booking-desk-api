<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Booking::query()->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:160',
            'service' => 'required|string|max:160',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'sometimes|in:confirmed,cancelled,completed'
        ]);

        $record = Booking::create($data);
        return response()->json(['data' => $record], 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json(['data' => $booking]);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate(['customer_name' => 'sometimes|string|max:160', 'service' => 'sometimes|string|max:160', 'starts_at' => 'sometimes|date', 'ends_at' => 'sometimes|date|after:starts_at', 'status' => 'sometimes|in:confirmed,cancelled,completed']);
        $booking->update($data);
        return response()->json(['data' => $booking->refresh()]);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json([], 204);
    }
}
