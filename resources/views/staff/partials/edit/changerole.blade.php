<script>

    $(document).on('click', '.change-role', function () {
        let role = $(this).data('role');
        let userId = $('#dangerZone').data('user-id');

        if (!confirm('Change role to ' + role + '?')) return;

        $.post({
            url: `/staff/${userId}/role`,
            data: {
                role: role,
                _token: '{{ csrf_token() }}'
            },
            success: function () {
                toast.success('Role updated successfully!');
                location.reload();
            },
            error: function () {
                toast.error('Failed to change role');
            }
        });
    });
</script>