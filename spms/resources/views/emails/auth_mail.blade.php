<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Created | SPMS</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#2563eb; padding:20px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px;">
                                Security Post Management System (SPMS)
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#374151; font-size:15px; line-height:1.6;">
                            <p style="margin-top:0;">
                                Hello <strong>{{ $fullName }}</strong>,
                            </p>

                            <p>
                                We are pleased to inform you that an account has been successfully created for you on 
                                <strong>SPMS</strong>.
                            </p>

                            <p>
                                Your temporary password is:
                            </p>

                            <p style="background:#f3f4f6; padding:12px; border-radius:5px; text-align:center; font-size:16px;">
                                <strong>{{ $temproaryPassword }}</strong>
                            </p>

                            <p>
                                For security reasons, we recommend that you set a new password immediately by clicking the button below to activate your account.
                            </p>

                            <!-- Button -->
                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $resetUrl }}"
                                   style="display:inline-block; padding:12px 28px; background-color:#2563eb;
                                   color:#ffffff; text-decoration:none; font-weight:bold; border-radius:6px;">
                                    Set Password
                                </a>
                            </p>
                            <p>
                                Alternatively, you can log in to your account using the link below:
                            </p>
                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $loginUrl }}"
                                   style="display:inline-block; padding:12px 28px; background-color:#10b981;
                                   color:#ffffff; text-decoration:none; font-weight:bold; border-radius:6px;">
                                    Log In
                                </a>
                            </p>

                            <p style="font-size:14px; color:#6b7280;">
                                If you did not expect this email, you can safely ignore it. No action is required.
                            </p>

                            <p style="margin-bottom:0;">
                                Regards,<br>
                                <strong>SPMS Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb; padding:15px; text-align:center; font-size:12px; color:#9ca3af;">
                            © {{ date('Y') }} SPMS. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
