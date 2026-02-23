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
    const toastr = new ToastMagic();

$(document).ready(function() {


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
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
            submitLogout(formId);
        }
    } catch (e) {
        console.error('Logout failed', e);
    }
}
</script>
