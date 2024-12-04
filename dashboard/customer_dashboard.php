<?php
session_start();
require '../connect.php';

// Pastikan hanya customer yang dapat mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

// Query untuk melihat stok barang
$stok_masuk_query = "SELECT * FROM barang";
$stok_masuk_result = mysqli_query($conn, $stok_masuk_query);

$stok_keluar_query = "SELECT struk.id_struk, struk.id_barang, struk.jumlah, struk.status, barang.id_barang, barang.nama_barang
FROM struk
Join barang ON struk.id_barang = barang.id_barang";
$stok_keluar_result = mysqli_query($conn, $stok_keluar_query);
?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
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
    <div class="container mx-auto py-8">
        <h1 class="text-4xl font-bold text-center mb-6 text-primary-orange">Customer Dashboard</h1>

        <!-- Section: Stok Barang -->
        <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Stok Barang</h2>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-primary-orange text-white">
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Nama Barang</th>
                        <th class="border border-gray-300 px-4 py-2">Stok</th>
                        <th class="border border-gray-300 px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($stok_masuk_result)) {
                        echo "<tr class='hover:bg-gray-200'>
                                <td class='border border-gray-300 px-4 py-2'>{$row['id_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['nama_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['stok']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['status']}</td>
                            </tr>";
                    }
                    while ($row = mysqli_fetch_assoc($stok_keluar_result)) {
                        echo "<tr class='hover:bg-gray-200'>
                                <td class='border border-gray-300 px-4 py-2'>{$row['id_struk']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['nama_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['jumlah']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['status']}</td>
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