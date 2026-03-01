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

<!-- App Sidebar Toggle (for app pages with sidebars) -->
<script src="{{ asset('assets/js/apps-sidebar-toggle.js') }}"></script>
@livewireScripts

@php

    use Devrabiul\ToastMagic\Facades\ToastMagic;
@endphp
{!! ToastMagic::scripts() !!}



<script>

    const toastr = new ToastMagic();

    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });


        const notificationList = document.getElementById('notification-list');

        if (notificationList) { // <-- check if element exists
            notificationList.addEventListener('click', function (e) {

                const item = e.target.closest('.notification-item');
                if (!item) return;

                e.preventDefault();

                const id = item.dataset.id;
                const url = item.getAttribute('href');

                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    }).then(data => {
                        if (data.success) {
                            item.classList.remove('bg-light', 'unread');

                            const badge = document.getElementById('notification-badge');
                            const countSpan = document.getElementById('notification-count');

                            if (badge) {
                                let count = Math.max((parseInt(badge.innerText, 10) || 0) - 1, 0);
                                if (count <= 0) {
                                    badge.style.display = 'none';
                                    countSpan.innerText = '0 new';
                                } else {
                                    badge.innerText = count;
                                    countSpan.innerText = count + ' new';
                                }
                            }

                            if (url && url !== '#') {
                                window.location.href = url;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Notification error:', error);
                        if (url && url !== '#') {
                            window.location.href = url;
                        }
                    });

            });
        }

    });
</script>


<script>
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
                // CSRF expired → fetch new token
                const tokenRes = await fetch("{{ route('csrf.refresh') }}");
                const data = await tokenRes.json();
                form.querySelector('input[name=_token]').value = data.csrf_token;

                // Retry logout automatically
                await submitLogout(formId);
            }
        } catch (e) {
            console.error('Logout failed', e);
        }
    }
</script>