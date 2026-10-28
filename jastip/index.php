<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/koneksi.php';

$stmt = $pdo->query("SELECT COUNT(*) as total_jenis FROM barang");
$total_jenis = $stmt->fetch()['total_jenis'];

$stmt = $pdo->query("SELECT COALESCE(SUM(stok), 0) as total_stok FROM barang");
$total_stok = $stmt->fetch()['total_stok'];

$stmt = $pdo->query("SELECT COALESCE(SUM(harga * stok), 0) as total_nilai FROM barang");
$total_nilai = $stmt->fetch()['total_nilai'];

$stmt = $pdo->query("SELECT * FROM barang ORDER BY created_at DESC LIMIT 10");
$recent_barang = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Jastip</title>
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <div class="logo">
                <?php if (file_exists('assets/img/logo.png')): ?>
                    <img src="assets/img/logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                <?php else: ?>
                    J
                <?php endif; ?>
            </div>
            JASTIP
        </a>
        
        <ul class="navbar-menu">
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="data_barang.php">Data Barang</a></li>
            <li><a href="cetak_pdf.php" target="_blank">Cetak Laporan</a></li>
        </ul>
        
        <div class="navbar-user">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <button class="btn-logout" onclick="if(confirm('Yakin ingin keluar?')) window.location.href='logout.php'">
                Logout
            </button>
        </div>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <button class="btn btn-primary" onclick="window.open('cetak_pdf.php', '_blank')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Laporan
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Jenis Barang</div>
                <div class="stat-value"><?php echo number_format($total_jenis, 0, ',', '.'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Akumulasi Stok</div>
                <div class="stat-value"><?php echo number_format($total_stok, 0, ',', '.'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Valuasi Nilai</div>
                <div class="stat-value">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.25rem;">Data Barang Terbaru</h3>
                    <p style="color: var(--text-muted); font-size: 0.8125rem; font-weight: 500;">Update inventaris terkini</p>
                </div>
                <button class="btn btn-primary" onclick="window.location.href='data_barang.php'">
                    Lihat Semua
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_barang) > 0): ?>
                            <?php foreach ($recent_barang as $barang): ?>
                                <tr>
                                    <td><strong style="color: var(--red-primary);"><?php echo htmlspecialchars($barang['kode_barang']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($barang['nama_barang']); ?></td>
                                    <td>
                                        <span style="display: inline-block; padding: 0.25rem 0.625rem; background: rgba(251, 191, 36, 0.1); color: var(--yellow-dark); border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                            <?php echo htmlspecialchars($barang['kategori']); ?>
                                        </span>
                                    </td>
                                    <td>Rp <?php echo number_format($barang['harga'], 0, ',', '.'); ?></td>
                                    <td><strong><?php echo number_format($barang['stok'], 0, ',', '.'); ?></strong></td>
                                    <td><strong style="color: var(--red-primary);">Rp <?php echo number_format($barang['harga'] * $barang['stok'], 0, ',', '.'); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    Belum ada data barang
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button class="floating-button secondary" onclick="toggleWindow('calculatorWindow')" title="Kalkulator">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="4" y="2" width="16" height="20" rx="2" stroke-width="2"/>
            <line x1="8" y1="6" x2="16" y2="6" stroke-width="2"/>
            <line x1="8" y1="10" x2="16" y2="10" stroke-width="2"/>
            <line x1="8" y1="14" x2="12" y2="14" stroke-width="2"/>
            <line x1="8" y1="18" x2="12" y2="18" stroke-width="2"/>
            <rect x="15" y="13" width="2" height="6" stroke-width="2"/>
        </svg>
    </button>

    <button class="floating-button" onclick="window.location.href='data_barang.php'" title="Data Barang">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
    </button>

    <div class="window-overlay" id="calculatorOverlay" onclick="closeWindow('calculatorWindow')"></div>
    
    <div class="floating-window" id="calculatorWindow" style="width: 380px;">
        <div class="window-header">
            <h3 class="window-title">Kalkulator</h3>
            <button class="btn-close" onclick="closeWindow('calculatorWindow')">&times;</button>
        </div>
        <div class="window-body">
            <div class="calc-display">
                <div class="calc-expression" id="calcExpression"></div>
                <div class="calc-result" id="calcResult">0</div>
            </div>
            
            <div class="calc-buttons">
                <button class="calc-btn clear" onclick="clearCalculator()">C</button>
                <button class="calc-btn operator" onclick="appendOperator('/')">/</button>
                <button class="calc-btn operator" onclick="appendOperator('*')">×</button>
                <button class="calc-btn operator" onclick="deleteLastChar()">⌫</button>
                
                <button class="calc-btn" onclick="appendNumber('7')">7</button>
                <button class="calc-btn" onclick="appendNumber('8')">8</button>
                <button class="calc-btn" onclick="appendNumber('9')">9</button>
                <button class="calc-btn operator" onclick="appendOperator('-')">−</button>
                
                <button class="calc-btn" onclick="appendNumber('4')">4</button>
                <button class="calc-btn" onclick="appendNumber('5')">5</button>
                <button class="calc-btn" onclick="appendNumber('6')">6</button>
                <button class="calc-btn operator" onclick="appendOperator('+')">+</button>
                
                <button class="calc-btn" onclick="appendNumber('1')">1</button>
                <button class="calc-btn" onclick="appendNumber('2')">2</button>
                <button class="calc-btn" onclick="appendNumber('3')">3</button>
                <button class="calc-btn equals" onclick="calculateResult()">=</button>
                
                <button class="calc-btn" onclick="appendNumber('0')" style="grid-column: span 2;">0</button>
                <button class="calc-btn" onclick="appendNumber('.')">.</button>
            </div>
        </div>
    </div>

    <script>
        let currentExpression = '';
        let currentResult = '0';

        function toggleWindow(windowId) {
            const window = document.getElementById(windowId);
            const overlay = document.getElementById(windowId.replace('Window', 'Overlay'));
            
            if (window.classList.contains('active')) {
                closeWindow(windowId);
            } else {
                window.classList.add('active');
                overlay.classList.add('active');
            }
        }

        function closeWindow(windowId) {
            const window = document.getElementById(windowId);
            const overlay = document.getElementById(windowId.replace('Window', 'Overlay'));
            window.classList.remove('active');
            overlay.classList.remove('active');
        }

        function updateDisplay() {
            document.getElementById('calcExpression').textContent = currentExpression || '';
            document.getElementById('calcResult').textContent = currentResult;
        }

        function appendNumber(num) {
            if (currentResult === '0' && num !== '.') {
                currentResult = num;
            } else {
                currentResult += num;
            }
            currentExpression += num;
            updateDisplay();
        }

        function appendOperator(op) {
            if (currentExpression && !isNaN(currentExpression.slice(-1))) {
                currentExpression += op;
                currentResult = '0';
                updateDisplay();
            }
        }

        function deleteLastChar() {
            if (currentExpression.length > 0) {
                currentExpression = currentExpression.slice(0, -1);
                if (currentExpression.length === 0) {
                    currentResult = '0';
                } else {
                    try {
                        let expr = currentExpression.replace(/×/g, '*').replace(/−/g, '-');
                        currentResult = eval(expr).toString();
                    } catch {
                        currentResult = '0';
                    }
                }
                updateDisplay();
            }
        }

        function calculateResult() {
            if (currentExpression) {
                try {
                    let expression = currentExpression.replace(/×/g, '*').replace(/−/g, '-');
                    let result = eval(expression);
                    currentResult = result.toString();
                    currentExpression = result.toString();
                    updateDisplay();
                } catch (error) {
                    currentResult = 'Error';
                    updateDisplay();
                    setTimeout(() => {
                        clearCalculator();
                    }, 1500);
                }
            }
        }

        function clearCalculator() {
            currentExpression = '';
            currentResult = '0';
            updateDisplay();
        }

        document.addEventListener('keydown', function(e) {
            const calcWindow = document.getElementById('calculatorWindow');
            if (!calcWindow.classList.contains('active')) return;

            if (e.key >= '0' && e.key <= '9') {
                appendNumber(e.key);
            } else if (e.key === '.') {
                appendNumber('.');
            } else if (e.key === '+' || e.key === '-' || e.key === '*' || e.key === '/') {
                appendOperator(e.key);
            } else if (e.key === 'Enter' || e.key === '=') {
                e.preventDefault();
                calculateResult();
            } else if (e.key === 'Backspace') {
                e.preventDefault();
                deleteLastChar();
            } else if (e.key === 'Escape') {
                closeWindow('calculatorWindow');
            }
        });
    </script>
</body>
</html>
