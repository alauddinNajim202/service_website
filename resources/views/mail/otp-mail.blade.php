<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $header_message }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0B0B0B; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #FFFFFF;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #0B0B0B; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; background-color: #161616; border-radius: 8px; border: 1px solid #2A2A2A; overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 0 20px 0;">
                            <h1 style="color: #CFA267; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: 2px;">VUQIA</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <h2 style="color: #FFFFFF; font-size: 24px; font-weight: 600; margin: 0 0 20px 0; text-align: center;">{{ htmlspecialchars($header_message) }}</h2>
                            
                            <p style="color: #E0E0E0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">Hi {{ $user->name }},</p>
                            
                            <p style="color: #E0E0E0; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">Please use the following OTP (One-Time Password) to complete your request. This OTP is valid for 1 hour.</p>
                            
                            <div style="text-align: center; margin-bottom: 30px;">
                                <h2 style="background-color: #CFA267; color: #121212; padding: 14px 32px; border-radius: 6px; margin: 0 auto; width: max-content; font-size: 32px; font-weight: 700; display: inline-block; letter-spacing: 6px;">{{ $otp }}</h2>
                            </div>
                            
                            <p style="color: #A0A0A0; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">If you did not request this OTP, please safely ignore this email.</p>
                            
                            <p style="color: #E0E0E0; font-size: 16px; line-height: 1.6; margin: 0;">Regards,<br><span style="color: #CFA267; font-weight: 600;">The VUQIA Team</span></p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #0F0F0F; padding: 20px 40px; text-align: center; border-top: 1px solid #2A2A2A;">
                            <p style="color: #707070; font-size: 12px; margin: 0 0 5px 0;">&copy; {{ date('Y') }} VUQIA Inc. All rights reserved.</p>
                            <p style="color: #707070; font-size: 12px; margin: 0;">1600 Amphitheatre Parkway, California</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
