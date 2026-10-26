<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'SiAbsen') }} — Sistem Monitoring Presensi SDM</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                --font-sans: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }
            * {
                box-sizing: border-box;
            }
            body {
                font-family: var(--font-sans);
                margin: 0;
                padding: 0;
                background-color: #f8fafc;
                color: #1e293b;
                line-height: 1.6;
            }
            @media (prefers-color-scheme: dark) {
                body {
                    background-color: #0b1120;
                    color: #f1f5f9;
                }
            }
            .container-box {
                max-width: 1120px;
                margin-left: auto;
                margin-right: auto;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            header {
                border-bottom: 1px solid #e2e8f0;
                background-color: #ffffff;
                position: sticky;
                top: 0;
                z-index: 40;
            }
            @media (prefers-color-scheme: dark) {
                header {
                    background-color: #0f172a;
                    border-bottom-color: #1e293b;
                }
            }
            .header-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 4.25rem;
            }
            .brand-group {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                text-decoration: none;
                color: inherit;
            }
            .brand-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
                color: #ffffff;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.3);
            }
            .brand-text h1 {
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0;
                line-height: 1.2;
                letter-spacing: -0.025em;
            }
            .brand-text p {
                font-size: 0.75rem;
                color: #64748b;
                margin: 0;
            }
            @media (prefers-color-scheme: dark) {
                .brand-text p {
                    color: #94a3b8;
                }
            }
            .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                background-color: #f59e0b;
                color: #ffffff;
                font-weight: 600;
                padding: 0.65rem 1.25rem;
                border-radius: 0.5rem;
                text-decoration: none;
                transition: all 0.2s;
                box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.25), 0 2px 4px -2px rgba(245, 158, 11, 0.25);
            }
            .btn-primary:hover {
                background-color: #d97706;
                transform: translateY(-1px);
                box-shadow: 0 6px 12px -2px rgba(245, 158, 11, 0.35);
            }
            .btn-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                background-color: #ffffff;
                color: #334155;
                font-weight: 600;
                padding: 0.65rem 1.25rem;
                border-radius: 0.5rem;
                text-decoration: none;
                border: 1px solid #cbd5e1;
                transition: all 0.2s;
            }
            .btn-secondary:hover {
                background-color: #f1f5f9;
                border-color: #94a3b8;
            }
            @media (prefers-color-scheme: dark) {
                .btn-secondary {
                    background-color: #1e293b;
                    color: #f1f5f9;
                    border-color: #334155;
                }
                .btn-secondary:hover {
                    background-color: #334155;
                }
            }
            .hero-section {
                padding-top: 4rem;
                padding-bottom: 3.5rem;
                text-align: center;
            }
            .badge-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.35rem 0.85rem;
                border-radius: 9999px;
                font-size: 0.8rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                background-color: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
                margin-bottom: 1.5rem;
            }
            @media (prefers-color-scheme: dark) {
                .badge-pill {
                    background-color: #451a03;
                    color: #fde68a;
                    border-color: #78350f;
                }
            }
            .hero-title {
                font-size: 2.75rem;
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1.15;
                margin: 0 0 1.25rem 0;
            }
            @media (max-width: 640px) {
                .hero-title {
                    font-size: 2rem;
                }
            }
            .hero-subtitle {
                font-size: 1.15rem;
                color: #64748b;
                max-width: 760px;
                margin: 0 auto 2.25rem auto;
            }
            @media (prefers-color-scheme: dark) {
                .hero-subtitle {
                    color: #94a3b8;
                }
            }
            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
                margin-bottom: 2.5rem;
            }
            .disclaimer-banner {
                background-color: #fffbeb;
                border: 1px solid #fef08a;
                border-left: 5px solid #f59e0b;
                color: #92400e;
                padding: 1.15rem 1.5rem;
                border-radius: 0.5rem;
                font-size: 0.925rem;
                line-height: 1.5;
                text-align: left;
                margin-bottom: 3rem;
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
            }
            @media (prefers-color-scheme: dark) {
                .disclaimer-banner {
                    background-color: #1e1b12;
                    border-color: #451a03;
                    border-left-color: #f59e0b;
                    color: #fef3c7;
                }
            }
            .grid-two {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem;
                margin-bottom: 3rem;
            }
            @media (max-width: 768px) {
                .grid-two {
                    grid-template-columns: repeat(1, minmax(0, 1fr));
                }
            }
            .grid-three {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1.5rem;
                margin-bottom: 4rem;
            }
            @media (max-width: 900px) {
                .grid-three {
                    grid-template-columns: repeat(1, minmax(0, 1fr));
                }
            }
            .card {
                background-color: #ffffff;
                border-radius: 0.75rem;
                border: 1px solid #e2e8f0;
                padding: 1.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
                text-align: left;
            }
            @media (prefers-color-scheme: dark) {
                .card {
                    background-color: #131c31;
                    border-color: #1e293b;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3);
                }
            }
            .card-header-badge {
                display: inline-block;
                padding: 0.25rem 0.65rem;
                font-size: 0.75rem;
                font-weight: 700;
                border-radius: 0.375rem;
                margin-bottom: 0.75rem;
            }
            .badge-admin {
                background-color: #eff6ff;
                color: #1d4ed8;
                border: 1px solid #bfdbfe;
            }
            @media (prefers-color-scheme: dark) {
                .badge-admin {
                    background-color: #1e3a8a;
                    color: #bfdbfe;
                    border-color: #1d4ed8;
                }
            }
            .badge-pegawai {
                background-color: #ecfdf5;
                color: #047857;
                border: 1px solid #a7f3d0;
            }
            @media (prefers-color-scheme: dark) {
                .badge-pegawai {
                    background-color: #064e3b;
                    color: #a7f3d0;
                    border-color: #047857;
                }
            }
            .card h3 {
                font-size: 1.2rem;
                font-weight: 700;
                margin: 0 0 0.5rem 0;
            }
            .card p {
                font-size: 0.9rem;
                color: #64748b;
                margin: 0 0 1rem 0;
            }
            @media (prefers-color-scheme: dark) {
                .card p {
                    color: #94a3b8;
                }
            }
            .credential-list {
                list-style: none;
                padding: 0;
                margin: 0 0 1.25rem 0;
                font-size: 0.875rem;
            }
            .credential-list li {
                display: flex;
                justify-content: space-between;
                padding: 0.4rem 0;
                border-bottom: 1px dashed #e2e8f0;
            }
            @media (prefers-color-scheme: dark) {
                .credential-list li {
                    border-bottom-color: #1e293b;
                }
            }
            .code-pill {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                background-color: #f1f5f9;
                color: #0f172a;
                padding: 0.15rem 0.45rem;
                border-radius: 0.25rem;
                font-size: 0.825rem;
                font-weight: 600;
            }
            @media (prefers-color-scheme: dark) {
                .code-pill {
                    background-color: #1e293b;
                    color: #f8fafc;
                }
            }
            .feature-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.75rem;
                height: 2.75rem;
                border-radius: 0.5rem;
                background-color: #f8fafc;
                color: #f59e0b;
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0;
            }
            @media (prefers-color-scheme: dark) {
                .feature-icon {
                    background-color: #1e293b;
                    border-color: #334155;
                }
            }
            footer {
                border-top: 1px solid #e2e8f0;
                padding: 2rem 0;
                text-align: center;
                font-size: 0.85rem;
                color: #64748b;
            }
            @media (prefers-color-scheme: dark) {
                footer {
                    border-top-color: #1e293b;
                    color: #94a3b8;
                }
            }
        </style>
    </head>
    <body>
        <!-- Header / Navigation -->
        <header>
            <div class="container-box">
                <div class="header-inner">
                    <a href="{{ url('/') }}" class="brand-group">
                        <div class="brand-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="brand-text">
                            <h1>SiAbsen</h1>
                            <p>Sistem Monitoring Presensi SDM</p>
                        </div>
                    </a>

                    <nav style="display: flex; align-items: center; gap: 0.75rem;">
                        @auth
                            <a href="{{ url('/admin') }}" class="btn-primary" style="padding: 0.55rem 1.15rem; font-size: 0.875rem;">
                                <span>Buka Dashboard Admin</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        @else
                            <a href="{{ url('/admin/login') }}" class="btn-primary" style="padding: 0.55rem 1.15rem; font-size: 0.875rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                <span>Login Admin</span>
                            </a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container-box">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="badge-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>Proyek Demo & Pembelajaran</span>
                </div>

                <h2 class="hero-title">
                    Sistem Monitoring & Presensi Pegawai<br>
                    <span style="color: #f59e0b;">Dual Geofencing GPS</span>
                </h2>

                <p class="hero-subtitle">
                    Aplikasi presensi pegawai terintegrasi dengan validasi poligon koordinat kantor (Jordan Curve Theorem & Haversine Formula). Dilengkapi <strong>Panel Admin Filament v5</strong> untuk HR SDM dan <strong>RESTful Mobile API</strong> berstandar Laravel Sanctum.
                </p>

                <div class="hero-actions">
                    <a href="{{ url('/admin/login') }}" class="btn-primary" style="font-size: 1rem; padding: 0.85rem 1.75rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Masuk ke Panel Admin (Filament)</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>

                    <a href="{{ url('/api/v1/office-locations') }}" target="_blank" class="btn-secondary" style="font-size: 1rem; padding: 0.85rem 1.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>Endpoint API Lokasi Kantor</span>
                    </a>
                </div>

                <!-- Disclaimer Banner -->
                <div class="disclaimer-banner">
                    <div style="font-size: 1.35rem; line-height: 1;">⚠️</div>
                    <div>
                        <strong>Pemberitahuan Demo & Pembelajaran:</strong>
                        Aplikasi ini dibuat secara khusus untuk keperluan <strong>demonstrasi dan pembelajaran</strong> arsitektur Laravel 13, Filament v5, Service Pattern, dan Geofencing GPS. Seluruh akun, NIP pegawai, titik koordinat, dan riwayat presensi yang tersedia merupakan data contoh (*dummy*).
                    </div>
                </div>
            </section>

            <!-- Quick Access & Credentials -->
            <section class="grid-two">
                <!-- Admin Card -->
                <div class="card">
                    <span class="card-header-badge badge-admin">Akses Web Dashboard HR</span>
                    <h3>Admin SDM (Filament v5)</h3>
                    <p>Dashboard analitik 5 KPI Cards, Peta Interaktif Leaflet OSM titik presensi pegawai, pengelolaan geofence poligon kantor, dan data master pegawai.</p>
                    
                    <ul class="credential-list">
                        <li>
                            <span style="color: #64748b;">URL Login Panel:</span>
                            <a href="{{ url('/admin/login') }}" class="code-pill" style="color: #2563eb; text-decoration: none;">/admin/login</a>
                        </li>
                        <li>
                            <span style="color: #64748b;">Email:</span>
                            <span class="code-pill">admin@kantor.com</span>
                        </li>
                        <li>
                            <span style="color: #64748b;">Password:</span>
                            <span class="code-pill">password</span>
                        </li>
                        <li>
                            <span style="color: #64748b;">Hak Akses:</span>
                            <span style="font-weight: 600;">Full HR Administrator</span>
                        </li>
                    </ul>

                    <a href="{{ url('/admin/login') }}" class="btn-primary" style="width: 100%; box-sizing: border-box;">
                        <span>Buka Panel Admin Sekarang &rarr;</span>
                    </a>
                </div>

                <!-- Mobile / Employee API Card -->
                <div class="card">
                    <span class="card-header-badge badge-pegawai">Akses Mobile API (Sanctum)</span>
                    <h3>Pegawai (Mobile Smartphone Client)</h3>
                    <p>Endpoint RESTful API untuk otentikasi NIP/Email, pengambilan poligon kantor aktif, perekaman check-in & check-out geofencing, serta riwayat presensi.</p>

                    <ul class="credential-list">
                        <li>
                            <span style="color: #64748b;">Endpoint Auth:</span>
                            <span class="code-pill">POST /api/v1/auth/login</span>
                        </li>
                        <li>
                            <span style="color: #64748b;">NIP / Email:</span>
                            <span class="code-pill">199205122018021002</span>
                        </li>
                        <li>
                            <span style="color: #64748b;">Password:</span>
                            <span class="code-pill">password</span>
                        </li>
                        <li>
                            <span style="color: #64748b;">Keamanan:</span>
                            <span style="font-weight: 600;">Sanctum Bearer Token</span>
                        </li>
                    </ul>

                    <a href="{{ url('/api/v1/attendance/today') }}" target="_blank" class="btn-secondary" style="width: 100%; box-sizing: border-box; text-align: center;">
                        <span>Cek Status Presensi API (/today)</span>
                    </a>
                </div>
            </section>

            <!-- Feature Highlights -->
            <section class="grid-three">
                <div class="card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                            <line x1="8" y1="2" x2="8" y2="18"></line>
                            <line x1="16" y1="6" x2="16" y2="22"></line>
                        </svg>
                    </div>
                    <h3>Dual Geofencing Murni</h3>
                    <p>Validasi koordinat GPS menggunakan algoritma <strong>Ray-Casting (Jordan Curve Theorem)</strong> murni dalam PHP tanpa ketergantungan PostGIS/SpatiaLite, dilengkapi formula Haversine untuk kalkulasi radius meter eksak.</p>
                </div>

                <div class="card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                    </div>
                    <h3>Filament v5 & Peta Leaflet</h3>
                    <p>Antarmuka manajemen SDM yang responsif dengan 5 kartu indikator performa kehadiran (KPI), modal peta interaktif OpenStreetMap untuk visualisasi koordinat absensi vs poligon kantor, dan manajemen unit kerja pegawai.</p>
                </div>

                <div class="card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                    </div>
                    <h3>RESTful Mobile API</h3>
                    <p>8 endpoint terstruktur di bawah <code>/api/v1/</code> dengan proteksi Laravel Sanctum, pencegahan absen ganda (<em>double check-in</em>), pelacakan perangkat smartphone pegawai (<em>device tracking & anti-fraud</em>), dan log IP address.</p>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer>
            <div class="container-box">
                <p style="margin: 0 0 0.5rem 0;">
                    <strong>SiAbsen</strong> &mdash; Aplikasi Monitoring Presensi SDM & Geofencing Pegawai
                </p>
                <p style="margin: 0; font-size: 0.8rem; color: #94a3b8;">
                    Dibuat untuk keperluan <strong>Demo & Pembelajaran</strong> &bull; Laravel v{{ app()->version() }} &bull; PHP v{{ PHP_VERSION }} &bull; Filament v5
                </p>
            </div>
        </footer>
    </body>
</html>
