<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/koneksi.php';

$stmt = $pdo->query("SELECT * FROM barang ORDER BY kode_barang ASC");
$barang_list = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COUNT(*) as total_jenis FROM barang");
$total_jenis = $stmt->fetch()['total_jenis'];

$stmt = $pdo->query("SELECT COALESCE(SUM(stok), 0) as total_stok FROM barang");
$total_stok = $stmt->fetch()['total_stok'];

$stmt = $pdo->query("SELECT COALESCE(SUM(harga * stok), 0) as total_nilai FROM barang");
$total_nilai = $stmt->fetch()['total_nilai'];

$fpdf_exists = file_exists('fpdf/fpdf.php');

if ($fpdf_exists) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    require_once 'fpdf/fpdf.php';
    
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(0, 12, 'LAPORAN BARANG JASTIP', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell(0, 6, 'Tanggal Cetak: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Cell(0, 6, 'Dicetak oleh: ' . htmlspecialchars($_SESSION['username']), 0, 1, 'C');
    $pdf->Ln(8);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Kode', 1, 0, 'C', true);
    $pdf->Cell(55, 8, 'Nama Barang', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Kategori', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Harga', 1, 0, 'C', true);
    $pdf->Cell(15, 8, 'Stok', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Total Nilai', 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(15, 23, 42);
    
    $no = 1;
    foreach ($barang_list as $barang) {
        $total_item = $barang['harga'] * $barang['stok'];
        
        $pdf->Cell(10, 7, $no++, 1, 0, 'C');
        $pdf->Cell(30, 7, $barang['kode_barang'], 1, 0, 'L');
        $pdf->Cell(55, 7, substr($barang['nama_barang'], 0, 35), 1, 0, 'L');
        $pdf->Cell(30, 7, $barang['kategori'], 1, 0, 'C');
        $pdf->Cell(30, 7, 'Rp ' . number_format($barang['harga'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(15, 7, $barang['stok'], 1, 0, 'C');
        $pdf->Cell(30, 7, 'Rp ' . number_format($total_item, 0, ',', '.'), 1, 1, 'R');
    }
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(251, 191, 36);
    $pdf->Cell(170, 9, 'TOTAL VALUASI', 1, 0, 'R', true);
    $pdf->Cell(30, 9, 'Rp ' . number_format($total_nilai, 0, ',', '.'), 1, 1, 'R', true);
    
    $pdf->Ln(8);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(63, 6, 'RINGKASAN:', 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(15, 23, 42);
    $pdf->Cell(63, 5, 'Total Jenis Barang: ' . $total_jenis, 0, 1, 'L');
    $pdf->Cell(63, 5, 'Total Akumulasi Stok: ' . number_format($total_stok, 0, ',', '.'), 0, 1, 'L');
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(148, 163, 184);
    $pdf->Cell(0, 5, 'Dokumen ini dicetak secara otomatis oleh Sistem Jastip', 0, 1, 'C');
    
    $pdf->Output('D', 'Laporan_Barang_Jastip_' . date('Ymd_His') . '.pdf');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - Sistem Jastip</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .report-container {
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #dc2626;
        }
        
        .report-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            background: #dc2626;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .report-title {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1rem;
        }
        
        .report-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 600;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .summary-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        
        .summary-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #dc2626;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .report-table thead th {
            background: #dc2626;
            color: white;
            padding: 0.875rem 1rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .report-table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .report-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fef3c7;
            padding: 1.25rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 2px solid #fbbf24;
        }
        
        .total-label {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }
        
        .total-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #dc2626;
        }
        
        .footer-text {
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: #dc2626;
            color: white;
        }
        
        .btn-primary:hover {
            background: #991b1b;
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #475569;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .report-container {
                box-shadow: none;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .report-container {
                padding: 2rem 1.5rem;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <div class="report-logo">
                <?php if (file_exists('assets/img/logo.png')): ?>
                    <img src="assets/img/logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                <?php else: ?>
                    J
                <?php endif; ?>
            </div>
            <h1 class="report-title">LAPORAN BARANG JASTIP</h1>
            <div class="report-info">
                <span><?php echo date('d/m/Y H:i:s'); ?></span>
                <span>Operator: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </div>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-label">Total Jenis</div>
                <div class="summary-value"><?php echo number_format($total_jenis, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Stok</div>
                <div class="summary-value"><?php echo number_format($total_stok, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Valuasi</div>
                <div class="summary-value" style="font-size: 1.25rem;">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></div>
            </div>
        </div>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 13%;">Kode</th>
                    <th style="width: 27%;">Nama Barang</th>
                    <th style="width: 13%;">Kategori</th>
                    <th style="width: 15%;">Harga</th>
                    <th style="width: 10%;">Stok</th>
                    <th style="width: 17%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($barang_list) > 0): ?>
                    <?php $no = 1; foreach ($barang_list as $barang): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong style="color: #dc2626;"><?php echo htmlspecialchars($barang['kode_barang']); ?></strong></td>
                            <td><?php echo htmlspecialchars($barang['nama_barang']); ?></td>
                            <td><?php echo htmlspecialchars($barang['kategori']); ?></td>
                            <td>Rp <?php echo number_format($barang['harga'], 0, ',', '.'); ?></td>
                            <td><strong><?php echo number_format($barang['stok'], 0, ',', '.'); ?></strong></td>
                            <td><strong style="color: #dc2626;">Rp <?php echo number_format($barang['harga'] * $barang['stok'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">Belum ada data barang</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <span class="total-label">TOTAL VALUASI KESELURUHAN</span>
            <span class="total-value">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></span>
        </div>
        
        <p class="footer-text">
            Dokumen ini dicetak secara otomatis oleh Sistem Jastip
        </p>
        
        <div class="action-buttons no-print">
            <button class="btn btn-primary" onclick="window.print()">
                Cetak Laporan
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>
</body>
</html>
