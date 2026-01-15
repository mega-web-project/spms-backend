<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgotten Password</title>
</head>
<body>
    <p>Hello {{ $fullName }},</p>

    <p>
        You have requested a password reset for your <strong>SPMS</strong> account.
    </p>

    <p>
        Please click the button below to set your password and activate your account:
    </p>

    <p>
        <a href="{{ $resetUrl }}"
           style="display:inline-block;padding:10px 20px;
           background:#2563eb;color:#fff;text-decoration:none;
           border-radius:5px;">
            Set Password
        </a>
    </p>

    <p>
        If you did not expect this email, please ignore it.
    </p>

    <p>Regards,<br>SPMS Team</p>
</body>
</html>
