<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>BPC Attendance</title>
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #scanner-status-widget {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid transparent;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            background: #f8fafc;
            border-radius: 12px;
        }
        #scanner-status-widget:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .scanner-status-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-width: 90px;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
        }

        #scanner-status-widget.online { background-color: #f0fdf4; border-color: #bbf7d0; }
        #scanner-status-widget.online .scanner-status-badge {
            background-color: #10b981;
            color: #ffffff;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
        }
        
        #scanner-status-widget.online .device-status-icon { 
            color: #10b981; 
        }

        #scanner-status-widget.offline { 
            background-color: #fef2f2; border-color: #fecaca; 
        }

        #scanner-status-widget.offline .scanner-status-badge {
            background-color: #ef4444;
            color: #ffffff;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
        }

        #scanner-status-widget.offline .device-status-icon { 
            color: #ef4444; 
        }

        .scanner-status-text-main { 
            font-weight: 800; font-size: 0.8rem; color: #1e293b; 
        }

        .scanner-status-text-sub { 
            font-size: 0.7rem; color: #64748b; font-weight: 600; 
        }
    </style>
</head>
<body>
    <div class="dashboard-container" id="dashboardContainer">
        
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="main-content" id="mainContent">
            <header class="main-header">
                <button type="button" class="btn-mobile-toggle" id="mobileMenuBtn">
                    <i class="fa-solid fa-bars"></i>
                </button>

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

                    <?php if (Helper::isAdmin() && isset($pageTitle) && $pageTitle === 'Dashboard'): ?>
                    <div class="header-scanner-status offline hide-on-mobile" 
                         id="scanner-status-widget"
                         onclick="openModal('scannerDetailModal')">
                        <div class="device-icon-container">
                            <i class="fa-brands fa-usb device-status-icon" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="scanner-status-text">
                            <div class="scanner-status-text-main">Fingerprint Scanner</div>
                            <div class="scanner-status-text-sub" id="scanner-status-msg">Initializing...</div>
                        </div>
                        <div class="scanner-status-details">
                            <div class="scanner-status-badge" id="scanner-status-label">OFFLINE</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </header>

            <div id="scannerDetailModal" class="modal">
                <div class="modal-content" style="max-width: 400px; text-align: center;">
                    <div class="modal-body" style="padding: 2rem;">
                        <i class="fa-solid fa-microchip" id="modal-scanner-icon" style="font-size: 3.5rem; margin-bottom: 1.5rem;"></i>
                        <h3 id="modal-scanner-title" style="margin-bottom: 0.5rem; font-weight: 800;">Scanner Status</h3>
                        <p id="modal-scanner-desc" style="color: #64748b; margin-bottom: 1.5rem; font-size: 0.9rem;">Checking connection...</p>
                        <button class="btn btn-primary btn-full-width" onclick="closeModal('scannerDetailModal')">Close</button>
                    </div>
                </div>
            </div>