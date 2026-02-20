<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Contact Inquiry</title>
</head>
<body style="margin:0;padding:24px;font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;background:#f7f8fa;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #d8dee8;border-radius:12px;">
        <tr>
            <td style="padding:24px;border-bottom:1px solid #d8dee8;">
                <h1 style="margin:0;font-size:22px;line-height:1.3;font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">New Contact Form Inquiry</h1>
                <p style="margin:10px 0 0;font-size:14px;line-height:1.6;">A new message was submitted on the Arosoft website.</p>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p style="margin:0 0 10px;"><strong>Full name:</strong> {{ $formData['full_name'] }}</p>
                <p style="margin:0 0 10px;"><strong>Email:</strong> {{ $formData['email'] }}</p>
                <p style="margin:0 0 10px;"><strong>Phone:</strong> {{ $formData['phone'] ?: 'Not provided' }}</p>
                <p style="margin:0 0 10px;"><strong>Subject:</strong> {{ $formData['subject'] }}</p>
                <p style="margin:0 0 8px;"><strong>Message:</strong></p>
                <div style="padding:14px;border:1px solid #d8dee8;border-radius:8px;white-space:pre-line;line-height:1.7;">{{ $formData['message'] }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 24px;background:#f7f8fa;border-top:1px solid #d8dee8;font-size:12px;color:#475467;">
                Arosoft Innovations Ltd | Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School
            </td>
        </tr>
    </table>
</body>
</html>
