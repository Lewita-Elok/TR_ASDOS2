<?php
session_start();
require '../connect.php';

// Pastikan hanya kasir yang dapat mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: index.php");
    exit();
}

// Ambil ID barang dari URL
if (isset($_GET['id_barang'])) {
    $id_barang = (int) $_GET['id_barang'];

    // Query untuk mendapatkan data barang berdasarkan ID
    $query = "SELECT * FROM barang WHERE id_barang = $id_barang";
    $result = mysqli_query($conn, $query);
    $barang = mysqli_fetch_assoc($result);

    // Pastikan barang ditemukan
    if (!$barang) {
        echo "Barang tidak ditemukan.";
        exit();
    }

    // Mendapatkan data transaksi terakhir dengan status keluar
    $transaksi_query = "SELECT * FROM struk WHERE id_barang = $id_barang AND status = 'keluar' ORDER BY id_struk DESC LIMIT 1";
    $transaksi_result = mysqli_query($conn, $transaksi_query);
    $transaksi = mysqli_fetch_assoc($transaksi_result);

    // Proses untuk menambahkan transaksi ke database jika ada pengajuan transaksi baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jumlah'])) {
        $jumlah = (int) $_POST['jumlah'];
        $tanggal_penjualan = date('Y-m-d'); // Ambil tanggal saat ini

        // Validasi input jumlah
        if ($jumlah <= 0) {
            echo "Jumlah transaksi tidak valid.";
            exit();
        }

        // Tentukan status transaksi baru
        $status = 'keluar'; // Transaksi default adalah keluar

        if ($barang['stok'] < $jumlah) {
            echo "Stok tidak mencukupi untuk transaksi ini.";
            exit();
        }

        // Query untuk menambahkan data ke tabel struk
        $query = "INSERT INTO struk (jumlah, id_barang, tanggal_penjualan, status) 
                  VALUES ($jumlah, $id_barang, '$tanggal_penjualan', '$status')";

        // Query untuk mengurangi stok barang
        $update_stok_query = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang";

        if (mysqli_query($conn, $query)) {
            // Update stok barang setelah transaksi
            if (mysqli_query($conn, $update_stok_query)) {
                echo "Transaksi berhasil ditambahkan.";
            } else {
                echo "Gagal memperbarui stok barang.";
            }
        } else {
            echo "Gagal menambahkan transaksi.";
        }
    }
} else {
    echo "ID Barang tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Pengiriman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Colors */
        :root {
            --primary-orange: #E67E22;
            --dark-gray: #34495E;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            orange: '#E67E22',
                        },
                        dark: {
                            gray: '#34495E',
                        },
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-dark-gray text-white">

    <!-- Container -->
    <div class="max-w-lg mx-auto bg-white text-dark-gray shadow-lg rounded-lg p-6 mt-10">
        <h2 class="text-2xl font-bold text-center text-primary-orange mb-4">Struk Pengiriman</h2>

        <!-- Informasi Barang -->
        <div class="mb-6">
            <p class="mb-2"><strong>ID Barang:</strong> <?php echo $barang['id_barang']; ?></p>
            <p class="mb-2"><strong>Nama Barang:</strong> <?php echo $barang['nama_barang']; ?></p>
            <p class="mb-2"><strong>Harga:</strong> Rp <?php echo number_format($barang['harga'], 0, ',', '.'); ?></p>
            <p class="mb-2"><strong>Stok:</strong> <?php echo $barang['stok']; ?></p>
        </div>

        <!-- Informasi Transaksi -->
        <?php if ($transaksi): ?>
            <h3 class="text-lg font-semibold mb-2 mt-6">Transaksi Terakhir (Keluar)</h3>
            <div class="mb-6">
                <p class="mb-2"><strong>ID Struk:</strong> <?php echo $transaksi['id_struk']; ?></p>
                <p class="mb-2"><strong>Jumlah:</strong> <?php echo $transaksi['jumlah']; ?></p>
                <p class="mb-4"><strong>Status:</strong> <?php echo ucfirst($transaksi['status']); ?></p>
                <p class="mb-4"><strong>Total:</strong> Rp
                    <?php echo number_format($transaksi['jumlah'] * $barang['harga'], 0, ',', '.'); ?>
                </p>
            </div>
        <?php else: ?>
            <p class="text-gray-500">Belum ada transaksi keluar untuk barang ini.</p>
        <?php endif; ?>

        <!-- Tombol Aksi -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-500 mb-4">Terima kasih telah berbelanja di toko kami!</p>
            <button onclick="window.print();"
                class="px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600">Cetak Struk</button>
            <a href="kasir_dashboard.php"
                class="ml-4 px-4 py-2 bg-gray-200 text-dark-gray rounded-lg hover:bg-gray-300">Kembali ke Dashboard</a>
        </div>
    </div>

</body>

</html>