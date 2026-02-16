<?php

namespace App\Http\Controllers;

use App\Models\Lokaliti;
use App\Models\Dm;
use App\Models\Dun;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

    // Show create form
    public function create()
    {
        $dms = Dm::all();
        return view('lokaliti.create', compact('dms'));
    }

    // Store new Lokaliti
    public function store(Request $request)
    {
        $request->validate([
            'dm_id' => 'required|exists:dm,id',
            'kod_lokaliti' => 'required|unique:lokaliti,kod_lokaliti',
            'nama_lokaliti' => 'required|string|max:255',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        Lokaliti::create($request->only('dm_id', 'kod_lokaliti', 'nama_lokaliti', 'effective_from', 'effective_to'));

        return redirect()->route('lokaliti.index')->with('success', 'Lokaliti added successfully.');
    }

    // Show edit form
    public function edit(Lokaliti $lokaliti)
    {
        $dms = Dm::all();
        return view('lokaliti.edit', compact('lokaliti', 'dms'));
    }

    // Update Lokaliti
    public function update(Request $request, Lokaliti $lokaliti)
    {
        $request->validate([
            'dm_id' => 'required|exists:dm,id',
            'kod_lokaliti' => 'required|unique:lokaliti,kod_lokaliti,' . $lokaliti->id,
            'nama_lokaliti' => 'required|string|max:255',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $lokaliti->update($request->only('dm_id', 'kod_lokaliti', 'nama_lokaliti', 'effective_from', 'effective_to'));

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
        return view('lokaliti.show', compact('lokaliti'));
    }

    // Server-side DataTables
    public function getList(Request $request)
    {
        $query = Lokaliti::with('dm');

        return datatables($query)
            ->addColumn('kod_lokaliti', fn($row) => $row->kod_lokaliti)
            ->addColumn('nama_lokaliti', fn($row) => '<a href="' . route('lokaliti.show', $row->id) . '">' . $row->nama_lokaliti . '</a>')
            ->addColumn('dm_name', fn($row) => $row->dm?->namadm ?? '-')
            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('lokaliti.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-lokaliti">Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['nama_lokaliti', 'actions'])
            ->make(true);
    }
}
