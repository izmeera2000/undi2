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










    // List all
    public function index()
    {
        $dms = Dm::orderBy('namadm')->get();
        $duns = Dun::all();

        return view('dm.index', compact('dms', 'duns'));
    }




    // Show create form
    public function create()
    {
        return view('dm.create');
    }

    // Store new
    public function store(Request $request)
    {
        $request->validate([
            'koddm' => 'required|unique:dm,koddm',
            'namadm' => 'required',
        ]);

        Dm::create($request->all());

        return redirect()->route('dm.index')->with('success', 'Dm added.');
    }

    // Show edit form
    public function edit(Dm $dm)
    {
        $duns = Dun::all(); // for dropdown
        return view('dm.edit', compact('dm', 'duns'));
    }

    // Update
    public function update(Request $request, Dm $dm)
    {
        $request->validate([
            'koddm' => 'required|unique:dm,koddm,' . $dm->id, // ignore current DM
            'namadm' => 'required',
            'dun_id' => 'required|exists:dun,id', // ensure selected DUN exists
        ]);

        $dm->update($request->only('koddm', 'namadm', 'dun_id'));

        return redirect()->route('dm.index')->with('success', 'DM updated successfully.');
    }

    // Delete
    public function destroy(Dm $dm)
    {
        $dm->delete();

        return redirect()->route('dm.index')->with('success', 'Dm deleted.');
    }

    // Optional: show single
    public function show(Dm $dm)
    {
        return view('dm.show', compact('dm'));
    }



    public function getList(Request $request)
    {
        // Eager load dun relationship for each DM
        $query = Dm::with('dun');

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
            ->addColumn('actions', function ($row) {
                $edit = '<a href="' . route('dm.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-dm">Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['namadm', 'actions']) // namadm has HTML link, actions have buttons
            ->make(true);
    }



}
