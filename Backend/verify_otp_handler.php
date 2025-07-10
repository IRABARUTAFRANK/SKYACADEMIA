<?php
session_start();
require_once '../connection/connect.php';

$conn = getPDOConnection();
$otp = $_POST['otp'] ?? '';
$email = $_SESSION['pending_registration']['school_email'] ?? '';

if (empty($otp) || empty($email)) {
    echo "❌ Invalid OTP or session expired.";
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM EmailVerificationTokens WHERE Email = ? AND OTP = ? AND Purpose = 'Registration' AND IsUsed = FALSE AND ExpiresAt >= NOW()");
    $stmt->execute([$email, $otp]);

    if ($stmt->rowCount() === 0) {
        echo "<p style='color:red;'>❌ Invalid or expired OTP.</p>";
        exit;
    }

    // Mark OTP as used
    $markUsed = $conn->prepare("UPDATE EmailVerificationTokens SET IsUsed = TRUE WHERE Email = ? AND OTP = ?");
    $markUsed->execute([$email, $otp]);

    // Insert school from session
    $data = $_SESSION['pending_registration'];
    $insert = $conn->prepare("INSERT INTO Schools (SchoolName, SchoolEmail, SchoolContacts, ContactPerson, AreaOfLocation, SchoolStatus) VALUES (?, ?, ?, ?, ?, 'Pending Verification')");
    $insert->execute([
        $data['school_name'],
        $data['school_email'],
        $data['school_contacts'],
        $data['contact_person'],
        $data['area_location']
    ]);

    unset($_SESSION['pending_registration']);
    echo "<h3 style='color:green;'>✅ Email verified. School has been registered!</h3>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
