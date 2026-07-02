<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourtController extends Controller
{
    public function index()
    {
        $courts = Court::query()
            ->withExists(['movements', 'dispatchBatches'])
            ->orderBy('id')
            ->get();
        return view('admin.courts.index', compact('courts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:courts,code',
            'is_active' => 'nullable|boolean',
        ]);

        $court = Court::create([
            'name_en' => $validated['name_en'],
            'name_bn' => $validated['name_bn'] ?? null,
            'code' => strtoupper(trim($validated['code'])),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return response()->json([
            'success' => true,
            'court' => $court,
            'message' => 'Court created successfully.',
        ]);
    }

    public function edit(Court $court)
    {
        return response()->json($court);
    }

    public function update(Request $request, Court $court)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('courts', 'code')->ignore($court->id)],
            'is_active' => 'nullable|boolean',
        ]);

        $hasHistory = $court->movements()->exists() || $court->dispatchBatches()->exists();
        $newCode = strtoupper(trim($validated['code']));

        if ($hasHistory && $newCode !== $court->code) {
            return response()->json([
                'success' => false,
                'message' => 'Court code cannot be changed after the court has movement or dispatch history.',
            ], 422);
        }

        $court->update([
            'name_en' => $validated['name_en'],
            'name_bn' => $validated['name_bn'] ?? null,
            'code' => $newCode,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return response()->json([
            'success' => true,
            'court' => $court,
            'message' => 'Court updated successfully.',
        ]);
    }

    public function destroy(Court $court)
    {
        $hasHistory = $court->movements()->exists() || $court->dispatchBatches()->exists();
        if ($hasHistory) {
            return response()->json([
                'success' => false,
                'message' => 'This court has movement history and cannot be deleted.',
            ], 422);
        }

        $court->delete();

        return response()->json([
            'success' => true,
            'message' => 'Court deleted successfully.',
        ]);
    }
}
