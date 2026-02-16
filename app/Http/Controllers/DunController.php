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
        ]);

        Dun::create($request->all());

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
        ]);

        $dun->update($request->only('kod_dun', 'namadun', 'parlimen_id'));

        return redirect()->route('dun.index')->with('success', 'DUN updated successfully.');
    }


    // Delete
    public function destroy(Dun $dun)
    {
        $dun->delete();

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


            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('dun.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-dun">Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['dun_name', 'actions'])
            ->make(true);
    }

}
