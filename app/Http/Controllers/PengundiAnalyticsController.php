<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

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
        /*
        |--------------------------------------------------------------------------
        | 0️⃣ Extract Inputs
        |--------------------------------------------------------------------------
        */

        $type1 = $request->input('type1', 'PRU');
        $series1 = (int) $request->input('series1', 12);
        $mode = $request->input('mode', 'single');

        $type2 = $request->input('type2');
        $series2 = $request->input('series2');

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Resolve Election Years From Database
        |--------------------------------------------------------------------------
        */

        $year1 = DB::table('elections')
            ->where('type', $type1)
            ->where('number', $series1)
            ->value('year');

        if (!$year1) {
            return response()->json([
                'error' => 'Invalid first PR type/series'
            ], 400);
        }

        $year2 = null;

        if ($mode === 'compare' && $type2 && $series2) {

            $year2 = DB::table('elections')
                ->where('type', $type2)
                ->where('number', (int) $series2)
                ->value('year');

            if (!$year2) {
                return response()->json([
                    'error' => 'Invalid second PR type/series'
                ], 400);
            }
        }
        /*
        |----------------------------------------------------------------------
        | 1️⃣ Base Filters
        |----------------------------------------------------------------------
        */
        $parlimen = 1; // static for now
        $dun_kod = $request->input('dun');
        $dm = $request->input('dm');
        $lokaliti = $request->input('lokaliti');

        $extraFilters = $request->only([
            'jantina',
            'status_umno',
            'status_baru',
            'negeri'
        ]);

        /*
        |----------------------------------------------------------------------
        | 2️⃣ Determine Valid DM & Lokaliti (Effective Dates)
        |----------------------------------------------------------------------
        */
        $resolveValidDMs = function ($year) use ($parlimen, $dun_kod, $dm) {
            return DB::table('dm')
                ->when($parlimen, fn($q) => $q->whereIn('kod_dun', function ($sub) use ($parlimen) {
                    $sub->select('kod_dun')->from('dun')->where('parlimen_id', $parlimen);
                }))
                ->when($dun_kod, fn($q) => $q->where('kod_dun', $dun_kod))
                ->when($dm, fn($q) => $q->where('koddm', $dm))
                ->whereYear('effective_from', '<=', $year)
                ->where(function ($q) use ($year) {
                    $q->whereYear('effective_to', '>=', $year)->orWhereNull('effective_to');
                })
                ->select('koddm', 'namadm', 'kod_dun')
                ->get();
        };

        $resolveValidLokaliti = function ($year, $validDMCodes) use ($lokaliti) {
            return DB::table('lokaliti')
                ->whereIn('koddm', $validDMCodes)
                ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
                ->whereYear('effective_from', '<=', $year)
                ->where(function ($q) use ($year) {
                    $q->whereYear('effective_to', '>=', $year)->orWhereNull('effective_to');
                })
                ->select('kod_lokaliti', 'nama_lokaliti', 'koddm')
                ->get();
        };

        $validDMs1 = $resolveValidDMs($year1);
        $validDMCodes1 = $validDMs1->pluck('koddm')->toArray();
        $validLokaliti1 = $resolveValidLokaliti($year1, $validDMCodes1);
        $validLokalitiCodes1 = $validLokaliti1->pluck('kod_lokaliti')->toArray();

        $validDMs2 = collect();
        $validLokaliti2 = collect();
        $validLokalitiCodes2 = [];
        if ($mode === 'compare' && $year2) {
            $validDMs2 = $resolveValidDMs($year2);
            $validDMCodes2 = $validDMs2->pluck('koddm')->toArray();
            $validLokaliti2 = $resolveValidLokaliti($year2, $validDMCodes2);
            $validLokalitiCodes2 = $validLokaliti2->pluck('kod_lokaliti')->toArray();
        }

        // Merge valid lokaliti codes for query
        $mergedLokalitiCodes = array_unique(array_merge($validLokalitiCodes1, $validLokalitiCodes2));
        if (empty($mergedLokalitiCodes))
            return response()->json(['message' => 'No valid Lokaliti found']);

        /*
        |----------------------------------------------------------------------
        | 3️⃣ Base Query Builder (Handle Single & Compare PR)
        |----------------------------------------------------------------------
        */
        $baseQuery = DB::table('pengundi as p')
            ->whereIn('p.kod_lokaliti', $mergedLokalitiCodes);

        if ($mode === 'compare' && $type2 && $series2) {
            $baseQuery->where(function ($q) use ($type1, $series1, $type2, $series2) {
                $q->where([['p.pilihan_raya_type', $type1], ['p.pilihan_raya_series', $series1]])
                    ->orWhere([['p.pilihan_raya_type', $type2], ['p.pilihan_raya_series', $series2]]);
            });
        } else {
            $baseQuery->where('p.pilihan_raya_type', $type1)
                ->where('p.pilihan_raya_series', $series1);
        }

        foreach ($extraFilters as $col => $val) {
            if ($val !== null && $val !== '')
                $baseQuery->where("p.$col", $val);
        }

        /*
        |----------------------------------------------------------------------
        | 4️⃣ Jantina Chart
        |----------------------------------------------------------------------
        */
        // Dataset 1
        $jantinaChart1 = (clone $baseQuery)
            ->selectRaw("
        CASE 
            WHEN p.jantina = 'L' THEN 'Lelaki'
            WHEN p.jantina = 'P' THEN 'Perempuan'
        END AS jantina,
        status_umno,
        COUNT(*) as total
    ")
            ->groupBy('jantina', 'status_umno')
            ->get();

        // Dataset 2
        $jantinaChart2 = (clone $baseQuery)
            ->selectRaw("
        CASE 
            WHEN p.jantina = 'L' THEN 'Lelaki'
            WHEN p.jantina = 'P' THEN 'Perempuan'
        END AS jantina,
        status_umno,
        COUNT(*) as total
    ")
            ->groupBy('jantina', 'status_umno')
            ->get();

        /*
        |----------------------------------------------------------------------
        | 5️⃣ Umur Chart
        |----------------------------------------------------------------------
        */
        $umurChart1 = (clone $baseQuery)
            ->selectRaw("
        CASE
            WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
            WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
            WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
            WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
            WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
            ELSE '60+'
        END AS umur_group,
        p.status_umno,
        p.status_baru,
        COUNT(*) as total
    ")
            ->groupBy(
                DB::raw("
            CASE
                WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END
        "),
                'p.status_umno',
                'p.status_baru'
            )
            ->get();

        $umurChart2 = null;
        if ($mode === 'compare' && $year2) {
            $umurChart2 = (clone $baseQuery)
                ->selectRaw("
        CASE
            WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
            WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
            WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
            WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
            WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
            ELSE '60+'
        END AS umur_group,
        p.status_umno,
        p.status_baru,
        COUNT(*) as total
    ")
                ->groupBy(
                    DB::raw("
            CASE
                WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END
        "),
                    'p.status_umno',
                    'p.status_baru'
                )
                ->get();

        }
        /*
        |----------------------------------------------------------------------
        | 6️⃣ Bangsa Chart
        |----------------------------------------------------------------------
        */
        $bangsaChart1 = (clone $baseQuery)
            ->selectRaw("
        CASE
            WHEN LOWER(p.bangsa) LIKE '%melayu%' THEN 'Melayu'
            WHEN LOWER(p.bangsa) LIKE '%cina%' THEN 'Cina'
            WHEN LOWER(p.bangsa) LIKE '%india%' THEN 'India'
            ELSE 'Lain-lain'
        END AS bangsa_group,
        CASE
            WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
            WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
            WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
            WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
            WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
            ELSE '60+'
        END AS umur_group,
        p.status_umno AS status_umno,
        COUNT(*) as total
    ")
            ->groupBy('bangsa_group', 'umur_group', 'status_umno')
            ->get();


        $bangsaChart2 = null;
        if ($mode === 'compare' && $year2) {
            $bangsaChart2 = (clone $baseQuery)
                ->where([['p.pilihan_raya_type', $type2], ['p.pilihan_raya_series', $series2]])
                ->selectRaw("
            CASE
                WHEN LOWER(p.bangsa) LIKE '%melayu%' THEN 'Melayu'
                WHEN LOWER(p.bangsa) LIKE '%cina%' THEN 'Cina'
                WHEN LOWER(p.bangsa) LIKE '%india%' THEN 'India'
                ELSE 'Lain-lain'
            END AS bangsa_group,
            CASE
                WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,
            p.status_umno AS status_umno,
            COUNT(*) as total
        ")
                ->groupBy('bangsa_group', 'umur_group', 'status_umno')
                ->get();
        }

        /*
        |----------------------------------------------------------------------
        | 7️⃣ Negeri Chart
        |----------------------------------------------------------------------
        */
        // Chart for year1
        $negeriChart1 = (clone $baseQuery)
            ->where([['p.pilihan_raya_type', $type1], ['p.pilihan_raya_series', $series1]])
            ->selectRaw("
        COALESCE(p.negeri, 'UNKNOWN') AS negeri,
        p.status_umno AS status_umno,
        p.status_baru AS status_baru,
        COUNT(*) AS total
    ")
            ->groupBy('negeri', 'status_umno', 'status_baru')
            ->get();

        // Chart for year2 (compare mode)
        $negeriChart2 = null;
        if ($mode === 'compare' && $year2) {
            $negeriChart2 = (clone $baseQuery)
                ->where([['p.pilihan_raya_type', $type2], ['p.pilihan_raya_series', $series2]])
                ->selectRaw("
            COALESCE(p.negeri, 'UNKNOWN') AS negeri,
            p.status_umno AS status_umno,
            p.status_baru AS status_baru,
            COUNT(*) AS total
        ")
                ->groupBy('negeri', 'status_umno', 'status_baru')
                ->get();
        }
        /*
        |----------------------------------------------------------------------
        | 8️⃣ DM × Umur Chart
        |----------------------------------------------------------------------
        */
        $buildDMUmurChart = function ($query, $year) {
            return (clone $query)
                ->join('lokaliti as l', function ($join) use ($year) {
                    $join->on('p.kod_lokaliti', '=', 'l.kod_lokaliti')
                        ->whereYear('l.effective_from', '<=', $year)
                        ->where(function ($q) use ($year) {
                            $q->whereYear('l.effective_to', '>=', $year)->orWhereNull('l.effective_to');
                        });
                })
                ->join('dm as d', function ($join) use ($year) {
                    $join->on('l.koddm', '=', 'd.koddm')
                        ->whereYear('d.effective_from', '<=', $year)
                        ->where(function ($q) use ($year) {
                            $q->whereYear('d.effective_to', '>=', $year)->orWhereNull('d.effective_to');
                        });
                })
                ->join('dun as du', 'd.kod_dun', '=', 'du.kod_dun')
                ->selectRaw("
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
                COUNT(DISTINCT p.id) AS total
            ")->groupBy('du.namadun', 'd.namadm', 'umur_group')
                ->get();
        };

        $dmUmurChart1 = $buildDMUmurChart($baseQuery, $year1);
        $dmUmurChart2 = null;
        if ($mode === 'compare' && $year2) {
            $dmUmurChart2 = $buildDMUmurChart($baseQuery, $year2);
        }

        /*
        |----------------------------------------------------------------------
        | 9️⃣ Totals
        |----------------------------------------------------------------------
        */
        // Base query for year1
        $baseQuery1 = clone $baseQuery;
        $totals1 = $baseQuery1->selectRaw("
        COUNT(*) AS total_pengundi,
        SUM(p.status_umno=1) AS total_umno,
        SUM(p.status_baru=1) AS total_first_time
    ")->first();

        // If year2 exists, compute totals for year2
        $totals2 = null;
        if ($year2) {
            $baseQuery2 = (clone $baseQuery)->whereYear('p.date_field', $year2); // adjust column if needed
            $totals2 = $baseQuery2->selectRaw("
            COUNT(*) AS total_pengundi,
            SUM(p.status_umno=1) AS total_umno,
            SUM(p.status_baru=1) AS total_first_time
        ")->first();
        }

        // Return JSON dynamically
        return response()->json([
            'year1' => $year1,
            'year2' => $year2,
            'validDMs1' => $validDMs1,
            'validDMs2' => $validDMs2,
            'validLokaliti1' => $validLokaliti1,
            'validLokaliti2' => $validLokaliti2,
            'jantinaChart1' => $jantinaChart1,
            'jantinaChart2' => $jantinaChart2,
            'umurChart1' => $umurChart1,
            'umurChart2' => $umurChart2,
            'bangsaChart1' => $bangsaChart1,
            'bangsaChart2' => $bangsaChart2,
            'negeriChart1' => $negeriChart1,
            'negeriChart2' => $negeriChart2,
            'dmUmurChart1' => $dmUmurChart1,
            'dmUmurChart2' => $dmUmurChart2,
            'totals1' => $totals1,
            'totals2' => $totals2,
            'mode' => $mode,
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
        // ----------------------------
        // 1️⃣ Get distinct pilihan_raya_type, pilihan_raya_series, and saluran
        // ----------------------------
        $pengundiData = Pengundi::where('type_data_id', 2)
            ->select('pilihan_raya_type', 'pilihan_raya_series', 'saluran')
            ->distinct()
            ->get();

        $pilihanRayaTypes = $pengundiData->pluck('pilihan_raya_type')->unique()->sort()->values();
        $pilihanRayaSeries = $pengundiData->pluck('pilihan_raya_series')->unique()->sort()->values();
        $saluranList = $pengundiData->pluck('saluran')->unique()->sort()->values();

        return view('pengundi.list', compact(
            'pilihanRayaTypes',
            'pilihanRayaSeries',
            'saluranList'
        ));
    }

    public function getHierarchyByPru(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'series' => 'required|integer',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Resolve Election Year From DB
        |--------------------------------------------------------------------------
        */

        $selectedPRUYear = DB::table('elections')
            ->where('type', $request->type)
            ->where('number', $request->series)
            ->value('year');

        if (!$selectedPRUYear) {
            return response()->json([
                'error' => 'Invalid election type/series',
                'test' => $selectedPRUYear,
            ], 400);
        }

        $selectedPRUDate = $selectedPRUYear . '-12-31';

        /*
        |--------------------------------------------------------------------------
        | Build Hierarchy Query
        |--------------------------------------------------------------------------
        */

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
                $join->on('lokaliti.koddm', '=', 'dm.koddm')
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
            ->join('parlimen', 'dun.parlimen_id', '=', 'parlimen.id')
            ->select(
                'pengundi.kod_lokaliti',
                'lokaliti.koddm',
                'lokaliti.nama_lokaliti',
                'dm.kod_dun',
                'dm.namadm',
                'dun.parlimen_id',
                'dun.namadun',
                'parlimen.namapar'
            )
            ->distinct()
            ->get();

        return response()->json($data);
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
        $selectedPRUYear = DB::table('elections')
            ->where('type', $type)
            ->where('number', $series)
            ->value('year');

        if (!$selectedPRUYear) {
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $selectedPRUDate = $selectedPRUYear . '-12-31';


        // -------------------------------
        // Step 2: Build query
        // -------------------------------
        $pengundi = DB::table(function ($query) use ($type, $series, $parlimen, $dun, $dm, $selectedPRUDate) {
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
                    $join->on('l.koddm', '=', 'd.koddm')
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
                ->where('p.pilihan_raya_type', $type)
                ->where('p.pilihan_raya_series', $series)
                ->where('dn.parlimen_id', $parlimen)
                ->where('d.kod_dun', $dun)
                ->where('l.koddm', $dm);
        }, 'p') // alias the subquery as p
            ->selectRaw("
    p.kod_lokaliti,
    p.nama_lokaliti,
    SUM(CASE WHEN p.saluran = 1 THEN 1 ELSE 0 END) AS saluran_1,
    SUM(CASE WHEN p.saluran = 2 THEN 1 ELSE 0 END) AS saluran_2,
    SUM(CASE WHEN p.saluran = 3 THEN 1 ELSE 0 END) AS saluran_3,
    SUM(CASE WHEN p.saluran = 4 THEN 1 ELSE 0 END) AS saluran_4,
    SUM(CASE WHEN p.saluran = 5 THEN 1 ELSE 0 END) AS saluran_5,
    SUM(CASE WHEN p.saluran = 6 THEN 1 ELSE 0 END) AS saluran_6,
    SUM(CASE WHEN p.saluran = 7 THEN 1 ELSE 0 END) AS saluran_7,
    COUNT(*) AS total
")
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
        // Step 0: Determine year
        // -------------------------------
        $year = null;

        if ($pilihan_raya_type && $pilihan_raya_series) {
            $year = DB::table('elections')
                ->where('type', $pilihan_raya_type)
                ->where('number', $pilihan_raya_series)
                ->value('year');
        }

        // fallback to current year if no record found
        $year = $year ?: date('Y');

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
        if ($pilihan_raya_type)
            $crumbs[] = ['label' => "$pilihan_raya_type"];
        if ($pilihan_raya_series)
            $crumbs[] = ['label' => "$pilihan_raya_series"];
        if ($parlimenName)
            $crumbs[] = ['label' => "$parlimenName"];
        if ($dunName)
            $crumbs[] = ['label' => "$dunName"];
        if ($dmName)
            $crumbs[] = ['label' => "$dmName"];
        if ($lokalitiName)
            $crumbs[] = ['label' => "$lokalitiName"];

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
        $parlimen = $request->input('parlimen');
        $dun_kod = $request->input('dun');
        $dm = $request->input('dm');
        $lokaliti = $request->input('lokaliti');
        $saluran = $request->input('saluran');
        $pilihan_raya_type = $request->input('pilihan_raya_type');
        $pilihan_raya_series = $request->input('pilihan_raya_series');

        $year = null;

        if ($pilihan_raya_type && $pilihan_raya_series) {
            $year = DB::table('elections')
                ->where('type', $pilihan_raya_type)
                ->where('number', $pilihan_raya_series)
                ->value('year');
        }

        // fallback to current year if no record found
        $year = $year ?: date('Y');

        // Step 1: Get valid DMs
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

        // Step 2: Get valid Lokaliti
        $validLokalitiCodes = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs)
            ->when($lokaliti, fn($q) => $q->where('kod_lokaliti', $lokaliti))
            ->pluck('kod_lokaliti')
            ->toArray();

        // Step 3: Build query for pengundi
        $query = DB::table('pengundi')
            ->where('type_data_id', 2)
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
                (str_contains($file, '_merged') || str_contains($file, '_summary'));
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


}




