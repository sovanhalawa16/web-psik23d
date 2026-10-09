<?php 
// 1. SET WAKTU JAKARTA
date_default_timezone_set('Asia/Jakarta');
include '../koneksi.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Laporan | PSIK 23 D</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #00a884 0%, #00a884 20%, #f0f2f5 20%, #f0f2f5 100%);
            min-height: 100vh;
            color: #3b4a54;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: white;
            border-radius: 12px 12px 0 0;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 1px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #008069;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 15px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .back-btn:hover {
            background-color: #f0f2f5;
            transform: translateX(-3px);
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #111b21;
            margin-bottom: 5px;
        }
        
        .page-subtitle {
            color: #667781;
            font-size: 14px;
        }
        
        /* Search Box */
        .search-container {
            background: white;
            padding: 20px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .search-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .input-group {
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e9edef;
            transition: all 0.3s ease;
        }
        
        .input-group:focus-within {
            border-color: #00a884;
            box-shadow: 0 0 0 2px rgba(0, 168, 132, 0.2);
        }
        
        .form-control {
            border: none;
            padding: 12px 20px;
            font-size: 15px;
            background: #f0f2f5;
        }
        
        .form-control:focus {
            box-shadow: none;
            background: white;
        }
        
        .search-btn {
            background: #00a884;
            border: none;
            padding: 12px 30px;
            font-weight: 500;
            color: white;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #008069;
            transform: scale(1.02);
        }
        
        /* Chat Container */
        .chat-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            display: none;
        }
        
        .chat-container.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        /* Chat Header */
        .chat-header {
            background: #f0f2f5;
            padding: 15px 20px;
            border-bottom: 1px solid #e9edef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .chat-avatar {
            width: 45px;
            height: 45px;
            background: #00a884;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .chat-details h4 {
            font-size: 16px;
            font-weight: 600;
            color: #111b21;
            margin-bottom: 3px;
        }
        
        .chat-details p {
            font-size: 13px;
            color: #667781;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .ticket-id {
            background: #008069;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            font-family: monospace;
        }
        
        /* Chat Messages */
        .chat-messages {
            padding: 20px;
            background: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%2300a884' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
        }
        
        /* Message Bubbles */
        .message {
            margin-bottom: 20px;
            display: flex;
            max-width: 70%;
        }
        
        .message.sent {
            margin-left: auto;
            justify-content: flex-end;
        }
        
        .message.received {
            margin-right: auto;
            justify-content: flex-start;
        }
        
        .message-content {
            padding: 12px 16px;
            border-radius: 8px;
            position: relative;
            word-wrap: break-word;
            white-space: pre-line;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .message.sent .message-content {
            background: #d9fdd3;
            border-radius: 8px 8px 0 8px;
        }
        
        .message.received .message-content {
            background: white;
            border-radius: 8px 8px 8px 0;
        }
        
        .message-time {
            font-size: 11px;
            color: #667781;
            margin-top: 4px;
            text-align: right;
        }
        
        .message.sent .message-time {
            text-align: right;
        }
        
        .message.received .message-time {
            text-align: left;
        }
        
        /* Info Message */
        .info-message {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(255, 204, 0, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(255, 204, 0, 0.3);
            color: #997404;
            font-size: 14px;
        }
        
        .info-message i {
            margin-right: 8px;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-completed {
            background: rgba(0, 168, 132, 0.1);
            color: #008069;
            border: 1px solid rgba(0, 168, 132, 0.3);
        }
        
        .status-processing {
            background: rgba(255, 204, 0, 0.1);
            color: #b38600;
            border: 1px solid rgba(255, 204, 0, 0.3);
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            display: none;
        }
        
        .no-results.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .no-results-icon {
            font-size: 64px;
            color: #e9edef;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .no-results-title {
            color: #667781;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .no-results-text {
            color: #8696a0;
            max-width: 400px;
            margin: 0 auto 25px;
            font-size: 14px;
        }
        
        .search-again-btn {
            background: #00a884;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-again-btn:hover {
            background: #008069;
            transform: scale(1.05);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-icon {
            font-size: 80px;
            color: #e9edef;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-title {
            color: #667781;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .empty-text {
            color: #8696a0;
            max-width: 400px;
            margin: 0 auto;
            font-size: 15px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .message.sent {
            animation: slideInRight 0.3s ease;
        }
        
        .message.received {
            animation: slideInLeft 0.3s ease;
        }
        
        /* Scrollbar Styling */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
            border-radius: 10px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: #008069;
            border-radius: 10px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #00a884;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                background: linear-gradient(135deg, #00a884 0%, #00a884 15%, #f0f2f5 15%, #f0f2f5 100%);
            }
            
            .container {
                padding: 15px;
            }
            
            .message {
                max-width: 85%;
            }
            
            .chat-messages {
                max-height: 500px;
                padding: 15px;
            }
            
            .search-btn {
                padding: 12px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .message {
                max-width: 90%;
            }
            
            .chat-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="page-title">Cek Status Laporan</h1>
            <p class="page-subtitle">Masukkan email atau ID tiket untuk melihat percakapan</p>
        </div>
        
        <div class="search-container">
            <form method="GET" class="search-form">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" 
                           placeholder="Email atau ID Tiket (cth: LAP-X7B...)" required 
                           value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>"
                           autocomplete="off">
                    <button class="search-btn" type="submit">
                        <i class="fas fa-search me-2"></i> Cari
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($_GET['keyword'])): ?>
            <?php
            $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
            
            // QUERY SAKTI: Cari berdasarkan Email ATAU Ticket ID
            $query = mysqli_query($koneksi, "SELECT * FROM laporan 
                                             WHERE email = '$keyword' OR ticket_id = '$keyword' 
                                             ORDER BY created_at DESC");
            $result_count = mysqli_num_rows($query);
            ?>

            <?php if ($result_count > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <div class="chat-container active" data-ticket-id="<?= $row['ticket_id'] ?>">
                        <div class="chat-header">
                            <div class="chat-info">
                                <div class="chat-avatar">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="chat-details">
                                    <h4>Layanan Pelaporan</h4>
                                    <p>
                                        <i class="far fa-clock"></i>
                                        <?= date('d M Y, H:i', strtotime($row['created_at'])) ?> WIB
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="ticket-id">#<?= $row['ticket_id'] ?></span>
                                <?php if ($row['status'] == 'Dibalas'): ?>
                                    <span class="status-badge status-completed">
                                        <i class="fas fa-check-circle"></i> Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-processing">
                                        <i class="fas fa-spinner fa-spin"></i> Proses
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="chat-messages">
                            <!-- Laporan dari user (kanan) -->
                            <div class="message sent">
                                <div class="message-content">
                                    <div style="font-weight: 500; color: #008069; font-size: 12px; margin-bottom: 5px;">
                                        <i class="fas fa-user me-1"></i> Anda - <?= $row['jenis_laporan'] ?>
                                    </div>
                                    <?= nl2br(htmlspecialchars($row['deskripsi'])) ?>
                                    <div class="message-time">
                                        <?= date('H:i', strtotime($row['created_at'])) ?> • <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($row['balasan'])): ?>
                                <!-- Balasan dari admin (kiri) -->
                                <div class="message received">
                                    <div class="message-content">
                                        <div style="font-weight: 500; color: #008069; font-size: 12px; margin-bottom: 5px;">
                                            <i class="fas fa-user-shield me-1"></i> Admin PSIK 23 D
                                        </div>
                                        <?= nl2br(htmlspecialchars($row['balasan'])) ?>
                                        <div class="message-time">
                                            <?= date('H:i', strtotime($row['created_at'])) ?> • <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Info menunggu balasan -->
                                <div class="info-message">
                                    <i class="fas fa-clock"></i>
                                    Laporan Anda sudah diterima. Admin akan membalas secepatnya. Terima kasih atas kesabaran Anda.
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status info -->
                            <div class="message received">
                                <div class="message-content" style="background: #f0f2f5; font-size: 12px; color: #667781;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Percakapan ini bersifat <strong>hanya-baca</strong>. Untuk update lebih lanjut, silakan tunggu balasan admin atau hubungi kami melalui email.
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results active">
                    <div class="no-results-icon">
                        <i class="far fa-comment-dots"></i>
                    </div>
                    <h3 class="no-results-title">Percakapan tidak ditemukan</h3>
                    <p class="no-results-text">
                        Tidak ada laporan dengan email atau ID tiket 
                        <strong>"<?= htmlspecialchars($_GET['keyword']) ?>"</strong>
                    </p>
                    <a href="?" class="search-again-btn">
                        <i class="fas fa-search me-1"></i> Cari Lagi
                    </a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="far fa-comments"></i>
                </div>
                <h3 class="empty-title">Lacak Percakapan Laporan</h3>
                <p class="empty-text">
                    Masukkan email atau ID tiket Anda untuk melihat riwayat percakapan dengan admin
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on input if there's keyword
            const keywordInput = document.querySelector('input[name="keyword"]');
            if (keywordInput && keywordInput.value) {
                keywordInput.focus();
                keywordInput.select();
            }
            
            // Add loading state to button
            const form = document.querySelector('.search-form');
            if (form) {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mencari...';
                        button.disabled = true;
                    }
                });
            }
            
            // Auto scroll to bottom of chat
            const chatMessages = document.querySelector('.chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Add click effect to search again button
            const searchAgainBtn = document.querySelector('.search-again-btn');
            if (searchAgainBtn) {
                searchAgainBtn.addEventListener('click', function(e) {
                    if (!this.href.includes('?')) {
                        e.preventDefault();
                        keywordInput.focus();
                        keywordInput.select();
                    }
                });
            }
            
            // Add message hover effect
            const messages = document.querySelectorAll('.message-content');
            messages.forEach(msg => {
                msg.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                msg.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
        
        // Smooth scrolling for chat messages
        function scrollToBottom() {
            const chatMessages = document.querySelector('.chat-messages');
            if (chatMessages) {
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: 'smooth'
                });
            }
        }
        
        // Scroll to bottom when page loads
        window.addEventListener('load', scrollToBottom);
    </script>
</body>
</html>