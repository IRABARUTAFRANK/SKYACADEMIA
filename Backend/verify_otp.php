<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - SkyAcademia</title>
    <link rel="stylesheet" href="../CSS/verify.css">
</head>
<body>
    <div class="verify-box">
        <h2>Email Verification</h2>
        <form action="verify_otp_handler.php" method="POST">
            <label for="otp">Enter the OTP sent to your email:</label><br>
            <input type="text" name="otp" id="otp" maxlength="6" required pattern="\d{6}" placeholder="e.g., 123456"><br><br>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
