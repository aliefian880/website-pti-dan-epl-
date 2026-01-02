<?php
session_start();

// =========================================
// KONEKSI DATABASE (config.php digabung disini)
// =========================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "web_perusahaan_cv_global_indotech";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// =========================================
// CEK LOGIN (WAJIB LOGIN DULU)
// =========================================
if (!isset($_SESSION['user_id'])) {
    echo "<p>Anda harus login dulu! <a href='login.php'>Login</a></p>";
    exit;
}

// =========================================
// LOAD PHPMailer
// =========================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// =========================================
// PROSES SIMPAN & KIRIM PESAN
// =========================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION['user_id'];
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Validasi form
    if (empty($name) || empty($email) || empty($message)) {
        echo "<p style='color:red;'>Semua field wajib diisi.</p>";
        exit;
    }

    // =========================================
    // SIMPAN PESAN KE DATABASE
    // =========================================
    $sql = "INSERT INTO pesan_kontak (user_id, nama, email, pesan)
            VALUES ('$user_id', '$name', '$email', '$message')";

    if ($conn->query($sql)) {

        // -----------------------------------------
        // KONFIGURASI EMAIL ADMIN
        // -----------------------------------------
        $gmail_user = "EMAIL_ADMIN_KAMU@gmail.com"; // <- Ganti
        $gmail_pass = "APP_PASSWORD_16_DIGIT";      // <- Ganti
        $admin_email = "aliefiansubagia803@gmail.com";

        $mail = new PHPMailer(true);

        try {
            // Set SMTP
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = true;
            $mail->Username   = $gmail_user;
            $mail->Password   = $gmail_pass;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;

            // Email pengirim
            $mail->setFrom($gmail_user, "Admin Website");

            // Email penerima
            $mail->addAddress($admin_email, "Admin");

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = "Pesan Baru dari: $name";
            $mail->Body = "
                <h3>Ada Pesan Baru dari Website</h3>
                <p><strong>Nama:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Pesan:</strong><br>$message</p>
            ";

            $mail->send();

            echo "<p style='color:green;'>Pesan berhasil dikirim & disimpan ke database!</p>";

        } catch (Exception $e) {
            echo "<p style='color:red;'>Pesan tersimpan, tetapi email gagal terkirim: {$mail->ErrorInfo}</p>";
        }

    } else {
        echo "<p style='color:red;'>Gagal menyimpan pesan ke database!</p>";
    }
}
?>

<!-- ==============================
FORM INPUT PESAN (TERMASUK DALAM 1 FILE)
============================== -->
<h2>Kirim Pesan ke Admin</h2>

<form action="" method="POST">
    <label>Nama:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Pesan:</label><br>
    <textarea name="message" rows="5" required></textarea><br><br>

    <button type="submit">Kirim Pesan</button>
</form>
