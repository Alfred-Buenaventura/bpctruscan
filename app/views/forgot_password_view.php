<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BPC Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 40px !important; }
        .toggle-password-btn { position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 1rem; padding: 0; display: flex; align-items: center; height: 100%; }
        .toggle-password-btn:hover { color: var(--emerald-600, #059669); }
    </style>
</head>
<body class="login-page">
    
    <div class="card login-card-new" style="max-width: 450px;">
        
        <div class="login-new-header">
            <div class="login-logo-container">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="login-title">Reset Password</h2>
            <p class="login-subtitle">Secure Account Recovery</p>
        </div>

        <div class="login-new-body">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success) && $step !== 4): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <p style="text-align: center; color: #6b7280; margin-bottom: 1.5rem; font-size: 0.95rem;">
                    Please enter both your <strong>Email Address</strong> and <strong>Faculty ID</strong> to verify your identity.
                </p>
                <form method="POST">
                    <div class="form-group">
                        <label>Faculty ID</label>
                        <input type="text" name="faculty_id" class="form-control" placeholder="e.g., FAC-001" required>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>Registered Email</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g., faculty@bpc.edu.ph" required>
                    </div>
                    <button type="submit" name="send_otp" class="btn btn-primary btn-full-width" style="margin-top: 1.5rem;">
                        Verify & Send OTP <i class="fa-solid fa-paper-plane" style="margin-left:8px;"></i>
                    </button>
                </form>
                <a href="login.php" class="login-new-forgot-link" style="text-align: center; margin-top: 1.5rem;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Login
                </a>
            <?php endif; ?>

            <?php if ($step === 2): ?>
                <p style="text-align: center; color: #6b7280; margin-bottom: 1.5rem;">
                    We sent a 6-character code to your email. It expires in 5 minutes.
                </p>
                <form method="POST">
                    <div class="form-group">
                        <label>Enter OTP</label>
                        <input type="text" name="otp" class="form-control" maxlength="6" placeholder="######" style="text-align: center; letter-spacing: 4px; font-size: 1.2rem;" required>
                    </div>
                    <button type="submit" name="verify_otp" class="btn btn-primary btn-full-width" style="margin-top: 1rem;">
                        Verify Code
                    </button>
                </form>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="forgot_password.php?action=backtologin" class="btn btn-secondary btn-sm">Cancel</a>
                </div>
            <?php endif; ?>

            <?php if ($step === 3): ?>
                <p style="text-align: center; color: #6b7280; margin-bottom: 1.5rem;">
                    Create a new, strong password for your account.
                </p>
                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 8 characters" required>
                            <button type="button" class="toggle-password-btn" onclick="togglePass('new_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter new password" required>
                            <button type="button" class="toggle-password-btn" onclick="togglePass('confirm_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="reset_password" class="btn btn-primary btn-full-width" style="margin-top: 1rem;">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($step === 4): ?>
                <div style="text-align: center; padding: 1rem;">
                    <div style="width: 80px; height: 80px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
                        <i class="fa-solid fa-check" style="font-size: 40px; color: #10b981;"></i>
                    </div>
                    <h3 style="color: #065f46; margin-bottom: 0.5rem;">Password Reset!</h3>
                    <p style="color: #6b7280; margin-bottom: 2rem;">
                        Your password has been successfully updated. You can now log in with your new credentials.
                    </p>
                    <a href="login.php" class="btn btn-primary btn-full-width">
                        Go to Login
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        function togglePass(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>