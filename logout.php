<?php
session_start();

// Hancurkan sesi yang ada untuk logout
session_destroy();

// Arahkan kembali ke halaman login (index.php)
header('Location: index.php');
exit();
?>
