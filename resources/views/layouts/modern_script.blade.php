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
                const res = await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    item.classList.remove('bg-light', 'unread');

                    // Update badge
                    let count = Math.max((parseInt(badge.innerText, 10) || 0) - 1, 0);
                    if (count <= 0) {
                        badge.style.display = 'none';
                        countSpan.innerText = '0 new';
                    } else {
                        badge.innerText = count;
                        countSpan.innerText = count + ' new';
                    }

                    if (url && url !== '#') window.location.href = url;
                }
            } catch (err) {
                console.error('Notification error:', err);
                if (url && url !== '#') window.location.href = url;
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

                    item.href = notification.url || '#';
                    item.dataset.id = notification.id;

                    item.className =
                        "notification-item d-flex gap-3 p-3 border-bottom unread bg-light";

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

                            <div class="notification-time small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i>
                                just now
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