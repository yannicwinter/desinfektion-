<?php
// Wartungsseite ohne Website-Dateien. Texte hier direkt anpassen.
http_response_code(503);
header('Retry-After: 3600');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Wir sind gleich wieder da · DRK Arbeitssicherheit Mittelweser</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;background:#F5F6F8;color:#41454F;line-height:1.55}
  main{min-height:100vh;min-height:100dvh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 16px;text-align:center}
  .box{width:100%;max-width:560px;background:#fff;border:1px solid #E3E5E9;border-radius:8px;padding:40px 32px}
  img{display:block;width:100%;max-width:360px;height:auto;margin:0 auto 32px}
  h1{font-size:clamp(24px,4vw,32px);line-height:1.2;color:#14161B;margin:0 0 12px;letter-spacing:-.01em}
  p{margin:0 0 12px}
  .btns{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:24px}
  .btn{display:inline-flex;align-items:center;gap:8px;min-height:48px;padding:0 20px;border-radius:6px;font-weight:600;text-decoration:none;border:1px solid #D3D6DC;color:#14161B}
  .btn--red{background:#E60005;border-color:#E60005;color:#fff}
  .btn--red:hover{background:#C70004}
  .foot{margin-top:24px;font-size:13px;color:#6B7080}
  @media (max-width:520px){.box{padding:28px 20px}.btn{width:100%;justify-content:center}}
</style>
</head>
<body>
<main>
  <div class="box">
    <img src="logo.png" width="1100" height="105" alt="Deutsches Rotes Kreuz – DRK Arbeitssicherheit Mittelweser – DRK-Kreisverband Verden e.V.">
    <h1>Wir sind gleich wieder da</h1>
    <p>Unsere Website bekommt gerade ein neues Zuhause. Kurse und Termine kannst du in der Zwischenzeit telefonisch oder per E-Mail anfragen.</p>
    <div class="btns">
      <a class="btn btn--red" href="tel:+49423192450">04231 / 9245-0</a>
      <a class="btn" href="mailto:kontakt@drk-sicherheit.de">kontakt@drk-sicherheit.de</a>
    </div>
  </div>
  <p class="foot">DRK-Kreisverband Verden e.V. · Lindhooper Str. 20/22 · 27283 Verden (Aller)</p>
</main>
</body>
</html>
