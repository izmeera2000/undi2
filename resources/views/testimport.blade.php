@extends('layouts.app')

@section('title', 'Notification Test')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <h3>Laravel Echo Notification Test</h3>
        <button id="sendNotificationBtn" class="btn btn-primary mb-3">Send Test Notification</button>

        <ul id="notification-list" class="list-group">
            <!-- New notifications will appear here -->
        </ul>

        <span id="notification-badge" class="badge bg-danger" style="display:none;">0</span>
        <span id="notification-count">0 new</span>
    </div>
</div>
@endsection


@push('scripts')
  <script>

    document.getElementById('sendNotificationBtn').addEventListener('click', async () => {
    try {
        const res = await fetch('{{ route("send.test.notification") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        alert(data.status);
    } catch (err) {
        console.error(err);
        alert('Failed to send notification.');
    }
});
  </script>
@endpush