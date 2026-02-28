   

<script>
    $('#revokeSessionsBtn').on('click', function () {
        let userId = $('#dangerZone').data('user-id');

        if (!confirm('Revoke all sessions?')) return;

        $.post({
            url: `/staff/${userId}/suspend`,
            data: { _token: document.querySelector('meta[name="csrf-token"]').content },
            success: function () {
                toast.success('All sessions revoked');
            }
        });
    });
</script>
