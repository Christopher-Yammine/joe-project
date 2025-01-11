<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body>
    <p>Hello {{ $user->full_name }},</p>
    <p>You recently requested to reset your password. Click the link below to reset it:</p>
    <p><a href="{{ $resetUrl }}">Reset Password</a></p>
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Thank you,</p>
</body>
</html>
