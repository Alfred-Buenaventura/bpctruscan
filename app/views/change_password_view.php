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

<div class="main-body">
    <?php if ($error): ?> <div class="alert alert-error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="alert alert-success"><?= htmlspecialchars($success) ?></div> <?php endif; ?>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <h3>Change Account Password</h3>
            <p>Ensure your account remains secure with a strong password.</p>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8">
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

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