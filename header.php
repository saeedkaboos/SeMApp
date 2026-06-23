<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SecureSOC') ?> | نظام إدارة الأمن السيبراني</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #050d1a; color: #e2e8f0; }

        /* ===== القائمة الجانبية ===== */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: #0a1628;
            border-left: 1px solid rgba(6,182,212,.15);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            position: relative;
            z-index: 40;
        }

        /* ستارة الخلفية على الموبايل */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.7);
            z-index: 39;
            backdrop-filter: blur(3px);
            cursor: pointer;
        }
        #sidebar-overlay.show { display: block; }

        /* زر الهامبرغر (موبايل فقط) */
        #sidebar-toggle {
            display: none;
            position: fixed;
            top: 14px;
            right: 14px;
            z-index: 50;
            width: 44px;
            height: 44px;
            background: #0f2040;
            border: 1px solid rgba(6,182,212,.35);
            border-radius: 10px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #22d3ee;
            box-shadow: 0 4px 20px rgba(0,0,0,.5);
        }

        /* زر الإغلاق داخل القائمة */
        #sidebar-close {
            display: none;
            position: absolute;
            top: 14px;
            left: 14px;
            width: 30px;
            height: 30px;
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 7px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #f87171;
            z-index: 10;
            transition: background .2s;
        }
        #sidebar-close:hover { background: rgba(239,68,68,.28); }

        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0;
                right: 0;
                bottom: 0;
                height: 100vh;
                transform: translateX(110%);
                z-index: 40;
                box-shadow: none;
            }
            #sidebar.open {
                transform: translateX(0);
                box-shadow: -10px 0 50px rgba(0,0,0,.7);
            }
            #sidebar-toggle { display: flex; }
            #sidebar-close  { display: flex; }
            .page-main { padding-top: 68px !important; }
        }

        /* ===== ستايلات مشتركة ===== */
        .sidebar-link { transition: all .2s; border-right: 3px solid transparent; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(6,182,212,.12); border-right-color: #06b6d4; color: #22d3ee; }
        .card { background: #0f2040; border: 1px solid rgba(6,182,212,.15); border-radius: 12px; }
        .badge-critical { background:rgba(239,68,68,.15); color:#f87171; border:1px solid rgba(239,68,68,.3); }
        .badge-high     { background:rgba(249,115,22,.15); color:#fb923c; border:1px solid rgba(249,115,22,.3); }
        .badge-medium   { background:rgba(234,179,8,.15);  color:#facc15; border:1px solid rgba(234,179,8,.3); }
        .badge-low      { background:rgba(34,197,94,.15);  color:#4ade80; border:1px solid rgba(34,197,94,.3); }
        .badge-open          { background:rgba(239,68,68,.1);  color:#f87171; }
        .badge-investigating { background:rgba(249,115,22,.1); color:#fb923c; }
        .badge-resolved      { background:rgba(34,197,94,.1);  color:#4ade80; }
        .badge-closed        { background:rgba(100,116,139,.1);color:#94a3b8; }
        .badge-admin   { background:rgba(139,92,246,.15); color:#a78bfa; }
        .badge-analyst { background:rgba(6,182,212,.15);  color:#22d3ee;  }
        .badge-viewer  { background:rgba(100,116,139,.15);color:#94a3b8;  }
        .btn-primary { background:linear-gradient(135deg,#06b6d4,#0891b2); color:#fff; border-radius:8px; padding:8px 20px; font-weight:600; transition:opacity .2s; border:none; cursor:pointer; font-family:'Cairo',sans-serif; }
        .btn-primary:hover { opacity:.85; }
        .btn-danger  { background:rgba(239,68,68,.15); color:#f87171; border:1px solid rgba(239,68,68,.3); border-radius:8px; padding:6px 14px; font-weight:600; transition:all .2s; cursor:pointer; font-family:'Cairo',sans-serif; text-decoration:none; display:inline-block; }
        .btn-danger:hover { background:rgba(239,68,68,.3); }
        .input-field { background:#0a1628; border:1px solid rgba(6,182,212,.2); border-radius:8px; color:#e2e8f0; padding:10px 14px; width:100%; transition:border-color .2s; font-family:'Cairo',sans-serif; }
        .input-field:focus { outline:none; border-color:#06b6d4; }
        table { border-collapse:separate; border-spacing:0; width:100%; }
        th { background:#0a1628; color:#94a3b8; font-weight:600; font-size:.8rem; padding:12px 16px; text-align:right; }
        td { padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.05); }
        tr:hover td { background:rgba(6,182,212,.04); }
        .alert-success { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#4ade80; border-radius:8px; padding:12px 16px; margin-bottom:16px; }
        .alert-error   { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3);  color:#f87171; border-radius:8px; padding:12px 16px; margin-bottom:16px; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:60; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:#0f2040; border:1px solid rgba(6,182,212,.2); border-radius:16px; padding:28px; min-width:300px; max-width:600px; width:100%; max-height:90vh; overflow-y:auto; }
        select.input-field option { background:#0a1628; }
    </style>
</head>
<body class="dark">

<!-- زر فتح القائمة (موبايل) -->
<button id="sidebar-toggle" onclick="openSidebar()" aria-label="فتح القائمة">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
        <line x1="3" y1="6"  x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<!-- الستارة الداكنة — النقر عليها يغلق القائمة -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('show');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
});
</script>
