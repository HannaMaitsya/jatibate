<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/koneksi.php';

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE kode_barang LIKE ? OR nama_barang LIKE ? ORDER BY created_at DESC");
    $search_param = '%' . $search . '%';
    $stmt->execute([$search_param, $search_param]);
} else {
    $stmt = $pdo->query("SELECT * FROM barang ORDER BY created_at DESC");
}

$barang_list = $stmt->fetchAll();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$message = '';
$error_message = '';

if ($success === 'tambah') {
    $message = 'Data barang berhasil ditambahkan';
} elseif ($success === 'edit') {
    $message = 'Data barang berhasil diperbarui';
} elseif ($success === 'hapus') {
    $message = 'Data barang berhasil dihapus';
}

if ($error === 'duplicate') {
    $error_message = 'Kode barang sudah digunakan';
} elseif ($error === 'empty') {
    $error_message = 'Semua field harus diisi';
} elseif ($error === 'invalid') {
    $error_message = 'Data tidak valid';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Sistem Jastip</title>
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
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="data_barang.php" class="active">Data Barang</a></li>
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
                <h1 class="page-title">Data Barang</h1>
                <p class="page-subtitle">Kelola seluruh inventaris barang jastip</p>
            </div>
            <button class="btn btn-primary" onclick="openModalTambah()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Barang
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="search-box">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       id="searchInput" 
                       placeholder="Cari berdasarkan kode atau nama barang..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 12%;">Kode Barang</th>
                            <th style="width: 25%;">Nama Barang</th>
                            <th style="width: 12%;">Kategori</th>
                            <th style="width: 13%;">Harga</th>
                            <th style="width: 8%;">Stok</th>
                            <th style="width: 15%;">Total Nilai</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($barang_list) > 0): ?>
                            <?php $no = 1; foreach ($barang_list as $barang): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
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
                                    <td>
                                        <div style="display: flex; gap: 0.375rem;">
                                            <button class="btn btn-warning btn-sm" onclick='openModalEdit(<?php echo json_encode($barang, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $barang['id']; ?>)">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                    <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.5rem;">Belum ada data barang</div>
                                    <div style="font-size: 0.875rem;">Tambahkan barang pertama Anda sekarang</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="modalTambah">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Barang Baru</h3>
                <button type="button" class="btn-close" onclick="closeModal('modalTambah')">&times;</button>
            </div>
            <form method="POST" action="proses.php">
                <input type="hidden" name="action" value="tambah">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kode_barang_tambah" class="form-label">Kode Barang</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="kode_barang_tambah" 
                                   name="kode_barang" 
                                   placeholder="Contoh: JTP-001" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama_barang_tambah" class="form-label">Nama Barang</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama_barang_tambah" 
                                   name="nama_barang" 
                                   placeholder="Nama barang jastip" 
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kategori_tambah" class="form-label">Kategori</label>
                            <select class="form-control" id="kategori_tambah" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Kosmetik">Kosmetik</option>
                                <option value="Fashion">Fashion</option>
                                <option value="Makanan">Makanan</option>
                                <option value="Elektronik">Elektronik</option>
                                <option value="Aksesoris">Aksesoris</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_tambah" class="form-label">Harga (Rp)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="harga_tambah" 
                                   name="harga" 
                                   placeholder="Harga jual" 
                                   min="0" 
                                   step="0.01" 
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stok_tambah" class="form-label">Stok</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="stok_tambah" 
                                   name="stok" 
                                   placeholder="Jumlah stok" 
                                   min="0" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" onclick="closeModal('modalTambah')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalEdit">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Edit Barang</h3>
                <button type="button" class="btn-close" onclick="closeModal('modalEdit')">&times;</button>
            </div>
            <form method="POST" action="proses.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kode_barang_edit" class="form-label">Kode Barang</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="kode_barang_edit" 
                                   name="kode_barang" 
                                   readonly 
                                   style="background: #f8fafc; cursor: not-allowed;">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama_barang_edit" class="form-label">Nama Barang</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama_barang_edit" 
                                   name="nama_barang" 
                                   placeholder="Nama barang jastip" 
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kategori_edit" class="form-label">Kategori</label>
                            <select class="form-control" id="kategori_edit" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Kosmetik">Kosmetik</option>
                                <option value="Fashion">Fashion</option>
                                <option value="Makanan">Makanan</option>
                                <option value="Elektronik">Elektronik</option>
                                <option value="Aksesoris">Aksesoris</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_edit" class="form-label">Harga (Rp)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="harga_edit" 
                                   name="harga" 
                                   placeholder="Harga jual" 
                                   min="0" 
                                   step="0.01" 
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stok_edit" class="form-label">Stok</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="stok_edit" 
                                   name="stok" 
                                   placeholder="Jumlah stok" 
                                   min="0" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" onclick="closeModal('modalEdit')">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = this.value.trim();
                window.location.href = 'data_barang.php' + (searchValue ? '?search=' + encodeURIComponent(searchValue) : '');
            }, 500);
        });

        function openModalTambah() {
            document.getElementById('modalTambah').classList.add('show');
        }

        function openModalEdit(barang) {
            document.getElementById('edit_id').value = barang.id;
            document.getElementById('kode_barang_edit').value = barang.kode_barang;
            document.getElementById('nama_barang_edit').value = barang.nama_barang;
            document.getElementById('kategori_edit').value = barang.kategori;
            document.getElementById('harga_edit').value = barang.harga;
            document.getElementById('stok_edit').value = barang.stok;
            document.getElementById('modalEdit').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }

        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
                window.location.href = 'proses.php?action=hapus&id=' + id;
            }
        }
    </script>
</body>
</html>
