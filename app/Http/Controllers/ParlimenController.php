<?php

namespace App\Http\Controllers;

use App\Models\Parlimen;
use Illuminate\Http\Request;

class ParlimenController extends Controller
{
    // List all
    public function index()
    {
        $parlimens = Parlimen::orderBy('namapar')->get();
        return view('parlimen.index', compact('parlimens'));
    }

    // Show create form
    public function create()
    {
        return view('parlimen.create');
    }

    // Store new
    public function store(Request $request)
    {
        $request->validate([
            'kod_par' => 'required|unique:parlimen,kod_par',
            'namapar' => 'required',
        ]);

        Parlimen::create($request->all());

        return redirect()->route('parlimen.index')->with('success', 'Parlimen added.');
    }

    // Show edit form
    public function edit(Parlimen $parlimen)
    {
        return view('parlimen.edit', compact('parlimen'));
    }

    // Update
    public function update(Request $request, Parlimen $parlimen)
    {
        $request->validate([
            'kod_par' => 'required|unique:parlimen,kod_par,' . $parlimen->id,
            'namapar' => 'required',
        ]);

        $parlimen->update($request->all());

        return redirect()->route('parlimen.index')->with('success', 'Parlimen updated.');
    }

    // Delete
    public function destroy(Parlimen $parlimen)
    {
        $parlimen->delete();

        return redirect()->route('parlimen.index')->with('success', 'Parlimen deleted.');
    }

    // Optional: show single
    public function show(Parlimen $parlimen)
    {
        return view('parlimen.show', compact('parlimen'));
    }



    public function getList(Request $request)
    {
        $query = Parlimen::query();
        return datatables($query)
            ->addColumn('name', function ($row) {
                // Ensure that the route is generated properly
                return '<a href="' . route('parlimen.show', ['parlimen' => $row->id]) . '">' . $row->namapar . '</a>';
            })
            ->addColumn('actions', function ($row) {
                // Add 'Edit' and 'Delete' buttons
                $edit = '<a href="' . route('parlimen.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                $delete = '<button data-id="' . $row->id . '" class="btn btn-sm btn-danger delete-parlimen">Delete</button>';
                return $edit . ' ' . $delete;
            })
            ->rawColumns(['name', 'actions'])  // Mark 'name' and 'actions' as raw HTML
            ->make(true);
    }


}
