<?php

namespace App\Http\Controllers;

use App\Models\Dm;
use App\Models\Dun;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

    // Show create form
    public function create()
    {
        $duns = Dun::all(); // for dropdown
        return view('dm.create', compact('duns'));
    }

    // Store new DM
    public function store(Request $request)
    {
        $request->validate([
            'koddm' => 'required',
            'namadm' => 'required',
            'dun_id' => 'required|exists:dun,id', // ensure selected DUN exists
            'effective_from' => 'nullable|date', // Add validation for effective_from
            'effective_to' => 'nullable|date', // Ensure effective_to is after effective_from
        ]);

        // Create DM record
        Dm::create($request->all());

        return redirect()->route('dm.index')->with('success', 'DM added successfully.');
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
        $request->validate([
            'koddm' => 'required|unique:dm,koddm,' . $dm->id, // ignore current DM
            'namadm' => 'required',
            'dun_id' => 'required|exists:dun,id', // ensure selected DUN exists
            'effective_from' => 'nullable|date', // Add validation for effective_from
            'effective_to' => 'nullable|date|after_or_equal:effective_from', // Ensure effective_to is after effective_from
        ]);

        // Update DM record
        $dm->update($request->only('koddm', 'namadm', 'dun_id', 'effective_from', 'effective_to'));

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
        $query = Dm::with('dun'); // Eager load dun relationship for each DM

        return datatables($query)
            ->addColumn('koddm', function ($row) {
                return $row->koddm;
            })
            ->addColumn('namadm', function ($row) {
                // Link to DM show page
                return '<a href="' . route('dm.show', ['dm' => $row->id]) . '">' . $row->namadm . '</a>';
            })
            ->addColumn('dun_name', function ($row) {
                // Show related DUN name
                return $row->dun ? $row->dun->namadun : '-';
            })
            ->addColumn('effective_from', function ($row) {
                return $row->effective_from ? $row->effective_from->format('Y-m-d') : '-';
            })
            ->addColumn('effective_to', function ($row) {
                return $row->effective_to ? $row->effective_to->format('Y-m-d') : '-';
            })
            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('dm.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-dm">Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['namadm', 'actions']) // namadm has HTML link, actions have buttons
            ->make(true);
    }
}
