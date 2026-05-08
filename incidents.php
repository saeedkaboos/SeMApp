<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

$db      = getDB();
$message = '';
$error   = '';

// حذف حادثة
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM incidents WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $message = 'تم حذف الحادثة بنجاح';
}

// إضافة حادثة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $severity    = $_POST['severity'] ?? '';
    $category    = trim($_POST['category'] ?? '');
    $assignedTo  = trim($_POST['assigned_to'] ?? '');

    if ($title && $description && $severity && $category) {
        $stmt = $db->prepare("INSERT INTO incidents (title, description, severity, status, category, assigned_to, created_at, updated_at) VALUES (?, ?, ?, 'open', ?, ?, NOW(), NOW())");
        $stmt->execute([$title, $description, $severity, $category, $assignedTo ?: null]);
        $message = 'تم إضافة الحادثة بنجاح';
    } else {
        $error = 'يرجى ملء جميع الحقول المطلوبة';
    }
}

// تحديث حادثة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id     = (int)$_POST['id'];
    $status = $_POST['status'] ?? '';
    $sev    = $_POST['severity'] ?? '';
    $stmt   = $db->prepare("UPDATE incidents SET status=?, severity=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$status, $sev, $id]);
    $message = 'تم تحديث الحادثة';
}

// جلب كل الحوادث
$incidents = $db->query("SELECT * FROM incidents ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'الحوادث الأمنية';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh;">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="page-main" style="flex:1;padding:28px;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:#e2e8f0;margin:0 0 4px;">الحوادث الأمنية</h1>
                <p style="color:#64748b;font-size:.88rem;margin:0;"><?= count($incidents) ?> حادثة مسجلة</p>
            </div>
            <button onclick="document.getElementById('modal-add').classList.add('show')" class="btn-primary">+ إضافة حادثة</button>
        </div>

        <?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card" style="overflow:hidden;">
            <div style="overflow-x:auto;">
                <table style="width:100%;">
                    <thead><tr>
                        <th style="text-align:right;">#</th>
                        <th style="text-align:right;">العنوان</th>
                        <th style="text-align:right;">الخطورة</th>
                        <th style="text-align:right;">الحالة</th>
                        <th style="text-align:right;">الفئة</th>
                        <th style="text-align:right;">المسؤول</th>
                        <th style="text-align:right;">التاريخ</th>
                        <th style="text-align:right;">إجراءات</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($incidents as $inc): ?>
                    <tr>
                        <td style="color:#64748b;font-size:.8rem;"><?= $inc['id'] ?></td>
                        <td>
                            <div style="font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($inc['title']) ?></div>
                            <div style="font-size:.78rem;color:#64748b;margin-top:2px;"><?= htmlspecialchars(mb_substr($inc['description'],0,50)) ?>...</div>
                        </td>
                        <td><span class="badge-<?= $inc['severity'] ?>" style="padding:3px 10px;border-radius:99px;font-size:.78rem;font-weight:700;"><?= $inc['severity'] ?></span></td>
                        <td><span class="badge-<?= $inc['status'] ?>" style="padding:3px 10px;border-radius:99px;font-size:.78rem;"><?= $inc['status'] ?></span></td>
                        <td style="color:#94a3b8;"><?= htmlspecialchars($inc['category']) ?></td>
                        <td style="color:#94a3b8;"><?= $inc['assigned_to'] ? htmlspecialchars($inc['assigned_to']) : '<span style="color:#475569;">غير محدد</span>' ?></td>
                        <td style="color:#64748b;font-size:.82rem;"><?= substr($inc['created_at'],0,10) ?></td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($inc)) ?>)" style="background:rgba(6,182,212,.12);color:#22d3ee;border:none;border-radius:6px;padding:5px 10px;cursor:pointer;font-family:'Cairo',sans-serif;font-size:.78rem;">تعديل</button>
                                <a href="?delete=<?= $inc['id'] ?>" onclick="return confirm('هل تريد حذف هذه الحادثة؟')" class="btn-danger" style="font-size:.78rem;text-decoration:none;">حذف</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal إضافة حادثة -->
<div id="modal-add" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <h2 style="font-size:1.2rem;font-weight:700;color:#e2e8f0;margin:0 0 20px;">إضافة حادثة جديدة</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div style="display:grid;gap:14px;">
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">عنوان الحادثة *</label>
                    <input type="text" name="title" class="input-field" required placeholder="عنوان الحادثة"></div>
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">الوصف *</label>
                    <textarea name="description" class="input-field" rows="3" required placeholder="وصف تفصيلي للحادثة" style="resize:vertical;"></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">مستوى الخطورة *</label>
                        <select name="severity" class="input-field" required>
                            <option value="">اختر الخطورة</option>
                            <option value="critical">حرج</option>
                            <option value="high">عالي</option>
                            <option value="medium">متوسط</option>
                            <option value="low">منخفض</option>
                        </select></div>
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">الفئة *</label>
                        <input type="text" name="category" class="input-field" required placeholder="مثال: Network Security"></div>
                </div>
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">تعيين إلى</label>
                    <input type="text" name="assigned_to" class="input-field" placeholder="اسم المحلل المسؤول"></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-add').classList.remove('show')" style="background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:8px;padding:9px 20px;cursor:pointer;font-family:'Cairo',sans-serif;">إلغاء</button>
                <button type="submit" class="btn-primary">إضافة الحادثة</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal تعديل حادثة -->
<div id="modal-edit" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <h2 style="font-size:1.2rem;font-weight:700;color:#e2e8f0;margin:0 0 20px;">تعديل الحادثة</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div style="display:grid;gap:14px;">
                <div id="edit-title-display" style="background:rgba(6,182,212,.06);border-radius:8px;padding:12px;color:#22d3ee;font-weight:600;"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">مستوى الخطورة</label>
                        <select name="severity" id="edit-severity" class="input-field">
                            <option value="critical">حرج</option>
                            <option value="high">عالي</option>
                            <option value="medium">متوسط</option>
                            <option value="low">منخفض</option>
                        </select></div>
                    <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">الحالة</label>
                        <select name="status" id="edit-status" class="input-field">
                            <option value="open">مفتوح</option>
                            <option value="investigating">قيد التحقيق</option>
                            <option value="resolved">محلول</option>
                            <option value="closed">مغلق</option>
                        </select></div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-edit').classList.remove('show')" style="background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:8px;padding:9px 20px;cursor:pointer;font-family:'Cairo',sans-serif;">إلغاء</button>
                <button type="submit" class="btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(incident) {
    document.getElementById('edit-id').value = incident.id;
    document.getElementById('edit-title-display').textContent = incident.title;
    document.getElementById('edit-severity').value = incident.severity;
    document.getElementById('edit-status').value = incident.status;
    document.getElementById('modal-edit').classList.add('show');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
