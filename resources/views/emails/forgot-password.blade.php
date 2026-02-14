<h3>Hello {{ $user->name }}</h3>

<p>You requested a password reset. Click the button below to reset your password:</p>

<a href="{{ $url }}" 
   style="padding:10px 20px; background:#0d6efd; color:white; text-decoration:none;">
   Reset Password
</a>

<p>This link will expire in 60 minutes.</p>
