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

        // Base query with robust join: try dm_id first, fallback to koddm
        $query = DB::table('pengundi as p')
            ->leftJoin('dm as d', function ($join) {
                $join->on('p.dm_id', '=', 'd.id')
                    ->orOn('p.koddm', '=', 'd.koddm');
            })
            ->leftJoin('dun as du', 'd.dun_id', '=', 'du.id')
            ->selectRaw("
            du.namadun,
            d.namadm,
            p.negeri,  

            CASE
                WHEN p.umur BETWEEN 18 AND 20 THEN '18-20'
                WHEN p.umur BETWEEN 21 AND 29 THEN '21-29'
                WHEN p.umur BETWEEN 30 AND 39 THEN '30-39'
                WHEN p.umur BETWEEN 40 AND 49 THEN '40-49'
                WHEN p.umur BETWEEN 50 AND 59 THEN '50-59'
                ELSE '60+'
            END AS umur_group,

            CASE
                WHEN p.jantina = 'L' THEN 'Lelaki'
                WHEN p.jantina = 'P' THEN 'Perempuan'
                ELSE p.jantina
            END AS jantina2,

            CASE
                WHEN LOWER(p.bangsa) LIKE '%melayu%' THEN 'Melayu'
                WHEN LOWER(p.bangsa) LIKE '%cina%' OR LOWER(p.bangsa) LIKE '%chinese%' THEN 'Cina'
                WHEN LOWER(p.bangsa) LIKE '%india%' THEN 'India'
                ELSE 'Lain-lain'
            END AS bangsa_group,

            p.jantina,
            p.status_umno,
            p.status_baru,
            p.tarikh_undian,
            COUNT(*) AS total
        ");

        // Apply year filters
        if ($request->mode === 'compare' && $request->year1 && $request->year2) {
            $query->whereIn('p.tarikh_undian', [$request->year1, $request->year2]);
        } elseif ($request->year1) {
            $query->where('p.tarikh_undian', $request->year1);
        }

        // Apply additional filters
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where("p.$column", $value);
            }
        }

        // Analytics data
        $analytics = $query
            ->groupBy([
                'du.namadun',
                'd.namadm',
                'p.negeri',
                'umur_group',
                'bangsa_group',
                'p.jantina',
                'p.status_umno',
                'p.status_baru',
                'p.tarikh_undian',
            ])
            ->get();

        // Totals query (reuse filters)
        $totalsQuery = DB::table('pengundi as p');

        if ($request->mode === 'compare' && $request->year1 && $request->year2) {
            $totalsQuery->whereIn('p.tarikh_undian', [$request->year1, $request->year2]);
        } elseif ($request->year1) {
            $totalsQuery->where('p.tarikh_undian', $request->year1);
        }

        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $totalsQuery->where("p.$column", $value);
            }
        }

        $totals = $totalsQuery
            ->selectRaw("
            p.tarikh_undian,
            COUNT(*) AS total_pengundi,
            SUM(p.status_umno = 1) AS total_umno,
            SUM(p.status_baru = 1) AS total_first_time_voter
        ")
            ->groupBy('p.tarikh_undian')
            ->get();

        return response()->json([
            'cube' => $analytics,
            'totals' => $totals,
        ]);
    }








    public function generatePdf(Request $request)
    {
        $charts = $request->input('charts');

        $user = User::find(1);

        $user->notify(new NewPengundiNotification("New Pengundi registered"));

        return Pdf::loadView('pengundi.pdf', [
            'charts' => $charts
        ])
            ->setPaper('a4', 'portrait')
            ->stream('pengundi-analytics.pdf');
    }


























}
