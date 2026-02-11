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
        input::-ms-reveal,
        input::-ms-clear {
        display: none;
        }
        
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px !important; }
        .toggle-password {
            position: absolute; right: 10px; background: none; border: none;
            cursor: pointer; color: #9ca3af; font-size: 1.1rem; height: 100%;
        }
        
        /* Strength Meter Styling */
        .strength-meter {
            height: 6px; width: 100%; background-color: #e5e7eb;
            border-radius: 3px; margin-top: 8px; overflow: hidden;
        }
        .strength-bar {
            height: 100%; width: 0%; transition: width 0.3s, background-color 0.3s;
        }
        .strength-text { font-size: 0.8rem; margin-top: 4px; font-weight: 600; }

        #welcomeModal {
            display: none; position: fixed; z-index: 9999; left: 0; top: 0; 
            width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); 
            backdrop-filter: blur(4px); align-items: center; justify-content: center;
        }
        .welcome-modal-content {
            background-color: #fff; padding: 2rem; border-radius: 16px; width: 90%;
            max-width: 450px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-top: 5px solid #10b981;
        }
        .welcome-btn {
            margin-top: 1.5rem; background: #059669; color: white; border: none;
            padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%;
        }
    </style>
</head>

<div class="main-body">
    <?php if ($error): ?> <div class="alert alert-error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="alert alert-success"><?= htmlspecialchars($success) ?></div> <?php endif; ?>

    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-header">
            <h3>Update Your Password</h3>
            <p style="font-size: 0.9rem; color: #64748b;">A strong password helps protect your personal attendance records.</p>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php csrf_field(); ?>
                
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="current_password" class="form-control" id="currentPass" required>
                        <button type="button" class="toggle-password" onclick="togglePass('currentPass')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password" class="form-control" id="newPass" required oninput="updateMeter(this.value)">
                        <button type="button" class="toggle-password" onclick="togglePass('newPass')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-meter"><div id="bar" class="strength-bar"></div></div>
                    <div id="strengthText" class="strength-text" style="color: #9ca3af;">Enter a new password...</div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" class="form-control" id="confirmPass" required>
                        <button type="button" class="toggle-password" onclick="togglePass('confirmPass')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save New Password</button>
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="welcomeModal">
    <div class="welcome-modal-content">
        <div style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"><i class="fa-solid fa-shield-halved"></i></div>
        <h2 style="margin-bottom: 0.5rem; color: #333;">Security Notice</h2>
        <p style="color: #555; line-height: 1.6;">
            Welcome, <strong><?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></strong>.<br>
            For security purposes, we advise you to change your password before accessing your account.
        </p>
        <button class="welcome-btn" onclick="document.getElementById('welcomeModal').style.display='none'">
            Okay, I understand
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($firstLogin && empty($error) && empty($success)): ?>
            // Only show if it's first login AND form hasn't been submitted yet (no error/success msg)
            const modal = document.getElementById('welcomeModal');
            if(modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        <?php endif; ?>
        const closeBtn = document.querySelector('.welcome-btn');
            if(closeBtn) {
                closeBtn.addEventListener('click', () => {
                    document.body.style.overflow = 'auto';
                });
            }
        });

    document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Strength Meter Logic
    const newPassInput = document.getElementById('newPass');
    const bar = document.getElementById('bar');
    const text = document.getElementById('strengthText');

    if (newPassInput) {
        newPassInput.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;

            if (val.length >= 6) strength++;
            if (val.length >= 12) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[A-Z]/.test(val)) strength++;

            if (val === "") {
                bar.style.width = '0%';
                text.innerText = 'Enter a new password...';
                text.style.color = '#9ca3af';
            } else {
                switch(strength) {
                    case 0: case 1:
                        bar.style.width = '25%'; bar.style.backgroundColor = '#ef4444';
                        text.innerText = 'Weak - Keep typing...'; text.style.color = '#ef4444';
                        break;
                    case 2:
                        bar.style.width = '50%'; bar.style.backgroundColor = '#f59e0b';
                        text.innerText = 'Fair - Try adding a number or more letters.'; text.style.color = '#f59e0b';
                        break;
                    case 3:
                        bar.style.width = '75%'; bar.style.backgroundColor = '#10b981';
                        text.innerText = 'Good - This password is secure.'; text.style.color = '#10b981';
                        break;
                    case 4:
                        bar.style.width = '100%'; bar.style.backgroundColor = '#059669';
                        text.innerText = 'Excellent - Very strong password!'; text.style.color = '#059669';
                        break;
                }
            }
        });
    }

    // 2. Global Toggle Password Function
    window.togglePass = function(id) {
        const input = document.getElementById(id);
        const icon = input.parentElement.querySelector('.toggle-password i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    // 3. Welcome Modal Logic
    <?php if (isset($firstLogin) && $firstLogin && empty($error) && empty($success)): ?>
        const modal = document.getElementById('welcomeModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    <?php endif; ?>
    
    document.querySelector('.welcome-btn')?.addEventListener('click', () => {
        document.getElementById('welcomeModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    });
});

</script>

<?php if (!$firstLogin) { 
    require_once __DIR__ . '/partials/footer.php'; 
    } 
    else { 
        echo '</body></html>'; } 
?>