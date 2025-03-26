<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::orderBy('name')->get();

        return response()->json([
            'message' => 'Deliveries retrieved successfully',
            'data' => $deliveries
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $delivery = DB::transaction(function () use ($validated) {
            return Delivery::create($validated);
        });

        return response()->json([
            'message' => 'Delivery created successfully',
            'data' => $delivery
        ], 201);
    }

    public function show(Delivery $delivery)
    {
        return response()->json([
            'message' => 'Delivery retrieved successfully',
            'data' => $delivery
        ]);
    }

    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        DB::transaction(function () use ($validated, $delivery) {
            $delivery->update($validated);
        });

        return response()->json([
            'message' => 'Delivery updated successfully',
            'data' => $delivery
        ]);
    }

    public function destroy(Delivery $delivery)
    {
        DB::transaction(function () use ($delivery) {
            $delivery->delete();
        });

        return response()->json([
            'message' => 'Delivery deleted successfully'
        ]);
    }
}
