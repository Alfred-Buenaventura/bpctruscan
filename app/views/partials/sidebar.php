<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <div class="sidebar-title">
                <h1>BPC Attendance</h1>
                <p><?= Helper::isAdmin() ? 'Admin Panel' : 'Staff Dashboard' ?></p>
            </div>
        </div>
        <button class="btn sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item">
            <i class="fa-solid fa-house nav-icon"></i>
            <span class="nav-text">Home</span>
        </a>
        
        <?php if (Helper::isAdmin()): ?>
        <a href="create_account.php" class="nav-item">
            <i class="fa-solid fa-user-plus nav-icon"></i>
            <span class="nav-text">Create Account</span>
        </a>
        <a href="complete_registration.php" class="nav-item">
             <i class="fa-solid fa-fingerprint nav-icon"></i>
            <span class="nav-text">Complete Registration</span>
        </a>
        <?php endif; ?>
        
        <a href="attendance_reports.php" class="nav-item">
            <i class="fa-solid fa-clipboard-list nav-icon"></i>
            <span class="nav-text">Attendance Reports</span>
        </a>
        <a href="schedule_management.php" class="nav-item">
            <i class="fa-solid fa-calendar-days nav-icon"></i>
            <span class="nav-text">Schedule Management</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div id="settings-menu">
            <?php if (Helper::isAdmin()): ?>
            <a href="create_admin.php" class="settings-menu-item">
                <i class="fa-solid fa-user-shield"></i>
                <span>Create Admin</span>
            </a>
            <?php endif; ?>
            <a href="profile.php" class="settings-menu-item">
                <i class="fa-solid fa-user"></i>
                <span>My Profile</span>
            </a>
            <a href="about.php" class="settings-menu-item">
                <i class="fa-solid fa-circle-info"></i>
                <span>About Us</span>
            </a>
            <a href="contact.php" class="settings-menu-item">
                <i class="fa-solid fa-envelope"></i>
                <span>Contact Support</span>
            </a>
        </div>
        
        <div class="user-info">
            <div class="user-info-inner">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['first_name'] ?? 'A', 0, 1) . substr($_SESSION['last_name'] ?? 'D', 0, 1)) ?>
                </div>
                <div class="user-details">
                    <p>Logged in as</p>
                    <div class="user-name"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></div>
                    <div class="user-id">ID: <?= htmlspecialchars($_SESSION['faculty_id'] ?? 'N/A') ?></div>
                </div>

                <button class="btn user-settings-btn" id="userSettingsBtn" title="Settings">
                    <i class="fa-solid fa-gear"></i>
                </button>
            </div>
        </div>

        <button class="btn logout-btn" onclick="showLogoutConfirm()">
             <i class="fa-solid fa-right-from-bracket logout-icon"></i>
            <span class="logout-text">Log out</span>
        </button>
    </div>

    <div id="logoutConfirmModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3><i class="fa-solid fa-arrow-right-from-bracket"></i> Confirm Logout</h3>
                <button type="button" class="modal-close" onclick="closeModal('logoutConfirmModal')">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 1rem; color: var(--gray-700);">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('logoutConfirmModal')">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='logout.php'">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
                </button>
            </div>
        </div>
    </div>
</aside>