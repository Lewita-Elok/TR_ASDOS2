<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Default role adalah 'customer'
    $role = 'customer';

    $query = "INSERT INTO login (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
    if (mysqli_query($conn, $query)) {
        header('Location: index.php'); // Arahkan ke halaman login setelah berhasil daftar
        exit();
    } else {
        $error = "Gagal mendaftarkan akun. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
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
        <h2 class="text-3xl font-bold text-center text-primary-orange mb-6">Daftar Akun</h2>

        <!-- Form Pendaftaran -->
        <form method="POST" action="" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-orange">
            <input type="email" name="email" placeholder="Email" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-orange">
            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-orange">

            <button type="submit"
                class="w-full px-4 py-2 bg-primary-orange text-white rounded-lg hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-primary-orange">
                Daftar
            </button>

            <!-- Pesan Error jika ada -->
            <?php if (isset($error)): ?>
                <p class="text-red-500 text-center mt-4"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>

        <!-- Link ke halaman login -->
        <p class="text-center text-sm mt-4">Sudah punya akun? <a href="index.php"
                class="text-primary-orange hover:underline">Login di sini</a></p>
    </div>

</body>

</html>