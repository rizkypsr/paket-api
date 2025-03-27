<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('name')->get();

        return response()->json([
            'message' => 'Berhasil mengambil data karyawan',
            'data' => $employees
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
       
        ], [
            'name.required' => 'Nama karyawan harus diisi',
            'name.max' => 'Nama karyawan maksimal 255 karakter',
        ]);

        $employee = DB::transaction(function () use ($validated) {
            return Employee::create($validated);
        });

        return response()->json([
            'message' => 'Berhasil menambah karyawan',
        ], 201);
    }

    public function show(Employee $employee)
    {
        return response()->json([
            'message' => 'Berhasil mengambil data karyawan',
            'data' => $employee
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama karyawan harus diisi',
            'name.max' => 'Nama karyawan maksimal 255 karakter',
        ]);

        DB::transaction(function () use ($validated, $employee) {
            $employee->update($validated);
        });

        return response()->json([
            'message' => 'Berhasil mengubah data karyawan',
        ]);
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $employee->delete();
        });

        return response()->json([
            'message' => 'Berhasil menghapus karyawan'
        ]);
    }
}
