<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CryLogController extends Controller
{
    /**
     * GET /api/cry-logs
     * Filter opsional: device_id, cry_type, is_crying, from, to, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from'     => ['nullable', 'date'],
            'to'       => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = CryLog::query()
            ->orderByDesc('recorded_at')
            ->orderByDesc('id');

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->query('device_id'));
        }
        if ($request->filled('cry_type')) {
            $query->where('cry_type', $request->query('cry_type'));
        }
        if ($request->has('is_crying')) {
            $query->where('is_crying', $request->boolean('is_crying'));
        }
        if ($request->filled('from')) {
            $query->where('recorded_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->where('recorded_at', '<=', $request->query('to'));
        }

        $logs = $query->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Daftar data tangisan bayi',
            'data'    => $logs->items(),
            'meta'    => [
                'current_page' => $logs->currentPage(),
                'per_page'     => $logs->perPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }

    /** GET /api/cry-logs/{id} */
    public function show(CryLog $cryLog): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail data tangisan bayi',
            'data'    => $cryLog,
        ]);
    }

    /** POST /api/cry-logs  (dipanggil oleh perangkat IoT) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $data['recorded_at'] = $data['recorded_at'] ?? now();

        $log = CryLog::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data'    => $log->fresh(),
        ], 201);
    }

    /** PUT/PATCH /api/cry-logs/{id} */
    public function update(Request $request, CryLog $cryLog): JsonResponse
    {
        $data = $request->validate($this->rules(partial: true));

        if (array_key_exists('recorded_at', $data) && $data['recorded_at'] === null) {
            unset($data['recorded_at']);
        }

        $cryLog->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data'    => $cryLog->fresh(),
        ]);
    }

    /** DELETE /api/cry-logs/{id} */
    public function destroy(CryLog $cryLog): JsonResponse
    {
        $cryLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    /** GET /api/cry-logs/latest?device_id=BABYCRY-001 */
    public function latest(Request $request): JsonResponse
    {
        $query = CryLog::query();

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->query('device_id'));
        }

        $log = $query->orderByDesc('recorded_at')->orderByDesc('id')->first();

        if (! $log) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data tangisan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data tangisan terbaru',
            'data'    => $log,
        ]);
    }

    /** GET /api/cry-logs/summary?device_id=BABYCRY-001  (ringkasan 24 jam terakhir) */
    public function summary(Request $request): JsonResponse
    {
        $query = CryLog::query()
            ->where('is_crying', true)
            ->where('recorded_at', '>=', now()->subHours(24));

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->query('device_id'));
        }

        $byType = (clone $query)
            ->selectRaw('cry_type, COUNT(*) as total')
            ->groupBy('cry_type')
            ->pluck('total', 'cry_type');

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan tangisan 24 jam terakhir',
            'data'    => [
                'total_cries'        => (clone $query)->count(),
                'by_type'            => $byType,
                'average_confidence' => round((float) (clone $query)->avg('confidence'), 2),
                'last_cry_at'        => (clone $query)->max('recorded_at'),
            ],
        ]);
    }

    /** Aturan validasi. Untuk update (PUT/PATCH) semua field dibuat opsional. */
    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'device_id'        => [$required, 'string', 'max:50'],
            'is_crying'        => ['sometimes', 'boolean'],
            'cry_type'         => ['sometimes', Rule::in(CryLog::CRY_TYPES)],
            'confidence'       => ['nullable', 'numeric', 'between:0,100'],
            'sound_level'      => ['nullable', 'numeric', 'between:0,200'],
            'temperature'      => ['nullable', 'numeric', 'between:-40,100'],
            'humidity'         => ['nullable', 'numeric', 'between:0,100'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'recorded_at'      => ['nullable', 'date'],
        ];
    }
}
