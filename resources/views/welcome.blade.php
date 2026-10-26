<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'SiAbsen') }} — Portal Monitoring Presensi Pegawai | Kementerian PPN/Bappenas</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) && !file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                --font-sans: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                --bappenas-navy: #002d62;
                --bappenas-dark-navy: #001f44;
                --bappenas-light-navy: #0b3c5d;
                --bappenas-blue: #0284c7;
                --bappenas-gold: #c5a059;
                --bappenas-gold-light: #fef3c7;
            }
            * {
                box-sizing: border-box;
            }
            body {
                font-family: var(--font-sans);
                margin: 0;
                padding: 0;
                background-color: #f8fafc;
                color: #0f172a;
                line-height: 1.6;
                -webkit-font-smoothing: antialiased;
            }
            @media (prefers-color-scheme: dark) {
                body {
                    background-color: #080e1a;
                    color: #f1f5f9;
                }
            }
            .container-box {
                max-width: 1180px;
                margin-left: auto;
                margin-right: auto;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            /* Top Government Header Strip */
            .gov-top-bar {
                background: linear-gradient(90deg, #001f44 0%, #002d62 100%);
                color: #ffffff;
                font-size: 0.75rem;
                padding: 0.4rem 0;
                border-bottom: 2px solid var(--bappenas-gold);
            }
            .gov-top-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .gov-identity {
                display: flex;
                align-items: center;
                gap: 0.6rem;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                font-weight: 600;
            }
            .gov-identity-tag {
                background-color: rgba(197, 160, 89, 0.25);
                color: #fef08a;
                padding: 0.1rem 0.5rem;
                border-radius: 0.25rem;
                font-size: 0.68rem;
                border: 1px solid rgba(197, 160, 89, 0.4);
            }

            /* Main Header Navigation */
            header.main-nav {
                border-bottom: 1px solid #e2e8f0;
                background-color: #ffffff;
                position: sticky;
                top: 0;
                z-index: 50;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            }
            @media (prefers-color-scheme: dark) {
                header.main-nav {
                    background-color: #0d1527;
                    border-bottom-color: #1e293b;
                    box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.3);
                }
            }
            .nav-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 4.5rem;
            }
            .brand-wrapper {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                text-decoration: none;
                color: inherit;
            }
            .brand-badge-logo {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 2.75rem;
                height: 2.75rem;
                background: linear-gradient(135deg, var(--bappenas-dark-navy) 0%, var(--bappenas-navy) 60%, #0284c7 100%);
                color: #ffffff;
                border-radius: 0.6rem;
                box-shadow: 0 4px 10px rgba(0, 45, 98, 0.25);
                border: 1px solid rgba(197, 160, 89, 0.3);
            }
            .brand-titles h1 {
                font-size: 1.35rem;
                font-weight: 800;
                margin: 0;
                line-height: 1.15;
                letter-spacing: -0.02em;
                color: var(--bappenas-navy);
            }
            @media (prefers-color-scheme: dark) {
                .brand-titles h1 {
                    color: #ffffff;
                }
            }
            .brand-titles p {
                font-size: 0.75rem;
                color: #64748b;
                margin: 0;
                font-weight: 500;
            }
            @media (prefers-color-scheme: dark) {
                .brand-titles p {
                    color: #94a3b8;
                }
            }

            /* Buttons */
            .btn-bappenas-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                background: linear-gradient(135deg, #002d62 0%, #0b3c5d 100%);
                color: #ffffff;
                font-weight: 600;
                padding: 0.7rem 1.4rem;
                border-radius: 0.5rem;
                text-decoration: none;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 6px -1px rgba(0, 45, 98, 0.25), 0 2px 4px -2px rgba(0, 45, 98, 0.15);
                border: 1px solid rgba(197, 160, 89, 0.4);
            }
            .btn-bappenas-primary:hover {
                background: linear-gradient(135deg, #001f44 0%, #002d62 100%);
                transform: translateY(-1px);
                box-shadow: 0 6px 12px -2px rgba(0, 45, 98, 0.35);
                color: #ffffff;
            }
            .btn-bappenas-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                background-color: #ffffff;
                color: #002d62;
                font-weight: 600;
                padding: 0.7rem 1.35rem;
                border-radius: 0.5rem;
                text-decoration: none;
                border: 1px solid #cbd5e1;
                transition: all 0.2s;
            }
            .btn-bappenas-secondary:hover {
                background-color: #f1f5f9;
                border-color: #94a3b8;
            }
            @media (prefers-color-scheme: dark) {
                .btn-bappenas-secondary {
                    background-color: #0d1527;
                    color: #f1f5f9;
                    border-color: #334155;
                }
                .btn-bappenas-secondary:hover {
                    background-color: #1e293b;
                }
            }

            /* Hero Section */
            .hero-section {
                padding-top: 3.5rem;
                padding-bottom: 3rem;
                text-align: center;
            }
            .badge-bappenas {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.35rem 0.9rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                background-color: #f0f7ff;
                color: #002d62;
                border: 1px solid #bfdbfe;
                margin-bottom: 1.25rem;
            }
            @media (prefers-color-scheme: dark) {
                .badge-bappenas {
                    background-color: #0b1f3d;
                    color: #93c5fd;
                    border-color: #1e3a8a;
                }
            }
            .hero-title {
                font-size: 2.75rem;
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1.2;
                margin: 0 0 1rem 0;
                color: #0f172a;
            }
            @media (prefers-color-scheme: dark) {
                .hero-title {
                    color: #ffffff;
                }
            }
            .hero-highlight {
                color: var(--bappenas-navy);
                background: linear-gradient(135deg, #002d62 0%, #0284c7 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            @media (prefers-color-scheme: dark) {
                .hero-highlight {
                    background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
            }
            .hero-description {
                font-size: 1.15rem;
                color: #475569;
                max-width: 780px;
                margin: 0 auto 2.25rem auto;
                line-height: 1.6;
            }
            @media (prefers-color-scheme: dark) {
                .hero-description {
                    color: #94a3b8;
                }
            }
            .hero-actions-group {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
                margin-bottom: 2.5rem;
            }

            /* Disclaimer Alert */
            .alert-demo-box {
                background-color: #fffbeb;
                border: 1px solid #fde68a;
                border-left: 5px solid #d97706;
                color: #92400e;
                padding: 1rem 1.35rem;
                border-radius: 0.5rem;
                font-size: 0.875rem;
                line-height: 1.5;
                text-align: left;
                margin-bottom: 2.75rem;
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
            }
            @media (prefers-color-scheme: dark) {
                .alert-demo-box {
                    background-color: #1a1508;
                    border-color: #451a03;
                    border-left-color: #f59e0b;
                    color: #fef3c7;
                }
            }

            /* KPI Cards Section (PRD Section 5) */
            .section-header {
                text-align: left;
                margin-bottom: 1.25rem;
            }
            .section-header h3 {
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0;
                color: #0f172a;
            }
            @media (prefers-color-scheme: dark) {
                .section-header h3 {
                    color: #ffffff;
                }
            }
            .section-header p {
                font-size: 0.85rem;
                color: #64748b;
                margin: 0.2rem 0 0 0;
            }
            @media (prefers-color-scheme: dark) {
                .section-header p {
                    color: #94a3b8;
                }
            }

            .kpi-grid {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 1rem;
                margin-bottom: 2.75rem;
            }
            @media (max-width: 1024px) {
                .kpi-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }
            @media (max-width: 640px) {
                .kpi-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            .kpi-card {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 0.65rem;
                padding: 1.15rem 1rem;
                text-align: left;
                position: relative;
                overflow: hidden;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
            }
            @media (prefers-color-scheme: dark) {
                .kpi-card {
                    background-color: #0d1527;
                    border-color: #1e293b;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
                }
            }
            .kpi-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background-color: var(--bappenas-navy);
            }
            .kpi-card.kpi-success::before {
                background-color: #10b981;
            }
            .kpi-card.kpi-warning::before {
                background-color: #f59e0b;
            }
            .kpi-card.kpi-slate::before {
                background-color: #64748b;
            }
            .kpi-card.kpi-info::before {
                background-color: #0284c7;
            }
            .kpi-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                margin-bottom: 0.35rem;
            }
            @media (prefers-color-scheme: dark) {
                .kpi-label {
                    color: #94a3b8;
                }
            }
            .kpi-value {
                font-size: 1.65rem;
                font-weight: 800;
                line-height: 1.1;
                color: #0f172a;
            }
            @media (prefers-color-scheme: dark) {
                .kpi-value {
                    color: #ffffff;
                }
            }
            .kpi-desc {
                font-size: 0.72rem;
                color: #94a3b8;
                margin-top: 0.4rem;
            }

            /* Architecture & Pillars Grid (PRD Section 6, 7, 8) */
            .features-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1.5rem;
                margin-bottom: 3rem;
            }
            @media (max-width: 860px) {
                .features-grid {
                    grid-template-columns: repeat(1, minmax(0, 1fr));
                }
            }
            .feature-card {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 0.75rem;
                padding: 1.6rem;
                text-align: left;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            }
            @media (prefers-color-scheme: dark) {
                .feature-card {
                    background-color: #0d1527;
                    border-color: #1e293b;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3);
                }
            }
            .feature-badge-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.85rem;
                height: 2.85rem;
                border-radius: 0.5rem;
                background-color: #eff6ff;
                color: var(--bappenas-navy);
                margin-bottom: 1rem;
                border: 1px solid #bfdbfe;
            }
            @media (prefers-color-scheme: dark) {
                .feature-badge-icon {
                    background-color: #0b1f3d;
                    color: #60a5fa;
                    border-color: #1e3a8a;
                }
            }
            .feature-card h4 {
                font-size: 1.15rem;
                font-weight: 700;
                margin: 0 0 0.5rem 0;
                color: #0f172a;
            }
            @media (prefers-color-scheme: dark) {
                .feature-card h4 {
                    color: #ffffff;
                }
            }
            .feature-card p {
                font-size: 0.88rem;
                color: #475569;
                margin: 0;
                line-height: 1.55;
            }
            @media (prefers-color-scheme: dark) {
                .feature-card p {
                    color: #94a3b8;
                }
            }

            /* Credential Cards */
            .account-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem;
                margin-bottom: 3.5rem;
            }
            @media (max-width: 768px) {
                .account-grid {
                    grid-template-columns: repeat(1, minmax(0, 1fr));
                }
            }
            .account-card {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 0.75rem;
                padding: 1.5rem;
                text-align: left;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            }
            @media (prefers-color-scheme: dark) {
                .account-card {
                    background-color: #0d1527;
                    border-color: #1e293b;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.25);
                }
            }
            .badge-role {
                display: inline-block;
                padding: 0.25rem 0.65rem;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                border-radius: 0.375rem;
                margin-bottom: 0.75rem;
            }
            .badge-role-admin {
                background-color: #eff6ff;
                color: #1e40af;
                border: 1px solid #bfdbfe;
            }
            @media (prefers-color-scheme: dark) {
                .badge-role-admin {
                    background-color: #172554;
                    color: #93c5fd;
                    border-color: #1e3a8a;
                }
            }
            .badge-role-pegawai {
                background-color: #ecfdf5;
                color: #065f46;
                border: 1px solid #a7f3d0;
            }
            @media (prefers-color-scheme: dark) {
                .badge-role-pegawai {
                    background-color: #064e3b;
                    color: #6ee7b7;
                    border-color: #047857;
                }
            }
            .cred-table {
                width: 100%;
                font-size: 0.85rem;
                border-collapse: collapse;
                margin: 0.75rem 0 1.25rem 0;
            }
            .cred-table td {
                padding: 0.45rem 0;
                border-bottom: 1px dashed #e2e8f0;
            }
            @media (prefers-color-scheme: dark) {
                .cred-table td {
                    border-bottom-color: #1e293b;
                }
            }
            .cred-table td:first-child {
                color: #64748b;
                width: 38%;
            }
            @media (prefers-color-scheme: dark) {
                .cred-table td:first-child {
                    color: #94a3b8;
                }
            }
            .code-text {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                background-color: #f1f5f9;
                color: #002d62;
                padding: 0.15rem 0.45rem;
                border-radius: 0.25rem;
                font-size: 0.8rem;
                font-weight: 600;
            }
            @media (prefers-color-scheme: dark) {
                .code-text {
                    background-color: #1e293b;
                    color: #93c5fd;
                }
            }

            /* Footer */
            footer.gov-footer {
                border-top: 1px solid #e2e8f0;
                background-color: #ffffff;
                padding: 2.25rem 0;
                text-align: center;
                font-size: 0.825rem;
                color: #64748b;
            }
            @media (prefers-color-scheme: dark) {
                footer.gov-footer {
                    background-color: #080e1a;
                    border-top-color: #1e293b;
                    color: #94a3b8;
                }
            }
        </style>
    </head>
    <body>
        <!-- Government Top Identity Strip (Bappenas Brand Reference) -->
        <div class="gov-top-bar">
            <div class="container-box">
                <div class="gov-top-inner">
                    <div class="gov-identity">
                        <span>🇮🇩 KEMENTERIAN PPN / BAPPENAS</span>
                        <span style="opacity: 0.5;">|</span>
                        <span>REPUBLIK INDONESIA</span>
                    </div>
                    <div>
                        <span class="gov-identity-tag">Sistem Informasi SDM Terpadu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header / Navigation -->
        <header class="main-nav">
            <div class="container-box">
                <div class="nav-inner">
                    <a href="{{ url('/') }}" class="brand-wrapper">
                        <div class="brand-badge-logo">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="brand-titles">
                            <h1>SiAbsen</h1>
                            <p>Admin Web & Monitoring Absensi Pegawai</p>
                        </div>
                    </a>

                    <nav style="display: flex; align-items: center; gap: 0.75rem;">
                        @auth
                            <a href="{{ url('/admin') }}" class="btn-bappenas-primary" style="padding: 0.55rem 1.15rem; font-size: 0.875rem;">
                                <span>Buka Dashboard Admin</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        @else
                            <a href="{{ url('/admin/login') }}" class="btn-bappenas-primary" style="padding: 0.55rem 1.25rem; font-size: 0.875rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                <span>Login Admin Web</span>
                            </a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="container-box">
            <!-- Hero Section (PRD Section 1 & 4) -->
            <section class="hero-section">
                <div class="badge-bappenas">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>Kementerian PPN / Bappenas &bull; Platform Monitoring SDM</span>
                </div>

                <h2 class="hero-title">
                    Sistem Monitoring & Pemetaan Absensi<br>
                    <span class="hero-highlight">Geofencing Pegawai Modern</span>
                </h2>

                <p class="hero-description">
                    <strong>SiAbsen Admin Web</strong> berfungsi sebagai <em>monitoring dan management console</em> bagi <strong>Admin HR</strong> untuk memantau kehadiran harian, memverifikasi log posisi GPS pegawai, serta memetakan titik koordinat poligon kantor secara presisi.
                </p>

                <div class="hero-actions-group">
                    <a href="{{ url('/admin/login') }}" class="btn-bappenas-primary" style="font-size: 1rem; padding: 0.85rem 1.85rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Masuk ke Admin Web (SiAbsen Console)</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>

                    <a href="{{ url('/api/v1/office-locations') }}" target="_blank" class="btn-bappenas-secondary" style="font-size: 1rem; padding: 0.85rem 1.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>Endpoint Lokasi Absensi Mobile</span>
                    </a>
                </div>

                <!-- Disclaimer Demo & Pembelajaran -->
                <div class="alert-demo-box">
                    <div style="font-size: 1.35rem; line-height: 1;">⚠️</div>
                    <div>
                        <strong>Pemberitahuan Demo & Pembelajaran:</strong>
                        Aplikasi ini dibangun untuk keperluan <strong>demonstrasi teknis dan pembelajaran</strong> arsitektur <em>Laravel 13, Filament v5, Service Pattern</em>, serta <em>Geofencing Poligon GPS</em> sesuai spesifikasi dokumen <strong>PRD SiAbsen</strong>. Seluruh akun pengujian, data NIP pegawai, dan titik koordinat absensi merupakan data simulasi (*dummy*).
                    </div>
                </div>
            </section>

            <!-- 5 KPI Cards Section (PRD Section 5: Dashboard Overview) -->
            <section>
                <div class="section-header">
                    <h3>5 Indikator Utama (KPI Dashboard Harian)</h3>
                    <p>Ringkasan cepat (*quick overview*) kondisi kehadiran pegawai yang dipantau oleh Admin HR hari ini:</p>
                </div>

                <div class="kpi-grid">
                    <!-- KPI 1: Total Pegawai -->
                    <div class="kpi-card kpi-info">
                        <div class="kpi-label">Total Pegawai</div>
                        <div class="kpi-value">4</div>
                        <div class="kpi-desc">Pegawai terdaftar aktif</div>
                    </div>

                    <!-- KPI 2: Sudah Absen -->
                    <div class="kpi-card kpi-success">
                        <div class="kpi-label">Sudah Absen</div>
                        <div class="kpi-value" style="color: #059669;">1</div>
                        <div class="kpi-desc">Tercatat hadir tepat waktu</div>
                    </div>

                    <!-- KPI 3: Belum Absen -->
                    <div class="kpi-card kpi-slate">
                        <div class="kpi-label">Belum Absen</div>
                        <div class="kpi-value" style="color: #64748b;">3</div>
                        <div class="kpi-desc">Menunggu kehadiran pegawai</div>
                    </div>

                    <!-- KPI 4: Terlambat -->
                    <div class="kpi-card kpi-warning">
                        <div class="kpi-label">Terlambat</div>
                        <div class="kpi-value" style="color: #d97706;">0</div>
                        <div class="kpi-desc">Melewati batas jam 08:00 WIB</div>
                    </div>

                    <!-- KPI 5: Lokasi Aktif -->
                    <div class="kpi-card">
                        <div class="kpi-label">Lokasi Aktif</div>
                        <div class="kpi-value" style="color: #002d62;">2</div>
                        <div class="kpi-desc">Titik kantor geofence aktif</div>
                    </div>
                </div>
            </section>

            <!-- Fitur Utama Berdasarkan Dokumen PRD (PRD Section 6, 7, 8, 9) -->
            <section>
                <div class="section-header">
                    <h3>Struktur Fungsional Admin Web SiAbsen</h3>
                    <p>Fitur inti yang disediakan bagi Admin HR untuk monitoring dan verifikasi:</p>
                </div>

                <div class="features-grid">
                    <!-- Feature 1: Log & Transaksi Absensi -->
                    <div class="feature-card">
                        <div class="feature-badge-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <h4>1. Log Absensi & Detail Transaksi</h4>
                        <p>Memantau seluruh transaksi kehadiran secara <em>real-time</em> dengan filter tanggal, nama/NIP pegawai, unit kerja, status (<span style="color: #059669; font-weight:600;">Hadir</span>, <span style="color: #d97706; font-weight:600;">Terlambat</span>, <span style="color: #dc2626; font-weight:600;">Di Luar Area</span>), identitas perangkat smartphone, serta IP address.</p>
                    </div>

                    <!-- Feature 2: Visualisasi Peta Leaflet OSM -->
                    <div class="feature-card">
                        <div class="feature-badge-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        </div>
                        <h4>2. Peta Interaktif Titik Absensi</h4>
                        <p>Komponen visual utama pada detail absensi: peta interaktif <strong>Leaflet OpenStreetMap</strong> yang membandingkan koordinat GPS pegawai saat absen secara langsung terhadap titik pusat radius dan batas poligon area kantor.</p>
                    </div>

                    <!-- Feature 3: Pemetaan Lokasi Geofence -->
                    <div class="feature-card">
                        <div class="feature-badge-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                                <line x1="8" y1="2" x2="8" y2="18"></line>
                                <line x1="16" y1="6" x2="16" y2="22"></line>
                            </svg>
                        </div>
                        <h4>3. Pemetaan Titik Lokasi Kantor</h4>
                        <p>Manajemen titik absensi dengan pendekatan <em>List + Map</em>. Admin HR dapat menentukan titik pusat koordinat, radius toleransi meter, serta batas poligon multi-titik menggunakan algoritma <strong>Ray-Casting (Jordan Curve Theorem)</strong>.</p>
                    </div>
                </div>
            </section>

            <!-- Kredensial Akses Pengujian (Demo Akun) -->
            <section class="account-grid">
                <!-- Kartu Akun Admin Web -->
                <div class="account-card">
                    <span class="badge-role badge-role-admin">Kredensial Admin HR (Web Console)</span>
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: #002d62;">Console Admin HR (Filament v5)</h3>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0.35rem 0 0.85rem 0;">Akses penuh untuk monitoring absensi harian, audit transaksi, dan konfigurasi geofence kantor.</p>

                    <table class="cred-table">
                        <tr>
                            <td>URL Halaman Login</td>
                            <td><a href="{{ url('/admin/login') }}" class="code-text" style="text-decoration: none;">/admin/login</a></td>
                        </tr>
                        <tr>
                            <td>Email Pengguna</td>
                            <td><span class="code-text">admin@kantor.com</span></td>
                        </tr>
                        <tr>
                            <td>Kata Sandi (Password)</td>
                            <td><span class="code-text">password</span></td>
                        </tr>
                        <tr>
                            <td>Hak Akses</td>
                            <td><span style="font-weight: 700; color: #002d62;">Admin HR / SDM</span></td>
                        </tr>
                    </table>

                    <a href="{{ url('/admin/login') }}" class="btn-bappenas-primary" style="width: 100%; box-sizing: border-box; text-align: center;">
                        <span>Masuk ke Panel Admin &rarr;</span>
                    </a>
                </div>

                <!-- Kartu Akun Pegawai API -->
                <div class="account-card">
                    <span class="badge-role badge-role-pegawai">Kredensial Pegawai (Mobile Client)</span>
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: #065f46;">Simulasi Mobile API Pegawai</h3>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0.35rem 0 0.85rem 0;">Akun pegawai untuk pengujian otentikasi RESTful API, validasi check-in geofencing, dan check-out.</p>

                    <table class="cred-table">
                        <tr>
                            <td>Endpoint Otentikasi</td>
                            <td><span class="code-text">POST /api/v1/auth/login</span></td>
                        </tr>
                        <tr>
                            <td>Nomor Induk Pegawai</td>
                            <td><span class="code-text">199205122018021002</span></td>
                        </tr>
                        <tr>
                            <td>Email Pegawai</td>
                            <td><span class="code-text">pegawai@kantor.com</span></td>
                        </tr>
                        <tr>
                            <td>Kata Sandi (Password)</td>
                            <td><span class="code-text">password</span></td>
                        </tr>
                    </table>

                    <a href="{{ url('/api/v1/attendance/today') }}" target="_blank" class="btn-bappenas-secondary" style="width: 100%; box-sizing: border-box; text-align: center;">
                        <span>Lihat Status Presensi Hari Ini (/today) &rarr;</span>
                    </a>
                </div>
            </section>
        </main>

        <!-- Government Standard Footer (PRD Brand Identity) -->
        <footer class="gov-footer">
            <div class="container-box">
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.6rem;">
                    <div style="font-weight: 800; color: var(--bappenas-navy); font-size: 0.95rem;">SiAbsen</div>
                    <span style="opacity: 0.4;">&bull;</span>
                    <div style="font-weight: 600; color: #334155;">Kementerian PPN / Bappenas</div>
                </div>
                <p style="margin: 0 0 0.4rem 0;">
                    Aplikasi Administrasi & Monitoring Presensi Pegawai Berbasis Geofencing GPS
                </p>
                <p style="margin: 0; font-size: 0.75rem; color: #94a3b8;">
                    Dibuat untuk keperluan <strong>Demo &amp; Pembelajaran</strong> &bull; Laravel v{{ app()->version() }} &bull; Filament v5 &bull; PHP v{{ PHP_VERSION }}
                </p>
            </div>
        </footer>
    </body>
</html>
