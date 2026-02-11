<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BPC Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=1.2">
</head>

<style>
.form-options-new {
    width: 100%;             /* Occupy full width of the form */
    margin: 15px 0;          /* Space above and below */
    display: flex;           /* Use flexbox */
    justify-content: flex-start; /* FORCE ALIGN TO LEFT */
    text-align: left;        /* Override any parent center alignment */
}

.checkbox {
    display: flex;
    align-items: center;     /* Centers checkbox vertically with text */
    gap: 8px;                /* Space between box and "Remember me" */
    cursor: pointer;
    font-size: 0.85rem;
    color: #4b5563;
    margin: 0;               /* Remove any default margins */
}

.checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
    margin: 0;               /* Ensure no extra spacing around the box */
    cursor: pointer;
    accent-color: #10b981;   /* Emerald green theme color */
}
    
</style>
<body class="login-page">
    
    <div class="card login-card-new">
        
        <div class="login-new-header">
            <div class="login-logo-container">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <h2 class="login-title">BPC Attendance System</h2>
            <p class="login-subtitle">Biometric Attendance Monitoring</p>
        </div>

        <div class="login-new-body">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

<form action="login.php" method="POST">
    <?php csrf_field(); ?> <div class="form-group">
        <label for="username">Username or Faculty ID</label>
        <input type="text" name="username" id="username" class="form-control" required>
    </div>
    
    <div class="form-group" style="margin-top: 15px;">
        <label for="password">Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="loginPass" class="form-control" required>
            <button type="button" class="toggle-password" onclick="togglePass('loginPass')">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
    </div>

    <div class="form-options-new">
        <label class="checkbox">
            <input type="checkbox" name="remember">
            <span>Remember me for 30 days</span>
        </label>
    </div>

    <button type="submit" name="login" class="btn btn-primary btn-full-width">
        <i class="fa-solid fa-arrow-right-to-bracket"></i> <span>Sign In</span> 
    </button>
</form>

<?php if (isset($_GET['timeout'])): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-clock-rotate-left"></i> 
        You have been logged out due to inactivity for your security.
    </div>
<?php endif; ?>

<a href="forgot_password.php" class="login-new-forgot-link">Forgot your password?</a>
</div>
</div>
    
    <script>
        function togglePass(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
    </script>
</body>
</html>