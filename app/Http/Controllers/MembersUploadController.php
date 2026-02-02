<?php


namespace App\Http\Controllers;
use App\Imports\MembersRawImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;


class MembersUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:20480'
        ]);

        Excel::import(new MembersRawImport, $request->file('file'));

        return back()->with('success', 'Excel berjaya dimuat naik');
    }
}
