<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur & Layanan | PSIK 23 D</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --primary-light: #60a5fa;
            --secondary: #64748b;
            --dark: #0f172a;
            --light: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: var(--dark);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Navigation */
        .main-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: white;
            border: 2px solid var(--border);
            border-radius: 50px;
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.2);
        }

        /* Hero Section */
        .hero-section {
            padding: 80px 20px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            z-index: -1;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
            background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--secondary);
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        /* Features Grid */
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        /* Feature Card */
        .feature-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-light);
        }

        .card-header {
            padding: 30px 30px 20px;
            position: relative;
            flex: 1;
        }

        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 28px;
            transition: all 0.3s ease;
        }

        .feature-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .card-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--secondary);
            border: 1px solid var(--border);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .card-description {
            color: var(--secondary);
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .card-features {
            list-style: none;
            margin: 20px 0;
        }

        .card-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .card-features i {
            color: var(--success);
            font-size: 0.8rem;
        }

        .card-footer {
            padding: 0 30px 30px;
            margin-top: auto;
        }

        .card-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid;
            width: 100%;
        }

        .card-btn i {
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .card-btn i {
            transform: translateX(5px);
        }

        /* Card Color Variants */
        .card-blue .card-icon { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
        .card-blue .card-btn { background: var(--primary); color: white; border-color: var(--primary); }
        .card-blue .card-btn:hover { background: var(--primary-dark); border-color: var(--primary-dark); }

        .card-yellow .card-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .card-yellow .card-btn { background: var(--warning); color: white; border-color: var(--warning); }
        .card-yellow .card-btn:hover { background: #d97706; border-color: #d97706; }

        .card-red .card-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .card-red .card-btn { background: var(--danger); color: white; border-color: var(--danger); }
        .card-red .card-btn:hover { background: #dc2626; border-color: #dc2626; }

        .card-green .card-icon { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .card-green .card-btn { background: var(--success); color: white; border-color: var(--success); }
        .card-green .card-btn:hover { background: #059669; border-color: #059669; }

        .card-purple .card-icon { background: rgba(139, 92, 246, 0.1); color: var(--purple); }
        .card-purple .card-btn { background: var(--purple); color: white; border-color: var(--purple); }
        .card-purple .card-btn:hover { background: #7c3aed; border-color: #7c3aed; }

        /* Stats Section */
        .stats-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin: 60px auto;
            max-width: 1000px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        .stats-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .stat-item {
            text-align: center;
            padding: 25px;
            background: var(--light);
            border-radius: 15px;
            border: 1px solid var(--border);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--secondary);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary);
            border-top: 1px solid var(--border);
            background: white;
            margin-top: 60px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
                padding: 0 20px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .card-header, .card-footer {
                padding: 25px;
            }
            
            .stats-section {
                padding: 30px 20px;
                margin: 40px 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-container {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .card-title {
                font-size: 1.3rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-item {
                padding: 20px;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feature-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }
        .feature-card:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <span class="logo-text">PSIK 23 D</span>
            </a>
            <a href="../index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </nav>

    <!-- Features Grid -->
    <div class="features-container">
        <div class="features-grid">
            <!-- E-KTM Generator -->
            <div class="feature-card card-blue">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="card-badge">
                        <i class="fas fa-bolt me-1"></i> Hot
                    </div>
                    <h3 class="card-title">E-KTM PSIK 23 D</h3>
                    <p class="card-description">
                        Buat Kartu Tanda Mahasiswa digital kamu disini sebagai tanda pengenal Mahasiswa aktif kelas PSIK 23 D!
                    </p>
                    <ul class="card-features">
                        <li><i class="fas fa-check-circle"></i> Desain yang menarik dan elegan</li>
                        <li><i class="fas fa-check-circle"></i> QR Code verification</li>
                        <li><i class="fas fa-check-circle"></i> Format PNG HD quality</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="fitur_ktm.php" class="card-btn">
                        <span>Buat KTM Sekarang</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- The Team Gacha -->
            <div class="feature-card card-yellow">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-dice"></i>
                    </div>
                    <div class="card-badge">
                        <i class="fas fa-random me-1"></i> Randomizer
                    </div>
                    <h3 class="card-title">Team Gacha</h3>
                    <p class="card-description">
                        Bingung bagi kelompok? Generator acak yang fair dan transparan. 
                        Bisa tarik data langsung dari database atau input manual.
                    </p>
                    <ul class="card-features">
                        <li><i class="fas fa-check-circle"></i> Mode otomatis & manual</li>
                        <li><i class="fas fa-check-circle"></i> One-click copy to WA</li>
                        <li><i class="fas fa-check-circle"></i> Pembagian yang adil</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="fitur_gacha.php" class="card-btn">
                        <span>Mulai Gacha</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- PSIK-FESS -->
            <div class="feature-card card-red">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="card-badge">
                        <i class="fas fa-user-secret me-1"></i> Anonymous
                    </div>
                    <h3 class="card-title">PSIK-FESS</h3>
                    <p class="card-description">
                        Dinding pesan rahasia untuk confession, motivasi, atau curhat. 
                        100% anonim tanpa perlu login. Bikin suasana kelas lebih hidup!
                    </p>
                    <ul class="card-features">
                        <li><i class="fas fa-check-circle"></i> 100% anonim</li>
                        <li><i class="fas fa-check-circle"></i> Sticky notes style</li>
                        <li><i class="fas fa-check-circle"></i> Simple & private</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="fitur_fess.php" class="card-btn">
                        <span>Mulai Curhat</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- IPK Simulator -->
            <div class="feature-card card-green">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="card-badge">
                        <i class="fas fa-chart-line me-1"></i> Calculator
                    </div>
                    <h3 class="card-title">IPK Simulator</h3>
                    <p class="card-description">
                        Targetkan IP semestermu! Hitung prediksi IP secara realtime. 
                        Simulasi nilai untuk strategi kuliah yang lebih baik.
                    </p>
                    <ul class="card-features">
                        <li><i class="fas fa-check-circle"></i> Auto-save data</li>
                        <li><i class="fas fa-check-circle"></i> Real-time calculation</li>
                        <li><i class="fas fa-check-circle"></i> Target analysis</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="fitur_ipk.php" class="card-btn">
                        <span>Hitung IPK</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- DevTyper -->
            <div class="feature-card card-purple">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-keyboard"></i>
                    </div>
                    <div class="card-badge">
                        <i class="fas fa-code me-1"></i> Developer
                    </div>
                    <h3 class="card-title">DevTyper</h3>
                    <p class="card-description">
                        Uji kecepatan coding dengan syntax asli! Bukan kata biasa, 
                        tapi ngetik kode program sebenarnya. Seberapa dewa jari codingmu?
                    </p>
                    <ul class="card-features">
                        <li><i class="fas fa-check-circle"></i> Real code syntax</li>
                        <li><i class="fas fa-check-circle"></i> VS Code theme</li>
                        <li><i class="fas fa-check-circle"></i> WPM & accuracy counter</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="fitur_devtyper.php" class="card-btn">
                        <span>Test Kecepatan</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
                            
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scroll animation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add hover effect to cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });
    </script>
</body>
</html>