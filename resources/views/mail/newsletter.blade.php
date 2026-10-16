<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<title>{{ $email->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#EDF2F9;font-family:Arial,Helvetica,sans-serif;color:#1C2B3F;">
@if ($email->preheader)
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $email->preheader }}&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;</div>
@endif
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EDF2F9;">
<tr><td align="center" style="padding:24px 12px;">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#FFFFFF;border-radius:12px;overflow:hidden;">
    <tr><td style="background:#003275;padding:20px 28px;">
      <a href="{{ $siteUrl }}" style="text-decoration:none;"><img src="{{ $logoUrl }}" alt="IT Security Nepal" height="40" style="display:block;height:40px;border:0;background:#FFFFFF;border-radius:6px;padding:4px 8px;"></a>
    </td></tr>
    <tr><td style="height:4px;background:#F9B233;line-height:4px;font-size:0;">&nbsp;</td></tr>
    @if ($imageUrl)
    <tr><td><img src="{{ $imageUrl }}" alt="" width="600" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></td></tr>
    @endif
    <tr><td style="padding:28px 28px 8px;">
      @if ($email->heading)<h1 style="margin:0 0 16px;font-size:24px;line-height:1.3;color:#003275;">{{ $email->heading }}</h1>@endif
      <div style="font-size:16px;line-height:1.65;color:#1C2B3F;">{!! $bodyHtml !!}</div>
    </td></tr>
    @if ($ctaUrl && $email->cta_label)
    <tr><td style="padding:12px 28px 28px;">
      <a href="{{ $ctaUrl }}" style="display:inline-block;background:#F9B233;color:#002152;font-weight:bold;font-size:16px;text-decoration:none;padding:14px 26px;border-radius:8px;">{{ $email->cta_label }}</a>
    </td></tr>
    @endif
    <tr><td style="padding:20px 28px;border-top:1px solid #DCE4F0;font-size:13px;line-height:1.6;color:#5B6B82;">
      IT Security Nepal@if ($address) · {{ $address }}@endif @if ($phone)· {{ $phone }}@endif<br>
      You are receiving this because you subscribed to updates on itsecuritynepal.com.
      <a href="{{ $unsubscribeUrl }}" style="color:#1F5BB8;">Unsubscribe</a>
    </td></tr>
  </table>
</td></tr>
</table>
</body>
</html>
