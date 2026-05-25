<?php

namespace App\Http\Controllers\Api; // Pastiin udah di dalem folder Api

use App\Http\Controllers\Controller;
use App\Models\SustainabilityData; // Pastiin nama model lo sesuai
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SustainabilityDataController extends Controller
{
    /**
     * Display a listing of the resource (Dashboard Feed + Stats Summary).
     */
    public function index()
    {
        // 1. Ambil semua data buat "Real Time Sensor Feed" (Tabel Bawah)
        // Kita ambil data terbaru di posisi paling atas
        $sensorFeed = SustainabilityData::orderBy('created_at', 'desc')->get();

        // 2. Kritis! Kita hitung Aggregat (Summary) di Backend biar meringankan beban Front-End
        $totalEnergy  = SustainabilityData::where('category', 'energy')->where('status', 'approved')->sum('value');
        $totalWater   = SustainabilityData::where('category', 'water')->where('status', 'approved')->sum('value');
        $totalWaste   = SustainabilityData::where('category', 'waste')->where('status', 'approved')->sum('value');
        $totalEmission = SustainabilityData::where('category', 'emissions')->where('status', 'approved')->sum('value');

        // 3. Gabungkan semua jatah data dalam satu response JSON dewa
        return response()->json([
            'success' => true,
            'message' => 'Data Dashboard Keberlanjutan KST Ngijo berhasil dirangkum.',
            'dashboard_summary' => [
                'total_renewable_energy' => [
                    'value' => (double) $totalEnergy,
                    'unit'  => 'MWh'
                ],
                'water_recycling_rate' => [
                    'value' => (double) $totalWater,
                    'unit'  => 'm³'
                ],
                'waste_processed' => [
                    'value' => (double) $totalWaste,
                    'unit'  => 'ton'
                ],
                'carbon_offset' => [
                    'value' => (double) $totalEmission,
                    'unit'  => 'kg CO2e'
                ]
            ],
            'real_time_sensor_feed' => $sensorFeed
        ], 200);
    }

    /**
     * Store a newly created resource in storage (Input Data Baru / Simulasi IoT).
     */
    public function store(Request $request)
    {
        // Validasi ketat sesuai skema enum dan decimal punya kelompok lo
        $validator = Validator::make($request->all(), [
            'record_date'   => 'required|date',
            'category'      => 'required|in:energy,water,waste,emissions,social',
            'metric_name'   => 'required|string|max:255',
            'value'         => 'required|numeric',
            'unit'          => 'required|string|max:50',
            'target_value'  => 'nullable|numeric',
            'notes'         => 'nullable|string',
            'status'        => 'in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi IoT gagal, cek struktur payload lo.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Simpan ke Postgres
        $data = SustainabilityData::create([
            'record_date'   => $request->record_date,
            'category'      => $request->category,
            'metric_name'   => $request->metric_name,
            'value'         => $request->value,
            'unit'          => $request->unit,
            'target_value'  => $request->target_value,
            'notes'         => $request->notes,
            'created_by_user_id' => auth()->id(), // Otomatis rekam ID user yang lagi login
            'status'        => $request->input('status', 'pending'), // Default pending kalau gak diisi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log data keberlanjutan baru berhasil direkam.',
            'data'    => $data
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = SustainabilityData::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data log tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $data
        ], 200);
    }

    /**
     * Update the specified resource in storage (Ubah Status Approval / Edit Data).
     */
    public function update(Request $request, $id)
    {
        $data = SustainabilityData::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data log gak ada, Va.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'record_date'   => 'sometimes|required|date',
            'category'      => 'sometimes|required|in:energy,water,waste,emissions,social',
            'metric_name'   => 'sometimes|required|string|max:255',
            'value'         => 'sometimes|required|numeric',
            'unit'          => 'sometimes|required|max:50',
            'status'        => 'sometimes|required|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // Kalau status diubah jadi approved/rejected, catat siapa admin yang nge-approve
        if ($request->has('status')) {
            $data->approved_by_user_id = auth()->id();
            $data->synced_at = now();
        }

        $data->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data log keberlanjutan berhasil diperbarui.',
            'data'    => $data
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = SustainabilityData::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data emang kagak ada dari awal.'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log data keberlanjutan berhasil dihapus.'
        ], 200);
    }
}