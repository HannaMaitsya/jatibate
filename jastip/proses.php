<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/koneksi.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'tambah') {
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $stok = trim($_POST['stok'] ?? '');
    
    if (empty($kode_barang) || empty($nama_barang) || empty($kategori) || empty($harga) || empty($stok)) {
        header('Location: data_barang.php?error=empty');
        exit;
    }
    
    if (!is_numeric($harga) || !is_numeric($stok) || $harga < 0 || $stok < 0) {
        header('Location: data_barang.php?error=invalid');
        exit;
    }
    
    try {
        $check = $pdo->prepare("SELECT id FROM barang WHERE kode_barang = ?");
        $check->execute([$kode_barang]);
        
        if ($check->fetch()) {
            header('Location: data_barang.php?error=duplicate');
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, kategori, harga, stok, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$kode_barang, $nama_barang, $kategori, $harga, $stok]);
        
        header('Location: data_barang.php?success=tambah');
        exit;
    } catch (PDOException $e) {
        header('Location: data_barang.php?error=system');
        exit;
    }
}

if ($action === 'edit') {
    $id = $_POST['id'] ?? '';
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $stok = trim($_POST['stok'] ?? '');
    
    if (empty($id) || empty($kode_barang) || empty($nama_barang) || empty($kategori) || empty($harga) || empty($stok)) {
        header('Location: data_barang.php?error=empty');
        exit;
    }
    
    if (!is_numeric($harga) || !is_numeric($stok) || $harga < 0 || $stok < 0) {
        header('Location: data_barang.php?error=invalid');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE barang SET nama_barang = ?, kategori = ?, harga = ?, stok = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nama_barang, $kategori, $harga, $stok, $id]);
        
        header('Location: data_barang.php?success=edit');
        exit;
    } catch (PDOException $e) {
        header('Location: data_barang.php?error=system');
        exit;
    }
}

if ($action === 'hapus') {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        header('Location: data_barang.php?error=invalid');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: data_barang.php?success=hapus');
        exit;
    } catch (PDOException $e) {
        header('Location: data_barang.php?error=system');
        exit;
    }
}

header('Location: data_barang.php');
exit;
