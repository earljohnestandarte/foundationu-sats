<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { margin:0; padding:0; background:#f3f4f5; font-family:'Segoe UI',Arial,sans-serif; color:#191c1d; }
  .wrap { max-width:600px; margin:32px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .header { background:#800000; padding:28px 32px; text-align:center; }
  .header img { height:48px; margin-bottom:8px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; letter-spacing:.02em; }
  .header p  { margin:4px 0 0; color:rgba(255,255,255,.8); font-size:13px; }
  .body { padding:32px; }
  .ticket-box { background:#f8f9fa; border:1px solid #e2bfb9; border-radius:8px; padding:16px 20px; margin:20px 0; }
  .ticket-box .ref { font-size:12px; font-weight:700; color:#800000; letter-spacing:.04em; margin-bottom:4px; }
  .ticket-box .subject { font-size:16px; font-weight:600; color:#191c1d; margin:0 0 8px; }
  .ticket-box .meta { font-size:13px; color:#5a413d; display:flex; gap:16px; flex-wrap:wrap; }
  .badge { display:inline-block; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:600; }
  .badge-open        { background:#dbeafe; color:#1e40af; }
  .badge-inprogress  { background:#fef3c7; color:#92400e; }
  .badge-pending     { background:#fce7f3; color:#9d174d; }
  .badge-resolved    { background:#dcfce7; color:#15803d; }
  .badge-closed      { background:#f3f4f6; color:#4b5563; }
  .btn { display:inline-block; background:#800000; color:#fff !important; text-decoration:none; padding:12px 28px; border-radius:8px; font-weight:600; font-size:15px; margin:20px 0; }
  .reply-box { background:#f8f9fa; border-left:3px solid #800000; padding:12px 16px; border-radius:0 8px 8px 0; margin:16px 0; font-size:14px; line-height:1.6; color:#191c1d; }
  .footer { background:#f3f4f5; padding:20px 32px; text-align:center; font-size:12px; color:#5a413d; border-top:1px solid #e2bfb9; }
  .footer a { color:#800000; text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>Foundation University — SATS</h1>
    <p>Student Affairs Ticketing System</p>
  </div>
  <div class="body">
    <?= $emailContent ?>
  </div>
  <div class="footer">
    <p>© <?= date('Y') ?> Foundation University — Office of Student Life<br>
    This is an automated message. <a href="<?= $baseUrl ?>">Visit FU-SATS</a></p>
  </div>
</div>
</body>
</html>
