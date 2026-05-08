<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$user = getCurrentUser();
?>
<div id="sidebar">

    <!-- زر الإغلاق (موبايل) -->
    <button id="sidebar-close" onclick="closeSidebar()" aria-label="إغلاق القائمة">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6"  y1="6" x2="18" y2="18"/>
        </svg>
    </button>

    <!-- الشعار -->
    <div style="padding:24px 20px;border-bottom:1px solid rgba(6,182,212,.1);">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;background:linear-gradient(135deg,#06b6d4,#0284c7);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div>
                <div style="font-weight:800;font-size:1.1rem;color:#22d3ee;">SecureSOC</div>
                <div style="font-size:.72rem;color:#64748b;">نظام الأمن السيبراني</div>
            </div>
        </div>
    </div>

    <!-- روابط التنقل -->
    <nav style="flex:1;padding:16px 12px;display:flex;flex-direction:column;gap:4px;">
        <?php
        $links = [
            ['href'=>'dashboard.php', 'page'=>'dashboard', 'label'=>'لوحة التحكم',
             'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['href'=>'incidents.php', 'page'=>'incidents', 'label'=>'الحوادث الأمنية',
             'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['href'=>'users.php',    'page'=>'users',     'label'=>'إدارة المستخدمين',
             'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['href'=>'reports.php',  'page'=>'reports',   'label'=>'التقارير الأمنية',
             'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['href'=>'settings.php', 'page'=>'settings',  'label'=>'الإعدادات',
             'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ];
        foreach ($links as $link):
            $isActive = ($currentPage === $link['page']);
        ?>
        <a href="/<?= $link['href'] ?>"
           class="sidebar-link <?= $isActive ? 'active' : '' ?>"
           onclick="closeSidebar()"
           style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:8px;text-decoration:none;color:<?= $isActive ? '#22d3ee' : '#94a3b8' ?>;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="<?= $link['icon'] ?>"/>
            </svg>
            <span style="font-size:.9rem;font-weight:500;"><?= $link['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- معلومات المستخدم + تسجيل الخروج -->
    <div style="padding:16px;border-top:1px solid rgba(6,182,212,.1);">
        <div style="display:flex;align-items:center;gap:10px;padding:10px;background:rgba(6,182,212,.06);border-radius:10px;margin-bottom:10px;">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#06b6d4,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:.85rem;flex-shrink:0;">
                <?= mb_substr($user['fullName'] ?? 'U', 0, 1) ?>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.85rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($user['fullName'] ?? '') ?></div>
                <div style="font-size:.72rem;color:#64748b;"><?= htmlspecialchars($user['role'] ?? '') ?></div>
            </div>
        </div>
        <a href="/php-cyber/logout.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:8px;text-decoration:none;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);font-size:.85rem;font-weight:600;justify-content:center;transition:all .2s;"
           onmouseover="this.style.background='rgba(239,68,68,.2)'"
           onmouseout="this.style.background='rgba(239,68,68,.08)'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            تسجيل الخروج
        </a>
    </div>
</div>
