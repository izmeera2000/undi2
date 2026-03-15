<!-- Notifications -->
<div class="header-action dropdown notification-dropdown">
    <button class="dropdown-toggle position-relative" data-bs-toggle="dropdown" aria-expanded="false"
        title="Notifications">

        <i class="bi bi-bell fs-5"></i>

        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        <span class="badge"
            id="notification-badge" style="{{ $unreadCount ? '' : 'display:none;' }}">
            {{ $unreadCount }}
        </span>
    </button>



    <div class="dropdown-menu dropdown-menu-end shadow-lg p-0"
        style="width: 350px; max-height: 500px; overflow: hidden;">

        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
            $notifications = auth()->user()
                ->notifications()
                ->latest()
                ->take(5)
                ->get();
        @endphp

        <!-- Header -->
        <div class="notification-header d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="mb-0 fw-bold">Notifications</h6>
            <span class="notification-count text-muted small">
                {{ $unreadCount }} new
            </span>
        </div>

        <!-- Notification List -->
        <div class="notification-list" style="max-height: 380px; overflow-y: auto;">

            @forelse ($notifications as $notification)

                @php
                    $type = $notification->data['notify_type'] ?? 'primary';
                    $icon = $notification->data['icon'] ?? 'bi-bell';
                
                    $url = $notification->data['url'] ?? '#';
                    $isPdf = str_ends_with($url, '.pdf'); // Laravel 9+ helper
                @endphp

                <a href="{{ $url }}"
                    class="notification-item d-flex gap-3 p-3 border-bottom {{ is_null($notification->read_at) ? 'unread bg-primary-light' : '' }}"
                    data-id="{{ $notification->id }}" {{ $isPdf ? 'target=_blank' : '' }}>

                    <div
                        class="notification-avatar {{ $type }} rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi {{ $icon }}"></i>
                    </div>

                    <div class="notification-content flex-grow-1">
                        <div class="notification-title fw-semibold">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </div>

                        <div class="notification-text small text-muted">
                            {{ $notification->data['message'] ?? '' }}
                        </div>

                        <div class="notification-time small text-muted mt-1"
                            data-time="{{ $notification->created_at->toISOString() }}">
                            <i class="bi bi-clock me-1"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                </a>

            @empty
                <div class="text-center p-4 text-muted">
                    No notifications
                </div>
            @endforelse

        </div>

        <!-- Footer -->
        <div class="notification-footer text-center border-top p-2">
            <a href="{{ route('notifications.index') }}" class="text-decoration-none small">
                View all notifications
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

    </div>



</div>