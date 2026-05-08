<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

$db      = getDB();
$message = '';
$error   = '';

// حذف مستخدم
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $current = getCurrentUser();
    if ((int)$_GET['delete'] === (int)$current['id']) {
        $error = 'لا يمكنك حذف حسابك الخاص';
    } else {
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([(int)$_GET['delete']]);
        $message = 'تم حذف المستخدم';
    }
}

// إضافة مستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'analyst';

    if ($username && $fullName && $email && $password) {
        $check = $db->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $error = 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً';
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, full_name, email, password_hash, role, status, created_at) VALUES (?,?,?,?,'$role','active',NOW())");
            $stmt->execute([$username, $fullName, $email, hashPassword($password)]);
            $message = 'تم إضافة المستخدم بنجاح';
        }
    } else {
        $error = 'يرجى ملء جميع الحقول';
    }
}

// تغيير حالة مستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    $id  = (int)$_POST['id'];
    $new = $_POST['new_status'] ?? 'active';
    $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new, $id]);
    $message = 'تم تحديث حالة المستخدم';
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'إدارة المستخدمين';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh;">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="page-main" style="flex:1;padding:28px;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:#e2e8f0;margin:0 0 4px;">إدارة المستخدمين</h1>
                <p style="color:#64748b;font-size:.88rem;margin:0;"><?= count($users) ?> مستخدم مسجل</p>
            </div>
            <button onclick="document.getElementById('modal-add').classList.add('show')" class="btn-primary">+ إضافة مستخدم</button>
        </div>

        <?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
        <?php foreach ($users as $u): ?>
            <div class="card" style="padding:20px;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                    <div style="width:48px;height:48px;background:linear-gradient(135deg,#06b6d4,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.1rem;flex-shrink:0;">
                        <?= mb_substr($u['full_name'],0,1) ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;color:#e2e8f0;"><?= htmlspecialchars($u['full_name']) ?></div>
                        <div style="font-size:.8rem;color:#64748b;">@<?= htmlspecialchars($u['username']) ?></div>
                    </div>
                    <span class="badge-<?= $u['role'] ?>" style="padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:700;"><?= $u['role'] ?></span>
                </div>
                <div style="font-size:.82rem;color:#64748b;margin-bottom:14px;"><?= htmlspecialchars($u['email']) ?></div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.78rem;padding:3px 10px;border-radius:99px;background:<?= $u['status']==='active' ? 'rgba(34,197,94,.12)' : 'rgba(100,116,139,.12)' ?>;color:<?= $u['status']==='active' ? '#4ade80' : '#94a3b8' ?>;">
                        <?= $u['status']==='active' ? 'نشط' : 'غير نشط' ?>
                    </span>
                    <div style="display:flex;gap:6px;">
                        <!-- تغيير الحالة -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $u['status']==='active' ? 'inactive' : 'active' ?>">
                            <button type="submit" style="background:rgba(6,182,212,.1);color:#22d3ee;border:none;border-radius:6px;padding:5px 10px;cursor:pointer;font-family:'Cairo',sans-serif;font-size:.78rem;">
                                <?= $u['status']==='active' ? 'تعطيل' : 'تفعيل' ?>
                            </button>
                        </form>
                        <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('هل تريد حذف هذا المستخدم؟')" class="btn-danger" style="font-size:.78rem;text-decoration:none;">حذف</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Modal إضافة مستخدم -->
<div id="modal-add" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <h2 style="font-size:1.2rem;font-weight:700;color:#e2e8f0;margin:0 0 20px;">إضافة مستخدم جديد</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div style="display:grid;gap:14px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">اسم المستخدم *</label>
                        <input type="text" name="username" class="input-field" required placeholder="username"></div>
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">الاسم الكامل *</label>
                        <input type="text" name="full_name" class="input-field" required placeholder="الاسم الكامل"></div>
                </div>
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">البريد الإلكتروني *</label>
                    <input type="email" name="email" class="input-field" required placeholder="user@example.com"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">كلمة المرور *</label>
                        <input type="password" name="password" class="input-field" required placeholder="••••••••"></div>
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">الدور</label>
                        <select name="role" class="input-field">
                            <option value="analyst">محلل</option>
                            <option value="admin">مدير</option>
                            <option value="viewer">مشاهد</option>
                        </select></div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-add').classList.remove('show')" style="background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:8px;padding:9px 20px;cursor:pointer;font-family:'Cairo',sans-serif;">إلغاء</button>
                <button type="submit" class="btn-primary">إضافة المستخدم</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
