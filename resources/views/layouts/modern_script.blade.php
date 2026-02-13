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


@php

    use Devrabiul\ToastMagic\Facades\ToastMagic;
@endphp
{!! ToastMagic::scripts() !!}


@vite(['resources/js/app.js'])

<script>

    const toast = new ToastMagic();


    document.addEventListener('DOMContentLoaded', function () {

        const notificationList = document.getElementById('notification-list');

        notificationList.addEventListener('click', function (e) {

            const item = e.target.closest('.notification-item');
            if (!item) return;

            e.preventDefault(); // stop navigation first

            const id = item.dataset.id;
            const url = item.getAttribute('href');

            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        // Remove unread style
                        item.classList.remove('bg-light', 'unread');

                        // Update badge
                        const badge = document.getElementById('notification-badge');
                        const countSpan = document.getElementById('notification-count');

                        if (badge) {
                            let count = parseInt(badge.innerText) || 0;
                            count--;

                            if (count <= 0) {
                                badge.style.display = 'none';
                                countSpan.innerText = '0 new';
                            } else {
                                badge.innerText = count;
                                countSpan.innerText = count + ' new';
                            }
                        }

                        // Navigate after marking as read
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

    });
</script>