<?php
session_start();
require '../connect.php';

// Pastikan hanya kasir yang dapat mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: index.php");
    exit();
}

// Query untuk menampilkan semua barang
$barang_query = "SELECT * FROM barang";
$barang_result = mysqli_query($conn, $barang_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir</title>
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
        <h1 class="text-4xl font-bold text-center mb-6 text-primary-orange">Dashboard Kasir</h1>

        <!-- Daftar Barang -->
        <div class="bg-white text-dark-gray p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Daftar Barang</h2>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-primary-orange text-white">
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Nama Barang</th>
                        <th class="border border-gray-300 px-4 py-2">Stok</th>
                        <th class="border border-gray-300 px-4 py-2">Harga</th>
                        <th class="border border-gray-300 px-4 py-2">Status</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($barang_result)) {
                        echo "<tr class='hover:bg-gray-200'>
                                <td class='border border-gray-300 px-4 py-2'>{$row['id_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['nama_barang']}</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['stok']}</td>
                                <td class='border border-gray-300 px-4 py-2'>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                                <td class='border border-gray-300 px-4 py-2'>{$row['status']}</td>
                                <td class='border border-gray-300 px-4 py-2 text-center'>
                                    <a href='cetak_struk.php?id_barang={$row['id_barang']}' 
                                       class='px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-6000'>
                                       Cetak Struk
                                    </a>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Logout Button -->
        <button onclick="window.location.href='../logout.php';"
            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</button>

    </div>

</body>

</html>