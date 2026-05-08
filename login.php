<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// إذا كان مسجلاً دخوله، اذهب للوحة التحكم
if (isLoggedIn()) {
    header('Location:/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['password_hash'] === hashPassword($password)) {
            if ($user['status'] === 'inactive') {
                $error = 'الحساب غير مفعّل';
            } else {
                // تحديث آخر تسجيل دخول
                $upd = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $upd->execute([$user['id']]);

                // حفظ بيانات الجلسة
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role']      = $user['role'];

                header('Location:/dashboard.php');
                exit;
            }
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | SecureSOC</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background: #050d1a; color: #e2e8f0; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .glow { box-shadow: 0 0 80px rgba(6,182,212,.25), 0 0 160px rgba(6,182,212,.08); }
        .input-field { background: rgba(255,255,255,.04); border: 1px solid rgba(6,182,212,.25); border-radius: 10px; color: #e2e8f0; padding: 13px 16px; width: 100%; font-family: 'Cairo', sans-serif; font-size: .95rem; transition: border-color .2s; outline: none; }
        .input-field:focus { border-color: #06b6d4; background: rgba(6,182,212,.06); }
        .btn-login { width: 100%; background: linear-gradient(135deg, #06b6d4, #0284c7); color: #fff; border: none; border-radius: 10px; padding: 14px; font-family: 'Cairo', sans-serif; font-size: 1.05rem; font-weight: 700; cursor: pointer; transition: opacity .2s, transform .1s; letter-spacing: .03em; }
        .btn-login:hover { opacity: .88; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }
        .grid-bg { background-image: linear-gradient(rgba(6,182,212,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(6,182,212,.04) 1px, transparent 1px); background-size: 40px 40px; }
    </style>
</head>
<body class="grid-bg">
    <!-- Decorative circles -->
    <div style="position:fixed;top:-100px;left:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(6,182,212,.12),transparent 70%);pointer-events:none;"></div>
    <div style="position:fixed;bottom:-100px;right:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(124,58,237,.1),transparent 70%);pointer-events:none;"></div>

    <div style="width:100%;max-width:440px;padding:24px;">
        <!-- Logo -->
        <div style="text-align:center;margin-bottom:36px;">
            <div style="width:72px;height:72px;background:linear-gradient(135deg,#06b6d4,#0284c7);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;" class="glow">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <h1 style="font-size:1.7rem;font-weight:800;color:#22d3ee;margin:0 0 4px;">SecureSOC</h1>
            <p style="color:#64748b;font-size:.9rem;margin:0;">نظام إدارة العمليات السيبرانية</p>
        </div>

        <!-- Card -->
        <div style="background:#0f2040;border:1px solid rgba(6,182,212,.18);border-radius:18px;padding:32px;">
            <h2 style="font-size:1.3rem;font-weight:700;text-align:center;margin:0 0 6px;color:#e2e8f0;">تسجيل الدخول</h2>
            <p style="color:#64748b;text-align:center;font-size:.85rem;margin:0 0 24px;">الرجاء إدخال بيانات الاعتماد الخاصة بك للوصول</p>

            <?php if ($error): ?>
            <div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#f87171;border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:.9rem;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#94a3b8;margin-bottom:8px;">اسم المستخدم</label>
                    <div style="position:relative;">
                        <input type="text" name="username" class="input-field" placeholder="أدخل اسم المستخدم" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" required>
                        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>
                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#94a3b8;margin-bottom:8px;">كلمة المرور</label>
                    <div style="position:relative;">
                        <input type="password" name="password" class="input-field" placeholder="••••••••" autocomplete="current-password" required>
                        <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </div>
                </div>
                <button type="submit" class="btn-login">دخول للنظام</button>
            </form>
        </div>

        <p style="text-align:center;color:#475569;font-size:.78rem;margin-top:20px;">
            دخول غير مصرح به يعرضك للمساءلة القانونية. النظام مراقب بالكامل.
        </p>
    </div>
</body>
</html>
