<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->range ?? 30;

        $query = Activity::with('causer')->latest();

        // Date filter
        if ($range === 'today') {
            $query->whereDate('created_at', today());
        } elseif (in_array($range, [7, 30, 90])) {
            $query->where('created_at', '>=', now()->subDays($range));
        }

        $logs = $query->get()->map(function ($log) {

            $model = class_basename($log->subject_type);

            // 🔥 Icon mapping here
            $icon = match ($model) {
                'Task' => 'bi-list-check',
                'Pengundi' => 'bi-people',
                'User' => 'bi-person',
                'Dun' => 'bi-geo-alt',
                default => 'bi-circle',
            };

            // 🔥 Color based on event
            $color = match ($log->event) {
                'created' => 'success',
                'updated' => 'info',
                'deleted' => 'danger',
                default => 'primary',
            };

            $log->model_name = $model;
            $log->icon = $icon;
            $log->color = $color;

            return $log;
        })->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d');
        });

        return view('activity_logs.index', compact('logs', 'range'));
    }


    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->back()
            ->with('success', 'Activity log deleted successfully.');
    }

    public function clear()
    {
        Activity::query()->delete();

        return redirect()->back()
            ->with('success', 'All activity logs cleared.');
    }
}
