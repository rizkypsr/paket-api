<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = Product::with(['delivery', 'status', 'employee']);

            if ($request->has('search')) {
                $query->whereRaw('LOWER(receipt_number) LIKE ?', ['%' . strtolower($request->search) . '%']);
            }

            if ($request->has('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->has('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('status_product_id')) {
                $query->where('status_product_id', $request->status_product_id);
            }

            if ($request->has('delivery_id')) {
                $query->where('delivery_id', $request->delivery_id);
            }

            $products = $query->latest()->get();

            return response()->json([
                'message' => 'Products retrieved successfully',
                'data' => $products
            ]);
        });
    }

    public function getChartData(Request $request)
    {
        $cacheKey = 'chart_data_' . $request->input('month', Carbon::now()->month) . '_' . $request->input('year', Carbon::now()->year);

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($request) {
            $query = Product::query();

            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);

            $returnCount = $query->clone()->whereHas('status', function ($q) {
                $q->where('name', 'return');
            })->count();

            $sendedCount = $query->clone()->whereHas('status', function ($q) {
                $q->where('name', 'sended');
            })->count();

            $chartData = [
                'maxY' => max($returnCount, $sendedCount) + 2, // Adding padding for better visualization
                'minY' => 0,
                'barGroups' => [
                    [
                        'x' => 0,
                        'barRods' => [
                            [
                                'y' => $returnCount,
                                'color' => '#FF0000', // Red for returned
                                'width' => 20,
                                'borderRadius' => 4
                            ]
                        ],
                        'label' => 'Returned'
                    ],
                    [
                        'x' => 1,
                        'barRods' => [
                            [
                                'y' => $sendedCount,
                                'color' => '#00FF00', // Green for sent
                                'width' => 20,
                                'borderRadius' => 4
                            ]
                        ],
                        'label' => 'Sent'
                    ]
                ]
            ];

            return response()->json([
                'message' => 'Chart data retrieved successfully',
                'data' => $chartData
            ]);
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_number' => 'required|unique:products',
            'delivery_id' => 'required|exists:deliveries,id',
            'status_product_id' => 'required|exists:status_products,id',
            'employee_id' => 'required|exists:employees,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'unboxing_image' => 'nullable|image|max:2048'
        ], [
            'receipt_number.required' => 'Nomor resi harus diisi',
            'receipt_number.unique' => 'Nomor resi sudah digunakan',
            'delivery_id.required' => 'Kurir harus dipilih',
            'delivery_id.exists' => 'Kurir tidak ditemukan',
            'status_product_id.required' => 'Status harus dipilih',
            'status_product_id.exists' => 'Status tidak ditemukan',
            'description.string' => 'Deskripsi harus berupa teks',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB',
            'unboxing_image.image' => 'File unboxing harus berupa gambar',
            'unboxing_image.max' => 'Ukuran gambar unboxing tidak boleh lebih dari 2MB',
            'employee_id.required' => 'Pegawai harus dipilih',
            'employee_id.exists' => 'Pegawai tidak ditemukan'
        ]);

        $validated['created_by'] = auth()->id();

        $product = DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
            }

            if ($request->hasFile('unboxing_image')) {
                $path = $request->file('unboxing_image')->store('products/unboxing', 'public');
                $validated['unboxing_image'] = $path;
            }

            return Product::create($validated);
        });

        return response()->json([
            'message' => 'Berhasil menyimpan paket',
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'receipt_number' => 'required|unique:products,receipt_number,' . $product->id,
            'delivery_id' => 'required|exists:deliveries,id',
            'status_product_id' => 'required|exists:status_products,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'unboxing_image' => 'nullable|image|max:2048',
            'employee_id' =>'required|exists:employees,id'
        ], [
            'receipt_number.required' => 'Nomor resi harus diisi',
            'receipt_number.unique' => 'Nomor resi sudah digunakan',
            'delivery_id.required' => 'Kurir harus dipilih',
            'delivery_id.exists' => 'Kurir tidak ditemukan',
            'status_product_id.required' => 'Status harus dipilih',
            'status_product_id.exists' => 'Status tidak ditemukan',
            'description.string' => 'Deskripsi harus berupa teks',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB',
            'unboxing_image.image' => 'File unboxing harus berupa gambar',
            'unboxing_image.max' => 'Ukuran gambar unboxing tidak boleh lebih dari 2MB',
            'employee_id.required' => 'Pegawai harus dipilih',
            'employee_id.exists' => 'Pegawai tidak ditemukan'
        ]);

        DB::transaction(function () use ($request, $validated, $product) {
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
            }

            if ($request->hasFile('unboxing_image')) {
                if ($product->unboxing_image) {
                    Storage::disk('public')->delete($product->unboxing_image);
                }
                $path = $request->file('unboxing_image')->store('products/unboxing', 'public');
                $validated['unboxing_image'] = $path;
            }

            $product->update($validated);
        });

        return response()->json([
            'message' => 'Berhasil mengubah paket',
        ]);
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            if ($product->unboxing_image) {
                Storage::disk('public')->delete($product->unboxing_image);
            }

            $product->delete();
        });

        return response()->json([
            'message' => 'Berhasil menghapus paket'
        ]);
    }
}
