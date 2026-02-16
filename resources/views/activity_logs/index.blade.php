@extends('layouts.app')

@section('title', 'Activity Log')


@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">


    <section class="section">

      <div class="row">
        <!-- Main Activity Log -->
        <div class="col ">
          <div class="card">
            <div class="card-header">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                <div class="d-flex gap-2">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                      <i class="bi bi-calendar3 me-1"></i>Last 30 days
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item {{ $range == 'today' ? 'active' : '' }}"
                          href="{{ route('activity-logs.index', ['range' => 'today']) }}">
                          Today
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item {{ $range == 7 ? 'active' : '' }}"
                          href="{{ route('activity-logs.index', ['range' => 7]) }}">
                          Last 7 days
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item {{ $range == 30 ? 'active' : '' }}"
                          href="{{ route('activity-logs.index', ['range' => 30]) }}">
                          Last 30 days
                        </a>
                      </li>

                      <li>
                        <a class="dropdown-item {{ $range == 90 ? 'active' : '' }}"
                          href="{{ route('activity-logs.index', ['range' => 90]) }}">
                          Last 90 days
                        </a>
                      </li>
                    </ul>

                  </div>
                </div>
              </div>
            </div>
            <div class="card-body p-0">

              @foreach($logs as $date => $activities)

                @php
                  $carbonDate = \Carbon\Carbon::parse($date);

                  if ($carbonDate->isToday()) {
                    $label = 'Today';
                  } elseif ($carbonDate->isYesterday()) {
                    $label = 'Yesterday';
                  } elseif ($carbonDate->isCurrentWeek()) {
                    $label = 'This Week';
                  } else {
                    $label = $carbonDate->format('d M Y');
                  }
                @endphp

                <div class="activity-group">
                  <div class="activity-group-header">
                    <span class="activity-group-date">{{ $label }}</span>
                    <span class="activity-group-count">
                      {{ $activities->count() }} activities
                    </span>
                  </div>

                  <div class="activity-timeline">

                    @foreach($activities as $log)

                      <div class="activity-timeline-item">
                        <div class="activity-timeline-marker {{ $log->color }}"></div>

                        <div class="activity-timeline-time">
                          {{ $log->created_at->format('h:i A') }}
                        </div>

                        <div class="activity-timeline-content">
                          <div class="activity-timeline-icon {{ $log->color }}">
                            <i class="bi {{ $log->icon }}"></i>
                          </div>

                          <div class="activity-timeline-details">
                            <h6>
                              {{ ucfirst($log->description) }}
                            </h6>

                            <p>
                              {{ $log->causer->name ?? 'System' }}
                              performed
                              {{ $log->event }}
                              on
                              {{ class_basename($log->subject_type) }}
                              #{{ $log->subject_id }}
                            </p>

                            @if($log->properties && $log->event === 'updated')
                              <div class="activity-timeline-changes">
                                @foreach($log->properties['attributes'] ?? [] as $key => $value)
                                  @php
                                    $old = $log->properties['old'][$key] ?? null;
                                  @endphp

                                  <div class="activity-change">
                                    <span class="activity-change-field">
                                      {{ ucfirst($key) }}:
                                    </span>

                                    @if($old)
                                      <span class="activity-change-old">
                                        {{ $old }}
                                      </span>
                                      <i class="bi bi-arrow-right"></i>
                                    @endif

                                    <span class="activity-change-new">
                                      {{ $value }}
                                    </span>
                                  </div>
                                @endforeach
                              </div>
                            @endif

                          </div>
                        </div>
                      </div>

                    @endforeach


                  </div>
                </div>

              @endforeach

            </div>

{{-- 
            <!-- Load More -->
            <div class="card-footer bg-transparent text-center">
              <button class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise me-1"></i> Load More Activity
              </button>
            </div> --}}
          </div>
        </div>

      </div>
    </section>

@endsection