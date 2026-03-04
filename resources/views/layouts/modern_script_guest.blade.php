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

@php
    use Devrabiul\ToastMagic\Facades\ToastMagic;
@endphp
{!! ToastMagic::scripts() !!}

@vite(['resources/js/app.js'])

<script>
    // ==========================
    // ToastMagic Init
    // ==========================
    const toastr = new ToastMagic();

    // ==========================
    // Global AJAX / Fetch Setup
    // ==========================
    document.addEventListener('DOMContentLoaded', () => {

        // jQuery AJAX CSRF setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

    });

    // ==========================
    // Logout Function (async + retry)
    // ==========================
    async function submitLogout(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        const tokenInput = form.querySelector('input[name="_token"]');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenInput.value,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                window.location.href = '/';
            } 
            else if (response.status === 419) {
                // CSRF token expired, refresh it
                const tokenRes = await fetch("{{ route('csrf.refresh') }}");
                const data = await tokenRes.json();
                tokenInput.value = data.csrf_token;

                // Retry logout
                await submitLogout(formId);
            } 
            else {
                console.error('Logout failed with status:', response.status);
            }

        } catch (error) {
            console.error('Logout failed', error);
        }
    }
</script>