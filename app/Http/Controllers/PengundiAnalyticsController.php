<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Jobs\TransferPengundiJob;
use App\Models\{Dun, Dm, Lokaliti, Parlimen, Pengundi};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

use App\Models\User;
use App\Notifications\NewPengundiNotification;

use Illuminate\Routing\Controller;

class PengundiAnalyticsController extends Controller
{
    //


    protected array $PRUMAP = [
        '12' => '2008',
        '15' => '2022',
    ];

    public function __construct()
    {
        // Only users with the corresponding permissions can access the methods
        $this->middleware('permission:pengundi.view')->only(['dropdowns', 'index']);
        $this->middleware('permission:pengundi.add')->only(['importpage']);
        $this->middleware('permission:pengundi.export')->only(['generatePdf']);
    }



    public function analytics()
    {
        // Get all DUNs
        $duns = Dun::orderBy('namadun')->get();

        $datas = Pengundi::selectRaw('tarikh_undian as year, pilihan_raya_type,pilihan_raya_series')
            ->where('type_data_id', 1)
            ->distinct()
            ->orderBy('year', 'desc')->get();

        // Pass to the view
        return view('pengundi.analytics', compact('duns', 'datas'));
    }





    public function analytics_data(Request $request)
    {
        $filters = $request->only([
            'koddm',
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



        $parlimen = 1;
        $dun_kod = $request->input('dun');
        $dm = $request->input('dm');
        $lokaliti = $request->input('lokaliti');
        $tarikh_undian = $request->input('tarikh_undian');

        $year = $tarikh_undian
            ? date('Y', strtotime($tarikh_undian))
            : date('Y');


        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Filter DM (Effective Date + Optional Filters)
        |--------------------------------------------------------------------------
        */
        $validDMs = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            })
            ->when($parlimen, fn($q) => $q->whereIn('kod_dun', function ($q2) use ($parlimen) {
                $q2->select('kod_dun')->from('dun')->where('parlimen_id', $parlimen);
            }))
            ->when($dun_kod, fn($q) => $q->where('kod_dun', $dun_kod))
            ->when($dm, fn($q) => $q->where('koddm', $dm))
            ->pluck('koddm')
            ->toArray();

        $validLokalitiCodes = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs)
            ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
            ->pluck('kod_lokaliti')
            ->toArray();


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
                ->join('dm as d', 'l.koddm', '=', 'd.koddm')
                ->join('dun as du', 'd.kod_dun', '=', 'du.kod_dun')
                ->selectRaw("
                p.tarikh_undian,
                du.namadun,
                d.namadm,
                CASE
                     WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                    WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                    WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                    WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                    WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                    ELSE '60+'
                END AS umur_group,
                COUNT(*) as total
            ")
                ->groupBy('p.tarikh_undian', 'du.namadun', 'd.namadm', 'umur_group')
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
            END AS jantina,
            CASE
                WHEN p.status_umno = 1 THEN 'UMNO'
                WHEN p.status_umno = 0 THEN 'Bukan UMNO'
            END AS status_umno,
            COUNT(*) as total
        ")
                ->groupBy('p.tarikh_undian', 'jantina', 'status_umno')
        )->get();

        $umurChart = $applyFilters(
            DB::table('pengundi as p')
                ->selectRaw("
            p.tarikh_undian,

            CASE
                WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,

            CASE
                WHEN p.status_umno = 1 THEN '1'
                WHEN p.status_umno = 0 THEN '0'
            END AS status_umno,

            CASE
                WHEN p.status_baru = 1 THEN '1'
                WHEN p.status_baru = 0 THEN '0'
            END AS status_baru,

            COUNT(*) as total
        ")
                ->groupBy(
                    'p.tarikh_undian',
                    'umur_group',
                    'status_umno',
                    'status_baru'
                )
        )->get();



        /*
        |--------------------------------------------------------------------------
        | 5️⃣ Totals
        |--------------------------------------------------------------------------
        */
        $totals = $applyFilters(
            DB::table('pengundi as p')
                ->selectRaw("
                p.tarikh_undian,
                COUNT(*) AS total_pengundi,
                SUM(p.status_umno = 1) AS total_umno,
                SUM(p.status_baru = 1) AS total_first_time_voter
            ")
                ->groupBy('p.tarikh_undian')
        )->get();


        return response()->json([
            'bangsaChart' => $bangsaChart,
            'negeriChart' => $negeriChart,
            'dmUmurChart' => $dmUmurChart,
            'jantinaChart' => $jantinaChart,
            'umurChart' => $umurChart,
            'totals' => $totals,
            'validDMs' => $validDMs,
            'validLokalitiCodes ' => $validLokalitiCodes,
        ]);
    }



    public function generatePdf(Request $request)
    {
        $charts = $request->input('charts');

        // $user = User::find(1);

        // $user->notify(new NewPengundiNotification("New Pengundi registered"));

        return Pdf::loadView('pengundi.pdf', [
            'charts' => $charts
        ])
            ->setPaper('a4', 'portrait')
            ->stream('pengundi-analytics.pdf');
    }


















    public function bulkimport()
    {
        return view('pengundi.bulkimport');
    }





    public function importFromPaste(Request $request)
    {
        // Validate that the data is not empty
        $request->validate([
            'data' => 'required|string',
        ]);

        // Get the pasted data
        $rawData = $request->input('data');

        // Split the data into rows by new lines
        $rows = explode("\n", $rawData);

        // Initialize an array to hold the processed data
        $processedData = [];

        // Loop through each row
        foreach ($rows as $row) {
            // Trim any extra spaces from the row
            $row = trim($row);

            // Skip empty rows
            if (empty($row)) {
                continue;
            }

            // Normalize spaces: Replace multiple spaces/tabs with a single space, and then split by space
            // This allows us to handle inconsistent spacing between columns.
            $normalizedRow = preg_replace('/\s+/', ' ', $row);

            // Split the row into columns (by spaces now, after normalization)
            $columns = explode(' ', $normalizedRow);

            // Optional: Check if the row has the expected number of columns (e.g., 5)
            // If you want to check for an exact number of columns, you can validate here.
            if (count($columns) >= 5) { // You can adjust this check based on the expected number of columns
                $processedData[] = [
                    'column1' => $columns[0] ?? null,
                    'column2' => $columns[1] ?? null,
                    'column3' => $columns[2] ?? null,
                    'column4' => $columns[3] ?? null,
                    'column5' => $columns[4] ?? null,
                    // Add more columns if needed
                ];
            } else {
                // Log a warning if the row doesn't match the expected column count
                \Log::warning('Row skipped due to incorrect number of columns: ' . $row);
            }
        }

        // Log the processed data for debugging or testing purposes
        \Log::info('Processed Data:', $processedData);

        // Optionally use dd() or dump() to view data in browser or console
        // dd($processedData); // Uncomment if you want to dump the data

        // Return success message (or simulate it in a console context)
        return response()->json(['message' => 'Data imported successfully!', 'data' => $processedData]);
    }










    public function pasteimportpage()
    {
        $parlimens = Parlimen::all();
        $duns = Dun::all();
        $lokalitis = Lokaliti::select('kod_lokaliti', 'koddm')->distinct()
            ->orderBy('kod_lokaliti', 'asc') // order by name ascending
            ->get();
        ;

        $dms = Dm::select('koddm', 'dun_id')
            ->distinct('koddm')  // distinct by 'koddm'
            ->orderBy('koddm', 'asc')  // order by 'namadm' ascending
            ->get();


        return view('pengundi.pasteimport', compact('parlimens', 'duns', 'dms', 'lokalitis'));
    }









    /**
     * Handle pasted data submission
     */
    public function submit(Request $request)
    {
        $rows = json_decode($request->paste_data, true);

        if (!$rows) {
            return response()->json(['error' => 'No data received'], 422);
        }

        foreach ($rows as $row) {

            if (empty($row[0]))
                continue;

            Pengundi::updateOrCreate(
                [
                    'nokp_baru' => $row[0],
                    'tarikh_undian' => $request->tarikh_undian,
                ],
                [
                    'kod_lokaliti' => $request->kod_lokaliti,
                    'nokp_lama' => $row[1] ?? null,
                    'nama' => $row[2] ?? null,
                    'jantina' => $row[3] ?? null,
                    'bangsa' => $row[4] ?? null,
                    'umur' => $row[5] ?? null,
                    'tahun_lahir' => $row[6] ?? null,
                    'alamat_spr' => $row[7] ?? null,
                    'poskod' => $row[8] ?? null,
                    'bandar' => $row[9] ?? null,
                    'negeri' => $row[10] ?? null,
                ]
            );
        }

        return response()->json([
            'success' => count($rows) . ' records imported successfully.'
        ]);
    }















    public function analytics_test(Request $request)
    {
        $filters = $request->only([
            'koddm',
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
                ->join('dm as d', 'l.koddm', '=', 'd.koddm')
                ->join('dun as du', 'd.kod_dun', '=', 'du.kod_dun')
                ->selectRaw("
                p.tarikh_undian,
                du.namadun,
                d.namadm,
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
                ->groupBy('p.tarikh_undian', 'du.namadun', 'd.namadm', 'umur_group')
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
        return view('pengundi.bulkimport2');
    }



    public function list()
    {
        $parlimens = Parlimen::all();
        $duns = Dun::all();

        $lokalitis = Lokaliti::select('kod_lokaliti', 'koddm')
            ->distinct()
            ->orderBy('kod_lokaliti', 'asc')
            ->get();

        $dms = Dm::select('koddm', 'kod_dun')
            ->distinct('koddm')
            ->orderBy('koddm', 'asc')
            ->get();

        // 🔹 Distinct pilihan_raya_type and series
        $pilihanRayaTypes = Pengundi::select('pilihan_raya_type')
            ->distinct()
            ->orderBy('pilihan_raya_type')
            ->pluck('pilihan_raya_type');

        $pilihanRayaSeries = Pengundi::select('pilihan_raya_series')
            ->distinct()
            ->orderBy('pilihan_raya_series')
            ->pluck('pilihan_raya_series');

        return view('pengundi.list', compact(
            'parlimens',
            'duns',
            'dms',
            'lokalitis',
            'pilihanRayaTypes',
            'pilihanRayaSeries'
        ));
    }




    public function list_data(Request $request)
    {



        // Access the PRUMAP array inside a method
        $year = $this->PRUMAP[$request->pilihan_raya_series];

        // -------------------------------
        // Step 1: Filter DM based on selected Parlimen and DUN
        // -------------------------------
        $dmQuery = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            });

        if ($request->parlimen) {
            // Join through DUNs
            $dmQuery->whereIn('kod_dun', function ($q) use ($request) {
                $q->select('kod_dun')
                    ->from('dun')
                    ->where('parlimen_id', $request->parlimen);
            });
        }

        if ($request->dun) {
            $dmQuery->where('kod_dun', $request->dun);
        }

        if ($request->dm) {
            $dmQuery->where('koddm', $request->dm);
        }

        $validDMs = $dmQuery->pluck('koddm')->toArray();

        // -------------------------------
        // Step 2: Get valid lokaliti codes & names
        // -------------------------------
        $lokalitiQuery = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs)
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            });

        $validLokaliti = $lokalitiQuery
            ->select('kod_lokaliti', 'nama_lokaliti', 'koddm')
            ->get()
            ->keyBy('kod_lokaliti');

        $validLokalitiCodes = $validLokaliti->keys()->toArray();

        // -------------------------------
        // Step 3: Filter pengundi by lokaliti + pilihan_raya_type + series
        // -------------------------------
        $pengundiQuery = DB::table('pengundi')
            ->where('type_data_id', 2)
            ->whereIn('kod_lokaliti', $validLokalitiCodes);

        // if ($request->pilihan_raya_type) {
        //     $pengundiQuery->where('pilihan_raya_type', $request->pilihan_raya_type);
        // }

        // if ($request->pilihan_raya_series) {
        //     $pengundiQuery->where('pilihan_raya_series', $request->pilihan_raya_series);
        // }

        $pengundi = $pengundiQuery
            ->select('id', 'nama', 'kod_lokaliti', 'saluran')
            ->get()
            ->map(function ($p) use ($validLokaliti) {
                $p->nama_lokaliti = $validLokaliti[$p->kod_lokaliti]->nama_lokaliti ?? null;
                return $p;
            });

        // -------------------------------
        // Step 4: Aggregate totals per saluran
        // -------------------------------
        $saluranTotals = $pengundi
            ->groupBy('saluran')
            ->map(fn($group, $saluran) => count($group))
            ->sortKeys()
            ->toArray();

        // -------------------------------
        // Step 5: Prepare DataTable rows grouped by lokaliti
        // -------------------------------
        $dataByLokaliti = $pengundi
            ->groupBy('kod_lokaliti')
            ->map(function ($group, $kod_lokaliti) use ($request, $year) {
                $row = [
                    'parlimen_id' => $request->parlimen ?? null,
                    'dun' => $request->dun ?? null,
                    'dm' => $request->dm ?? null,
                    'kod_lokaliti' => $kod_lokaliti,
                    'nama_lokaliti' => $group[0]->nama_lokaliti,
                    'pilihan_raya_type' => $request->pilihan_raya_type ?? null,
                    'pilihan_raya_series' => $request->pilihan_raya_series ?? null,
                    'total' => count($group),
                ];

                // Saluran counts per lokaliti
                $saluranCounts = $group->groupBy('saluran')->map(fn($g) => count($g))->toArray();
                foreach ($saluranCounts as $s => $count) {
                    $row["saluran_$s"] = $count;

                    // Create RESTful-style link for each saluran
                    $row["link_saluran_$s"] = "/pengundi/list/"
                        . ($row['parlimen_id'] ?? '0') . "/"
                        . ($row['dun'] ?? '0') . "/"
                        . ($row['dm'] ?? '0') . "/"
                        . ($row['kod_lokaliti'] ?? '0') . "/"
                        . $s . "/" // saluran
                        . ($row['pilihan_raya_type'] ?? '0') . "/"
                        . ($row['pilihan_raya_series'] ?? '0');
                }


                return $row;
            })
            ->values();


        // -------------------------------
        // Step 6: Grand total
        // -------------------------------
        $grandTotal = $pengundi->count();

        return response()->json([
            'draw' => intval($request->draw ?? 1),
            'recordsTotal' => $grandTotal,
            'recordsFiltered' => $grandTotal,
            'saluranTotals' => $saluranTotals,
            'year' => $year,
            'data' => $dataByLokaliti
        ]);
    }


    public function list_details(
        $parlimen = null,
        $dun_kod = null,
        $dm = null,
        $lokaliti = null,
        $saluran = null,
        $pilihan_raya_type = null,
        $pilihan_raya_series = null
    ) {
        // -------------------------------
        // Step 0: Determine year
        // -------------------------------
        $year = $pilihan_raya_series && isset($this->PRUMAP[$pilihan_raya_series])
            ? $this->PRUMAP[$pilihan_raya_series]
            : date('Y');

        // -------------------------------
        // Step 1: Filter DM
        // -------------------------------
        $dmQuery = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            });

        if ($parlimen) {
            $dmQuery->whereIn('kod_dun', function ($q) use ($parlimen) {
                $q->select('kod_dun')->from('dun')->where('parlimen_id', $parlimen);
            });
        }

        if ($dun_kod) {
            $dmQuery->where('kod_dun', $dun_kod);
        }

        if ($dm) {
            $dmQuery->where('koddm', $dm);
        }

        $validDMs = $dmQuery->pluck('koddm')->toArray();

        // -------------------------------
        // Step 2: Filter Lokaliti
        // -------------------------------
        $lokalitiQuery = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs);

        if ($lokaliti) {
            $lokalitiQuery->where('kod_lokaliti', $lokaliti);
        }

        $validLokaliti = $lokalitiQuery
            ->select('kod_lokaliti', 'nama_lokaliti', 'koddm')
            ->get()
            ->keyBy('kod_lokaliti');

        $validLokalitiCodes = $validLokaliti->keys()->toArray();

        // -------------------------------
        // Step 3: Filter Pengundi
        // -------------------------------
        $pengundiQuery = DB::table('pengundi')
            ->where('type_data_id', 2)
            ->whereIn('kod_lokaliti', $validLokalitiCodes);

        if ($lokaliti) {
            $pengundiQuery->where('kod_lokaliti', $lokaliti);
        }

        if ($saluran) {
            $pengundiQuery->where('saluran', $saluran);
        }

        $pengundi = $pengundiQuery
            ->select('*')
            ->get()
            ->map(function ($p) use ($validLokaliti) {
                $p->nama_lokaliti = $validLokaliti[$p->kod_lokaliti]->nama_lokaliti ?? null;
                return $p;
            });

        // -------------------------------
        // Step 4: Get names for Parlimen, DUN, DM
        // -------------------------------
        $parlimenName = $parlimen
            ? DB::table('parlimen')->where('id', $parlimen)->value('namapar')
            : null;

        $dunName = $dun_kod
            ? DB::table('dun')->where('kod_dun', $dun_kod)->value('namadun')
            : null;

        $dmName = $dm
            ? DB::table('dm')->where('koddm', $dm)->value('namadm')
            : null;

        $lokalitiName = ($lokaliti && isset($validLokaliti[$lokaliti]))
            ? $validLokaliti[$lokaliti]->nama_lokaliti
            : null;

        // -------------------------------
        // Step 5: Build breadcrumbs
        // -------------------------------
        $crumbs = [
            ['label' => 'Pengundi'],
            ['label' => 'List', 'url' => route('pengundi.list')],

        ];

        if ($parlimenName)
            $crumbs[] = ['label' => "$parlimenName"];
        if ($dunName)
            $crumbs[] = ['label' => "$dunName"];
        if ($dmName)
            $crumbs[] = ['label' => "$dmName"];
        if ($lokalitiName)
            $crumbs[] = ['label' => "$lokalitiName"];
        if ($pilihan_raya_type)
            $crumbs[] = ['label' => "$pilihan_raya_type"];
        if ($pilihan_raya_series)
            $crumbs[] = ['label' => "$pilihan_raya_series"];
        if ($saluran)
            $crumbs[] = ['label' => "Saluran $saluran"];

        // -------------------------------
        // Step 6: Return Blade page
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
        // Read filters from POST
        $parlimen = $request->input('parlimen');
        $dun_kod = $request->input('dun');
        $dm = $request->input('dm');
        $lokaliti = $request->input('lokaliti');
        $saluran = $request->input('saluran');
        $pilihan_raya_type = $request->input('pilihan_raya_type');
        $pilihan_raya_series = $request->input('pilihan_raya_series');

        $year = $pilihan_raya_series && isset($this->PRUMAP[$pilihan_raya_series])
            ? $this->PRUMAP[$pilihan_raya_series]
            : date('Y');

        // Filter DM
        $validDMs = DB::table('dm')
            ->whereYear('effective_from', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->whereYear('effective_to', '>=', $year)
                    ->orWhereNull('effective_to');
            })
            ->when($parlimen, fn($q) => $q->whereIn('kod_dun', function ($q2) use ($parlimen) {
                $q2->select('kod_dun')->from('dun')->where('parlimen_id', $parlimen);
            }))
            ->when($dun_kod, fn($q) => $q->where('kod_dun', $dun_kod))
            ->when($dm, fn($q) => $q->where('koddm', $dm))
            ->pluck('koddm')
            ->toArray();

        // Filter Lokaliti
        $validLokaliti = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs)
            ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
            ->select('kod_lokaliti', 'nama_lokaliti', 'koddm')
            ->get()
            ->keyBy('kod_lokaliti');

        $validLokalitiCodes = $validLokaliti->keys()->toArray();

        // Filter Pengundi
        $pengundi = DB::table('pengundi')
            ->where('type_data_id', 2)
            ->whereIn('kod_lokaliti', $validLokalitiCodes)
            ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
            ->when($saluran, fn($q) => $q->where('saluran', $saluran))
            ->select('*')
            ->get();

        // Return plain JSON
        return response()->json([
            'success' => true,
            'year' => $year,
            'count' => $pengundi->count(),
            'data' => $pengundi
        ]);
    }

}

