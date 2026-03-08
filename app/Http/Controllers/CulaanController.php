<?php

namespace App\Http\Controllers;

use App\Models\Culaan;
use App\Models\CulaanPengundi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CulaanController extends Controller
{

    public function index()
    {
        return view('culaan.index');
    }

    public function data(Request $request)
    {
        $query = Culaan::with(['election', 'creator']);

        return DataTables::of($query)

            ->addColumn('date', function ($row) {
                return $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '-';
            })

            ->addColumn('election', function ($row) {
                if (!$row->election) {
                    return '-';
                }

                return $row->election->type . ' ' .
                    $row->election->number . ' (' .
                    $row->election->year . ')';
            })

            ->addColumn('creator', function ($row) {
                return $row->creator?->name ?? '-';
            })

            ->addColumn('actions', function ($row) {
                return '
                <div class="d-flex justify-content-center gap-2">
                    <a href="' . route('culaan.show', $row->id) . '" 
                    class="btn  btn-icon btn-primary"   
                    data-bs-toggle="tooltip" 
                    title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>

                    <button class="btn  btn-icon btn-danger delete-culaan"
                            data-id="' . $row->id . '"
                            data-bs-toggle="tooltip"
                            title="Delete Record">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>';
            })

            ->rawColumns(['actions'])

            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'election_id' => 'nullable|exists:elections,id',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Culaan::create([
            'election_id' => $request->election_id ?: null,
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'created_by' => auth()->id()
        ]);

        // Redirect back to the previous page with a success message
        return redirect()->back()->with('success', 'Culaan created successfully!');
    }

public function destroy(Culaan $culaan)
{
    $culaan->delete();

    return response()->json(['success' => true]);
}

    public function show(Culaan $culaan)
    {
        return view('culaan.show', compact('culaan'));
    }


    public function pengundiData(Request $request, Culaan $culaan)
    {

        $query = CulaanPengundi::where('culaan_id', $culaan->id);

        if ($request->lokaliti) {
            $query->where('lokaliti', 'like', '%' . $request->lokaliti . '%');
        }

        if ($request->status_culaan) {
            $query->where('status_culaan', 'like', $request->status_culaan . '%');
        }

        if ($request->search_name) {
            $query->where(function ($q) use ($request) {

                $q->where('nama', 'like', "%{$request->search_name}%")
                    ->orWhere('no_kp', 'like', "%{$request->search_name}%");

            });
        }

        return DataTables::of($query)



            ->addColumn('pengundi_details', function ($row) {

                return '
            <div class="d-flex align-items-center gap-3">


                <div>
                    <span class="fw-semibold">' . $row->nama . '</span>
                    <div class="text-muted small">' . $row->no_kp . '</div>
                </div>

            </div>';
            })

            ->addColumn('lokaliti_details', function ($row) {

                return '
            <div class="d-flex align-items-center gap-3">


                <div>
                    <span class="fw-semibold">' . $row->pm . '</span>
                    <div class="text-muted small">' . $row->lokaliti . ' (' . $row->kod_lokaliti . ')</div>
                </div>

            </div>';
            })



            ->addColumn('status_culaan', function ($row) {

                $statuses = [
                    'D' => ['label' => 'D', 'class' => 'success'],
                    'C' => ['label' => 'C', 'class' => 'danger'],
                    'A' => ['label' => 'A', 'class' => 'warning'],
                    'E' => ['label' => 'E', 'class' => 'secondary'],
                    'O' => ['label' => 'O', 'class' => 'primary'],
                ];

                $current = $row->status_culaan
                    ? strtoupper(substr(trim($row->status_culaan), 0, 1))
                    : 'O';


                $buttons = '<div class="btn-group btn-group-sm">';

                foreach ($statuses as $code => $status) {

                    $active = $current == $code
                        ? 'btn-' . $status['class']
                        : 'btn-outline-' . $status['class'];

                    $buttons .= '
                <button
                    class="btn ' . $active . ' change-status"
                    data-id="' . $row->id . '"
                    data-status="' . $code . '">
                    ' . $status['label'] . '
                </button>';
                }

                $buttons .= '</div>';

                return $buttons;
            })

            ->addColumn('actions', function ($row) {

                return '
            <button class="btn btn-sm btn-danger delete-pengundi"
                data-id="' . $row->id . '">
                <i class="bi bi-trash"></i>
            </button>';
            })

            ->rawColumns(['pengundi_details', 'lokaliti_details', 'status_culaan', 'actions'])

            ->make(true);
    }

    public function storePengundi(Request $request, Culaan $culaan)
    {
        $request->validate([
            'nama' => 'required',
            'no_kp' => 'nullable'
        ]);

        CulaanPengundi::create([
            'culaan_id' => $culaan->id,
            'nama' => $request->nama,
            'no_kp' => $request->no_kp,
            'lokaliti' => $request->lokaliti,
            'saluran' => $request->saluran,
            'status_culaan' => 'O',
            'updated_by' => auth()->id()
        ]);

        return response()->json(['success' => true]);
    }




    public function showBulkImport(Culaan $culaan)
    {
        return view('culaan.bulkimport', compact('culaan'));
    }


    public function updateStatus(Request $request)
    {
        CulaanPengundi::where('id', $request->id)
            ->update([
                'status_culaan' => $request->status
            ]);

        return response()->json(['success' => true]);
    }


    public function deletePengundi(Request $request)
    {
        $pengundi = CulaanPengundi::find($request->id);

        if ($pengundi) {
            $pengundi->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }
}