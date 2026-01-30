<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PengundiRawImport;

class PengundiImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new PengundiRawImport, $request->file('file'));

        return back()->with('success', 'Data pengundi berjaya diimport');
    }
}
