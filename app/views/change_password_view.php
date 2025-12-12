<?php 
if (!$firstLogin) { require_once __DIR__ . '/partials/header.php';  } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - BPC Attendance</title>
    <?php if ($firstLogin): ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php endif; ?>

    <style>
        /* Internal styles to ensure Toggles work in both Dashboard and Login views */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            padding-right: 40px !important; /* Make room for the icon */
        }
        .toggle-password-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 1rem;
            padding: 0;
            display: flex;
            align-items: center;
            height: 100%;
        }
        .toggle-password-btn:hover {
            color: var(--emerald-600, #059669);
        }
        
        /* Simple Welcome Modal Styles */
        #welcomeModal {
            display: none; 
            position: fixed; 
            z-index: 9999; 
            left: 0; top: 0; 
            width: 100%; height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            backdrop-filter: blur(4px);
            align-items: center; 
            justify-content: center;
            animation: fadeIn 0.3s;
        }
        .welcome-modal-content {
            background-color: #fff;
            margin: auto;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-top: 5px solid var(--emerald-500, #10b981);
        }
        .welcome-icon {
            font-size: 3rem;
            color: var(--emerald-500, #10b981);
            margin-bottom: 1rem;
        }
        .welcome-btn {
            margin-top: 1.5rem;
            background: var(--emerald-600, #059669);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .welcome-btn:hover { background: var(--emerald-700, #047857); }
    </style>
</head>

<body class="<?= $firstLogin ? 'login-page' : '' ?>">

    <?php if ($firstLogin): ?>
        <div class="card login-card-new" style="max-width: 500px;">
    <?php else: ?>
        <div class="main-body">
            <div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php endif; ?>

        <div class="<?= $firstLogin ? 'login-new-header' : 'card-header' ?>">
            <?php if($firstLogin): ?>
                <div class="login-logo-container"><i class="fa-solid fa-key"></i></div>
            <?php endif; ?>
            <h3>Change Password</h3>
        </div>
        
        <div class="<?= $firstLogin ? 'login-new-body' : 'card-body' ?>">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                        <button type="button" class="toggle-password-btn" onclick="togglePass('current_password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="new_password" class="form-control" minlength="8" required>
                        <button type="button" class="toggle-password-btn" onclick="togglePass('new_password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="8" required>
                        <button type="button" class="toggle-password-btn" onclick="togglePass('confirm_password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full-width">Update Password</button>
            </form>
            
            <?php if (!$firstLogin): ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$firstLogin): ?>
        </div> <?php endif; ?>

    <div id="welcomeModal">
        <div class="welcome-modal-content">
            <div class="welcome-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h2 style="margin-bottom: 0.5rem; color: #333;">Security Notice</h2>
            <p style="color: #555; line-height: 1.6;">
                Welcome, <strong><?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></strong>.<br>
                For security purposes, we advise you to change your password before accessing your account.
            </p>
            <p style="color: #555; margin-top: 0.5rem;">Have a nice day!</p>
            <button class="welcome-btn" onclick="document.getElementById('welcomeModal').style.display='none'">
                Okay, I understand
            </button>
        </div>
    </div>

    <script>
        // Toggle Password Visibility Logic
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

        // Show Welcome Modal on First Login
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($firstLogin && empty($error) && empty($success)): ?>
                // Only show if it's first login AND form hasn't been submitted yet (no error/success msg)
                const modal = document.getElementById('welcomeModal');
                if(modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden'; // Prevent scrolling
                }
            <?php endif; ?>

            // Allow closing modal to restore scrolling
            const closeBtn = document.querySelector('.welcome-btn');
            if(closeBtn) {
                closeBtn.addEventListener('click', () => {
                    document.body.style.overflow = 'auto';
                });
            }
        });
    </script>

<?php if (!$firstLogin) { require_once __DIR__ . '/partials/footer.php'; } else { echo '</body></html>'; } ?>