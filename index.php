<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menggunakan prepared statement untuk mencegah SQL injection
    $query = "SELECT * FROM login WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);  // Menyusun query dengan parameter
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];  // Simpan role di session

        // Redirect berdasarkan role
        if ($user['role'] == 'admin') {
            header('Location: dashboard/admin_dashboard.php');  // Pastikan path sesuai
        } elseif ($user['role'] == 'kasir') {
            header('Location: dashboard/kasir_dashboard.php');  // Pastikan path sesuai
        } else {
            header('Location: dashboard/customer_dashboard.php');  // Redirect ke dashboard customer
        }
        exit();
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

<body class="bg-dark-gray text-white flex items-center justify-center min-h-screen">

    <div class="bg-white text-dark-gray p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-primary-orange mb-6">Login</h2>

        <!-- Pesan jika ada error -->
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" action="" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-orange">
            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-orange">
            <button type="submit"
                class="w-full px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-primary-orange">
                Login
            </button>
        </form>

        <!-- Link ke halaman register -->
        <p class="text-center text-sm mt-4">Belum punya akun? <a href="register.php"
                class="text-primary-orange hover:underline">Daftar di sini</a></p>
    </div>

</body>

</html>