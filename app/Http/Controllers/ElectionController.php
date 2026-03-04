<?php

namespace App\Http\Controllers;



use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controller;

class ElectionController extends Controller
{


    public function __construct()
    {
        // View permissions
        $this->middleware('permission:elections.view')->only([
            'index',
            'show',
       
        ]);

        // Create permission
        $this->middleware('permission:elections.add')->only([
            'store'
        ]);

        // Edit permission
        $this->middleware('permission:elections.edit')->only([
            'update'
        ]);

        // Delete permission
        $this->middleware('permission:elections.delete')->only([
            'destroy'
        ]);
    }

    public function index()
    {
        $elections = Election::latest()->get();
        return view('elections.index', compact('elections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:50',
            'number' => 'required|string|max:10',
            'year' => 'required|digits:4',
        ]);

        Election::create([
            'type' => $request->type,
            'number' => $request->number,
            'year' => $request->year,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('elections.index')
            ->with('success', 'Election created successfully.');
    }

    public function update(Request $request, Election $election)
    {
        $request->validate([
            'type' => 'required|string|max:50',
            'number' => 'required|string|max:10',
            'year' => 'required|digits:4',
        ]);

        $election->update($request->only('type', 'number', 'year'));

        return back()->with('success', 'Election updated.');
    }

    public function destroy(Election $election)
    {
        $election->delete();
        return back()->with('success', 'Election deleted.');
    }

    public function show(Election $election)
    {
        return view('elections.show', compact('election'));
    }



    public function data()
    {
        $query = Election::all();

        return DataTables::of($query)

            ->addColumn('label', function ($election) {
                return "{$election->type}-{$election->number} ({$election->year})";
            })

         

            ->addColumn('actions', function ($election) {
                $showUrl = route('elections.show', $election);
                $deleteUrl = route('elections.destroy', $election);

                return '
    <div class="d-flex gap-1">
        <a href="' . $showUrl . '" class="btn btn-sm btn-info">View</a>

        <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
            ' . csrf_field() . '
            ' . method_field("DELETE") . '
            <button type="submit"
                class="btn btn-sm btn-danger"
                onclick="return confirm(\'Delete?\')">
                Delete
            </button>
        </form>
    </div>
';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }
}