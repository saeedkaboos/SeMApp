<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

$db = getDB();

// الإحصائيات
$incidents  = $db->query("SELECT * FROM incidents")->fetchAll();
$users      = $db->query("SELECT COUNT(*) as c FROM users")->fetch();
$reports    = $db->query("SELECT COUNT(*) as c FROM reports")->fetch();
$recent     = $db->query("SELECT * FROM incidents ORDER BY created_at DESC LIMIT 5")->fetchAll();

$today = date('Y-m-d');
$totalIncidents    = count($incidents);
$openIncidents     = count(array_filter($incidents, fn($i) => in_array($i['status'], ['open','investigating'])));
$criticalIncidents = count(array_filter($incidents, fn($i) => $i['severity'] === 'critical'));
$resolvedToday     = count(array_filter($incidents, fn($i) => in_array($i['status'],['resolved','closed']) && substr($i['updated_at'],0,10) === $today));

// بيانات اتجاه الحوادث (آخر 6 أشهر)
$trend = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd   = date('Y-m-t', strtotime("-$i months"));
    $label      = date('M y', strtotime($monthStart));
    $stmt = $db->prepare("SELECT COUNT(*) as c FROM incidents WHERE created_at BETWEEN ? AND ?");
    $stmt->execute([$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']);
    $trend[] = ['month' => $label, 'count' => (int)$stmt->fetch()['c']];
}

// توزيع الخطورة
$severityRows = $db->query("SELECT severity, COUNT(*) as cnt FROM incidents GROUP BY severity")->fetchAll();
$sevMap = [];
foreach ($severityRows as $r) $sevMap[$r['severity']] = $r['cnt'];

$pageTitle = 'لوحة التحكم';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;min-height:100vh;">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="page-main" style="flex:1;padding:28px;overflow-y:auto;">
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:#e2e8f0;margin:0 0 4px;">لوحة التحكم</h1>
            <p style="color:#64748b;font-size:.88rem;margin:0;">نظرة عامة على حالة الأمن السيبراني</p>
        </div>

        <!-- Stats Cards -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:28px;">
            <?php
            $stats = [
                ['label'=>'إجمالي الحوادث','value'=>$totalIncidents,'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z','color'=>'#06b6d4','bg'=>'rgba(6,182,212,.12)'],
                ['label'=>'حوادث مفتوحة','value'=>$openIncidents,'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'#fb923c','bg'=>'rgba(249,115,22,.12)'],
                ['label'=>'حوادث حرجة','value'=>$criticalIncidents,'icon'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636','color'=>'#f87171','bg'=>'rgba(239,68,68,.12)'],
                ['label'=>'محلولة اليوم','value'=>$resolvedToday,'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'#4ade80','bg'=>'rgba(34,197,94,.12)'],
                ['label'=>'المستخدمون','value'=>$users['c'],'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z','color'=>'#a78bfa','bg'=>'rgba(139,92,246,.12)'],
                ['label'=>'التقارير','value'=>$reports['c'],'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','color'=>'#facc15','bg'=>'rgba(234,179,8,.12)'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="card" style="padding:20px;display:flex;align-items:center;gap:16px;">
                <div style="width:48px;height:48px;background:<?= $s['bg'] ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="<?= $s['color'] ?>" stroke-width="2"><path d="<?= $s['icon'] ?>"/></svg>
                </div>
                <div>
                    <div style="font-size:1.8rem;font-weight:800;color:<?= $s['color'] ?>;"><?= $s['value'] ?></div>
                    <div style="font-size:.82rem;color:#64748b;"><?= $s['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Charts Row -->
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:28px;">
            <!-- Trend Chart -->
            <div class="card" style="padding:20px;">
                <h3 style="font-size:1rem;font-weight:700;color:#e2e8f0;margin:0 0 16px;">اتجاه الحوادث - آخر 6 أشهر</h3>
                <div style="position:relative;height:180px;display:flex;align-items:flex-end;gap:8px;padding-bottom:24px;">
                    <?php
                    $maxCount = max(array_column($trend, 'count') ?: [1]);
                    foreach ($trend as $t):
                        $pct = $maxCount > 0 ? round(($t['count'] / $maxCount) * 100) : 0;
                    ?>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <span style="font-size:.7rem;color:#94a3b8;"><?= $t['count'] ?></span>
                        <div style="width:100%;height:<?= max($pct,4) ?>%;background:linear-gradient(180deg,#06b6d4,#0284c7);border-radius:6px 6px 0 0;min-height:4px;transition:height .3s;"></div>
                        <span style="font-size:.7rem;color:#64748b;position:absolute;bottom:0;"><?= $t['month'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Severity Breakdown -->
            <div class="card" style="padding:20px;">
                <h3 style="font-size:1rem;font-weight:700;color:#e2e8f0;margin:0 0 16px;">توزيع الخطورة</h3>
                <?php
                $sevDef = [
                    'critical'=>['label'=>'حرج','color'=>'#f87171'],
                    'high'    =>['label'=>'عالي','color'=>'#fb923c'],
                    'medium'  =>['label'=>'متوسط','color'=>'#facc15'],
                    'low'     =>['label'=>'منخفض','color'=>'#4ade80'],
                ];
                $total = array_sum($sevMap) ?: 1;
                foreach ($sevDef as $key => $def):
                    $cnt = $sevMap[$key] ?? 0;
                    $pct = round(($cnt/$total)*100);
                ?>
                <div style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:.83rem;color:#94a3b8;"><?= $def['label'] ?></span>
                        <span style="font-size:.83rem;font-weight:700;color:<?= $def['color'] ?>;"><?= $cnt ?> (<?= $pct ?>%)</span>
                    </div>
                    <div style="height:8px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:<?= $pct ?>%;background:<?= $def['color'] ?>;border-radius:99px;transition:width .4s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Incidents -->
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:1rem;font-weight:700;color:#e2e8f0;margin:0;">آخر الحوادث</h3>
                <a href="/php-cyber/incidents.php" style="color:#06b6d4;font-size:.83rem;text-decoration:none;">عرض الكل</a>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;">
                    <thead><tr>
                        <th style="text-align:right;">الحادثة</th>
                        <th style="text-align:right;">الخطورة</th>
                        <th style="text-align:right;">الحالة</th>
                        <th style="text-align:right;">الفئة</th>
                        <th style="text-align:right;">التاريخ</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($recent as $inc): ?>
                    <tr>
                        <td style="font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($inc['title']) ?></td>
                        <td><span class="badge-<?= $inc['severity'] ?>" style="padding:3px 10px;border-radius:99px;font-size:.78rem;font-weight:700;"><?= $inc['severity'] ?></span></td>
                        <td><span class="badge-<?= $inc['status'] ?>" style="padding:3px 10px;border-radius:99px;font-size:.78rem;"><?= $inc['status'] ?></span></td>
                        <td style="color:#94a3b8;"><?= htmlspecialchars($inc['category']) ?></td>
                        <td style="color:#64748b;font-size:.82rem;"><?= substr($inc['created_at'],0,10) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
