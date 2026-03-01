

<script>
    $(document).ready(function () {
        $('#saveProfileBtn').on('click', function (e) {
            e.preventDefault();

            let formData = {
                name: $('input[name="name"]').val(),
                bio: $('textarea[name="bio"]').val(),
                email: $('input[name="email"]').val(),
                phone: $('input[name="phone"]').val(),
                address: $('input[name="address"]').val(),
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            $.ajax({
                url: '/staff/{{ $staff->id ?? auth()->id() }}/profile',
                method: 'POST',
                data: formData,
                success: function (res) {
                    if (res && res.user) {
                        $('input[name="name"]').val(res.user.name);
                        $('textarea[name="bio"]').val(res.user.profile?.bio ?? '');
                        $('input[name="phone"]').val(res.user.profile?.phone ?? '');
                        $('input[name="address"]').val(res.user.profile?.address ?? '');

                        toastr.info('Profile updated successfully!');
                    } else {
                        toastr.error('Failed to update profile. Invalid response data.');
                    }
                },
                error: function (err) {
                    console.log('Error:', err);
                    toastr.error('Failed to update profile. Check your input.');
                }
            });
        });
    });
</script>