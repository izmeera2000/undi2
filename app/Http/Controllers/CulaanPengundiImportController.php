<?php

namespace App\Http\Controllers;

use App\Jobs\CulaanPengundiImportJob;
use App\Models\Culaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class CulaanPengundiImportController extends Controller
{
    protected string $importCacheKey = 'culaan_import_progress';

    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:csv|max:51200', // 50MB
                'culaan_id' => 'required|exists:culaans,id',
            ]);

            $file = $request->file('file');

            // Save uploaded file to temporary location
            $path = $file->store('culaan_import');

            // Dispatch job to queue
            CulaanPengundiImportJob::dispatch($request->culaan_id, $path, auth()->id());
            // Activity log
            $culaan = Culaan::find($request->culaan_id);

            activity()
                ->performedOn($culaan)
                ->causedBy(auth()->user())
                ->withProperties([
                    'file_name' => $file->getClientOriginalName(),
                    'path' => $path,
                ])
                ->log('queued pengundi import');

            return response()->json([
                'success' => 'File queued for import. It may take a few minutes for large files.'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Invalid CSV file'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importProgress($culaanId)
    {
        return response()->json(
            Cache::get("culaan_import_progress_{$culaanId}", [
                'count' => 0,
                'total' => 1
            ])
        );
    }
}