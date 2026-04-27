<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attackers Lechon Manok POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --red-primary: #C0392B;
            --red-dark: #922B21;
            --red-darker: #641E16;
            --red-light: #E74C3C;
            --red-accent: #FF6B6B;
            --sidebar-bg: #1a0a08;
            --sidebar-width: 230px;
            --white: #FFFFFF;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-600: #4B5563;
            --gray-800: #1F2937;
            --success: #059669;
            --warning: #D97706;
            --info: #2563EB;
            --font: 'Outfit', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font);
            background: var(--gray-100);
            display: flex;
            min-height: 100vh;
            color: var(--gray-800);
        }

        /* ─── SIDEBAR ─────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(192,57,43,0.3) 0%, transparent 70%);
            pointer-events: none;
        }

        .sidebar-logo {
            padding: 22px 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px; height: 40px;
            background: var(--red-primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(192,57,43,0.5);
        }

        .logo-text { line-height: 1.2; }
        .logo-text strong { 
            display: block; color: #fff; 
            font-size: 14px; font-weight: 700; letter-spacing: 0.3px;
        }
        .logo-text span { 
            color: rgba(255,255,255,0.45); 
            font-size: 10px; text-transform: uppercase; letter-spacing: 1px;
        }

        .sidebar-user {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex; align-items: center; gap: 10px;
        }

        .user-avatar {
            width: 34px; height: 34px;
            background: var(--red-dark);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }

        .user-info { overflow: hidden; }
        .user-info .name { 
            color: #fff; font-size: 13px; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-info .role { 
            color: rgba(255,255,255,0.4); font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 10px;
            overflow-y: auto;
        }

        .nav-label {
            color: rgba(255,255,255,0.25);
            font-size: 9px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.5px;
            padding: 0 10px 8px;
            margin-top: 8px;
        }

        .nav-item {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 13.5px; font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
            position: relative;
        }

        .nav-item i { 
            width: 18px; text-align: center; 
            font-size: 14px;
            transition: color 0.15s;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.07);
            color: rgba(255,255,255,0.9);
        }

        .nav-item.active {
            background: var(--red-primary);
            color: #fff;
            box-shadow: 0 4px 14px rgba(192,57,43,0.45);
        }

        .nav-item.active i { color: #fff; }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .sidebar-date {
            color: rgba(255,255,255,0.35);
            font-size: 11px;
        }
        .sidebar-date strong { display: block; color: rgba(255,255,255,0.6); font-size: 12px; }

        /* ─── MAIN ────────────────────────────────────────────── */
        .main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }

        .page-title { font-size: 18px; font-weight: 700; color: var(--gray-800); }
        .page-subtitle { font-size: 12px; color: var(--gray-600); margin-top: 1px; }

        .topbar-actions { display: flex; align-items: center; gap: 12px; }

        .topbar-date {
            background: var(--gray-100);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 12px; color: var(--gray-600); font-weight: 500;
        }

        .content { padding: 24px 28px; flex: 1; }

        /* ─── CARDS ───────────────────────────────────────────── */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-200);
            display: flex; align-items: center; justify-content: space-between;
        }

        .card-title { font-size: 15px; font-weight: 700; color: var(--gray-800); }
        .card-subtitle { font-size: 12px; color: var(--gray-600); }
        .card-body { padding: 20px; }

        /* ─── STAT CARD ───────────────────────────────────────── */
        .stat-card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
        }

        .stat-card .icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .stat-card .value { font-size: 24px; font-weight: 800; color: var(--gray-800); }
        .stat-card .label { font-size: 12px; color: var(--gray-600); font-weight: 500; margin-top: 3px; }
        .stat-card .trend { 
            position: absolute; top: 18px; right: 18px;
            font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px;
        }
        .trend.up { background: #D1FAE5; color: #065F46; }
        .trend.down { background: #FEE2E2; color: #991B1B; }

        /* ─── BUTTONS ─────────────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            font-family: var(--font);
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-primary { background: var(--red-primary); color: #fff; }
        .btn-primary:hover { background: var(--red-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(192,57,43,0.35); }

        .btn-secondary { background: var(--gray-100); color: var(--gray-800); border: 1px solid var(--gray-200); }
        .btn-secondary:hover { background: var(--gray-200); }

        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #047857; }

        .btn-danger { background: #EF4444; color: #fff; }
        .btn-danger:hover { background: #DC2626; }

        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-lg { padding: 12px 24px; font-size: 15px; }

        /* ─── TABLE ───────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--gray-50);
            padding: 11px 16px;
            text-align: left;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--gray-600);
            border-bottom: 1px solid var(--gray-200);
        }
        tbody td {
            padding: 13px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-800);
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: var(--gray-50); }

        /* ─── BADGES ──────────────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .badge-red { background: #FEE2E2; color: #991B1B; }
        .badge-green { background: #D1FAE5; color: #065F46; }
        .badge-yellow { background: #FEF3C7; color: #92400E; }
        .badge-blue { background: #DBEAFE; color: #1E40AF; }
        .badge-gray { background: var(--gray-100); color: var(--gray-600); }

        /* ─── FORMS ───────────────────────────────────────────── */
        .form-group { margin-bottom: 16px; }
        .form-label { 
            display: block; font-size: 13px; font-weight: 600; 
            color: var(--gray-800); margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 9px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: 8px;
            font-family: var(--font);
            font-size: 14px;
            color: var(--gray-800);
            background: #fff;
            transition: border-color 0.15s;
            outline: none;
        }
        .form-control:focus { border-color: var(--red-primary); box-shadow: 0 0 0 3px rgba(192,57,43,0.1); }

        select.form-control { cursor: pointer; }

        /* ─── ALERTS ──────────────────────────────────────────── */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

        /* ─── GRID ────────────────────────────────────────────── */
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        @media (max-width: 1200px) { .grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { 
            .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .main { margin-left: 0; }
        }

        /* ─── SCROLLBAR ───────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 4px; }

        /* ─── FLASH MESSAGES ──────────────────────────────────── */
        .flash-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .flash {
            background: #1F2937;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13px;
            display: flex; align-items: center; gap: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
            margin-bottom: 8px;
        }
        .flash.success { border-left: 4px solid var(--success); }
        .flash.error { border-left: 4px solid #EF4444; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- ─── SIDEBAR ──────────────────────────────────────────────── -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🍗</div>
        <div class="logo-text">
            <strong>Attackers</strong>
            <span>Lechon Manok POS</span>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">C1</div>
        <div class="user-info">
            <div class="name">Cashier 01</div>
            <div class="role">Cashier</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="{{ route('pos') }}" class="nav-item {{ request()->routeIs('pos') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> POS / Cashier
        </a>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> Sales History
        </a>

        <div class="nav-label" style="margin-top:12px;">Management</div>
        <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            <i class="fas fa-boxes-stacked"></i> Inventory
        </a>
        <a href="{{ route('production.index') }}" class="nav-item {{ request()->routeIs('production.*') ? 'active' : '' }}">
            <i class="fas fa-fire-burner"></i> Production
        </a>
        <a href="{{ route('stock-in.index') }}" class="nav-item {{ request()->routeIs('stock-in.*') ? 'active' : '' }}">
            <i class="fas fa-truck-ramp-box"></i> Stock In
        </a>

        <div class="nav-label" style="margin-top:12px;">Settings</div>
        <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="fas fa-drumstick-bite"></i> Products
        </a>
        <a href="{{ route('suppliers.index') }}" class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="fas fa-people-carry-box"></i> Suppliers
        </a>
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-date">
            <strong>{{ now()->format('F d, Y') }}</strong>
            {{ now()->format('l') }}
        </div>
    </div>
</aside>

<!-- ─── MAIN ─────────────────────────────────────────────────── -->
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">@yield('title', 'Dashboard')</div>
            <div class="page-subtitle">@yield('subtitle', 'Attackers Lechon Manok — Bunawan Branch')</div>
        </div>
        <div class="topbar-actions">
            @yield('topbar-actions')
            <div class="topbar-date">
                <i class="fas fa-calendar-days" style="color:var(--red-primary)"></i>
                {{ now()->format('M d, Y · h:i A') }}
            </div>
        </div>
    </div>

    <div class="content">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </div>
</main>

<script>
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4000);
</script>
@stack('scripts')
</body>
</html>