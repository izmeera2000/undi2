<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Spatie\Activitylog\Models\Activity;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Jobs\TransferPengundiJob;
use App\Models\{Dun, Dm, Lokaliti, Parlimen, Pengundi};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Notifications\NewPengundiNotification;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateLokalitiSummaryPdfJob;
use App\Jobs\GenerateSingleLokalitiPdfJob;
use App\Jobs\MergeLokalitiPdfJob;
use App\Jobs\GenerateLokalitiBatchJob;

use Illuminate\Routing\Controller;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Models\Election;

use Throwable;


class PengundiAnalyticsController extends Controller
{
    //




    public function __construct()
    {
        // Only users with the corresponding permissions can access the methods
        $this->middleware('permission:pengundi.view')->only(['dropdowns', 'index']);
        $this->middleware('permission:pengundi.add')->only(['importpage']);
        $this->middleware('permission:pengundi.export')->only(['generatePdf']);
    }
































    public function analytics_test(Request $request)
    {
        $filters = $request->only([
            'kod_dm',
            'tarikh_undian',
            'jantina',
            'status_umno',
            'status_baru',
            'negeri',
        ]);

        $applyFilters = function ($query) use ($request, $filters) {

            // Year filters
            if ($request->mode === 'compare' && $request->year1 && $request->year2) {
                $query->whereIn('p.tarikh_undian', [$request->year1, $request->year2]);
            } elseif ($request->year1) {
                $query->where('p.tarikh_undian', $request->year1);
            }

            // Extra filters
            foreach ($filters as $column => $value) {
                if ($value !== null && $value !== '') {
                    $query->where("p.$column", $value);
                }
            }

            return $query;
        };

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Bangsa Chart (Umur × Bangsa × UMNO)
        |--------------------------------------------------------------------------
        */
        $bangsaChart = $applyFilters(
            DB::table('pengundi as p')
                ->selectRaw("
                p.tarikh_undian,
                CASE
                    WHEN p.umur IS NULL OR p.umur = ''  THEN 'UNKNOWN'
                    WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                    WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                    WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                    WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                    WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                    ELSE '60+'
                END AS umur_group,
                CASE
                    WHEN p.bangsa IS NULL OR p.bangsa = '' THEN 'UNKNOWN'
                    WHEN LOWER(p.bangsa) LIKE '%melayu%' THEN 'Melayu'
                    WHEN LOWER(p.bangsa) LIKE '%cina%' OR LOWER(p.bangsa) LIKE '%chinese%' THEN 'Cina'
                    WHEN LOWER(p.bangsa) LIKE '%india%' THEN 'India'
                    ELSE 'Lain-lain'
                END AS bangsa_group,
                COALESCE(p.status_umno,'UNKNOWN') as status_umno,
                COUNT(*) as total
            ")
                ->groupBy('p.tarikh_undian', 'umur_group', 'bangsa_group', 'status_umno')
        )->get();


        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Negeri Chart (Negeri × UMNO × First Time)
        |--------------------------------------------------------------------------
        */
        $negeriChart = $applyFilters(
            DB::table('pengundi as p')
                ->selectRaw("
                p.tarikh_undian,
                COALESCE(p.negeri,'UNKNOWN') as negeri,

                COALESCE(p.status_umno,'UNKNOWN') as status_umno,
                COALESCE(p.status_baru,'UNKNOWN') as status_baru,

                 COUNT(*) as total
            ")
                ->groupBy('p.tarikh_undian', 'negeri', 'p.status_umno', 'p.status_baru')
        )->get();


        /*
        |--------------------------------------------------------------------------
        | 3️⃣ DM × Umur Chart
        |--------------------------------------------------------------------------
        */
        $dmUmurChart = $applyFilters(
            DB::table('pengundi as p')
                ->join('lokaliti as l', 'p.kod_lokaliti', '=', 'l.kod_lokaliti')
                ->join('dm as d', 'l.kod_dm', '=', 'd.kod_dm')
                ->join('dun as du', 'd.kod_dun', '=', 'du.kod_dun')
                ->selectRaw("
                p.tarikh_undian,
                du.nama_dun,
                d.nama_dm,
                CASE
                    WHEN p.umur IS NULL OR p.umur = ''  THEN 'UNKNOWN'
                    WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                    WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                    WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                    WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                    WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                    ELSE '60+'
                END AS umur_group,
                COUNT(*) as total
            ")
                ->groupBy('p.tarikh_undian', 'du.nama_dun', 'd.nama_dm', 'umur_group')
        )->get();


        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Jantina Chart
        |--------------------------------------------------------------------------
        */
        $jantinaChart = $applyFilters(
            DB::table('pengundi as p')
                ->selectRaw("
                p.tarikh_undian,
                CASE
                    WHEN p.jantina = 'L' THEN 'Lelaki'
                    WHEN p.jantina = 'P' THEN 'Perempuan'
                    ELSE 'Unknown'
                END AS jantina,
                COUNT(*) as total
            ")
                ->groupBy('p.tarikh_undian', 'jantina')
        )->get();


        /*
        |--------------------------------------------------------------------------
        | 5️⃣ Totals
        |--------------------------------------------------------------------------
        */
        // $totals = $applyFilters(
        //     DB::table('pengundi as p')
        //         ->selectRaw("
        //         p.tarikh_undian,
        //         COUNT(*) AS total_pengundi,
        //         SUM(p.status_umno = 1) AS total_umno,
        //         SUM(p.status_baru = 1) AS total_first_time_voter
        //     ")
        //         ->groupBy('p.tarikh_undian')
        // )->get();


        return response()->json([
            'bangsaChart' => $bangsaChart,
            'negeriChart' => $negeriChart,
            // 'dmUmurChart' => $dmUmurChart,
            'jantinaChart' => $jantinaChart,
            // 'totals' => $totals,
        ]);
    }

    ///////////////////////data 2


    public function bulkimport2()
    {
        $elections = Election::orderBy('year')->orderBy('number')->get();

        return view('pengundi.bulkimport2', compact('elections'));
    }



    public function list()
    {
        // Get distinct election_id and saluran
        $pengundiData = Pengundi::where('type_data_id', 2)
            ->with('election') // eager load election
            ->select('election_id', 'saluran')
            ->distinct()
            ->get();

        // Extract distinct election numbers and years
        $electionNumbers = $pengundiData->pluck('election')
            ->filter() // remove nulls
            ->pluck('number')
            ->unique()
            ->sort()
            ->values();

        $electionType = $pengundiData->pluck('election')
            ->filter()
            ->pluck('type')
            ->unique()
            ->sort()
            ->values();

        $saluranList = $pengundiData
            ->pluck('saluran')
            ->filter(fn($s) => !is_null($s) && $s !== 0 && $s !== '')
            ->unique()
            ->sort()
            ->values();

        return view('pengundi.list', compact(
            'electionNumbers',
            'electionType',
            'saluranList'
        ));
    }

    public function getHierarchyByPru(Request $request)
    {
        Log::info('getHierarchyByPru called', [
            'type' => $request->type,
            'series' => $request->series,
        ]);

        $request->validate([
            'type' => 'required|string',
            'series' => 'required|integer',
        ]);

        // Resolve Election
        $election = DB::table('elections')
            ->where([
                'type' => $request->type,
                'number' => $request->series
            ])
            ->first(['id', 'year']);

        if (!$election) {
            Log::warning('Invalid election type/series', [
                'type' => $request->type,
                'series' => $request->series
            ]);

            return response()->json([
                'error' => 'Invalid election type/series'
            ], 400);
        }

        $selectedPRUYear = $election->year;
        $selectedPRUDate = $selectedPRUYear . '-12-31';

        Log::info('Election year fetched', ['year' => $selectedPRUYear]);
        Log::info('Using PRU date', ['selectedPRUDate' => $selectedPRUDate]);

        // Fetch distinct saluran for this election
        $saluranList = DB::table('pengundi')
            ->whereNotNull('saluran')
            ->where('saluran', '!=', 0)
            ->where('election_id', $election->id)
            ->distinct()
            ->pluck('saluran')
            ->sort()
            ->values();

        // Build Hierarchy Query
        $data = DB::table('pengundi')
            ->join('lokaliti', function ($join) use ($selectedPRUDate) {
                $join->on('pengundi.kod_lokaliti', '=', 'lokaliti.kod_lokaliti')
                    ->where('lokaliti.effective_from', '<=', $selectedPRUDate)
                    ->where(function ($q) use ($selectedPRUDate) {
                        $q->whereNull('lokaliti.effective_to')
                            ->orWhere('lokaliti.effective_to', '>=', $selectedPRUDate);
                    });
            })
            ->join('dm', function ($join) use ($selectedPRUDate) {
                $join->on('lokaliti.kod_dm', '=', 'dm.kod_dm')
                    ->where('dm.effective_from', '<=', $selectedPRUDate)
                    ->where(function ($q) use ($selectedPRUDate) {
                        $q->whereNull('dm.effective_to')
                            ->orWhere('dm.effective_to', '>=', $selectedPRUDate);
                    });
            })
            ->join('dun', function ($join) use ($selectedPRUDate) {
                $join->on('dm.kod_dun', '=', 'dun.kod_dun')
                    ->where('dun.effective_from', '<=', $selectedPRUDate)
                    ->where(function ($q) use ($selectedPRUDate) {
                        $q->whereNull('dun.effective_to')
                            ->orWhere('dun.effective_to', '>=', $selectedPRUDate);
                    });
            })
            ->join('parlimen', 'dun.kod_par', '=', 'parlimen.kod_par')
            ->select(
                'pengundi.kod_lokaliti',
                'lokaliti.kod_dm',
                'lokaliti.nama_lokaliti',
                'dm.kod_dun',
                'dm.nama_dm',
                'dun.kod_par',
                'dun.nama_dun',
                'parlimen.nama_par'
            )
            ->distinct()
            ->get();

        Log::info('Hierarchy query executed', ['record_count' => $data->count()]);

        return response()->json([
            'hierarchy' => $data,
            'saluran_list' => $saluranList
        ]);
    }


    public function list_data(Request $request)
    {
        // -------------------------------
        // Step 0: Validate required filters
        // -------------------------------
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'series' => 'required',
            'parlimen' => 'required',
            'dun' => 'required',
            'dm' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $type = $request->type;
        $series = $request->series;
        $dm = $request->dm;
        $dun = $request->dun;
        $parlimen = $request->parlimen;

        // -------------------------------
        // Step 1: Resolve PRU year
        // -------------------------------
        // -------------------------------
        // Step 1: Resolve PR year from elections table
        // -------------------------------
        $election = DB::table('elections')
            ->where([
                'type' => $type,
                'number' => $series
            ])
            ->first(['id', 'year']);



        if (!$election) {
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $selectedPRUYear = $election->year;

        $selectedPRUDate = $selectedPRUYear . '-12-31';

        $saluranList = DB::table('pengundi')
            ->whereNotNull('saluran')
            ->where('saluran', '!=', 0)
            ->where('election_id', '=', $election->id)
            ->distinct()
            ->pluck('saluran')
            ->sort()
            ->values();

        $selectParts = [
            'p.kod_lokaliti',
            'p.nama_lokaliti'
        ];

        foreach ($saluranList as $s) {
            $s = (int) $s; // safety
            $selectParts[] = "SUM(CASE WHEN p.saluran = $s THEN 1 ELSE 0 END) AS saluran_$s";
        }

        // total
        $selectParts[] = "COUNT(*) AS total";

        $selectRaw = implode(",\n", $selectParts);


        // -------------------------------
        // Step 2: Build query
        // -------------------------------
        $pengundi = DB::table(function ($query) use ($type, $series, $parlimen, $dun, $dm, $selectedPRUDate, $election) {
            $query->from('pengundi as p')
                ->select('p.id', 'p.kod_lokaliti', 'p.saluran', 'l.nama_lokaliti')

                ->distinct()
                ->join('lokaliti as l', function ($join) use ($selectedPRUDate) {
                    $join->on('p.kod_lokaliti', '=', 'l.kod_lokaliti')
                        ->where('l.effective_from', '<=', $selectedPRUDate)
                        ->where(function ($q) use ($selectedPRUDate) {
                            $q->whereNull('l.effective_to')
                                ->orWhere('l.effective_to', '>=', $selectedPRUDate);
                        });
                })
                ->join('dm as d', function ($join) use ($selectedPRUDate) {
                    $join->on('l.kod_dm', '=', 'd.kod_dm')
                        ->where('d.effective_from', '<=', $selectedPRUDate)
                        ->where(function ($q) use ($selectedPRUDate) {
                            $q->whereNull('d.effective_to')
                                ->orWhere('d.effective_to', '>=', $selectedPRUDate);
                        });
                })
                ->join('dun as dn', function ($join) use ($selectedPRUDate) {
                    $join->on('d.kod_dun', '=', 'dn.kod_dun')
                        ->where('dn.effective_from', '<=', $selectedPRUDate)
                        ->where(function ($q) use ($selectedPRUDate) {
                            $q->whereNull('dn.effective_to')
                                ->orWhere('dn.effective_to', '>=', $selectedPRUDate);
                        });
                })
                ->where('p.election_id', $election->id)
                ->where('dn.kod_par', $parlimen)
                ->where('d.kod_dun', $dun)
                ->where('l.kod_dm', $dm)
                ->whereNotNull('p.saluran')   // ✅ NEW
                ->where('p.saluran', '!=', ''); // ✅ NEW
            ;

        }, 'p') // alias the subquery as p
            ->selectRaw($selectRaw)
            ->groupBy('p.kod_lokaliti', 'p.nama_lokaliti')
            ->orderBy('p.kod_lokaliti')
            ->get();



        $dataWithLinks = $pengundi->map(function ($row) use ($parlimen, $dun, $dm, $type, $series) {
            $row = (array) $row; // convert object to array
            for ($i = 1; $i <= 7; $i++) {
                $row["link_saluran_$i"] = "/pengundi/list/"
                    . ($type ?? '0') . "/"
                    . ($series ?? '0') . "/"
                    . ($parlimen ?? '0') . "/"
                    . ($dun ?? '0') . "/"
                    . ($dm ?? '0') . "/"
                    . ($row['kod_lokaliti'] ?? '0') . "/"
                    . $i;

            }
            return $row;
        });

        // // -------------------------------
        // // Step 4: Return JSON safely
        // // -------------------------------


        // -------------------------------
        // Step 4: Return empty if no data
        // -------------------------------
        if ($pengundi->isEmpty()) {
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }


        return response()->json([
            'draw' => intval($request->draw ?? 1),
            'count' => $dataWithLinks->count(),
            'data' => $dataWithLinks
        ], 200, [], JSON_PRETTY_PRINT);


    }





    public function list_details(
        $pilihan_raya_type = null,
        $pilihan_raya_series = null,
        $parlimen = null,
        $dun_kod = null,
        $dm = null,
        $lokaliti = null,
        $saluran = null,
    ) {

        // -------------------------------
        // Step 0: Election
        // -------------------------------
        $election = null;

        if ($pilihan_raya_type && $pilihan_raya_series) {
            $election = DB::table('elections')
                ->where('type', $pilihan_raya_type)
                ->where('number', $pilihan_raya_series)
                ->first(['id', 'year']);
        }

        $year = $election->year ?? date('Y');
        $electionId = $election->id ?? null;

        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';

        // -------------------------------
        // Step 1: Main Query (JOIN instead of whereIn)
        // -------------------------------
        $pengundi = DB::table('pengundi as p')
            ->select('p.*', 'l.nama_lokaliti', 'd.nama_dm', 'dn.nama_dun')

            // ✅ JOIN lokaliti
            ->join('lokaliti as l', function ($join) use ($startDate, $endDate) {
                $join->on('p.kod_lokaliti', '=', 'l.kod_lokaliti')
                    ->where('l.effective_from', '<=', $endDate)
                    ->where(function ($q) use ($startDate) {
                        $q->where('l.effective_to', '>=', $startDate)
                            ->orWhereNull('l.effective_to');
                    });
            })

            // ✅ JOIN dm
            ->join('dm as d', function ($join) use ($startDate, $endDate) {
                $join->on('l.kod_dm', '=', 'd.kod_dm')
                    ->where('d.effective_from', '<=', $endDate)
                    ->where(function ($q) use ($startDate) {
                        $q->where('d.effective_to', '>=', $startDate)
                            ->orWhereNull('d.effective_to');
                    });
            })

            // ✅ JOIN dun
            ->join('dun as dn', function ($join) use ($startDate, $endDate) {
                $join->on('d.kod_dun', '=', 'dn.kod_dun')
                    ->where('dn.effective_from', '<=', $endDate)
                    ->where(function ($q) use ($startDate) {
                        $q->where('dn.effective_to', '>=', $startDate)
                            ->orWhereNull('dn.effective_to');
                    });
            })

            // -------------------------------
            // Filters
            // -------------------------------
            ->when($electionId, fn($q) => $q->where('p.election_id', $electionId))
            ->when($parlimen, fn($q) => $q->where('dn.kod_par', $parlimen))
            ->when($dun_kod, fn($q) => $q->where('d.kod_dun', $dun_kod))
            ->when($dm, fn($q) => $q->where('l.kod_dm', $dm))
            ->when($lokaliti, fn($q) => $q->where('p.kod_lokaliti', $lokaliti))
            ->when($saluran, fn($q) => $q->where('p.saluran', $saluran))

            ->get();

        // -------------------------------
        // Step 2: Names (unchanged)
        // -------------------------------
        $parlimenName = $parlimen
            ? DB::table('parlimen')->where('id', $parlimen)->value('nama_par')
            : null;

        $dunName = $dun_kod
            ? DB::table('dun')
                ->where('kod_dun', $dun_kod)
                ->whereYear('effective_from', '<=', $year)
                ->where(function ($q) use ($year) {
                    $q->whereYear('effective_to', '>=', $year)
                        ->orWhereNull('effective_to');
                })
                ->value('nama_dun')
            : null;

        $dmName = $dm
            ? DB::table('dm')
                ->where('kod_dm', $dm)
                ->whereYear('effective_from', '<=', $year)
                ->where(function ($q) use ($year) {
                    $q->whereYear('effective_to', '>=', $year)
                        ->orWhereNull('effective_to');
                })
                ->value('nama_dm')
            : null;

        $lokalitiName = $lokaliti
            ? DB::table('lokaliti')
                ->where('kod_lokaliti', $lokaliti)
                ->whereYear('effective_from', '<=', $year)
                ->where(function ($q) use ($year) {
                    $q->whereYear('effective_to', '>=', $year)
                        ->orWhereNull('effective_to');
                })
                ->value('nama_lokaliti')
            : null;

        // -------------------------------
        // Step 3: Breadcrumbs
        // -------------------------------
        $crumbs = [
            ['label' => 'Pengundi'],
            ['label' => 'List', 'url' => route('pengundi.list')],
        ];

        if ($pilihan_raya_type)
            $crumbs[] = ['label' => $pilihan_raya_type];
        if ($pilihan_raya_series)
            $crumbs[] = ['label' => $pilihan_raya_series];
        if ($parlimenName)
            $crumbs[] = ['label' => $parlimenName];
        if ($dunName)
            $crumbs[] = ['label' => $dunName];
        if ($dmName)
            $crumbs[] = ['label' => $dmName];
        if ($lokalitiName)
            $crumbs[] = ['label' => $lokalitiName];
        if ($saluran)
            $crumbs[] = ['label' => "Saluran $saluran"];

        // -------------------------------
        // Step 4: Return
        // -------------------------------
        return view('pengundi.list_details', compact(
            'pengundi',
            'parlimen',
            'dun_kod',
            'dm',
            'lokaliti',
            'saluran',
            'pilihan_raya_type',
            'pilihan_raya_series',
            'crumbs',
            'parlimenName',
            'dunName',
            'dmName',
            'lokalitiName'
        ));
    }

    public function list_details_data(Request $request)
    {
        $parlimen = $request->input('parlimen');
        $dun_kod = $request->input('dun');
        $dm = $request->input('dm');
        $lokaliti = $request->input('lokaliti');
        $saluran = $request->input('saluran');
        $pilihan_raya_type = $request->input('pilihan_raya_type');
        $pilihan_raya_series = $request->input('pilihan_raya_series');

        $election = null;

        if ($pilihan_raya_type && $pilihan_raya_series) {
            $election = DB::table('elections')
                ->where('type', $pilihan_raya_type)
                ->where('number', $pilihan_raya_series)
                ->first(['id', 'year']);
        }

        $year = $election->year ?? date('Y');



        // Step 1: Get valid DMs
        $validDMs = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            })
            ->when($parlimen, fn($q) => $q->whereIn('kod_dun', function ($q2) use ($parlimen) {
                $q2->select('kod_dun')->from('dun')->where('kod_par', $parlimen);
            }))
            ->when($dun_kod, fn($q) => $q->where('kod_dun', $dun_kod))
            ->when($dm, fn($q) => $q->where('kod_dm', $dm))
            ->pluck('kod_dm')
            ->toArray();

        // Step 2: Get valid Lokaliti
        $validLokalitiCodes = DB::table('lokaliti')
            ->whereIn('kod_dm', $validDMs)
            ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
            ->pluck('kod_lokaliti')
            ->toArray();

        // Step 3: Build query for pengundi
        $query = DB::table('pengundi')
            ->where('election_id', $election->id)
            ->whereIn('kod_lokaliti', $validLokalitiCodes)
            ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
            ->when($saluran, fn($q) => $q->where('saluran', $saluran));

        // Step 4: Use DataTables for server-side processing
        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($search = $request->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('nokp_baru', 'like', "%{$search}%")
                            ->orWhere('jantina', 'like', "%{$search}%")
                            ->orWhere('bangsa', 'like', "%{$search}%")
                            ->orWhere('alamat_spr', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('pengundi_details', function ($row) {

                return '
            <div class="d-flex align-items-center gap-3">


                <div>
                    <span class="fw-semibold">' . $row->nama . '</span>
                    <div class="text-muted small">' . $row->nokp_baru . '</div>
                </div>

            </div>';
            })

            ->rawColumns(['pengundi_details'])

            ->make(true);
    }




    public function list_data_pdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'series' => 'required',
            'parlimen' => 'required',
            'dun' => 'required',
            'dm' => 'required',
        ]);


        $filters = $request->only(['type', 'series', 'parlimen', 'dun', 'dm']);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Required filters missing'
            ], 400);
        }

        GenerateLokalitiBatchJob::dispatch(
            $filters,
            auth()->id()   // ✅ pass user id
        );
        return response()->json([
            'success' => true,
            'message' => 'PDF generation job dispatched successfully.'
        ]);
    }


    public function check_pdf(Request $request)
    {
        $type = $request->type;
        $series = $request->series;
        $dm = $request->dm;

        $folderPath = "pdfs/{$type}/{$series}/{$dm}";

        if (!Storage::disk('public')->exists($folderPath)) {
            return response()->json(['exists' => false]);
        }

        $files = Storage::disk('public')->files($folderPath);

        // Filter only PDFs that contain _merged or _summary
        $pdfFiles = array_filter($files, function ($file) {
            return str_ends_with($file, '.pdf') &&
                (str_contains($file, '_list') || str_contains($file, '_summary'));
        });

        if (empty($pdfFiles)) {
            return response()->json(['exists' => false]);
        }

        // Sort by file name ascending
        sort($pdfFiles, SORT_NATURAL | SORT_FLAG_CASE);

        $latestFile = $pdfFiles[0];

        return response()->json([
            'exists' => true,
            'url' => Storage::url($latestFile),
            'last_modified' => Carbon::createFromTimestamp(
                Storage::disk('public')->lastModified($latestFile)
            )->setTimezone('Asia/Kuala_Lumpur')
                ->format('d M Y, h:i A'),
            'files' => array_map(function ($file) {
                return [
                    'name' => basename($file),
                    'url' => Storage::url($file),
                    'last_modified' => Carbon::createFromTimestamp(
                        Storage::disk('public')->lastModified($file)
                    )->setTimezone('Asia/Kuala_Lumpur')
                        ->format('d M Y, h:i A'),
                ];
            }, $pdfFiles)
        ]);
    }


    public function activity()
    {
        $query = Activity::where('subject_type', Pengundi::class)
            ->latest();



        return DataTables::of($query)

            ->addColumn('user', function ($row) {
                return $row->causer->name ?? 'System';
            })

            ->addColumn('action', function ($row) {




                switch ($row->description) {

                    case 'Import pengundi completed':

                        $file = $row->properties['file'] ?? 'unknown file';
                        $type = $row->properties['election_type'] ?? '-';
                        $series = $row->properties['election_series'] ?? '-';
                        $year = $row->properties['election_year'] ?? '-';
                        $inserted = $row->properties['total_inserted'] ?? 0;

                        return "
                            <strong>Import Completed</strong><br>
                            File: <strong>{$file}</strong><br>
                            Election: <strong>{$type} {$series} ({$year})</strong><br>
                            Total Inserted: <strong>{$inserted}</strong>
                        ";


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




