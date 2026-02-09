<h2>Hello {{ $name }},</h2>

<p>Your account has been created. Please login using the details below:</p>

<p>
    Email: {{ $email }}<br>
    Temporary Password: {{ $password }}<br>
</p>

<p>Login here: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

<p>After logging in, you should reset your password.</p>
