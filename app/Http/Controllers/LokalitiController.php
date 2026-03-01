<?php

namespace App\Http\Controllers;

use App\Models\Lokaliti;
use App\Models\Dm;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class LokalitiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:lokaliti.view')->only(['index', 'show', 'getList']);
        $this->middleware('permission:lokaliti.add')->only(['create', 'store']);
        $this->middleware('permission:lokaliti.edit')->only(['edit', 'update']);
        $this->middleware('permission:lokaliti.delete')->only(['destroy']);
    }

    // List all Lokaliti
    public function index()
    {
        $dms = Dm::all(); // For dropdown in modal
        return view('lokaliti.index', compact('dms'));
    }

    // Store new Lokaliti
    public function store(Request $request)
    {
        // 1️⃣ Validate input
        $request->validate([
            'koddm' => 'required|exists:dm,koddm',
            'kod_lokaliti' => 'required|digits:3',  // ensure exactly 3 digits
            'nama_lokaliti' => 'required|string|max:255',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        // 2️⃣ Generate full kod_lokaliti
        $dm = $request->koddm;          // e.g., 0221101
        $lokalitiCode = $request->kod_lokaliti; // e.g., 001

        // Optional: Ensure 3 digits (pad if user enters less)
        $lokalitiCode = str_pad($lokalitiCode, 3, '0', STR_PAD_LEFT);

        // Combine DM + 3-digit lokaliti + '001' suffix (or any logic you need)
        $fullKodLokaliti = $dm . $lokalitiCode;

        // 3️⃣ Store in database
        Lokaliti::create([
            'koddm' => $dm,
            'kod_lokaliti' => $fullKodLokaliti,
            'nama_lokaliti' => $request->nama_lokaliti,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);

        return redirect()->route('lokaliti.index')->with('success', 'Lokaliti added successfully.');
    }

    // Show edit form
    public function edit(Lokaliti $lokaliti)
    {
        $dms = Dm::select('koddm', 'namadm', 'effective_from', 'effective_to')
            ->distinct()
            ->orderBy('effective_from', 'desc')
            ->get();
        return view('lokaliti.edit', compact('lokaliti', 'dms'));
    }

    // Update Lokaliti
    public function update(Request $request, Lokaliti $lokaliti)
    {
        // 1️⃣ Validate input
        $request->validate([
            'koddm' => 'required|exists:dm,koddm',       // Ensure selected DM exists
            'kod_lokaliti' => 'required|digits:3',       // User enters only 3-digit code
            'nama_lokaliti' => 'required|string|max:255',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        // 2️⃣ Generate full kod_lokaliti (DM code + 3-digit Lokaliti code)
        $fullKodLokaliti = $request->koddm . str_pad($request->kod_lokaliti, 3, '0', STR_PAD_LEFT);

        // 3️⃣ Update the Lokaliti record
        $lokaliti->update([
            'koddm' => $request->koddm,
            'kod_lokaliti' => $fullKodLokaliti,
            'nama_lokaliti' => $request->nama_lokaliti,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);

        return redirect()->route('lokaliti.index')->with('success', 'Lokaliti updated successfully.');
    }

    // Delete Lokaliti
    public function destroy(Lokaliti $lokaliti)
    {
        $lokaliti->delete();
        return response()->json(['message' => 'Lokaliti deleted successfully.']);
    }

    // Show single Lokaliti
    public function show(Lokaliti $lokaliti)
    {
        $from = $lokaliti->effective_from;
        $to = $lokaliti->effective_to ?? now();

        $lokaliti->load([
            'dm' => function ($query) use ($from, $to) {
                $query->where(function ($q) use ($from, $to) {
                    $q->where('effective_from', '<=', $to)
                        ->where(function ($q2) use ($from) {
                            $q2->whereNull('effective_to')
                                ->orWhere('effective_to', '>=', $from);
                        });
                });
            }
        ]);

        return view('lokaliti.show', compact('lokaliti'));
    }
    // Server-side DataTables
    public function getList(Request $request)
    {
        $query = Lokaliti::with('dm'); // Load DM relationship

        return datatables($query)
            // Direct column from table
            ->addColumn('kod_lokaliti', fn($row) => $row->kod_lokaliti)

            // HTML link column
            ->addColumn('nama_lokaliti', fn($row) => '<a href="' . route('lokaliti.show', $row->id) . '">' . $row->nama_lokaliti . '</a>')
            ->filterColumn('nama_lokaliti', function ($query, $keyword) {
                $query->where('nama_lokaliti', 'like', "%{$keyword}%");
            })

            // DM name column
            ->addColumn('dm_name', fn($row) => $row->dm?->namadm ?? '-')
            ->filterColumn('dm_name', function ($query, $keyword) {
                $query->whereHas('dm', function ($q) use ($keyword) {
                    $q->where('namadm', 'like', "%{$keyword}%"); // use actual column
                });
            })

            // Optional: search on effective_from/effective_to if needed
            ->filterColumn('effective_from', function ($query, $keyword) {
                $query->where('effective_from', 'like', "%{$keyword}%");
            })
            ->filterColumn('effective_to', function ($query, $keyword) {
                $query->where('effective_to', 'like', "%{$keyword}%");
            })

            // Actions column
            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('lokaliti.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-lokaliti">Delete</button>';
                return $edit . ' ' . $delete;
            })

            ->rawColumns(['nama_lokaliti', 'actions']) // Allow HTML
            ->make(true);
    }

    public function bulkStore(Request $request)
    {
        foreach ($request->data as $row) {

            Lokaliti::create([
                'koddm' => $row[0],
                'kod_lokaliti' => $row[0] . $row[1],
                'nama_lokaliti' => $row[2],
                'effective_from' => $row[3] ?? null,
                'effective_to' => $row[4] ?? null,
            ]);
        }

        return response()->json(['success' => true]);
    }


public function mergeDuplicates()
{
    // Step 1: Get duplicate keys as array (no Eloquent methods)
    $duplicates = Lokaliti::select('nama_lokaliti', 'kod_lokaliti')
        ->groupBy('nama_lokaliti', 'kod_lokaliti')
        ->havingRaw('COUNT(*) > 1')
        ->get()
        ->toArray(); // convert to array to avoid stdClass issues

    $mergedCount = 0;
    $deletedCount = 0;

    foreach ($duplicates as $dup) {
        // Step 2: Get all rows for this lokaliti AS ELOQUENT MODELS
        $rows = Lokaliti::where('nama_lokaliti', $dup['nama_lokaliti'])
            ->where('kod_lokaliti', $dup['kod_lokaliti'])
            ->orderBy('effective_from')
            ->get(); // returns Eloquent models

        if ($rows->count() <= 1) {
            continue; // nothing to merge
        }

        $mergedCount++;

        // Step 3: Determine merged dates
        $earliestFrom = $rows->min('effective_from');
        $latestTo = $rows->max('effective_to');

        // Step 4: Update the first model
        $keep = $rows->first(); // this is Eloquent model
        $keep->effective_from = $earliestFrom;
        $keep->effective_to = $latestTo;
        $keep->save();

        // Step 5: Delete the other duplicates
        $deletedCount += $rows->count() - 1;
        $rows->skip(1)->each(function ($row) {
            $row->delete();
        });
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Duplicate lokaliti merged successfully!',
        'merged_groups' => $mergedCount,
        'deleted_rows' => $deletedCount,
    ]);
}
}
