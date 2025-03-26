<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StatusProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusProductController extends Controller
{
    public function index()
    {
        $statuses = StatusProduct::orderBy('name')->get();

        return response()->json([
            'message' => 'Status products retrieved successfully',
            'data' => $statuses
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:status_products'
        ]);

        $status = DB::transaction(function () use ($validated) {
            return StatusProduct::create($validated);
        });

        return response()->json([
            'message' => 'Status product created successfully',
            'data' => $status
        ], 201);
    }

    public function show(StatusProduct $statusProduct)
    {
        return response()->json([
            'message' => 'Status product retrieved successfully',
            'data' => $statusProduct
        ], 200);
    }

    public function update(Request $request, StatusProduct $statusProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:status_products,name,' . $statusProduct->id
        ]);

        DB::transaction(function () use ($validated, $statusProduct) {
            $statusProduct->update($validated);
        });

        return response()->json([
            'message' => 'Status product updated successfully',
            'data' => $statusProduct
        ], 200);
    }

    public function destroy(StatusProduct $statusProduct)
    {
        DB::transaction(function () use ($statusProduct) {
            $statusProduct->delete();
        });

        return response()->json([
            'message' => 'Status product deleted successfully'
        ], 200);
    }
}
