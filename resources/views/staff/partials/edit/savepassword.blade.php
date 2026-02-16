

   <script>
    $('#savePasswordBtn').on('click', function () {
        let userId = $('#staff_hidden_id').val();
        let password = $('#newPassword').val();
        let confirm = $('#confirmPassword').val();

        if (!password || !confirm) {
            toast.warning('Please fill both password fields');
            return;
        }

        $.ajax({
            url: `/staff/${userId}/change-password`,
            method: 'POST',
            data: {
                password: password,
                password_confirmation: confirm,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                toast.success(res.message);

                $('#newPassword').val('');
                $('#confirmPassword').val('');
            },
            error: function (err) {
                console.log(err);

                if (err.responseJSON?.errors) {
                    toast.error(Object.values(err.responseJSON.errors)[0][0]);
                } else {
                    toast.error('Failed to change password');
                }
            }
        });
    });
</script>

