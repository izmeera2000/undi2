<!-- Vendor JS Files -->
<script src="{{ asset('assets/vendors/jquery/jquery-3.7.1.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('assets/vendors/echarts/echarts.min.js') }}"></script>
<script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
<script src="{{ asset('assets/vendors/quill/quill.js') }}"></script>
<script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/vendors/choices.js/choices.min.js') }}"></script>
<script src="{{ asset('assets/vendors/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/vendors/php-email-form/validate.js') }}"></script>

<!-- Template Main JS Files -->
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/js/apps-sidebar-toggle.js') }}"></script>


@vite(['resources/js/app.js'])
@livewireScripts


{!! Devrabiul\ToastMagic\Facades\ToastMagic::scripts() !!}
@include('sweetalert2::index')

<script>
    const toastr = new ToastMagic();

    // Setup CSRF for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

    const notificationList = document.querySelector('.notification-list');
    const badge = document.getElementById('notification-badge');
    const countSpan = document.querySelector('.notification-count');

    // Click to mark notification as read
    if (notificationList) {
        notificationList.addEventListener('click', async function (e) {
            const item = e.target.closest('.notification-item');
            if (!item) return;

            e.preventDefault();

            const id = item.dataset.id;
            const url = item.getAttribute('href');

            try {
                // Mark as read via AJAX
                const res = await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();

                if (data.success) {
                    // Remove unread styling
                    item.classList.remove('bg-primary-light', 'unread');

                    // Update badge count
                    let count = Math.max((parseInt(badge.innerText, 10) || 0) - 1, 0);
                    badge.innerText = count;
                    countSpan.innerText = count > 0 ? count + ' new' : '0 new';
                    badge.style.display = count > 0 ? '' : 'none';
                }

                // Navigate to the notification URL
                if (url && url !== '#') {
                    // Open PDFs in new tab
                    if (url.endsWith('.pdf')) {
                        window.open(url, '_blank');
                    } else {
                        window.location.href = url;
                    }
                }

            } catch (err) {
                console.error('Notification error:', err);

                // Fallback navigation
                if (url && url !== '#') {
                    if (url.endsWith('.pdf')) {
                        window.open(url, '_blank');
                    } else {
                        window.location.href = url;
                    }
                }
            }
        });
    }


    // Logout helper
    async function submitLogout(formId) {
        const form = document.getElementById(formId);
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (res.ok) {
                window.location = '/';
            } else if (res.status === 419) {
                // Refresh CSRF token
                const tokenRes = await fetch("{{ route('csrf.refresh') }}");
                const data = await tokenRes.json();
                form.querySelector('input[name=_token]').value = data.csrf_token;

                // Retry logout
                await submitLogout(formId);
            }
        } catch (e) {
            console.error('Logout failed', e);
        }
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const userId = "{{ auth()->id() }}";




        if (window.Echo) {

            // console.log('Echo listening for user', userId);

            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {

                    // console.log('Real-time notification:', notification);

                    // Toast
                    toastr.success(notification.message, notification.title);

                    if (notificationList) {

                        const item = document.createElement('a');

                        const url = notification.url || '#';
                        item.href = url;
                        item.dataset.id = notification.id;

                        // Open PDF links in new tab
                        if (url.endsWith('.pdf')) {
                            item.target = '_blank';
                        }



                        item.className =
                            "notification-item d-flex gap-3 p-3 border-bottom unread bg-primary-light";

                        const now = new Date().toISOString(); // current timestamp

                        item.innerHTML = `
                        <div class="notification-avatar ${notification.notify_type ?? 'primary'} rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi ${notification.icon ?? 'bi-bell'}"></i>
                        </div>

                        <div class="notification-content flex-grow-1">
                            <div class="notification-title fw-semibold">
                                ${notification.title ?? 'Notification'}
                            </div>

                            <div class="notification-text small text-muted">
                                ${notification.message ?? ''}
                            </div>

                            <div class="notification-time small text-muted mt-1" data-time="${now}">
                                <i class="bi bi-clock me-1"></i>Just now
                            </div>
                        </div>
                    `;

                        // prepend to top
                        notificationList.prepend(item);

                        // limit list to 5 items (same as blade)
                        const items = notificationList.querySelectorAll('.notification-item');
                        if (items.length > 5) {
                            items[items.length - 1].remove();
                        }
                    }

                    // Update badge
                    if (badge) {

                        let count = parseInt(badge.innerText || '0', 10) + 1;

                        badge.innerText = count;

                        if (countSpan) {
                            countSpan.innerText = count + ' new';
                        }
                    }

                });

        } else {
            console.log('Echo is not loaded');
        }

    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const notificationButton = document.querySelector('.notification-dropdown button');

        function formatTimeAgo(dateString) {
            const time = new Date(dateString);
            const now = new Date();

            const seconds = Math.floor((now - time) / 1000);

            if (seconds < 60) return "Just now";

            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + (minutes === 1 ? " minute ago" : " minutes ago");

            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + (hours === 1 ? " hour ago" : " hours ago");

            const days = Math.floor(hours / 24);
            return days + (days === 1 ? " day ago" : " days ago");
        }
        function updateNotificationTimes() {

            document.querySelectorAll('.notification-time').forEach(el => {

                const timestamp = el.dataset.time;
                if (!timestamp) return;

                const text = formatTimeAgo(timestamp);

                el.innerHTML = `<i class="bi bi-clock me-1"></i>${text}`;
            });

        }

        if (notificationButton) {

            notificationButton.addEventListener('shown.bs.dropdown', function () {
                updateNotificationTimes();
            });

        }

    });
</script>