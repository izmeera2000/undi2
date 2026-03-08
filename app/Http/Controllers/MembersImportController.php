<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ImportMembersJob;

class MembersImportController extends Controller
{
    protected string $cacheKey = 'members_import_progress';

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:30720',
        ]);

        $path = $request->file('file')->store('imports');

 

        ImportMembersJob::dispatch($path);

        return response()->json([
            'success' => 'Import started in background'
        ]);
    }

public function importProgress()
{
    $progress = Cache::get($this->cacheKey, [
        'count' => 0,
        'total' => 1
    ]);

    if ($progress['count'] >= $progress['total']) {
        Cache::forget($this->cacheKey);
    }

    return response()->json($progress);
}
}