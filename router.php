<?php
// ==========================================
// Router لـ PHP Built-in Server / Render
// ==========================================

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/dashboard.php';
    exit;
}

$file = __DIR__ . $uri;

// تشغيل ملفات PHP
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    require $file;
    exit;
}

// الملفات الثابتة
if (is_file($file)) {
    return false;
}

// صفحة 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>404</title>
<style>
body{
    background:#0a0e1a;
    color:#fff;
    font-family:Cairo,sans-serif;
    display:flex;
    align-items:center;
    justify-content:center;
    height:100vh;
    margin:0;
}
a{
    color:#00d4ff;
    text-decoration:none;
}
</style>
</head>
<body>
<div style="text-align:center">
    <h1>404</h1>
    <p>الصفحة غير موجودة</p>
    <a href="/">العودة للرئيسية</a>
</div>
</body>
</html>
