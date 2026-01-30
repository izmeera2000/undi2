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


        Excel::queueImport(
            new PengundiRawImport,
            $request->file('file')
        );

        return back()->with(
            'success',
            'Import sedang diproses di background'
        );
    }
}
