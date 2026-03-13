<?php

namespace App\Http\Controllers;

use App\Models\Dm;
use App\Models\Dun;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
class DmController extends Controller
{
    public function __construct()
    {
        // View permissions
        $this->middleware('permission:dm.view')->only([
            'index',
            'show',
            'getList'
        ]);

        // Create permission
        $this->middleware('permission:dm.add')->only([
            'create',
            'store'
        ]);

        // Edit permission
        $this->middleware('permission:dm.edit')->only([
            'edit',
            'update'
        ]);

        // Delete permission
        $this->middleware('permission:dm.delete')->only([
            'destroy'
        ]);
    }

    // List all DM
    public function index()
    {
        $dms = Dm::orderBy('namadm')->get();
        $duns = Dun::all();

        return view('dm.index', compact('dms', 'duns'));
    }

    // // Show create form
    // public function create()
    // {
    //     $duns = Dun::all(); // for dropdown
    //     return view('dm.create', compact('duns'));
    // }

    // Store new DM


    public function store(Request $request)
    {
        // 1️⃣ Validate input
        $request->validate([
            'kod_dun' => 'required|exists:dun,kod_dun',
            'koddm' => 'required|digits:2',
            'namadm' => 'required|string|max:255',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        DB::transaction(function () use ($request) {

            // 2️⃣ Generate full koddm
            $fullKoddm = $request->kod_dun . str_pad($request->koddm, 2, '0', STR_PAD_LEFT);

            $effectiveFrom = $request->effective_from
                ? Carbon::parse($request->effective_from)
                : now();

            // 3️⃣ Check existing active record
            $existing = Dm::where('koddm', $fullKoddm)
                ->whereNull('effective_to')
                ->latest('effective_from')
                ->first();

            // 4️⃣ Close previous record
            if ($existing) {
                $existing->update([
                    'effective_to' => $effectiveFrom->copy()->subDay()
                ]);
            }

            // 5️⃣ Insert new record
            Dm::create([
                'koddm' => $fullKoddm,
                'namadm' => $request->namadm,
                'kod_dun' => $request->kod_dun,
                'effective_from' => $effectiveFrom,
                'effective_to' => $request->effective_to,
            ]);
        });

        return redirect()
            ->route('dm.index')
            ->with('success', 'DM added successfully.');
    }

    // Show edit form
    public function edit(Dm $dm)
    {
        $duns = Dun::all(); // for dropdown
        return view('dm.edit', compact('dm', 'duns'));
    }

    // Update an existing DM
    public function update(Request $request, Dm $dm)
    {
        // 1️⃣ Validate input
        $request->validate([
            'kod_dun' => 'required|exists:dun,kod_dun',   // ensure selected DUN exists
            'koddm' => 'required|digits:2|unique:dm,koddm,' . $dm->id, // ignore current DM
            'namadm' => 'required|string|max:255',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        // 2️⃣ Generate full koddm (DUN code + 3-digit DM code)
        $fullKoddm = $request->kod_dun . str_pad($request->koddm, 2, '0', STR_PAD_LEFT);

        // 3️⃣ Update DM record
        $dm->update([
            'koddm' => $fullKoddm,
            'namadm' => $request->namadm,
            'kod_dun' => $request->kod_dun,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);

        return redirect()->route('dm.index')->with('success', 'DM updated successfully.');
    }

    // Delete a DM
    public function destroy(Dm $dm)
    {
        $dm->delete();

        return redirect()->route('dm.index')->with('success', 'DM deleted successfully.');
    }

    // Optional: Show a single DM's details
    public function show(Dm $dm)
    {
        return view('dm.show', compact('dm'));
    }

    // Get list of DM for Datatables (AJAX)
    public function getList(Request $request)
    {
        $query = Dm::with('dun')
               ->orderByDesc('effective_from')
            ->orderByDesc('effective_to');

        return datatables($query)
            ->addColumn('koddm', fn($row) => $row->koddm)
            ->filterColumn('koddm', function ($query, $keyword) {
                $query->where('koddm', 'like', "%{$keyword}%");
            })

            ->addColumn('namadm', fn($row) => '<a href="' . route('dm.show', $row->id) . '">' . $row->namadm . '</a>')
            ->filterColumn('namadm', function ($query, $keyword) {
                $query->where('namadm', 'like', "%{$keyword}%");
            })

            ->addColumn('dun_name', fn($row) => $row->dun?->namadun ?? '-') // null safe operator
            ->addColumn('effective_from', fn($row) => $row->effective_from?->format('Y-m-d') ?? '-')
            ->addColumn('effective_to', fn($row) => $row->effective_to?->format('Y-m-d') ?? '-')
            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('dm.edit', $row->id) . '" class="btn btn-sm btn-outline-primary action-btn"><i class="fas fa-cog me-1"></i> Manage</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-outline-danger delete-dm"><i class="fas fa-trash me-1"></i> Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['namadm', 'actions'])
            ->make(true);
    }




    public function bulkStore(Request $request)
    {
        $rows = $request->data;

        DB::transaction(function () use ($rows) {

            foreach ($rows as $row) {

                $kodDun = $row[0];
                $dmCode = str_pad($row[1], 2, '0', STR_PAD_LEFT);
                $fullKoddm = $kodDun . $dmCode;

                $effectiveFrom = !empty($row[3])
                    ? Carbon::parse($row[3])
                    : now();

                // Check existing active record
                $existing = Dm::where('koddm', $fullKoddm)
                    ->whereNull('effective_to')
                    ->latest('effective_from')
                    ->first();

                // Close previous record
                if ($existing) {
                    $existing->update([
                        'effective_to' => $effectiveFrom->copy()->subDay()
                    ]);
                }

                // Insert new record
                Dm::create([
                    'kod_dun' => $kodDun,
                    'koddm' => $fullKoddm,
                    'namadm' => $row[2],
                    'effective_from' => $effectiveFrom,
                    'effective_to' => $row[4] ?? null,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}
