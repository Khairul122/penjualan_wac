<?php
session_start();
include '../koneksi.php';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['password']) {
            $_SESSION['id_users'] = $user['id_users'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = $user['level'];

            $welcome = "Selamat datang, " . $user['username'];
            echo "<script>alert('$welcome');window.location.href='../index.php?page=dashboard';</script>";
            exit;
        } else {
            echo "<script>alert('Login gagal! Password salah.');window.location.href='../index.php?page=login';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Login gagal! Username tidak ditemukan.');window.location.href='../index.php?page=login';</script>";
        exit;
    }
} else {
    header("Location: ../index.php?page=login");
    exit;
} 

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_destroy();
    echo "<script>alert('Anda berhasil logout');window.location.href='../index.php?page=login';</script>";
    exit;
}

