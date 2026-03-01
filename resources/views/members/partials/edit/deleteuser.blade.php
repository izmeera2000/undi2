


  <script>
    $('#deleteUserBtn').on('click', function () {
        let userId = $('#dangerZone').data('user-id');

        if (!confirm('This will permanently remove the account. Continue?')) return;

        $.ajax({
            url: `/members/${userId}`,
            type: 'DELETE',
            data: { _token: document.querySelector('meta[name="csrf-token"]').content },
            success: function () {
                toastr.success('User deleted');
                window.location.href = "{{ route('members.list') }}";
            }
        });
    });
</script>
