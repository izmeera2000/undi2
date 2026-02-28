
<!-- Notifications -->
<div class="header-action dropdown notification-dropdown">
    <button class="dropdown-toggle position-relative"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            title="Notifications">

        <i class="bi bi-bell fs-5"></i>

        @php 
        $unreadCount = auth()->user()->unreadNotifications->count(); 
        @endphp

        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill"
              id="notification-badge"
              style="{{ $unreadCount ? '' : 'display:none;' }}">
            {{ $unreadCount }}
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow-lg p-0"
         style="width: 350px; max-height: 500px; overflow: hidden;">

        <!-- Header -->
        <div class="notification-header d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="mb-0 fw-bold">Notifications</h6>
            <span class="text-muted small" id="notification-count">
                {{ $unreadCount }} new
            </span>
        </div>

        <!-- Notification List -->
        <div class="notification-list" id="notification-list"
             style="max-height: 380px; overflow-y: auto;">

            @forelse (auth()->user()->notifications->take(10) as $notification)

                <a href="{{ $notification->data['url'] ?? '#' }}"
                   class="dropdown-item notification-item py-3 {{ is_null($notification->read_at) ? 'bg-light unread' : '' }}"
                   data-id="{{ $notification->id }}">

                    <div class="d-flex align-items-start gap-3">

                        <div class="notification-avatar text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width:40px;height:40px;
                             background:
                             @switch($notification->data['type'] ?? 'info')
                                @case('success') #198754 @break
                                @case('warning') #ffc107 @break
                                @case('danger') #dc3545 @break
                                @default #0d6efd
                             @endswitch;">

                            <i class="bi bi-bell"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="fw-semibold">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </div>

                            <div class="small text-muted">
                                {{ $notification->data['message'] ?? '' }}
                            </div>

                            <div class="small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i>
                                {{ $notification->created_at  }}
                            </div>
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



 