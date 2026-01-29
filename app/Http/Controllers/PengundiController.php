<?php

namespace App\Http\Controllers;

use App\Models\Pengundi;
use Illuminate\Http\Request;

class PengundiController extends Controller
{ 
    public function index()
{
    $pengundi = Pengundi::with([
        'locality:id,name,dm_id,code',
        'locality.dm:id,name,dun_id,code',
        'locality.dm.dun:id,name,code,parliament_id',
        'locality.dm.dun.parliament:id,name,code'
    ])->get([
        'id', 'locality_id', 'bangsa', 'jantina', 'umur', 'status'
    ]);

    return response()->json([
        'status' => 'success',
        'data' => $pengundi
    ]);
}



}
