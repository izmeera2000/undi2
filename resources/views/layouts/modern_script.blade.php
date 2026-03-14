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
 @livewireScripts

@php
    use Devrabiul\ToastMagic\Facades\ToastMagic;
@endphp
{!! ToastMagic::scripts() !!}
@include('sweetalert2::index')

<script>
    const toastr = new ToastMagic();

    // Setup CSRF for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

    const notificationList = document.getElementById('notification-list');
    const badge = document.getElementById('notification-badge');
    const countSpan = document.getElementById('notification-count');

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

 
    const userId = "{{ auth()->id() }}";

    if (window.Echo) {
        console.log('Echo is loaded, listening for notifications for user', userId);

        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                console.log('Real-time notification received:', notification); // <-- log the notification

                // Show toast
                toastr.success(notification.message, notification.title);

                // Prepend notification to list
                if (notificationList) {
                    const item = document.createElement('a');
                    item.href = notification.url || '#';
                    item.className = 'notification-item d-flex gap-3 p-3 border-bottom unread bg-light';
                    item.dataset.id = notification.id;

                    item.innerHTML = `
                        <div class="notification-avatar ${notification.type} rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi ${notification.icon}"></i>
                        </div>
                        <div class="notification-content flex-grow-1">
                            <div class="notification-title fw-semibold">${notification.title}</div>
                            <div class="notification-text small text-muted">${notification.message}</div>
                            <div class="notification-time small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i>${notification.created_at || ''}
                            </div>
                        </div>
                    `;
                    notificationList.prepend(item);
                }

                // Update badge count
                let count = parseInt(badge.innerText || '0', 10) + 1;
                badge.innerText = count;
                badge.style.display = 'inline-block';
                countSpan.innerText = count + ' new';
            });
    } else {
        console.log('Echo is not loaded');
    }
</script>