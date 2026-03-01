<script>

    $(document).on('click', '.change-role', function () {
        let role = $(this).data('role');
        let userId = $('#dangerZone').data('user-id');

        if (!confirm('Change role to ' + role + '?')) return;

        $.post({
            url: `/staff/${userId}/role`,
            data: {
                role: role,
                _token: document.querySelector('meta[name="csrf-token"]').content
            },
            success: function () {
                toastr.success('Role updated successfully!');
                location.reload();
            },
            error: function () {
                toastr.error('Failed to change role');
            }
        });
    });
</script>