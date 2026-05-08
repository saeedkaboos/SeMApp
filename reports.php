<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

$db      = getDB();
$message = '';
$error   = '';

// حذف تقرير
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM reports WHERE id=?")->execute([(int)$_GET['delete']]);
    $message = 'تم حذف التقرير';
}

// إضافة تقرير
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title   = trim($_POST['title']   ?? '');
    $content = trim($_POST['content'] ?? '');
    $type    = $_POST['type'] ?? '';
    $user    = getCurrentUser();

    if ($title && $content && $type) {
        $stmt = $db->prepare("INSERT INTO reports (title, content, type, created_by, created_at) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$title, $content, $type, $user['fullName']]);
        $message = 'تم إنشاء التقرير بنجاح';
    } else {
        $error = 'يرجى ملء جميع الحقول';
    }
}

$reports = $db->query("SELECT * FROM reports ORDER BY created_at DESC")->fetchAll();

$typeMap = [
    'vulnerability' => ['label'=>'ثغرات أمنية','color'=>'#f87171','bg'=>'rgba(239,68,68,.12)'],
    'audit'         => ['label'=>'تدقيق أمني', 'color'=>'#22d3ee','bg'=>'rgba(6,182,212,.12)'],
    'compliance'    => ['label'=>'امتثال',     'color'=>'#4ade80','bg'=>'rgba(34,197,94,.12)'],
    'incident'      => ['label'=>'حوادث',      'color'=>'#fb923c','bg'=>'rgba(249,115,22,.12)'],
];

$pageTitle = 'التقارير الأمنية';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh;">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="page-main" style="flex:1;padding:28px;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:#e2e8f0;margin:0 0 4px;">التقارير الأمنية</h1>
                <p style="color:#64748b;font-size:.88rem;margin:0;"><?= count($reports) ?> تقرير</p>
            </div>
            <button onclick="document.getElementById('modal-add').classList.add('show')" class="btn-primary">+ إنشاء تقرير</button>
        </div>

        <?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div style="display:grid;gap:14px;">
        <?php foreach ($reports as $r):
            $t = $typeMap[$r['type']] ?? ['label'=>$r['type'],'color'=>'#94a3b8','bg'=>'rgba(100,116,139,.12)'];
        ?>
            <div class="card" style="padding:20px;display:flex;align-items:start;gap:16px;">
                <div style="width:44px;height:44px;background:<?= $t['bg'] ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $t['color'] ?>" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                        <h3 style="font-size:1rem;font-weight:700;color:#e2e8f0;margin:0;"><?= htmlspecialchars($r['title']) ?></h3>
                        <span style="padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:700;background:<?= $t['bg'] ?>;color:<?= $t['color'] ?>;"><?= $t['label'] ?></span>
                    </div>
                    <p style="color:#94a3b8;font-size:.85rem;margin:0 0 10px;line-height:1.6;"><?= htmlspecialchars(mb_substr($r['content'],0,180)) ?>...</p>
                    <div style="display:flex;align-items:center;gap:16px;font-size:.78rem;color:#64748b;">
                        <span>بقلم: <?= htmlspecialchars($r['created_by']) ?></span>
                        <span><?= substr($r['created_at'],0,10) ?></span>
                    </div>
                </div>
                <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('هل تريد حذف هذا التقرير؟')" class="btn-danger" style="font-size:.78rem;text-decoration:none;flex-shrink:0;">حذف</a>
            </div>
        <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Modal إضافة تقرير -->
<div id="modal-add" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <h2 style="font-size:1.2rem;font-weight:700;color:#e2e8f0;margin:0 0 20px;">إنشاء تقرير جديد</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div style="display:grid;gap:14px;">
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">عنوان التقرير *</label>
                    <input type="text" name="title" class="input-field" required placeholder="عنوان التقرير"></div>
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">نوع التقرير *</label>
                    <select name="type" class="input-field" required>
                        <option value="">اختر النوع</option>
                        <option value="vulnerability">ثغرات أمنية</option>
                        <option value="audit">تدقيق أمني</option>
                        <option value="compliance">امتثال</option>
                        <option value="incident">حوادث</option>
                    </select></div>
                <div><label style="font-size:.83rem;color:#94a3b8;display:block;margin-bottom:6px;">محتوى التقرير *</label>
                    <textarea name="content" class="input-field" rows="6" required placeholder="اكتب محتوى التقرير هنا..." style="resize:vertical;"></textarea></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-add').classList.remove('show')" style="background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:8px;padding:9px 20px;cursor:pointer;font-family:'Cairo',sans-serif;">إلغاء</button>
                <button type="submit" class="btn-primary">إنشاء التقرير</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
