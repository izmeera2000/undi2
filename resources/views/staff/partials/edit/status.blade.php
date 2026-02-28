 
<script>
    $(document).ready(function () {
        $('#statusBtn').on('click', function () {
            let userId = $(this).data('user-id');
            let currentStatus = $(this).data('status'); // active, suspended, etc.
            let action = currentStatus === 'active' ? 'suspend' : 'activate';
            let confirmMsg = currentStatus === 'active' ? 'Suspend this account?' : 'Activate this account?';

            if (!confirm(confirmMsg)) return;

            $.post(`/staff/${userId}/${action}`, { _token: document.querySelector('meta[name="csrf-token"]').content }, function (res) {
                if (res.success) {
                    // Update button text & status dynamically
                    let newStatus = action === 'suspend' ? 'suspended' : 'active';
                    $('#statusBtn')
                        .text(newStatus === 'active' ? 'Suspend' : 'Activate')
                        .data('status', newStatus)
                        .removeClass('btn-outline-warning btn-outline-success')
                        .addClass(newStatus === 'active' ? 'btn-outline-warning' : 'btn-outline-success');

                    // Optionally update status badge elsewhere on page
                    $('#profileStatusBadge')
                        .removeClass()
                        .addClass('badge ' + getStatusBadgeClass(newStatus))
                        .text(capitalizeFirstLetter(newStatus));

                    toast.success(`User is now ${newStatus}`);
                }
            });
        });

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'active': return 'bg-success';
                case 'suspended': return 'bg-danger';
                case 'not_yet_login': return 'bg-warning';
                default: return 'bg-secondary';
            }
        }

        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
</script>
