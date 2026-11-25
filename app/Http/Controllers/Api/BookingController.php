<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        if ($this->overlaps($data['starts_at'], $data['ends_at'])) {
            return response()->json(['message' => 'The requested time overlaps an existing booking.'], 422);
        }

        $record = Booking::create($data);
        return response()->json(['data' => $record], 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json(['data' => $booking]);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => 'sometimes|string|max:160',
            'service' => 'sometimes|string|max:160',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date',
            'status' => 'sometimes|in:confirmed,cancelled,completed',
        ]);

        $start = Carbon::parse($data['starts_at'] ?? $booking->starts_at);
        $end = Carbon::parse($data['ends_at'] ?? $booking->ends_at);
        if (! $end->gt($start)) {
            return response()->json(['message' => 'The ends_at must be after starts_at.', 'errors' => ['ends_at' => ['The ends_at must be after starts_at.']]], 422);
        }

        $status = $data['status'] ?? $booking->status;
        if ($status !== 'cancelled' && $this->overlaps($start, $end, $booking->id)) {
            return response()->json(['message' => 'The requested time overlaps an existing booking.'], 422);
        }

        $booking->update($data);
        return response()->json(['data' => $booking->refresh()]);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json([], 204);
    }

    private function overlaps($start, $end, ?int $ignoreId = null): bool
    {
        return Booking::query()
            ->where('status', '!=', 'cancelled')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('starts_at', '<', Carbon::parse($end))
            ->where('ends_at', '>', Carbon::parse($start))
            ->exists();
    }
}
