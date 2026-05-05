<?php
session_start();
include("config.php");

function smtpSend($fromEmail, $fromName, $toEmail, $subject, $htmlBody, $smtpHost, $smtpPort, $smtpUser, $smtpPass) {
    $timeout = 30;
    $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $fp = stream_socket_client("ssl://{$smtpHost}:{$smtpPort}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if (!$fp) {
        throw new Exception("Không kết nối được SMTP: {$errstr} ({$errno})");
    }

    $response = fgets($fp, 515);
    if (substr($response, 0, 3) !== '220') {
        fclose($fp);
        throw new Exception('SMTP server không trả về 220: ' . trim($response));
    }

    $send = function($command, $expectedCode) use ($fp) {
        fwrite($fp, $command . "\r\n");
        $response = '';
        while ($line = fgets($fp, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        if (substr($response, 0, 3) !== (string)$expectedCode) {
            throw new Exception("Lỗi SMTP ({$expectedCode}): " . trim($response));
        }
        return $response;
    };

    $send('EHLO localhost', 250);
    $send('AUTH LOGIN', 334);
    $send(base64_encode($smtpUser), 334);
    $send(base64_encode($smtpPass), 235);
    $send('MAIL FROM:<' . $fromEmail . '>', 250);
    $send('RCPT TO:<' . $toEmail . '>', 250);
    $send('DATA', 354);

    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'To: ' . $toEmail;
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
    $headers[] = 'Date: ' . date('r');

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody;
    $message = str_replace("\r\n.\r\n", "\r\n..\r\n", $message);

    fwrite($fp, $message . "\r\n.\r\n");
    $send('', 250);
    $send('QUIT', 221);
    fclose($fp);
    return true;
}

if (isset($_POST['guimail'])) {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM nguoidungs WHERE Email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $otp = rand(100000, 999999);
        $token = bin2hex(random_bytes(16));

        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_token'] = $token;

        $reset_link = "http://127.0.0.1/webbantrangsuc/php/reset_password.php?email=" . urlencode($email) . "&token=$token";

        $subject = 'Khôi phục mật khẩu';
        $message = '<html><body>' .
            '<div style="font-family:Arial,sans-serif;color:#333;line-height:1.5;">' .
            '<p>Bạn vừa yêu cầu khôi phục mật khẩu.</p>' .
            '<p>Thông tin này chỉ có hiệu lực trong 10 phút.</p>' .
            '<p>Mã xác nhận (OTP) của bạn là: <strong>' . $otp . '</strong></p>' .
            '<p>Nhấn vào liên kết sau để tiếp tục:</p>' .
            '<p><a href="' . $reset_link . '">' . $reset_link . '</a></p>' .
            '<hr>' .
            '<p>Nếu bạn không yêu cầu, hãy bỏ qua email này hoặc đổi mật khẩu email để bảo mật.</p>' .
            '</div>' .
            '</body></html>';

        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 465;
        $smtpUser = 'ntkngoc.noliship@gmail.com';
        $smtpPass = 'byya bffe yuhx jxmx';
        $fromEmail = 'ntkngoc.noliship@gmail.com';
        $fromName = 'Luxury Shop';

        try {
            smtpSend($fromEmail, $fromName, $email, $subject, $message, $smtpHost, $smtpPort, $smtpUser, $smtpPass);
            echo "<script>alert('Đã gửi mail khôi phục thành công!'); window.location='reset_password.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Gửi mail thất bại: " . addslashes($e->getMessage()) . "'); window.location='quenmatkhau.php';</script>";
        }
    } else {
        echo "<script>alert('Email không tồn tại!'); window.location='quenmatkhau.php';</script>";
    }
}
?>