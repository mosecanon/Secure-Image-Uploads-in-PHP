<?php
declare(strict_types=1);
session_start();

if(empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secure Image Upload Demo</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Inter,Arial,sans-serif;max-width:720px;margin:3rem auto;padding:0 1rem}
    .card{border:1px solid #ddd;padding:1rem;border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,.06)}
    label{display:block;margin:.5rem 0}
    input[type=file]{margin:.5rem 0}
    .hint{color:#555;font-size:.9rem}
    .ok{color:green}
    .err{color:#b00020}
    pre{background:#f7f7f7;padding:1rem;border-radius:8px;overflow:auto}
  </style>
</head>
<body>
  <h1>Secure Image Upload Demo</h1>
  <div class="card">
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
      <label>Profile picture (JPG/PNG/WebP, max 5MB)
        <input required type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
      </label>
      <button type="submit">Upload</button>
    </form>
    <p class="hint">We strictly validate type, size, and re-encode the image to remove risky metadata and payloads.</p>
  </div>
</body>
</html>