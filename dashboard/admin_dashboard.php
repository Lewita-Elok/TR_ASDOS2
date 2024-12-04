<?php
session_start();
require '../connect.php';

// Pastikan hanya admin yang dapat mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fungsi untuk menambahkan barang
if (isset($_POST['add_item'])) {
    $nama_barang = $_POST['nama_barang'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];
    $status = 'Masuk'; // Status barang langsung menjadi 'Masuk' saat ditambahkan
    $query = "INSERT INTO barang (nama_barang, stok, harga, status) VALUES ('$nama_barang', $stok, $harga, '$status')";
    mysqli_query($conn, $query);
    header("Location: admin_dashboard.php");
    exit();
}

// Fungsi untuk mengedit barang
if (isset($_POST['edit_item'])) {
    $id_barang = (int) $_POST['id_barang'];
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok = (int) $_POST['stok'];
    $harga = (float) $_POST['harga'];
    $status = mysqli_real_escape_string($conn, $_POST['status']); // Menambahkan status barang

    // Query untuk memperbarui data barang
    $query = "UPDATE barang SET nama_barang='$nama_barang', stok=$stok, harga=$harga, status='$status' WHERE id_barang=$id_barang";

    // Menjalankan query dan menangani error
    if (mysqli_query($conn, $query)) {
        // Jika berhasil, alihkan ke halaman admin.php
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Jika error, tampilkan pesan error
        echo "Error: " . mysqli_error($conn);
    }
}

// Fungsi untuk menghapus barang
if (isset($_POST['delete_item'])) {
    $id_barang = $_POST['id_barang'];
    $query = "DELETE FROM barang WHERE id_barang=$id_barang";
    mysqli_query($conn, $query);
    header("Location: admin_dashboard.php");
    exit();
}

// Fungsi untuk menambahkan pemasok
if (isset($_POST['add_supplier'])) {
    $nama_pemasok = $_POST['nama_pemasok'];
    $kontak = $_POST['kontak'];
    $query = "INSERT INTO supplier (nama_pemasok, kontak) VALUES ('$nama_pemasok', '$kontak')";
    mysqli_query($conn, $query);
    header("Location: admin_dashboard.php");
    exit();
}

// Fungsi stok keluar
if (isset($_POST['process_sale'])) {
    $id_barang = $_POST['id_barang'];
    $jumlah = $_POST['jumlah'];
    $tanggal_penjualan = date('Y-m-d'); // Tanggal transaksi

    // Menambahkan transaksi keluar
    $query_transaksi_keluar = "
        INSERT INTO struk (id_barang, jumlah, tanggal_penjualan, status) 
        VALUES ($id_barang, $jumlah, '$tanggal_penjualan', 'keluar')";
    mysqli_query($conn, $query_transaksi_keluar);

    // Mengurangi stok barang
    $update_stok_query = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang";
    mysqli_query($conn, $update_stok_query);

    header("Location: admin_dashboard.php");
    exit();
}

// Query untuk laporan stok harian dan barang keluar
$stok_harian_query = "
    SELECT barang.id_barang, barang.nama_barang, barang.stok, 
           SUM(CASE WHEN struk.status = 'keluar' THEN struk.jumlah ELSE 0 END) AS total_keluar
    FROM barang
    LEFT JOIN struk ON barang.id_barang = struk.id_barang AND struk.status = 'keluar'
    GROUP BY barang.id_barang, barang.nama_barang, barang.stok";
$stok_harian_result = mysqli_query($conn, $stok_harian_query);

// Query untuk analisis barang terlaris
$terlaris_query = "
    SELECT barang.nama_barang, SUM(CASE WHEN struk.status = 'keluar' THEN struk.jumlah ELSE 0 END) AS total_terjual 
    FROM struk
    INNER JOIN barang ON struk.id_barang = barang.id_barang
    GROUP BY barang.id_barang
    ORDER BY total_terjual DESC
    LIMIT 5";
$terlaris_result = mysqli_query($conn, $terlaris_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

    <div class="container mx-auto py-8">
        <h1 class="text-4xl font-bold text-center mb-6 text-primary-orange">Admin Dashboard</h1>

        <!-- Form Tambah Barang -->
        <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Tambah Barang Baru</h2>
            <form method="POST" action="" class="space-y-4">
                <input type="text" name="nama_barang" placeholder="Nama Barang" required
                    class="w-full p-2 border border-gray-300 rounded-lg">
                <input type="number" name="stok" placeholder="Stok" required
                    class="w-full p-2 border border-gray-300 rounded-lg">
                <input type="number" name="harga" placeholder="Harga" required
                    class="w-full p-2 border border-gray-300 rounded-lg">
                <button type="submit" name="add_item"
                    class="px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600">Tambah Barang</button>
            </form>
        </div>

        <<!-- Form Kelola Barang -->
            <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold mb-4">Kelola Barang</h2>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-primary-orange text-white">
                            <th class="border border-gray-300 px-4 py-2">ID</th>
                            <th class="border border-gray-300 px-4 py-2">Nama Barang</th>
                            <th class="border border-gray-300 px-4 py-2">Stok</th>
                            <th class="border border-gray-300 px-4 py-2">Harga</th>
                            <th class="border border-gray-300 px-4 py-2">Status</th>
                            <th class="border border-gray-300 px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $barang_query = "SELECT * FROM barang";
                        $barang_result = mysqli_query($conn, $barang_query);
                        while ($row = mysqli_fetch_assoc($barang_result)) {
                            echo "<tr class='hover:bg-gray-200'>
                                <td class='border border-gray-300 px-4 py-2'>{$row['id_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['nama_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['stok']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['harga']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['status']}</td>
                                <td class='border border-gray-300 px-4 py-2 space-y-2'>
                                    <form method='POST' class='flex space-x-2'>
                                        <input type='hidden' name='id_barang' value='{$row['id_barang']}'>
                                        <input type='text' name='nama_barang' value='{$row['nama_barang']}' class='p-1 border border-gray-300 rounded-lg w-full'>
                                        <input type='number' name='stok' value='{$row['stok']}' class='p-1 border border-gray-300 rounded-lg w-full'>
                                        <input type='number' name='harga' value='{$row['harga']}' class='p-1 border border-gray-300 rounded-lg w-full'>
                                        <select name='status' class='p-1 border border-gray-300 rounded-lg w-full'>
                                            <option value='Masuk' " . ($row['status'] == 'Masuk' ? 'selected' : '') . ">Masuk</option>
                                            <option value='Keluar' " . ($row['status'] == 'Keluar' ? 'selected' : '') . ">Keluar</option>
                                        </select>
                                        <button type='submit' name='edit_item' class='px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600'>Edit</button>
                                    </form>
                                    <form method='POST' class='flex'>
                                        <input type='hidden' name='id_barang' value='{$row['id_barang']}'>
                                        <button type='submit' name='delete_item' class='px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600'>Hapus</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Tambah Pemasok -->
            <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold mb-4">Tambah Pemasok</h2>
                <form method="POST" action="" class="space-y-4">
                    <input type="text" name="nama_pemasok" placeholder="Nama Pemasok" required
                        class="w-full p-2 border border-gray-300 rounded-lg">
                    <input type="text" name="kontak" placeholder="Kontak Pemasok" required
                        class="w-full p-2 border border-gray-300 rounded-lg">
                    <button type="submit" name="add_supplier"
                        class="px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600">Tambah
                        Pemasok</button>
                </form>
            </div>


            <!-- Barang keluar -->
            <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold mb-4">Pengurangan Stok Barang (Barang Keluar)</h2>
                <form method="POST" action="" class="space-y-4">
                    <select name="id_barang" class="w-full p-2 border border-gray-300 rounded-lg">
                        <?php
                        // Menampilkan daftar barang
                        $barang_query = "SELECT * FROM barang";
                        $barang_result = mysqli_query($conn, $barang_query);
                        while ($barang = mysqli_fetch_assoc($barang_result)) {
                            echo "<option value='{$barang['id_barang']}'>{$barang['nama_barang']}</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="jumlah" placeholder="Jumlah Barang" required
                        class="w-full p-2 border border-gray-300 rounded-lg">
                    <button type="submit" name="process_sale"
                        class="px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600">Proses Barang
                        Keluar</button>
                </form>
            </div>

            <!-- Laporan Stok Harian -->
            <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold mb-4">Laporan Stok Harian</h2>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-primary-orange text-white">
                            <th class="border border-gray-300 px-4 py-2">ID</th>
                            <th class="border border-gray-300 px-4 py-2">Nama Barang</th>
                            <th class="border border-gray-300 px-4 py-2">Stok</th>
                            <th class="border border-gray-300 px-4 py-2">Barang Keluar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($stok_harian_result)) {
                            $barang_keluar = $row['total_keluar'] ? $row['total_keluar'] : 0;
                            echo "<tr class='hover:bg-gray-200'>
                                <td class='border border-gray-300 px-4 py-2'>{$row['id_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['nama_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['stok']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$barang_keluar}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Barang Terlaris -->
            <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold mb-4">Barang Terlaris</h2>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-primary-orange text-white">
                            <th class="border border-gray-300 px-4 py-2">Nama Barang</th>
                            <th class="border border-gray-300 px-4 py-2">Jumlah Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($terlaris_result)) {
                            echo "<tr class='hover:bg-gray-200'>
                                <td class='border border-gray-300 px-4 py-2'>{$row['nama_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['total_terjual']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Logout -->
            <button onclick="window.location.href='../logout.php';"
                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                Logout
            </button>
    </div>

</body>

</html>