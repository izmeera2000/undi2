<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dun;
use App\Models\Pengundi;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\User;
use App\Notifications\NewPengundiNotification;


class PengundiAnalyticsController extends Controller
{
    //


    public function dropdowns()
    {
        // Get all DUNs
        $duns = Dun::orderBy('namadun')->get();

        $years = Pengundi::selectRaw('tarikh_undian as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        // Pass to the view
        return view('pengundi.analysis', compact('duns', 'years'));
    }

    public function index(Request $request)
    {




        $filters = $request->only([
            'dm_id',
            'tarikh_undian',
            'jantina',
            'status_umno',
            'status_baru',
            'negeri',
        ]);

        // Base query with joins
        $query = DB::table('pengundi')
            ->join('dm', 'pengundi.dm_id', '=', 'dm.id')
            ->join('dun', 'dm.dun_id', '=', 'dun.id')
            ->selectRaw("
            dun.namadun,
            dm.namadm,
            pengundi.negeri,  
            CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,
            CASE
                WHEN jantina = 'L' THEN 'Lelaki'
                WHEN jantina = 'P' THEN 'Perempuan'
                ELSE jantina
            END AS jantina2,
            CASE
                WHEN LOWER(bangsa) LIKE '%melayu%' THEN 'Melayu'
                WHEN LOWER(bangsa) LIKE '%cina%' OR LOWER(bangsa) LIKE '%chinese%' THEN 'Cina'
                WHEN LOWER(bangsa) LIKE '%india%' THEN 'India'
                ELSE 'Lain-lain'
            END AS bangsa_group,
            jantina,
            status_umno,
            status_baru,
            tarikh_undian,
            COUNT(*) AS total
        ");

        if ($request->mode === 'compare' && $request->year1 && $request->year2) {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        } elseif ($request->year1) {
            $query->where('pengundi.tarikh_undian', $request->year1);
        }

        // Apply filters safely
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where("pengundi.$column", $value);
            }
        }

        // Analytics data
        $analytics = $query
            ->groupBy([
                'dun.namadun',
                'dm.namadm',
                'pengundi.negeri',
                'umur_group',
                'bangsa_group',
                'jantina',
                'status_umno',
                'status_baru',
                'tarikh_undian',
            ])
            ->get();

        // Totals (reuse filters)
        $totalsQuery = DB::table('pengundi');

        if ($request->mode === 'compare' && $request->year1 && $request->year2) {
            $totalsQuery->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        } elseif ($request->year1) {
            $totalsQuery->where('pengundi.tarikh_undian', $request->year1);
        }

        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $totalsQuery->where("pengundi.$column", $value);
            }
        }

        $totals = $totalsQuery
            ->selectRaw("
        tarikh_undian,
        COUNT(*) AS total_pengundi,
        SUM(status_umno = 1) AS total_umno,
        SUM(status_baru = 1) AS total_first_time_voter
    ")
            ->groupBy('tarikh_undian')
            ->get();

        return response()->json([
            'cube' => $analytics,
            'totals' => $totals,
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



























    public function overview(Request $request)
    {
        $query = DB::table('pengundi')
            ->selectRaw("
            CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,

            CASE
                WHEN bangsa IN ('MELAYU', 'CINA', 'INDIA') THEN bangsa
                ELSE 'LAIN-LAIN'
            END AS bangsa_group,

            YEAR(tarikh_undian) AS tahun,

            COUNT(*) AS total
        ");


        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        } elseif ($request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }





        // 🔹 Grouping
        $data = $query
            ->groupBy('umur_group', 'bangsa_group', 'tahun')
            ->orderBy('tahun')
            ->orderByRaw("FIELD(bangsa_group, 'MELAYU', 'CINA', 'INDIA', 'LAIN-LAIN')")
            ->get();

        return response()->json($data);
    }



    public function jantina(Request $request)
    {
        $query = DB::table('pengundi')
            ->selectRaw("
            CASE
                WHEN jantina = 'P' THEN 'Perempuan'
                WHEN jantina = 'L' THEN 'Lelaki'
                ELSE jantina
            END AS jantina,
            COUNT(*) as total
        ");

        // 🔹 Apply filters if needed

        if ($request->mode !== 'compare' && $request->year) {
            $query->where('tarikh_undian', $request->year);
        }

        // COMPARE
        if ($request->mode === 'compare') {
            $query->whereIn('tarikh_undian', [$request->year1, $request->year2]);
        }



        $data = $query->groupBy('jantina')->get();

        return response()->json($data);
    }

    public function overviewByJantina(Request $request)
    {
        $query = DB::table('pengundi')
            ->selectRaw("
            CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,

            CASE
                WHEN jantina = 'P' THEN 'Perempuan'
                WHEN jantina = 'L' THEN 'Lelaki'
                ELSE jantina
            END AS jantina,

            COUNT(*) AS total
        ");


        if ($request->mode !== 'compare' && $request->year) {
            $query->where('tarikh_undian', $request->year);
        }

        // COMPARE
        if ($request->mode === 'compare') {
            $query->whereIn('tarikh_undian', [$request->year1, $request->year2]);
        }


        $data = $query
            ->groupBy('umur_group', 'jantina')
            ->orderByRaw("
            CASE 
                WHEN umur_group = '18-20' THEN 1
                WHEN umur_group = '21-29' THEN 2
                WHEN umur_group = '30-39' THEN 3
                WHEN umur_group = '40-49' THEN 4
                WHEN umur_group = '50-59' THEN 5
                ELSE 6
            END
        ")
            ->get();

        return response()->json($data);
    }




    public function ahliumno(Request $request)
    {
        $query = DB::table('pengundi')

            ->selectRaw("
             CASE
                WHEN status_umno = 1 THEN 'Ahli UMNO'
                ELSE 'Bukan Ahli'
            END AS status_ahli,
            COUNT(*) AS total
        ");

        /* =======================
            FILTERS
        ========================*/

        // NORMAL MODE
        if ($request->mode !== 'compare' && $request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }

        // COMPARE MODE
        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        }

        if ($request->year) {
            $query->where('tarikh_undian', $request->year);
        }



        $data = $query
            ->groupBy('status_ahli')
            ->get();

        return response()->json($data);
    }



    public function overviewByAhliumno(Request $request)
    {
        $query = DB::table('pengundi')

            ->selectRaw("
             CASE
                WHEN status_umno = 1 THEN 'Ahli UMNO'
                ELSE 'Bukan Ahli'
            END AS status_ahli,

                        CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,


            COUNT(*) AS total
        ");

        /* =======================
            FILTERS
        ========================*/

        // NORMAL MODE
        if ($request->mode !== 'compare' && $request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }

        // COMPARE MODE
        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        }

        if ($request->year) {
            $query->where('tarikh_undian', $request->year);
        }




        $data = $query
            ->groupBy('umur_group', 'status_ahli')
            ->orderByRaw("
            CASE 
                WHEN umur_group = '18-20' THEN 1
                WHEN umur_group = '21-29' THEN 2
                WHEN umur_group = '30-39' THEN 3
                WHEN umur_group = '40-49' THEN 4
                WHEN umur_group = '50-59' THEN 5
                ELSE 6
            END
        ")
            ->get();

        return response()->json($data);
    }



    public function dundm(Request $request)
    {
        $query = DB::table('pengundi')
            ->join('dm', 'pengundi.dm_id', '=', 'dm.id')
            ->join('dun', 'dm.dun_id', '=', 'dun.id')
            ->selectRaw("
 
            dun.namadun,
            dm.namadm,
            COUNT(*) AS total
        ");

        /* =======================
            FILTERS
        ========================*/

        // NORMAL MODE
        if ($request->mode !== 'compare' && $request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }

        // COMPARE MODE
        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        }

        if ($request->year) {
            $query->where('tarikh_undian', $request->year);
        }




        $data = $query
            ->groupBy('dun.namadun', 'dm.namadm')
            ->get();

        return response()->json($data);
    }


    public function overviewByDundm(Request $request)
    {
        $query = DB::table('pengundi')
            ->join('dm', 'pengundi.dm_id', '=', 'dm.id')
            ->join('dun', 'dm.dun_id', '=', 'dun.id')
            ->selectRaw("
            dun.namadun,
            dm.namadm,

                        CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,


            COUNT(*) AS total
        ");

        /* =======================
            FILTERS
        ========================*/

        // NORMAL MODE
        if ($request->mode !== 'compare' && $request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }

        // COMPARE MODE
        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        }

        if ($request->year) {
            $query->where('tarikh_undian', $request->year);
        }




        $data = $query
            ->groupBy('umur_group', 'dm.namadm', 'dun.namadun')
            ->orderByRaw("
            CASE 
                WHEN umur_group = '18-20' THEN 1
                WHEN umur_group = '21-29' THEN 2
                WHEN umur_group = '30-39' THEN 3
                WHEN umur_group = '40-49' THEN 4
                WHEN umur_group = '50-59' THEN 5
                ELSE 6
            END
        ")
            ->get();

        return response()->json($data);
    }








    public function overviewByDundmSpecDun(Request $request)
    {
        $query = DB::table('pengundi')
            ->join('dm', 'pengundi.dm_id', '=', 'dm.id')
            ->join('dun', 'dm.dun_id', '=', 'dun.id')
            ->selectRaw("
            dun.namadun,
            dm.namadm,

                        CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,


            COUNT(*) AS total
        ");

        /* =======================
            FILTERS
        ========================*/

        // NORMAL MODE
        if ($request->mode !== 'compare' && $request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }

        // COMPARE MODE
        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        }

        if ($request->year) {
            $query->where('tarikh_undian', $request->year);
        }

        if ($request->dun) {
            $query->where('dun.namadun', $request->dun);
        }



        $data = $query
            ->groupBy('umur_group', 'dm.namadm', 'dun.namadun')
            ->orderByRaw("
            CASE 
                WHEN umur_group = '18-20' THEN 1
                WHEN umur_group = '21-29' THEN 2
                WHEN umur_group = '30-39' THEN 3
                WHEN umur_group = '40-49' THEN 4
                WHEN umur_group = '50-59' THEN 5
                ELSE 6
            END
        ")
            ->get();

        return response()->json($data);
    }







    public function overviewByFirstTime(Request $request)
    {
        $query = DB::table('pengundi')

            ->selectRaw("
              CASE
                WHEN status_baru = 1 THEN 'First Time'
                ELSE 'Bukan First Time'
            END AS status_baru2,

                        CASE
                WHEN umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,


            COUNT(*) AS total
        ");

        /* =======================
            FILTERS
        ========================*/

        // NORMAL MODE
        if ($request->mode !== 'compare' && $request->year) {
            $query->where('pengundi.tarikh_undian', $request->year);
        }

        // COMPARE MODE
        if ($request->mode === 'compare') {
            $query->whereIn('pengundi.tarikh_undian', [
                $request->year1,
                $request->year2
            ]);
        }

        if ($request->year) {
            $query->where('tarikh_undian', $request->year);
        }



        $data = $query
            ->groupBy('umur_group', 'status_baru2')
            ->orderByRaw("
            CASE 
                WHEN umur_group = '18-20' THEN 1
                WHEN umur_group = '21-29' THEN 2
                WHEN umur_group = '30-39' THEN 3
                WHEN umur_group = '40-49' THEN 4
                WHEN umur_group = '50-59' THEN 5
                ELSE 6
            END
        ")
            ->get();

        return response()->json($data);
    }

}
