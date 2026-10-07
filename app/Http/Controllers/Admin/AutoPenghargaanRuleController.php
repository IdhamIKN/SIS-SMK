<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoPenghargaanRule;
use App\Services\TatibPoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoPenghargaanRuleController extends Controller
{
    public function __construct(protected TatibPoinService $tatibPoinService) {}

    public function index(): JsonResponse
    {
        $rules = AutoPenghargaanRule::with('pasal')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => $this->format($r));

        return response()->json($rules);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $rule = AutoPenghargaanRule::create(array_merge($validated, [
            'aktif'                    => $request->boolean('aktif', true),
            'izin_dihitung_hadir'      => $request->boolean('izin_dihitung_hadir', true),
            'sakit_dihitung_hadir'     => $request->boolean('sakit_dihitung_hadir', true),
            'terlambat_dihitung_hadir' => $request->boolean('terlambat_dihitung_hadir', false),
            'urutan'                   => AutoPenghargaanRule::max('urutan') + 1,
        ]));

        $rule->load('pasal');

        return response()->json($this->format($rule), 201);
    }

    public function update(Request $request, AutoPenghargaanRule $rule): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $rule->update(array_merge($validated, [
            'aktif'                    => $request->boolean('aktif', $rule->aktif),
            'izin_dihitung_hadir'      => $request->boolean('izin_dihitung_hadir', $rule->izin_dihitung_hadir),
            'sakit_dihitung_hadir'     => $request->boolean('sakit_dihitung_hadir', $rule->sakit_dihitung_hadir),
            'terlambat_dihitung_hadir' => $request->boolean('terlambat_dihitung_hadir', $rule->terlambat_dihitung_hadir),
        ]));

        $rule->load('pasal');

        return response()->json($this->format($rule));
    }

    public function destroy(AutoPenghargaanRule $rule): JsonResponse
    {
        $rule->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(AutoPenghargaanRule $rule): JsonResponse
    {
        $rule->update(['aktif' => ! $rule->aktif]);

        return response()->json(['aktif' => $rule->aktif]);
    }

    private function rules(): array
    {
        return [
            'nama_rule'                => 'required|string|max:100',
            'trigger_type'             => 'required|string|in:' . implode(',', array_keys(AutoPenghargaanRule::TRIGGER_TYPES)),
            'periode_bulan'            => 'nullable|integer|min:1|max:12',
            'izin_dihitung_hadir'      => 'nullable|boolean',
            'sakit_dihitung_hadir'     => 'nullable|boolean',
            'terlambat_dihitung_hadir' => 'nullable|boolean',
            'pasal_id'                 => 'required|string|max:20|exists:tblsubpasal,idpasal',
            'poin_override'            => 'nullable|integer|min:1|max:9999',
            'keterangan'               => 'nullable|string|max:500',
            'aktif'                    => 'nullable|boolean',
        ];
    }

    private function format(AutoPenghargaanRule $r): array
    {
        return [
            'id'                       => $r->id,
            'nama_rule'                => $r->nama_rule,
            'aktif'                    => $r->aktif,
            'trigger_type'             => $r->trigger_type,
            'trigger_label'            => $r->trigger_label,
            'periode_bulan'            => $r->periode_bulan,
            'izin_dihitung_hadir'      => $r->izin_dihitung_hadir,
            'sakit_dihitung_hadir'     => $r->sakit_dihitung_hadir,
            'terlambat_dihitung_hadir' => $r->terlambat_dihitung_hadir,
            'pasal_id'                 => $r->pasal_id,
            'pasal_label'              => $r->pasal ? '[' . $r->pasal->idpasal . '] ' . $r->pasal->pasal : null,
            'poin_override'            => $r->poin_override,
            'poin_efektif'             => $r->poin_efektif,
            'keterangan'               => $r->keterangan,
            'urutan'                   => $r->urutan,
        ];
    }
}
