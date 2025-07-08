<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectText }}</title>
</head>
<body>
    <h2>{{ $subjectText }}</h2>
    <p>Dear {{ $user->name }},</p>
    <p>{!! nl2br(e($bodyMessage)) !!}</p>
    <p>Best regards,<br>Admin Team</p>
</body>
</html> 