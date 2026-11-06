<div style="font-family:Montserrat,Arial,sans-serif;max-width:620px;margin:auto;border:1px solid #e5e1d6;border-radius:12px;overflow:hidden">
  <div style="background:#1c2448;color:#fff;padding:22px 26px">
    <div style="color:#daad55;font-size:11px;letter-spacing:3px;text-transform:uppercase">{{ $kicker }}</div>
    <div style="font-size:20px;font-weight:700;margin-top:6px">{{ $title }}</div>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:14px;color:#1c2448">
    @foreach ($rows as $label => $value)
      <tr>
        <td style="padding:12px 26px;width:150px;color:#6b7086;vertical-align:top;border-bottom:1px solid #f0ede5">{{ $label }}</td>
        <td style="padding:12px 26px;border-bottom:1px solid #f0ede5;white-space:pre-wrap">{{ filled($value) ? $value : '—' }}</td>
      </tr>
    @endforeach
  </table>
  <div style="padding:18px 26px">
    <a href="{{ $adminUrl }}" style="display:inline-block;background:#daad55;color:#0b1024;font-weight:700;text-decoration:none;padding:10px 18px;border-radius:999px;font-size:13px">Open in admin</a>
  </div>
</div>
