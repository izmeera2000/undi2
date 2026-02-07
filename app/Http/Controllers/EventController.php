<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Return events for calendar (JSON)
     */
    public function index()
    {
        $events = Event::with('participants')
            ->where(function ($query) {
                $query->where('created_by', Auth::id())
                      ->orWhereHas('participants', function ($q) {
                          $q->where('user_id', Auth::id());
                      });
            })
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date,
                    'end'   => $event->end_date,
                    'allDay'=> $event->all_day,
                    'color' => $event->color,
                ];
            });

        return response()->json($events);
    }

    /**
     * Store new event
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'color' => 'nullable|string',
            'participants' => 'nullable|array'
        ]);

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'all_day' => $request->all_day ?? false,
            'color' => $request->color ?? '#3788d8',
            'created_by' => Auth::id(),
        ]);

        if ($request->participants) {
            $event->participants()->sync($request->participants);
        }

        return response()->json(['success' => true, 'event' => $event]);
    }

    /**
     * Show single event
     */
    public function show(Event $event)
    {
        $this->authorizeAccess($event);

        return response()->json($event->load('participants', 'creator'));
    }

    /**
     * Update event
     */
    public function update(Request $request, Event $event)
    {
        $this->authorizeCreator($event);

        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'color' => 'nullable|string',
            'participants' => 'nullable|array'
        ]);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'all_day' => $request->all_day ?? false,
            'color' => $request->color ?? '#3788d8',
        ]);

        if ($request->participants) {
            $event->participants()->sync($request->participants);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete event
     */
    public function destroy(Event $event)
    {
        $this->authorizeCreator($event);

        $event->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Ensure user is creator
     */
    private function authorizeCreator($event)
    {
        if ($event->created_by !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Ensure user can access event
     */
    private function authorizeAccess($event)
    {
        if (
            $event->created_by !== Auth::id() &&
            !$event->participants->contains(Auth::id())
        ) {
            abort(403, 'Unauthorized');
        }
    }
}
