<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Thank You</title>
</head>
<body style="margin:0;padding:24px;font-family:Montserrat,Arial,sans-serif;background:#f7f8fa;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #d8dee8;border-radius:12px;">
        <tr>
            <td style="padding:24px;border-bottom:1px solid #d8dee8;">
                <h1 style="margin:0;font-size:22px;line-height:1.3;font-family:Poppins,Arial,sans-serif;">Thank you for contacting us</h1>
                <p style="margin:10px 0 0;font-size:14px;line-height:1.7;">
                    Hello {{ $formData['full_name'] }}, we received your message and our team will respond as soon as possible.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p style="margin:0 0 10px;"><strong>Your subject:</strong> {{ $formData['subject'] }}</p>
                <p style="margin:0 0 8px;"><strong>Your message:</strong></p>
                <div style="padding:14px;border:1px solid #d8dee8;border-radius:8px;white-space:pre-line;line-height:1.7;">{{ $formData['message'] }}</div>
                <p style="margin:16px 0 0;line-height:1.7;">
                    If your request is urgent, you can reach us via WhatsApp at
                    <a href="https://wa.me/256787726388" style="color:#0f172a;">+256787726388</a>.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 24px;background:#f7f8fa;border-top:1px solid #d8dee8;font-size:12px;color:#475467;">
                Arosoft Innovations Ltd | info@arosoft.io | https://arosoft.io/contact
            </td>
        </tr>
    </table>
</body>
</html>
