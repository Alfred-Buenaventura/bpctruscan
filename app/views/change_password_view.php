<?php 
if (!$firstLogin) { require_once __DIR__ . '/partials/header.php';  } 
?>

<?php if ($firstLogin): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="card login-card-new" style="max-width: 500px;">
<?php else: ?>
<div class="main-body">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
<?php endif; ?>

        <div class="<?= $firstLogin ? 'login-new-header' : 'card-header' ?>">
            <?php if($firstLogin): ?><div class="login-logo-container"><i class="fa-solid fa-key"></i></div><?php endif; ?>
            <h3>Change Password</h3>
        </div>
        
        <div class="<?= $firstLogin ? 'login-new-body' : 'card-body' ?>">
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" minlength="8" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full-width">Update Password</button>
            </form>
            
            <?php if (!$firstLogin): ?>
                <br><a href="index.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$firstLogin) { include __DIR__ . '/partials/footer.php'; } else { echo '</body></html>'; } ?>