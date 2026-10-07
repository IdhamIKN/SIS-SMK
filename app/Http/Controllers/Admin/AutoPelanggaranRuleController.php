<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoPelanggaranRule;
use App\Services\TatibPoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoPelanggaranRuleController extends Controller
{
    public function __construct(protected TatibPoinService $tatibPoinService) {}

    public function index(): JsonResponse
    {
        $rules = AutoPelanggaranRule::with('pasal')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => $this->format($r));

        return response()->json($rules);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $rule = AutoPelanggaranRule::create(array_merge($validated, [
            'aktif'  => $request->boolean('aktif', true),
            'urutan' => AutoPelanggaranRule::max('urutan') + 1,
        ]));

        $rule->load('pasal');

        return response()->json($this->format($rule), 201);
    }

    public function update(Request $request, AutoPelanggaranRule $rule): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $rule->update(array_merge($validated, [
            'aktif' => $request->boolean('aktif', $rule->aktif),
        ]));

        $rule->load('pasal');

        return response()->json($this->format($rule));
    }

    public function destroy(AutoPelanggaranRule $rule): JsonResponse
    {
        $rule->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(AutoPelanggaranRule $rule): JsonResponse
    {
        $rule->update(['aktif' => ! $rule->aktif]);

        return response()->json(['aktif' => $rule->aktif]);
    }

    private function rules(): array
    {
        return [
            'nama_rule'      => 'required|string|max:100',
            'trigger_type'   => 'required|string|in:' . implode(',', array_keys(AutoPelanggaranRule::TRIGGER_TYPES)),
            'threshold_hari' => 'nullable|integer|min:1|max:366',
            'periode_bulan'  => 'nullable|integer|min:0|max:12',
            'pasal_id'       => 'required|string|max:20|exists:tblsubpasal,idpasal',
            'poin_override'  => 'nullable|integer|min:1|max:9999',
            'keterangan'     => 'nullable|string|max:500',
            'aktif'          => 'nullable|boolean',
        ];
    }

    private function format(AutoPelanggaranRule $r): array
    {
        return [
            'id'            => $r->id,
            'nama_rule'     => $r->nama_rule,
            'aktif'         => $r->aktif,
            'trigger_type'  => $r->trigger_type,
            'trigger_label' => $r->trigger_label,
            'threshold_hari'=> $r->threshold_hari,
            'periode_bulan' => $r->periode_bulan,
            'pasal_id'      => $r->pasal_id,
            'pasal_label'   => $r->pasal ? '[' . $r->pasal->idpasal . '] ' . $r->pasal->pasal : null,
            'poin_override' => $r->poin_override,
            'poin_efektif'  => $r->poin_efektif,
            'keterangan'    => $r->keterangan,
            'urutan'        => $r->urutan,
        ];
    }
}
