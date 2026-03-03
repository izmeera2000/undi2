<?php

use Livewire\Component;
use App\Models\Event;
use Carbon\Carbon;

new class extends Component {
    public $events = [];
    public $loadFailed = false;

    public function mount()
    {
        $this->loadEvents();
    }

    public function loadEvents()
    {
        try {
            $today = Carbon::today();

            $this->events = Event::whereDate('start_date', '>=', $today)
                ->orderBy('start_date')
                ->take(5)
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->start_date->toDateTimeString(),
                        'end' => $event->end_date?->toDateTimeString(),
                        'allDay' => $event->allDay,
                        'backgroundColor' => $event->backgroundColor ?? '#0d6efd'
                    ];
                })->toArray();

            $this->loadFailed = false;
        } catch (\Exception $e) {
            logger()->error("Events Widget Failed: " . $e->getMessage());
            $this->loadFailed = true;
            $this->events = [];
        }
    }
}; 
?>

@placeholder
<div class="card mb-3 shadow-sm border-0">
    <div class="card-header bg-white border-bottom-0 pt-3">
        <div class="skeleton-shimmer" style="width: 120px; height: 18px; border-radius: 4px;"></div>
    </div>
    <div class="card-body p-2">
        @for ($i = 0; $i < 3; $i++)
            <div class="d-flex mb-3">
                <div class="skeleton-shimmer me-2" style="width: 40px; height: 45px; border-radius: 4px;"></div>
                <div class="flex-grow-1">
                    <div class="skeleton-shimmer mb-2" style="width: 70%; height: 16px; border-radius: 4px;"></div>
                    <div class="skeleton-shimmer" style="width: 40%; height: 12px; border-radius: 4px;"></div>
                </div>
            </div>
        @endfor
    </div>
    <style>
        .skeleton-shimmer {
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 800px 104px; 
            animation: event-shimmer 1.5s infinite linear;
        }
        @keyframes event-shimmer {
            0% { background-position: -468px 0; }
            100% { background-position: 468px 0; }
        }
    </style>
</div>
@endplaceholder

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3 fw-bold">Upcoming Events</div>
    <div class="card-body p-2">
        @if($loadFailed)
            <div class="text-center text-muted py-4">
                <i class="bi bi-exclamation-circle text-danger" style="font-size:32px; opacity:.5;"></i>
                <div class="mt-2 fw-semibold">Couldn't load events</div>
                <button wire:click="loadEvents" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-arrow-clockwise"></i> Retry
                </button>
            </div>
        @elseif(count($events) === 0)
            <div class="text-center text-muted py-4">
                <i class="bi bi-calendar-x text-warning" style="font-size:36px; opacity:.4;"></i>
                <div class="mt-2 fw-semibold">No upcoming events</div>
                <div class="small">Check back later for new events.</div>
            </div>
        @else
            @foreach($events as $event)
                @php
                    $start = Carbon::parse($event['start']);
                    $end = $event['end'] ? Carbon::parse($event['end']) : null;
                    $day = $start->format('d');
                    $month = $start->format('M');
                    $timeText = $event['allDay'] ? 'All Day' : $start->format('H:i') . ($end ? ' - ' . $end->format('H:i') : '');
                @endphp
                
                {{-- Added wire:key to ensure Livewire tracks each item correctly --}}
                <div wire:key="event-{{ $event['id'] }}" class="upcoming-event-item d-flex mb-2 p-1 rounded-2">
                    <div class="upcoming-event-color rounded-start" style="background: {{ $event['backgroundColor'] }}; width:6px;"></div>
                    
                    <div class="upcoming-event-date text-center px-2 bg-light rounded-end me-2" style="min-width: 50px;">
                        <div class="fw-bold" style="font-size: 1.1rem; line-height: 1.2;">{{ $day }}</div>
                        <div class="small text-uppercase text-muted" style="font-size: 0.7rem;">{{ $month }}</div>
                    </div>
                    
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark text-truncate" style="font-size: 0.95rem; max-width: 180px;">
                            {{ $event['title'] }}
                        </div>
                        <div class="upcoming-event-time text-muted" style="font-size: 0.85rem;">
                            <i class="bi bi-clock me-1"></i>{{ $timeText }}
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>