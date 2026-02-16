<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dun;
use App\Models\Pengundi;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\User;
use App\Notifications\NewPengundiNotification;

use Illuminate\Routing\Controller;

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

        // $user = User::find(1);

        // $user->notify(new NewPengundiNotification("New Pengundi registered"));

        return Pdf::loadView('pengundi.pdf', [
            'charts' => $charts
        ])
            ->setPaper('a4', 'portrait')
            ->stream('pengundi-analytics.pdf');
    }


















    public function importpage()
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

}
