<?php
// ==========================================
// موجّه الطلبات الرئيسي لـ PHP Built-in Server
// ==========================================

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// إزالة البادئة /php-cyber
$uri = preg_replace('#^/php-cyber#', '', $uri);
if ($uri === '' || $uri === false) {
    $uri = '/';
}

// الصفحة الرئيسية → لوحة التحكم
if ($uri === '/') {
    require __DIR__ . '/dashboard.php';
    return true;
}

$file = __DIR__ . $uri;

// تنفيذ ملفات PHP مباشرةً (يمنع التعامل معها كملفات ثابتة)
if (is_file($file) && str_ends_with($file, '.php')) {
    require $file;
    return true;
}

// خدمة الملفات الثابتة (CSS, JS, images, fonts)
if (is_file($file)) {
    return false;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8">
<style>body{background:#0a0e1a;color:#fff;font-family:Cairo,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
h1{color:#00d4ff;}p{color:#8892a4;}</style></head>
<body><div style="text-align:center"><h1>404</h1><p>الصفحة غير موجودة</p><a href="/php-cyber/" style="color:#00d4ff">العودة للرئيسية</a></div></body></html>';
