<h2>Hello {{ $name }},</h2>

<p>Welcome to our platform! Your account has been successfully created, and we`re excited to have you on board.</p>

<p>Before you get started, we need you to complete a few steps:</p>

<ul>
    Temporary Password: {{ $password }}<br>
    <li><strong>Set your password</strong> — For security, please set your own password to access your account.</li>

</ul>

<p>You can complete these steps by logging in using the details below:</p>

<p>
    Email: {{ $email }}<br>
</p>

<p>Login here: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

<p>Once you're logged in, you'll be prompted to set a new password and complete your profile. If you need help, feel free to reach out to our support team.</p>

<p>Thank you, and we look forward to having you with us!</p>
