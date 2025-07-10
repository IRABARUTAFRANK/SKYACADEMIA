<?php
session_start();
require_once '../connection/connect.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getPDOConnection();

// ✅ Validate Rwandan numbers: +250 followed by 72, 73, 77, 78, or 79 and 7 digits
function isValidRwandanNumber($phone) {
    return preg_match('/^\+250(72|73|77|78|79)[0-9]{7}$/', $phone);
}

// ✅ Collect form inputs
$school_name     = $_POST['school_name'] ?? '';
$school_email    = $_POST['school_email'] ?? '';
$phone_prefix    = $_POST['phone_prefix'] ?? '+250'; // ✅ FIXED
$phone_number    = $_POST['phone_number'] ?? '';
$contact_person  = $_POST['contact_person'] ?? '';
$area_location   = $_POST['area_location'] ?? '';

// ✅ Validate empty fields
if (
    empty($school_name) || empty($school_email) || 
    empty($phone_prefix) || empty($phone_number) || 
    empty($contact_person) || empty($area_location)
) {
    echo "<p style='color:red;'>❌ Please fill in all required fields.</p>";
    exit;
}

// ✅ Clean phone number and combine with prefix
$clean_number = preg_replace('/\\D/', '', $phone_number);
$full_phone = $phone_prefix . $clean_number;

// ✅ Validate phone format
if (!isValidRwandanNumber($full_phone)) {
    echo "<p style='color:red;'>❌ Invalid Rwandan phone number. Must be like +25078xxxxxxx.</p>";
    exit;
}

try {
    // ✅ Check for duplicate email
    $checkEmail = $conn->prepare("SELECT COUNT(*) FROM Schools WHERE SchoolEmail = ?");
    $checkEmail->execute([$school_email]);
    if ($checkEmail->fetchColumn() > 0) {
        echo "<p style='color:red;'>❌ This email is already in use. Try another.</p>";
        exit;
    }

    // ✅ Check for duplicate phone
    $checkPhone = $conn->prepare("SELECT COUNT(*) FROM Schools WHERE SchoolContacts = ?");
    $checkPhone->execute([$full_phone]);
    if ($checkPhone->fetchColumn() > 0) {
        echo "<p style='color:red;'>❌ This phone number already exists. Try another.</p>";
        exit;
    }

    // ✅ Generate OTP
    $otp = random_int(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // ✅ Save OTP in tokens table
    $insertOtp = $conn->prepare("INSERT INTO EmailVerificationTokens (Email, OTP, Purpose, ExpiresAt) VALUES (?, ?, 'Registration', ?)");
    $insertOtp->execute([$school_email, $otp, $expires_at]);

    // ✅ Store data in session
    $_SESSION['pending_registration'] = [
        'school_name'     => $school_name,
        'school_email'    => $school_email,
        'school_contacts' => $full_phone,
        'contact_person'  => $contact_person,
        'area_location'   => $area_location
    ];

    // ✅ Send Email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'irabarutafrank@gmail.com';  
    $mail->Password   = 'qlbe klix epsm dzmd';  // ✅ App password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('irabarutafrank@gmail.com', 'SkyAcademia');
    $mail->addAddress($school_email);

    $mail->isHTML(true);
    $mail->Subject = 'SKYACADEMIA Email Verification Code';
    $mail->Body    = "
        <h3>SKYACADEMIA Registration</h3>
        <p>Your OTP code for fully registering your school is: 
        <strong style='font-size:18px;'>$otp</strong></p>
        <p>This code expires in 10 minutes.<br>We are glad to have you as a SKYACADEMIA member.</p>
        <p>Best regards,<br>SKYACADEMIA  Team</p>
    ";

    $mail->send();

    header("Location: verify_email.php");
    exit();

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Email failed. Error: {$mail->ErrorInfo}</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
