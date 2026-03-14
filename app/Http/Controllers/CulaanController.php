<?php

namespace App\Http\Controllers;

use App\Models\Culaan;
use App\Models\CulaanPengundi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use App\Jobs\GenerateCulaanBatchJob;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

use Devrabiul\ToastMagic\Facades\ToastMagic;

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

        $query = CulaanPengundi::where('culaan_id', $culaan->id)
            ->orderBy('id', 'asc');


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


            ->addColumn('pengundi_details2', function ($row) {

                return '
            <div class="d-flex align-items-center gap-3">


                <div>
                    <span class="fw-semibold">' . $row->kategori_pengundi . '</span>
                    <div class="text-muted small">' . $row->stastus_pengundi . '</div>
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
                    'D' => ['label' => 'BN', 'color' => '#0033A0'],
                    'A' => ['label' => 'PH', 'color' => '#E31C23'],
                    'C' => ['label' => 'PAS', 'color' => '#009B3A'],
                    'E' => ['label' => 'TP', 'color' => '#800080'],
                    'O' => ['label' => 'BC', 'color' => '#999999'],
                ];

                $current = $row->status_culaan
                    ? strtoupper(substr(trim($row->status_culaan), 0, 1))
                    : 'O';

                $buttons = '<div class="btn-group btn-group-sm">';

                foreach ($statuses as $code => $status) {
                    if ($current == $code) {
                        // Active: solid color
                        $btnStyle = "background-color: {$status['color']}; color: white; border: none;";
                    } else {
                        // Inactive: lighter outline style
                        $btnStyle = "background-color: white; color: {$status['color']}; border: 1px solid {$status['color']};";
                    }

                    $buttons .= '
            <button
                class="btn btn-sm change-status"
                style="' . $btnStyle . '"
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


            ->rawColumns(['pengundi_details', 'pengundi_details2', 'lokaliti_details', 'status_culaan', 'actions'])

            ->make(true);
    }



    public function analytics(Request $request, Culaan $culaan)
    {
        return view('culaan.analytics', compact('culaan'));
    }


    public function analytics_data(Request $request, Culaan $culaan)
    {
        $query = CulaanPengundi::where('culaan_id', $culaan->id);

        if ($request->lokaliti) {
            $query->where('lokaliti', 'like', '%' . $request->lokaliti . '%');
        }

        if ($request->status_culaan) {
            if ($request->status_culaan == 'O') {
                $query->where(function ($q) {
                    $q->whereNull('status_culaan')
                        ->orWhere('status_culaan', 'O');
                });
            } else {
                $query->where('status_culaan', 'like', $request->status_culaan . '%');
            }
        }

        // if ($request->search_name) {
        //     $query->where(function ($q) use ($request) {
        //         $q->where('nama', 'like', "%{$request->search_name}%")
        //             ->orWhere('no_kp', 'like', "%{$request->search_name}%");
        //     });
        // }

        $colorsLabel = [
            'BN' => '#0033A0',
            'PH' => '#E31C23',
            'PAS' => '#009B3A',
            'Tidak Pasti' => '#800080',
            'Belum Cula' => '#999999'
        ];

        $base = clone $query;

        // TOTAL
        $total = $base->count();

        // STATUS CULAAN
        $status = (clone $query)
            ->selectRaw("
        CASE 
            WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'D' THEN 'BN'
            WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'A' THEN 'PH'
            WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'C' THEN 'PAS'
            WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'E' THEN 'Tidak Pasti'
            WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'O' THEN 'Belum Cula'
        END as status,
        COUNT(*) as total
    ")
            ->groupByRaw("status")
            ->orderByRaw("FIELD(status, 'BN', 'PH', 'PAS', 'Tidak Pasti', 'Belum Cula')")
            ->get()
            ->pluck('total', 'status');

        $statusData = $status->map(function ($total, $status) {
            return [
                'status' => $status,
                'total' => $total
            ];
        });

        // Labels and series for chart
        $labels = $statusData->pluck('status')->toArray();
        $series = $statusData->pluck('total')->toArray();

        // Map colors dynamically based on labels

        $colors = collect($labels)->map(fn($label) => $colorsLabel[$label] ?? '#000')->toArray();

        // Build chart payload
        $statusChart = [
            'labels' => $labels,
            'series' => $series,
            'colors' => $colors
        ];


        // 1️⃣ Get aggregated data by 'saluran'
        $saluran = (clone $query)
            ->selectRaw("
        saluran,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'D' THEN 1 ELSE 0 END) AS BN,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'A' THEN 1 ELSE 0 END) AS PH,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'C' THEN 1 ELSE 0 END) AS PAS,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'E' THEN 1 ELSE 0 END) AS `Tidak Pasti`,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan, 1), ''), 'O') = 'O' THEN 1 ELSE 0 END) AS `Belum Cula`
    ")
            ->groupBy('saluran')
            ->orderBy('saluran')
            ->get();

        // 2️⃣ Extract labels (saluran names)
        $labels = $saluran->pluck('saluran')->filter()->toArray();

        // 3️⃣ Only build chart if labels exist
        $saluranChart = null;

        if (!empty($labels)) {
            $statusList = ['BN', 'PH', 'PAS', 'Tidak Pasti', 'Belum Cula'];

            $series = collect($statusList)->map(function ($status) use ($saluran) {
                $data = $saluran->pluck($status)->map(fn($v) => (int) $v)->toArray(); // convert to integers

                // Only include series if there's at least one non-zero value
                return !empty(array_filter($data)) ? [
                    'name' => $status,
                    'data' => $data
                ] : null;
            })->filter()->values()->toArray(); // remove nulls

            $seriesColors = collect($series)->map(fn($s) => $colorsLabel[$s['name']] ?? '#000')->toArray();

            $saluranChart = [
                'labels' => $labels,
                'series' => $series,
                'colors' => $seriesColors
            ];
        }




        // JANTINA
        $jantina = (clone $query)
            ->selectRaw("
                CASE 
                    WHEN jantina = 'P' THEN 'Perempuan'
                    WHEN jantina = 'L' THEN 'Lelaki'
                    ELSE 'Tidak Diketahui'
                END as jantina,
                COUNT(*) as total
            ")
            ->groupByRaw("
                CASE 
                    WHEN jantina = 'P' THEN 'Perempuan'
                    WHEN jantina = 'L' THEN 'Lelaki'
                    ELSE 'Tidak Diketahui'
                END
            ")
            ->pluck('total', 'jantina');

        $bangsa = (clone $query)
            ->selectRaw("
        CASE 
            WHEN bangsa = 'M' THEN 'Melayu'
            WHEN bangsa = 'C' THEN 'Cina'
            WHEN bangsa = 'I' THEN 'India'
            WHEN bangsa = 'L' THEN 'Lain-lain'
            ELSE 'Tidak Diketahui'
        END as bangsa_label,
        COUNT(*) as total
    ")
            ->groupBy('bangsa_label')
            ->orderByRaw("
        FIELD(bangsa_label, 'Melayu', 'Cina', 'India', 'Tidak Diketahui', 'Lain-lain')
    ")
            ->pluck('total', 'bangsa_label');

        // UMUR GROUP
        $umur = (clone $base)
            ->leftJoin('pengundi', 'pengundi.nokp_baru', '=', 'culaan_pengundis.no_kp')
            ->selectRaw("
        CASE
            WHEN culaan_pengundis.umur < 30 THEN '18-29'
            WHEN culaan_pengundis.umur BETWEEN 30 AND 39 THEN '30-39'
            WHEN culaan_pengundis.umur BETWEEN 40 AND 49 THEN '40-49'
            WHEN culaan_pengundis.umur BETWEEN 50 AND 59 THEN '50-59'
            ELSE '60+'
        END as umur_group,
        SUM(
            CASE 
                WHEN pengundi.nokp_baru IS NULL THEN 1 
                ELSE 0
            END
        ) as first_time,
        SUM(
            CASE 
                WHEN pengundi.nokp_baru IS NOT NULL THEN 1 
                ELSE 0
            END
        ) as not_first_time
    ")
            ->groupBy('umur_group')
            ->orderByRaw("FIELD(umur_group,'18-29','30-39','40-49','50-59','60+')")
            ->get();

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

            'status_chart' => $statusChart,

            'saluran_chart' => $saluranChart,

            'jantina_chart' => [
                'labels' => $jantina->keys(),
                'series' => $jantina->values(),
            ],

            'bangsa_chart' => [
                'labels' => $bangsa->keys(),
                'series' => $bangsa->values(),
            ],

            'umur_chart' => [
                'labels' => $umur->pluck('umur_group'),
                'series' => [
                    [
                        'name' => 'First Time',
                        'data' => $umur->pluck('first_time')->map(fn($v) => (int) $v)->values(),
                    ],
                    [
                        'name' => 'Bukan First Time',
                        'data' => $umur->pluck('not_first_time')->map(fn($v) => (int) $v)->values(),
                    ],
                ],
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

        $pengundi = CulaanPengundi::create([
            'culaan_id' => $culaan->id,
            'nama' => $request->nama,
            'no_kp' => $request->no_kp,
            'lokaliti' => $request->lokaliti,
            'saluran' => $request->saluran,
            'status_culaan' => 'O',
            'updated_by' => auth()->id()
        ]);

        activity()
            ->performedOn($pengundi)
            ->causedBy(auth()->user())
            ->withProperties([
                'nama' => $pengundi->nama,
                'no_kp' => $pengundi->no_kp
            ])
            ->log('created pengundi');

        return response()->json(['success' => true]);
    }



    public function showBulkImport(Culaan $culaan)
    {
        return view('culaan.bulkimport', compact('culaan'));
    }


    public function updateStatus(Request $request)
    {
        $statusMap = [
            'D' => ['label' => 'BN', 'color' => '#0033A0'],
            'A' => ['label' => 'PH', 'color' => '#E31C23'],
            'C' => ['label' => 'PAS', 'color' => '#009B3A'],
            'E' => ['label' => 'TP', 'color' => '#800080'],
            'O' => ['label' => 'BC', 'color' => '#999999'],
        ];

        $pengundi = CulaanPengundi::findOrFail($request->id);

        $oldStatus = $pengundi->status_culaan;

        $statusCode = strtoupper($request->status);

        if (!isset($statusMap[$statusCode])) {
            return response()->json(['error' => 'Invalid status'], 422);
        }

        $pengundi->update([
            'status_culaan' => $statusCode
        ]);

        activity()
            ->performedOn($pengundi)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_status' => $statusMap[$oldStatus]['label'],
                'new_status' => $statusMap[$statusCode]['label'],
            ])
            ->log('updated status_culaan');

        return response()->json(['success' => true]);

    }


    public function deletePengundi(Request $request)
    {
        $pengundi = CulaanPengundi::find($request->id);

        if ($pengundi) {

            activity()
                ->performedOn($pengundi)
                ->causedBy(auth()->user())
                ->withProperties([
                    'nama' => $pengundi->nama,
                    'no_kp' => $pengundi->no_kp
                ])
                ->log('deleted pengundi');

            $pengundi->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }



    public function generatePdf(Request $request, Culaan $culaan)
    {
        $charts = $request->input('charts');

        return Pdf::loadView('culaan.analytics_pdf', [
            'charts' => $charts,
            'culaan' => $culaan
        ])
            ->setPaper('a4', 'portrait')
            ->stream('culaan-analytics.pdf');
    }




    public function exportPdf(Request $request, Culaan $culaan)
    {
        $validator = Validator::make($request->all(), [
            'lokaliti' => 'nullable|string',
            'status_culaan' => 'nullable|string',
            'search_name' => 'nullable|string',
            'force' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filters',
            ], 400);
        }

        $filters = $request->only([
            'lokaliti',
            'status_culaan',
            'search_name',
        ]);

        $force = $request->boolean('force');

        // sanitize filters for filename
        $lokaliti = $filters['lokaliti']
            ? preg_replace('/[^A-Za-z0-9]/', '_', $filters['lokaliti'])
            : 'all';

        $status = $filters['status_culaan'] ?: 'all';

        $search = $filters['search_name']
            ? preg_replace('/[^A-Za-z0-9]/', '_', $filters['search_name'])
            : 'all';

        $fileName = "culaan_{$culaan->id}_lokaliti_{$lokaliti}_status_{$status}_search_{$search}.pdf";

        $path = "pdfs/culaan/{$culaan->id}/{$fileName}";

        if (!$force && Storage::disk('public')->exists($path)) {

            return response()->json([
                'success' => true,
                'exists' => true,
                'url' => asset('storage/' . $path),
                'message' => 'PDF already exists',
            ]);

        }

        // Optional: delete old file if force
        if ($force && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        GenerateCulaanBatchJob::dispatch(
            $culaan->id,
            $filters,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'exists' => false,
            'message' => $force ? 'PDF regeneration started.' : 'PDF generation started.',
        ]);
    }



    public function activity($culaanId)
    {
        $query = Activity::where(function ($q) use ($culaanId) {

            $q->where(function ($sub) use ($culaanId) {
                $sub->where('subject_type', Culaan::class)
                    ->where('subject_id', $culaanId);
            })

                ->orWhere(function ($sub) use ($culaanId) {
                    $sub->where('subject_type', CulaanPengundi::class)
                        ->whereIn('subject_id', function ($q2) use ($culaanId) {
                            $q2->select('id')
                                ->from('culaan_pengundis')
                                ->where('culaan_id', $culaanId);
                        });
                });

        })->latest();

        return DataTables::of($query)

            ->addColumn('user', function ($row) {
                return $row->causer->name ?? 'System';
            })

            ->addColumn('action', function ($row) {

                if ($row->description === 'updated status_culaan') {

                    $old = $row->properties['old_status'] ?? '-';
                    $new = $row->properties['new_status'] ?? '-';

                    return "Updated status of ID {$row->subject_id} from {$old} → {$new}";
                }

                if ($row->description === 'deleted pengundi') {

                    $nama = $row->properties['nama'] ?? '-';

                    return "Deleted pengundi ID {$row->subject_id} ({$nama})";
                }

                if ($row->description === 'created pengundi') {

                    $nama = $row->properties['nama'] ?? '-';

                    return "Created pengundi ID {$row->subject_id} ({$nama})";
                }

                if ($row->description === 'queued pengundi import') {

                    $file_name = $row->properties['file_name'] ?? '-';

                    return "Importing File  ({$file_name})";
                }

                return ucfirst($row->description);
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ->timezone('Asia/Kuala_Lumpur')
                    ->format('d M Y H:i');
            })

            ->make(true);
    }

}