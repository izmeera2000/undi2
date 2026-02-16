<?php

namespace App\Http\Controllers;

use App\Models\Dun;
use Illuminate\Http\Request;
use App\Models\Parlimen;
use Illuminate\Routing\Controller;

class DunController extends Controller
{
    public function __construct()
    {
        // View permissions
        $this->middleware('permission:dun.view')->only([
            'index',
            'show',
            'getList'
        ]);

        // Create permission
        $this->middleware('permission:dun.add')->only([
            'create',
            'store'
        ]);

        // Edit permission
        $this->middleware('permission:dun.edit')->only([
            'edit',
            'update'
        ]);

        // Delete permission
        $this->middleware('permission:dun.delete')->only([
            'destroy'
        ]);
    }

    // List all
    public function index()
    {
        $duns = Dun::orderBy('namadun')->get();
        return view('dun.index', compact('duns'));
    }

    // Show create form
    public function create()
    {
        return view('dun.create');
    }

    // Store new
    public function store(Request $request)
    {
        $request->validate([
            'kod_dun' => 'required|unique:dun,kod_dun',
            'namadun' => 'required',
            'status' => 'required|string', // Add validation for status
            'effective_from' => 'nullable|date', // Add validation for effective_from
            'effective_to' => 'nullable|date|after_or_equal:effective_from', // Ensure effective_to is after effective_from
        ]);

        // Create Dun record
        $dun = Dun::create($request->all());

        // Log activity on creation
        activity()->performedOn($dun)->log("Dun with ID {$dun->id} created.");

        return redirect()->route('dun.index')->with('success', 'Dun added.');
    }

    // Show edit form
    public function edit(Dun $dun)
    {
        $parlimens = Parlimen::all();
        return view('dun.edit', compact('dun', 'parlimens'));
    }

    // Update
    public function update(Request $request, Dun $dun)
    {
        $request->validate([
            'kod_dun' => 'required|unique:dun,kod_dun,' . $dun->id, // ignore current DUN
            'namadun' => 'required',
            'parlimen_id' => 'required|exists:parlimen,id', // ensure selected Parlimen exists
            'status' => 'required|string', // Add validation for status
            'effective_from' => 'nullable|date', // Add validation for effective_from
            'effective_to' => 'nullable|date|after_or_equal:effective_from', // Ensure effective_to is after effective_from
        ]);

        // Update Dun record
        $dun->update($request->only('kod_dun', 'namadun', 'parlimen_id', 'status', 'effective_from', 'effective_to'));

        // Log activity on update
        activity()->performedOn($dun)->log("Dun with ID {$dun->id} updated.");

        return redirect()->route('dun.index')->with('success', 'DUN updated successfully.');
    }

    // Delete
    public function destroy(Dun $dun)
    {
        $dun->delete();

        // Log activity on deletion
        activity()->performedOn($dun)->log("Dun with ID {$dun->id} deleted.");

        return redirect()->route('dun.index')->with('success', 'Dun deleted.');
    }

    // Optional: show single
    public function show(Dun $dun)
    {
        return view('dun.show', compact('dun'));
    }

    public function getList(Request $request)
    {
        $query = Dun::with('parlimen'); // eager load parlimen

        return datatables($query)
            ->addColumn('parlimen_name', function ($row) {
                return $row->parlimen ? $row->parlimen->namapar : '-';
            })
            ->addColumn('dun_name', function ($row) {
                // Ensure that the route is generated properly
                return '<a href="' . route('dun.show', ['dun' => $row->id]) . '">' . $row->namadun . '</a>';
            })
            ->addColumn('status', function ($row) {
                return $row->status; // Display status
            })
            ->addColumn('effective_from', function ($row) {
                return $row->effective_from ? $row->effective_from->format('Y-m-d') : '-';
            })
            ->addColumn('effective_to', function ($row) {
                return $row->effective_to ? $row->effective_to->format('Y-m-d') : '-';
            })
            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('dun.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-dun">Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['dun_name', 'status', 'effective_from', 'effective_to', 'actions'])
            ->make(true);
    }
}
