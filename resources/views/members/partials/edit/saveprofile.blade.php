<script>
$(document).ready(function () {
    $('#saveProfileBtn').on('click', function (e) {
        e.preventDefault();

        let formData = {
            name: $('input[name="name"]').val(),
            email: $('input[name="email"]').val(),
            phone: $('input[name="phone"]').val(),
            alamat_1: $('input[name="alamat_1"]').val(),
            alamat_2: $('input[name="alamat_2"]').val(),
            alamat_3: $('input[name="alamat_3"]').val(),
            poskod: $('input[name="poskod"]').val(),
            bandar: $('input[name="bandar"]').val(),
            negeri: $('input[name="negeri"]').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '/members/{{ $member->id }}/profile',
            method: 'POST',
            data: formData,
            success: function (res) {
                console.log(res);
                if (res && res.success) {
                    // Update the form fields directly from res.member
                    // $('input[name="name"]').val(res.member.name);
                    // $('input[name="email"]').val(res.member.email ?? '');
                    // $('input[name="phone"]').val(res.member.phone ?? '');
                    // $('input[name="alamat_1"]').val(res.member.alamat_1 ?? '');
                    // $('input[name="alamat_2"]').val(res.member.alamat_2 ?? '');
                    // $('input[name="alamat_3"]').val(res.member.alamat_3 ?? '');
                    // $('input[name="poskod"]').val(res.member.poskod ?? '');
                    // $('input[name="bandar"]').val(res.member.bandar ?? '');
                    // $('input[name="negeri"]').val(res.member.negeri ?? '');

                    toast.info('Profile updated successfully!');
                } else {
                    toast.error('Failed to update profile. Invalid response data.');
                }
            },
            error: function (err) {
                console.error('Error:', err);
                toast.error('Failed to update profile. Check your input.');
            }
        });
    });
});
</script>