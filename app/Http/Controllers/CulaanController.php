<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

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
        $year = $culaan->election?->year;

        $lokalitiList = DB::table('lokaliti')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            })
            ->get();

        $groupsList = DB::table('groups')
            ->whereYear('created_at', '<=', $year)
            ->get();

        $dmList = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            })
            ->get();

        return view('culaan.show', compact(
            'culaan',
            'lokalitiList',
            'groupsList',
            'dmList'
        ));
    }


    public function pengundiData(Request $request, Culaan $culaan)
    {

        $query = CulaanPengundi::where('culaan_id', $culaan->id)
            ->orderBy('id', 'asc');


        if ($request->lokaliti) {
            $query->where('culaan_pengundis.kod_lokaliti', $request->lokaliti);
        }

        if ($request->status_culaan) {
            $query->where('status_culaan', 'like', $request->status_culaan . '%');
        }

        if ($request->search_name) {
            $search = trim($request->search_name); // <-- trim whitespace

            $query->where(function ($q) use ($search) {
                // Determine search type based on '*' position
                if (str_starts_with($search, '*') && str_ends_with($search, '*')) {
                    // *something* → contains
                    $term = substr($search, 1, -1);
                    $q->where('nama', 'like', "%{$term}%")
                        ->orWhere('no_kp', 'like', "%{$term}%");
                } elseif (str_starts_with($search, '*')) {
                    // *something → ends with
                    $term = substr($search, 1);
                    $q->where('nama', 'like', "%{$term}")
                        ->orWhere('no_kp', 'like', "%{$term}");
                } elseif (str_ends_with($search, '*')) {
                    // something* → starts with
                    $term = substr($search, 0, -1);
                    $q->where('nama', 'like', "{$term}%")
                        ->orWhere('no_kp', 'like', "{$term}%");
                } else {
                    // default → contains
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_kp', 'like', "%{$search}%");
                }
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
                    <span class="fw-semibold">' . $row->lokaliti . ' (' . $row->kod_lokaliti . ')</span>
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
                return <<<HTML
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-primary edit-pengundi" data-id="{$row->id}">
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button class="btn btn-sm btn-danger delete-pengundi" data-id="{$row->id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                HTML;
            })


            ->rawColumns(['pengundi_details', 'pengundi_details2', 'lokaliti_details', 'status_culaan', 'actions'])

            ->make(true);
    }



    public function analytics(Request $request, Culaan $culaan)
    {


        $year = $culaan->election?->year;

        $lokalitiList =
            DB::table('lokaliti')
                ->whereYear('effective_from', '<=', $year)
                ->where(function ($q) use ($year) {
                    $q->whereYear('effective_to', '>=', $year)->orWhereNull('effective_to');
                })
                ->orderBy('koddm')

                ->get();


        $dmList = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            })
            ->orderBy('koddm')
            ->get();


        return view('culaan.analytics', compact('culaan', 'lokalitiList', 'dmList'));
    }


    public function analytics_data(Request $request, Culaan $culaan)
    {
        $query = CulaanPengundi::where('culaan_id', $culaan->id);

        if ($request->lokaliti) {
            $query->where('culaan_pengundis.kod_lokaliti', $request->lokaliti);
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
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan,1),''),'O') = 'D' THEN 1 ELSE 0 END) AS `BN`,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan,1),''),'O') = 'A' THEN 1 ELSE 0 END) AS `PH`,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan,1),''),'O') = 'C' THEN 1 ELSE 0 END) AS `PAS`,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan,1),''),'O') = 'E' THEN 1 ELSE 0 END) AS `Tidak Pasti`,
        SUM(CASE WHEN COALESCE(NULLIF(LEFT(status_culaan,1),''),'O') = 'O' THEN 1 ELSE 0 END) AS `Belum Cula`
    ")
            ->groupBy('saluran')
            ->orderBy('saluran')
            ->get();

        // 2️⃣ Remove rows with null/empty 'saluran' and reindex
        $saluran = $saluran->filter(fn($row) => !empty($row->saluran))->values();

        // 3️⃣ Extract labels
        $labels = $saluran->pluck('saluran')->toArray();

        // 4️⃣ Define statuses
        $statusList = ['BN', 'PH', 'PAS', 'Tidak Pasti', 'Belum Cula'];

        // 5️⃣ Build series, removing series where all data is zero
        $series = collect($statusList)->map(function ($status) use ($saluran) {
            $data = $saluran->pluck($status)->map(fn($v) => (int) $v)->toArray();
            // Only include series if at least one value > 0
            return array_sum($data) > 0
                ? ['name' => $status, 'data' => $data]
                : null;
        })->filter()->values()->toArray(); // remove null series

        // 6️⃣ Map colors for remaining series
        $seriesColors = collect($series)->map(fn($s) => $colorsLabel[$s['name']] ?? '#000')->toArray();

        // 7️⃣ Final chart data
        $saluranChart = [
            'labels' => $labels,
            'series' => $series,
            'colors' => $seriesColors
        ];





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
        // Validation rules
        $request->validate([
            'nama' => 'required',
            'no_kp' => 'nullable',
            'lokaliti' => 'required',
            'saluran' => 'nullable',
        ]);

        // Split the 'lokaliti' field into name and code
        $lokalitiData = explode(',', $request->lokaliti); // This will give an array like ['Lokaliti Name', '022001']
        $namaLokaliti = $lokalitiData[0];
        $kodLokaliti = $lokalitiData[1];

        // Create a new Pengundi model instance
        $pengundi = new CulaanPengundi();
        $pengundi->culaan_id = $culaan->id; // Associate this pengundi with the culaan
        $pengundi->nama = $request->nama;
        $pengundi->no_kp = $request->no_kp;
        $pengundi->pm = $request->pm;
        $pengundi->no_siri = $request->no_siri;
        $pengundi->saluran = $request->saluran;
        $pengundi->jantina = $request->jantina;
        $pengundi->umur = $request->umur;
        $pengundi->bangsa = $request->bangsa;
        $pengundi->kategori_pengundi = $request->kategori_pengundi;
        $pengundi->status_pengundi = $request->status_pengundi;
        $pengundi->cawangan = $request->cawangan;
        $pengundi->no_ahli = $request->no_ahli;
        $pengundi->alamat = $request->alamat;
        $pengundi->status_ahli = $request->status_ahli;
        $pengundi->kategori_ahli = $request->kategori_ahli;
        $pengundi->lokaliti = $namaLokaliti; // Store name or use the kod_lokaliti as needed
        $pengundi->kod_lokaliti = $kodLokaliti; // Store code separately
        $pengundi->status_culaan = $request->status_culaan ?? 'O'; // default value as 'O' if not set
        $pengundi->updated_by = auth()->id();

        // Save the pengundi instance
        $pengundi->save();

        // Log activity on the Culaan model (parent model)
        activity()
            ->performedOn($culaan)  // Log the activity on the Culaan model instance
            ->causedBy(auth()->user())
            ->withProperties([
                'nama' => $pengundi->nama,
                'no_kp' => $pengundi->no_kp,
                'culaan_id' => $culaan->id,
            ])
            ->log('created pengundi for culaan');

        // Optionally, you can also log the creation of the pengundi itself (on CulaanPengundi model)
        activity()
            ->performedOn($pengundi)  // Log the activity on the CulaanPengundi model instance
            ->causedBy(auth()->user())
            ->withProperties([
                'nama' => $pengundi->nama,
                'no_kp' => $pengundi->no_kp,
            ])
            ->log('created pengundi');

        // Return the response
        return response()->json(['success' => true]);
    }

    protected function insertWithoutDuplicates(array $rows)
    {
        if (empty($rows)) {
            return;
        }

        // Remove duplicates inside batch
        $uniqueRows = [];
        $seen = [];

        foreach ($rows as $r) {
            // Create unique key based on no_kp and kod_lokaliti
            $key = $r['no_kp'] . '_' . $r['lokaliti'];

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueRows[] = $r;
        }

        // Check existing records in DB
        $existing = DB::table('culaan_pengundis')
            ->where('culaan_id', $r['culaan_id'])
            ->whereIn('no_kp', array_column($uniqueRows, 'no_kp'))
            ->get(['no_kp', 'lokaliti']);

        $existingMap = [];
        foreach ($existing as $e) {
            $existingMap[$e->no_kp . '_' . $e->lokaliti] = true;
        }

        // Prepare the rows to be inserted (skip duplicates found in DB)
        $insert = [];

        foreach ($uniqueRows as $r) {
            $key = $r['no_kp'] . '_' . $r['lokaliti'];

            if (isset($existingMap[$key])) {
                continue;
            }

            $insert[] = $r;
        }

        // Insert the valid rows into the database
        if (!empty($insert)) {
            DB::table('culaan_pengundis')->insert($insert);
        }
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

        $oldStatus = strtoupper($pengundi->status_culaan[0] ?? '');
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
                'nama' => $pengundi->nama,
                'no_kp' => $pengundi->no_kp,
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


    public function pengundiEdit(Request $request, Culaan $culaan)
    {
        // Validation
        $request->validate([
            'id' => 'required|exists:culaan_pengundis,id',
            'nama' => 'required',
            'no_kp' => 'nullable',
            'lokaliti' => 'required',
            'saluran' => 'nullable',
        ]);

        // Find existing pengundi
        $pengundi = CulaanPengundi::findOrFail($request->id);

        // Store old values for logging
        $oldStatus = $pengundi->status_culaan;
        $oldData = $pengundi->toArray();

        // Split lokaliti
        $lokalitiData = explode(',', $request->lokaliti);
        $namaLokaliti = $lokalitiData[0] ?? '';
        $kodLokaliti = $lokalitiData[1] ?? '';

        // Update fields
        $pengundi->culaan_id = $culaan->id;
        $pengundi->nama = $request->nama;
        $pengundi->no_kp = $request->no_kp;
        $pengundi->pm = $request->pm;
        $pengundi->no_siri = $request->no_siri;
        $pengundi->saluran = $request->saluran;
        $pengundi->jantina = $request->jantina;
        $pengundi->umur = $request->umur;
        $pengundi->bangsa = $request->bangsa;
        $pengundi->kategori_pengundi = $request->kategori_pengundi;
        $pengundi->status_pengundi = $request->status_pengundi;
        $pengundi->cawangan = $request->cawangan;
        $pengundi->no_ahli = $request->no_ahli;
        $pengundi->alamat = $request->alamat;
        $pengundi->status_ahli = $request->status_ahli;
        $pengundi->kategori_ahli = $request->kategori_ahli;
        $pengundi->lokaliti = $namaLokaliti;
        $pengundi->kod_lokaliti = $kodLokaliti;
        $pengundi->status_culaan = $request->status_culaan ?? $oldStatus;
        $pengundi->updated_by = auth()->id();

        $pengundi->save();

        // Log activity with old vs new values
        activity()
            ->performedOn($pengundi)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $pengundi->toArray(),
            ])
            ->log('updated pengundi');

        return response()->json(['success' => true]);
    }

    public function pengundiFetch(Request $request, Culaan $culaan)
    {
        // Validate that we have a valid ID
        $request->validate([
            'id' => 'required|exists:culaan_pengundis,id',
        ]);

        // Find the pengundi
        $pengundi = CulaanPengundi::findOrFail($request->id);

        // Optionally, you can transform the data if needed
        $data = [
            'id' => $pengundi->id,
            'nama' => $pengundi->nama,
            'no_kp' => $pengundi->no_kp,
            'pm' => $pengundi->pm,
            'no_siri' => $pengundi->no_siri,
            'saluran' => $pengundi->saluran,
            'jantina' => $pengundi->jantina,
            'umur' => $pengundi->umur,
            'bangsa' => $pengundi->bangsa,
            'kategori_pengundi' => $pengundi->kategori_pengundi,
            'status_pengundi' => $pengundi->status_pengundi,
            'status_culaan' => $pengundi->status_culaan,
            'cawangan' => $pengundi->cawangan,
            'no_ahli' => $pengundi->no_ahli,
            'alamat' => $pengundi->alamat,
            'status_ahli' => $pengundi->status_ahli,
            'kategori_ahli' => $pengundi->kategori_ahli,
            'lokaliti2' => $pengundi->lokaliti . ',' . $pengundi->kod_lokaliti,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
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
            'lokaliti_name' => 'nullable|string',
            'dm' => 'nullable|string',
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
            'dm',
            'lokaliti_name',
        ]);

        $force = $request->boolean('force');

        // sanitize filters for filename
        $lokaliti = $filters['lokaliti']
            ? preg_replace('/[^A-Za-z0-9]/', '_', $filters['lokaliti'])
            : 'all';

        $status = $filters['status_culaan'] ?: 'all';

        // Trim search_name before sanitizing
        $search = $filters['search_name']
            ? preg_replace('/[^A-Za-z0-9]/', '_', trim($filters['search_name']))
            : 'all';

        $fileName = "culaan_{$culaan->id}_lokaliti_{$lokaliti}_status_{$status}_search_{$search}.pdf";
        $path = "pdfs/culaan/{$culaan->id}/{$fileName}";

        // Force delete old file if requested
        if ($force && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Check if file exists and not forcing
        if (!$force && Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'url' => asset('storage/' . $path) . '?t=' . time(),
                'message' => 'PDF already exists',
            ]);
        }

        // Dispatch job
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

                $statusMap2 = [
                    'BN' => ['label' => 'BN', 'color' => '#0033A0'],
                    'PH' => ['label' => 'PH', 'color' => '#E31C23'],
                    'PAS' => ['label' => 'PAS', 'color' => '#009B3A'],
                    'TP' => ['label' => 'TP', 'color' => '#800080'],
                    'BC' => ['label' => 'BC', 'color' => '#999999'],
                ];


                switch ($row->description) {

                    case 'updated status_culaan':

                        $oldFull = $row->properties['old_status'] ?? 'N/A';
                        $newFull = $row->properties['new_status'] ?? 'N/A';



                        // Get color from status map
                        $oldColor = $statusMap2[$oldFull]['color'];
                        $newColor = $statusMap2[$newFull]['color'];

                        $nama = $row->properties['nama'] ?? 'N/A';
                        $no_kp = $row->properties['no_kp'] ?? 'N/A';

                        return "<strong>{$nama}</strong> <small>{$no_kp}</small> (voter ID <strong>{$row->subject_id}</strong>) status updated: 
                            <span style='color: {$oldColor}; font-weight:bold;'>{$oldFull}</span> → 
                            <span style='color: {$newColor}; font-weight:bold;'>{$newFull}</span>";


                    case 'deleted pengundi':
                        $nama = $row->properties['nama'] ?? 'N/A';
                        $no_kp = $row->properties['no_kp'] ?? 'N/A';
                        return "Voter <strong>{$nama}</strong> <small>{$no_kp}</small> (ID {$row->subject_id}) was <span class='text-danger'>deleted</span>";

                    case 'created pengundi':
                        $nama = $row->properties['nama'] ?? 'N/A';
                        $no_kp = $row->properties['no_kp'] ?? 'N/A';
                        return "New voter <strong>{$nama}</strong> <small>{$no_kp}</small>  (ID {$row->subject_id}) was <span class='text-success'>created</span>";

                    case 'queued pengundi import':
                        $file_name = $row->properties['file_name'] ?? 'unknown file';
                        return "Import started for file: <strong>{$file_name}</strong>";

                    default:
                        // Make the description more human-friendly
                        return ucfirst(str_replace('_', ' ', $row->description));
                }
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ->timezone('Asia/Kuala_Lumpur')
                    ->format('d M Y H:i');
            })

            ->make(true);
    }

}