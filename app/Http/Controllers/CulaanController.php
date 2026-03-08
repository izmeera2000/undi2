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
                <div class="d-flex justify-content-end gap-2">
                    <a href="' . route('culaan.show', $row->id) . '" 
                    class="btn btn-sm btn-outline-primary action-btn"   
                    data-bs-toggle="tooltip" 
                    title="View Details">
                        <i class="fas fa-cog me-1"></i> Manage
                    </a>

                    <button class="btn btn-sm btn-outline-danger action-btn  delete-culaan"
                            data-id="' . $row->id . '"
                            data-bs-toggle="tooltip"
                            title="Delete Record">
                        <i class="fas fa-trash"></i> Delete
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
            ->editColumn('jantina', function ($row) {

                // Mapping codes to labels
                $jantinaAll = [
                    'L' => 'Lelaki',
                    'P' => 'Perempuan',
                ];

                $code = strtoupper($row->jantina);

                // Return label or original code if not found
                return $jantinaAll[$code] ?? $code;
            })



            ->editColumn('bangsa', function ($row) {

                // Mapping codes to labels
                $bangsaall = [
                    'M' => 'Melayu',
                    'C' => 'Cina',
                    'I' => 'India',
                    'L' => 'Lain-lain',
                ];

                $code = strtoupper($row->bangsa);

                // Return label or original code if not found
                return $bangsaall[$code] ?? $code;
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


public function analytics(Request $request, Culaan $culaan)
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

    $base = clone $query;

    // TOTAL
    $total = $base->count();

    // STATUS CULAAN
    $status = (clone $query)
        ->selectRaw("LEFT(status_culaan,1) as status, COUNT(*) as total")
        ->groupBy('status')
        ->pluck('total','status');

    // SALURAN
    $saluran = (clone $query)
        ->selectRaw("saluran, COUNT(*) as total")
        ->groupBy('saluran')
        ->orderBy('saluran')
        ->get();

    // JANTINA
    $jantina = (clone $query)
        ->selectRaw("jantina, COUNT(*) as total")
        ->groupBy('jantina')
        ->pluck('total','jantina');

    // BANGSA
    $bangsa = (clone $query)
        ->selectRaw("bangsa, COUNT(*) as total")
        ->groupBy('bangsa')
        ->pluck('total','bangsa');

    // UMUR GROUP
    $umur = (clone $query)
        ->selectRaw("
            CASE
                WHEN umur < 30 THEN '18-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END as umur_group,
            COUNT(*) as total
        ")
        ->groupBy('umur_group')
        ->pluck('total','umur_group');

    // TOP LOKALITI
    $lokaliti = (clone $query)
        ->selectRaw("lokaliti, COUNT(*) as total")
        ->groupBy('lokaliti')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    // TOP PM
    $pm = (clone $query)
        ->selectRaw("pm, COUNT(*) as total")
        ->groupBy('pm')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    return response()->json([

        'total' => $total,

        'status_chart' => [
            'labels' => $status->keys(),
            'series' => $status->values(),
        ],

        'saluran_chart' => [
            'labels' => $saluran->pluck('saluran'),
            'series' => $saluran->pluck('total'),
        ],

        'jantina_chart' => [
            'labels' => $jantina->keys(),
            'series' => $jantina->values(),
        ],

        'bangsa_chart' => [
            'labels' => $bangsa->keys(),
            'series' => $bangsa->values(),
        ],

        'umur_chart' => [
            'labels' => $umur->keys(),
            'series' => $umur->values(),
        ],

        'lokaliti_chart' => [
            'labels' => $lokaliti->pluck('lokaliti'),
            'series' => $lokaliti->pluck('total'),
        ],

        'pm_chart' => [
            'labels' => $pm->pluck('pm'),
            'series' => $pm->pluck('total'),
        ],

    ]);
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