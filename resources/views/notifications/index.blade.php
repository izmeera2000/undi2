@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumb')
    @php
        $crumbs[] = ['label' => 'Notifications', 'url' => route('notifications.index')];
      @endphp
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">

@endpush

@section('content')
    <section class="section">
        <div class="row">
            <div class="col">
                <div class="card notif-inbox-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-sm btn-outline-success" id="markSelectedBtn">Mark Selected as
                                Read</button>

                            <button class="btn btn-sm btn-outline-secondary" id="markAllBtn">Mark All as Read</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="notificationsTable">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="selectAllNotif">
                                    </th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Time</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(auth()->user()->notifications as $notification)
                                    @php
                                        $data = $notification->data;
                                    @endphp
                                    <tr class="notification-row {{ is_null($notification->read_at) ? 'bg-primary-light' : 'read' }}"
                                        data-id="{{ $notification->id }}">

                                        <td>
                                            <input type="checkbox" class="notif-checkbox">
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $data['notify_type'] ?? 'secondary' }}">
                                                {{ strtoupper($data['notify_type'] ?? 'info') }}
                                            </span>
                                        </td>

                                        <td>{{ $data['title'] ?? '-' }}</td>

                                        <td>{{ $data['message'] ?? '-' }}</td>

                                        <td>{{ $notification->created_at->diffForHumans() }}</td>

                                        <td>
                                            @if(isset($data['file']))
                                                <a href="{{ Storage::url($data['file']) }}" target="_blank"
                                                    class="btn btn-sm btn-primary">
                                                    PDF
                                                </a>
                                            @endif

                                            @if(is_null($notification->read_at))
                                                <button class="btn btn-sm btn-success mark-read-btn">
                                                    Read
                                                </button>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(auth()->user()->notifications->isEmpty())
                            <p class="text-center py-4">No notifications yet.</p>
                        @endif
                    </div>


                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')

    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        $(document).ready(function () {

            // Init DataTable
            let table = $('#notificationsTable').DataTable({
                order: [[4, 'desc']], // initially sort by column index 4 (zero-based)
                pageLength: 10,
                columnDefs: [
                    { targets: [0, 1], orderable: false }, // columns 0 and 1 cannot be sorted
                    { targets: '_all', orderable: true }   // all other columns sortable
                ]
            });

            // Custom search (link your existing search box)
            $('#notifSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Select all checkbox
            $('#selectAllNotif').on('click', function () {
                $('.notif-checkbox').prop('checked', this.checked);
            });

            // Mark individual as read
            $('#notificationsTable').on('click', '.mark-read-btn', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const btn = $(this);

                fetch(`/notifications/mark-as-read/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            row.removeClass('table-warning');
                            btn.remove();
                        }
                    });
            });

            // Mark all as read
            $('#markAllBtn').on('click', function () {
                fetch(`{{ route('notifications.allread') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            $('#notificationsTable tr').removeClass('table-warning');
                            $('.mark-read-btn').remove();

                            const badge = document.getElementById('notification-badge');
                            if (badge) badge.style.display = 'none';
                        }
                    });
            });

        });

        // Mark selected as read
        $('#markSelectedBtn').on('click', function () {

            // Collect IDs of checked notifications
            let selectedIds = [];
            $('#notificationsTable .notif-checkbox:checked').each(function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                if (id) selectedIds.push(id);
            });

            if (selectedIds.length === 0) {
                alert('Please select at least one notification.');
                return;
            }

            fetch(`{{ route('notifications.markSelected') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids: selectedIds })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update rows UI
                        selectedIds.forEach(id => {
                            const row = $(`#notificationsTable tr[data-id="${id}"]`);
                            row.removeClass('table-warning');
                            row.find('.mark-read-btn').remove();
                        });

                        // Update badge
                        const badge = document.getElementById('notification-badge');
                        const countSpan = document.querySelector('.notification-count');

                        if (badge) {
                            let current = parseInt(badge.innerText || '0', 10);
                            let newCount = Math.max(current - selectedIds.length, 0);
                            badge.innerText = newCount;
                            if (newCount === 0) badge.style.display = 'none';
                        }

                        if (countSpan) {
                            let current = parseInt(countSpan.innerText || '0', 10);
                            let newCount = Math.max(current - selectedIds.length, 0);
                            countSpan.innerText = newCount + ' new';
                        }
                    }
                })
                .catch(err => console.error('Error marking selected notifications as read:', err));
        });
    </script>
@endpush