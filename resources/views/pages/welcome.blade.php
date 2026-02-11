<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>TaskFlow</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="/css/pages/welcome.css">
</head>
<body>
    <div class="container page-full">
        <header class="nav">
            <div class="logo">
                <div class="logo-badge">✓</div>
                TaskFlow Workspace
            </div>
            <div class="nav-links">
                @if (Route::has('login'))
                    @auth
                        <a class="btn btn-primary" href="{{ url('/dashboard') }}">Buka Dashboard</a>
                    @else
                        <a class="btn btn-ghost" href="{{ route('login') }}">Masuk</a>
                    @endauth
                @endif
            </div>
        </header>

        <section class="hero">
            <div class="hero-left">
                <span class="pill">⚡ Sistem tugas & kolaborasi real-time</span>
                <h1>Kelola pekerjaan, tim, dan progres dalam satu dashboard.</h1>
                <p>TaskFlow membantu memprioritaskan pekerjaan, memantau KPI, dan menjaga komunikasi tetap rapi. Didesain untuk produktivitas harian dengan insight yang jelas.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ url('/dashboard') }}">Lihat Ringkasan</a>
                    <a class="btn btn-ghost" href="#fitur">Jelajahi Fitur</a>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="value">98%</div>
                        <div class="label">Kepatuhan SLA mingguan</div>
                    </div>
                    <div class="stat-card">
                        <div class="value">24h</div>
                        <div class="label">Rata-rata waktu penyelesaian</div>
                    </div>
                    <div class="stat-card">
                        <div class="value">128</div>
                        <div class="label">Tugas aktif hari ini</div>
                    </div>
                </div>
            </div>
            <div class="hero-card">
                <div class="illustration">
                    <img src="/images/welcome-illustration.svg" alt="TaskFlow illustration">
                </div>
                <h3>Progress Sprint</h3>
                <div class="progress">
                    <div>Implementasi modul inti</div>
                    <div class="progress-bar"><span data-progress="72"></span></div>
                </div>
                <div class="progress">
                    <div>QA & regresi</div>
                    <div class="progress-bar"><span data-progress="48"></span></div>
                </div>
                <div class="progress">
                    <div>Rilis & dokumentasi</div>
                    <div class="progress-bar"><span data-progress="31"></span></div>
                </div>
                <div class="tag">Update otomatis tiap 15 menit</div>
            </div>
        </section>

        <section class="section" id="fitur">
            <div class="section-title">
                <div>
                    <h2>Fitur yang fokus pada hasil</h2>
                    <p>Pilih prioritas, pantau kapasitas tim, dan eksekusi tanpa hambatan.</p>
                </div>
            </div>
            <div class="feature-list">
                <div class="feature">
                    <div class="tag">📊 Insight</div>
                    <h3>Dashboard KPI</h3>
                    <p>Lihat metrik harian, tren mingguan, dan status SLA dalam satu layar.</p>
                </div>
                <div class="feature">
                    <div class="tag">🧭 Prioritas</div>
                    <h3>Smart Priority</h3>
                    <p>Skor otomatis berdasarkan urgensi, dampak, dan kapasitas tim.</p>
                </div>
                <div class="feature">
                    <div class="tag">💬 Kolaborasi</div>
                    <h3>Catatan Kontekstual</h3>
                    <p>Diskusi langsung di tugas dengan riwayat lengkap dan mention.</p>
                </div>
                <div class="feature">
                    <div class="tag">🛡️ Kontrol</div>
                    <h3>Role & Akses</h3>
                    <p>Kelola peran, izin, dan kebijakan akses sesuai kebutuhan tim.</p>
                </div>
            </div>
        </section>

        <div class="split">
            <div class="card">
                <div class="section-title">
                    <div>
                        <h2>Panel informasi dinamis</h2>
                        <p>Ganti fokus tampilan untuk melihat kondisi operasional secara cepat.</p>
                    </div>
                </div>

                <div class="tabs" role="tablist">
                    <button class="tab active" data-tab="overview" type="button">Ringkasan</button>
                    <button class="tab" data-tab="workload" type="button">Beban Kerja</button>
                    <button class="tab" data-tab="alerts" type="button">Peringatan</button>
                </div>

                <div class="tab-panels">
                    <div class="panel active" data-panel="overview">
                        <strong>Ringkasan Hari Ini</strong>
                        <p>25 tugas selesai, 12 berjalan, 4 tertunda. Fokuskan pada backlog prioritas tinggi untuk menjaga SLA.</p>
                    </div>
                    <div class="panel" data-panel="workload">
                        <strong>Beban Kerja Tim</strong>
                        <p>Tim A di 78% kapasitas, Tim B di 64%, Tim C di 52%. Disarankan pindahkan 2 tugas dari Tim A.</p>
                    </div>
                    <div class="panel" data-panel="alerts">
                        <strong>Peringatan</strong>
                        <p>3 tugas mendekati tenggat 24 jam. 1 integrasi eksternal gagal sinkron 30 menit terakhir.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <div>
                        <h2>Timeline eksekusi</h2>
                        <p>Rencana peluncuran berikutnya agar semua tim on-track.</p>
                    </div>
                </div>
                <div class="timeline">
                    <div class="timeline-item">
                        <strong>Hari 1 - Sprint Planning</strong>
                        <span>Definisikan scope, kapasitas, dan risiko utama.</span>
                    </div>
                    <div class="timeline-item">
                        <strong>Hari 3 - Mid Review</strong>
                        <span>Review progres, update status, dan realokasi resource.</span>
                    </div>
                    <div class="timeline-item">
                        <strong>Hari 5 - Release Checkpoint</strong>
                        <span>QA final, dokumentasi, dan persiapan deploy.</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <div>
                        <h2>Pertanyaan umum</h2>
                        <p>Jawaban cepat agar onboarding lebih lancar.</p>
                    </div>
                </div>
                <div class="faq">
                    <div class="faq-item">
                        <div class="faq-question">Apakah bisa integrasi dengan layanan eksternal? <span>+</span></div>
                        <div class="faq-answer">Ya, TaskFlow mendukung integrasi webhook dan sinkronisasi data berkala.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Bagaimana pengaturan role dan izin? <span>+</span></div>
                        <div class="faq-answer">Role bisa dibuat sesuai kebutuhan dengan akses granular per modul.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Apakah tersedia audit trail? <span>+</span></div>
                        <div class="faq-answer">Setiap perubahan tugas terekam otomatis dan dapat diekspor.</div>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="cta">
                <h2>Siap optimalkan alur kerja?</h2>
                <p>Mulai dari dashboard utama untuk melihat data real-time dan rekomendasi tindakan.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ url('/dashboard') }}">Buka Dashboard</a>
                    <a class="btn btn-ghost" href="#fitur">Pelajari Fitur</a>
                </div>
            </div>
        </section>

        <footer class="footer">
            <span>© 2026 TaskFlow Workspace</span>
            <span>Keamanan data · Audit trail · SLA monitor</span>
        </footer>
    </div>

    <script>
        document.querySelectorAll(".progress-bar span").forEach((bar) => {
            const value = bar.getAttribute("data-progress");
            requestAnimationFrame(() => {
                bar.style.width = `${value}%`;
            });
        });

        const tabs = document.querySelectorAll(".tab");
        const panels = document.querySelectorAll(".panel");

        tabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                const target = tab.dataset.tab;
                tabs.forEach((t) => t.classList.remove("active"));
                panels.forEach((p) => p.classList.remove("active"));
                tab.classList.add("active");
                document.querySelector(`[data-panel="${target}"]`).classList.add("active");
            });
        });

        document.querySelectorAll(".faq-question").forEach((question) => {
            question.addEventListener("click", () => {
                const item = question.parentElement;
                item.classList.toggle("open");
            });
        });
    </script>
</body>
</html>
