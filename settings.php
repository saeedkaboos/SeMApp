<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

$user    = getCurrentUser();
$db      = getDB();
$message = '';

$fullUser = $db->prepare("SELECT * FROM users WHERE id=?")->execute([$user['id']]) 
    ? $db->prepare("SELECT * FROM users WHERE id=?")->execute([$user['id']]) 
    : null;
$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user['id']]);
$fullUser = $stmt->fetch();

// تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($newPass !== $confirm) {
        $error = 'كلمات المرور الجديدة غير متطابقة';
    } elseif (hashPassword($oldPass) !== $fullUser['password_hash']) {
        $error = 'كلمة المرور الحالية غير صحيحة';
    } elseif (strlen($newPass) < 4) {
        $error = 'كلمة المرور الجديدة قصيرة جداً';
    } else {
        $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([hashPassword($newPass), $user['id']]);
        $message = 'تم تغيير كلمة المرور بنجاح';
    }
}

$pageTitle = 'الإعدادات';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh;">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="page-main" style="flex:1;padding:28px;overflow-y:auto;max-width:700px;">
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:#e2e8f0;margin:0 0 4px;">الإعدادات</h1>
            <p style="color:#64748b;font-size:.88rem;margin:0;">إدارة بيانات الحساب</p>
        </div>

        <?php if (!empty($message)): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if (!empty($error)):   ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <!-- بطاقة الملف الشخصي -->
        <div class="card" style="padding:24px;margin-bottom:20px;">
            <div style="display:flex;align-items:center;gap:18px;margin-bottom:20px;">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#06b6d4,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;">
                    <?= mb_substr($fullUser['full_name'],0,1) ?>
                </div>
                <div>
                    <h2 style="font-size:1.2rem;font-weight:700;color:#e2e8f0;margin:0 0 4px;"><?= htmlspecialchars($fullUser['full_name']) ?></h2>
                    <span class="badge-<?= $fullUser['role'] ?>" style="padding:3px 12px;border-radius:99px;font-size:.8rem;font-weight:700;"><?= $fullUser['role'] ?></span>
                </div>
            </div>

            <div style="display:grid;gap:14px;">
                <?php
                $fields = [
                    ['label'=>'اسم المستخدم','value'=>$fullUser['username'],'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['label'=>'البريد الإلكتروني','value'=>$fullUser['email'],'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['label'=>'الدور','value'=>$fullUser['role'],'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['label'=>'آخر دخول','value'=>$fullUser['last_login'] ? substr($fullUser['last_login'],0,16) : 'لم يسجل بعد','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label'=>'تاريخ الإنشاء','value'=>substr($fullUser['created_at'],0,10),'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ];
                foreach ($fields as $f):
                ?>
                <div style="display:flex;align-items:center;gap:14px;padding:14px;background:rgba(6,182,212,.04);border-radius:10px;border:1px solid rgba(6,182,212,.08);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="2"><path d="<?= $f['icon'] ?>"/></svg>
                    <div>
                        <div style="font-size:.75rem;color:#64748b;margin-bottom:2px;"><?= $f['label'] ?></div>
                        <div style="font-size:.9rem;font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($f['value']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- تغيير كلمة المرور -->
        <div class="card" style="padding:24px;">
            <h3 style="font-size:1rem;font-weight:700;color:#e2e8f0;margin:0 0 18px;">تغيير كلمة المرور</h3>
            <form method="POST">
                <div style="display:grid;gap:14px;">
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">كلمة المرور الحالية</label>
                        <input type="password" name="old_password" class="input-field" required placeholder="••••••••"></div>
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="input-field" required placeholder="••••••••"></div>
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">تأكيد كلمة المرور</label>
                        <input type="password" name="confirm_password" class="input-field" required placeholder="••••••••"></div>
                </div>
                <div style="margin-top:18px;">
                    <button type="submit" class="btn-primary">تحديث كلمة المرور</button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
