@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Notifications', 'url' => route('notifications.index')];
      @endphp
@endsection

@section('content')
    <section class="section">
        <div class="row">
            <div class="col">
                <div class="card notif-inbox-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllNotif">
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" id="markAllBtn">Mark All as Read</button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <input type="text" class="form-control" placeholder="Search notifications..."
                                    id="notifSearch">
                                <button class="btn btn-outline-secondary" type="button"><i
                                        class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @php
                            $grouped = auth()->user()->notifications->groupBy(function ($notif) {
                                $date = $notif->created_at;
                                if ($date->isToday())
                                    return 'Today';
                                if ($date->isYesterday())
                                    return 'Yesterday';
                                return 'This Week';
                            });
                        @endphp

                        @foreach($grouped as $section => $notifications)
                            <div class="notif-section">
                                <div
                                    class="notif-section-header d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                                    <span>{{ $section }}</span>
                                    <span class="notif-section-count">{{ $notifications->count() }} notifications</span>
                                </div>

                                @foreach($notifications as $notification)
                                    @php
                                        $data = $notification->data;
                                    @endphp
                                    <div class="notif-item {{ $notification->data['type'] ?? 'info' }}">
                                        <div class="notif-item-avatar {{ $notification->data['type'] ?? 'info' }}">
                                            <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }}"></i>
                                        </div>
                                        <div class="notif-item-content">
                                            <div class="notif-item-header">
                                                <h6>{{ $notification->data['title'] }}</h6>
                                                <span class="notif-item-time">{{ $notification->data['time'] }}</span>
                                            </div>
                                            <p>{{ $notification->data['message'] }}</p>
                                            @if(isset($notification->data['file']))
                                                <a href="{{ Storage::url($notification->data['file']) }}" class="btn btn-sm btn-primary"
                                                    target="_blank">Download PDF</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        @if(auth()->user()->notifications->isEmpty())
                            <p class="text-center py-4">No notifications yet.</p>
                        @endif
                    </div>

                    <div class="card-footer bg-transparent">
                        <span class="text-muted small">Showing {{ auth()->user()->notifications->count() }}
                            notifications</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Mark individual notification as read
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const card = this.closest('.notif-item');
                const id = card.dataset.id;

                fetch(`/notifications/mark-as-read/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                }).then(res => res.json()).then(data => {
                    if (data.success) card.classList.remove('unread');
                    this.remove();
                });
            });
        });

        // Mark all notifications as read
        document.getElementById('markAllBtn').addEventListener('click', function () {
            fetch(`/notifications/mark-all-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
                    document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
                }
            });
        });
    </script>
@endpush