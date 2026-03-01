

   <script>
    $('#savePasswordBtn').on('click', function () {
        let userId = $('#staff_hidden_id').val();
        let password = $('#newPassword').val();
        let confirm = $('#confirmPassword').val();

        if (!password || !confirm) {
            toastr.warning('Please fill both password fields');
            return;
        }

        $.ajax({
            url: `/staff/${userId}/change-password`,
            method: 'POST',
            data: {
                password: password,
                password_confirmation: confirm,
                _token: document.querySelector('meta[name="csrf-token"]').content
            },
            success: function (res) {
                toastr.success(res.message);

                $('#newPassword').val('');
                $('#confirmPassword').val('');
            },
            error: function (err) {
                console.log(err);

                if (err.responseJSON?.errors) {
                    toastr.error(Object.values(err.responseJSON.errors)[0][0]);
                } else {
                    toastr.error('Failed to change password');
                }
            }
        });
    });
</script>

