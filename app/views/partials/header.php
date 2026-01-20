<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BPC TruScan' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container" id="dashboardContainer">
        <?php include_once __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">
            <header class="main-header">
                <div class="header-title">
                    <h2><?= $pageTitle ?? 'Dashboard' ?></h2>
                    <p><?= $pageSubtitle ?? 'Welcome back!' ?></p>
                </div>
                <div class="header-actions">
                    <div id="live-time-date" class="hide-on-mobile">
                        <div id="live-time">--:-- --</div>
                        <div id="live-date">Loading...</div>
                    </div>
                    <div class="header-user-id">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>ID: <?= htmlspecialchars($_SESSION['faculty_id'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </header>