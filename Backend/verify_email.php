<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email - SkyAcademia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="verification-container">
        <form action="verify_otp.php" method="POST">
            <h2>Email Verification</h2>
            <p>Please enter the 6-digit code sent to your email.</p>
            
            <label for="otp">Verification Code (OTP):</label>
            <input type="text" name="otp" id="otp" maxlength="6" pattern="\d{6}" required placeholder="e.g. 123456">
            
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
