/* =========================================
   1. GLOBAL MODAL FUNCTIONS
   ========================================= */

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

window.showLogoutConfirm = function() {
    const modal = document.getElementById('logoutConfirmModal');
    if (modal) {
        openModal('logoutConfirmModal');
    } else if (confirm("Are you sure you want to log out?")) {
        window.location.href = 'logout.php';
    }
};

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};

/* =========================================
   2. INTERACTIVE ELEMENTS (SIDEBAR & MENUS)
   ========================================= */

document.addEventListener('click', function(e) {
    
    // --- A. SETTINGS MENU TOGGLE ---
    const settingsBtn = e.target.closest('#userSettingsBtn');
    const settingsMenu = document.getElementById('settings-menu');
    if (settingsBtn && settingsMenu) {
        e.preventDefault();
        e.stopPropagation();
        settingsMenu.classList.toggle('active');
        return;
    }

    // --- B. CLOSE SETTINGS MENU (WHEN CLICKING OUTSIDE) ---
    if (settingsMenu && settingsMenu.classList.contains('active')) {
        if (!e.target.closest('#settings-menu') && !e.target.closest('#userSettingsBtn')) {
            settingsMenu.classList.remove('active');
        }
    }

    // --- C. SIDEBAR TOGGLE ---
    const sidebarToggle = e.target.closest('#sidebarToggle');
    if (sidebarToggle) {
        const sidebar = document.getElementById('sidebar');
        const dashboardContainer = document.getElementById('dashboardContainer');
        
        if (sidebar && dashboardContainer) {
            if (window.innerWidth <= 768) {
                dashboardContainer.classList.toggle('sidebar-mobile-open');
            } else {
                const isCurrentlyCollapsed = dashboardContainer.classList.toggle('sidebar-collapsed');
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', isCurrentlyCollapsed ? 'true' : 'false');
                setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 300);
            }
        }
    }

    // --- D. CLOSE SIDEBAR ON MOBILE WHEN CLICKING OUTSIDE ---
    if (window.innerWidth <= 768) {
        const dashboardContainer = document.getElementById('dashboardContainer');
        const sidebar = document.getElementById('sidebar');
        
        if (dashboardContainer && sidebar && 
            dashboardContainer.classList.contains('sidebar-mobile-open') && 
            !sidebar.contains(e.target) && 
            !e.target.closest('#sidebarToggle') &&
            !e.target.closest('#mobileMenuBtn')) {
            dashboardContainer.classList.remove('sidebar-mobile-open');
        }
    }
});

/* =========================================
   3. PAGE INITIALIZATION
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    const sidebar = document.getElementById('sidebar');
    const dashboardContainer = document.getElementById('dashboardContainer');
    const mobileBtn = document.getElementById('mobileMenuBtn');

    // Restore Sidebar State
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && sidebar && dashboardContainer && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        dashboardContainer.classList.add('sidebar-collapsed');
    }

    // Mobile Menu Button
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dashboardContainer) dashboardContainer.classList.toggle('sidebar-mobile-open');
        });
    }

    // Live Clock
    const liveTimeEl = document.getElementById('live-time');
    const liveDateEl = document.getElementById('live-date');
    if (liveTimeEl && liveDateEl) {
        const updateTime = () => {
            const now = new Date();
            liveTimeEl.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            liveDateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        };
        updateTime();
        setInterval(updateTime, 30000);
    }

    // Scanner Status Widget
    const scannerWidget = document.getElementById('scanner-status-widget');
    if (scannerWidget) { initScannerSocket(scannerWidget); }
});

/* =========================================
   4. SCANNER WEBSOCKET LOGIC
   ========================================= */

function initScannerSocket(widget) {
    const statusText = widget.querySelector('.scanner-status-text-sub');
    const statusBadge = widget.querySelector('.scanner-status-badge');
    
    function setStatus(online, msg) {
    const widget = document.getElementById('scanner-status-widget');
    const statusMsg = document.getElementById('scanner-status-msg');
    const statusLabel = document.getElementById('scanner-status-label');
    const modalIcon = document.getElementById('modal-scanner-icon');
    const modalDesc = document.getElementById('modal-scanner-desc');

    if (online) {
        // Swap Classes
        widget.classList.remove('offline');
        widget.classList.add('online');
        
        // Update Header UI
        if (statusLabel) statusLabel.textContent = 'ONLINE';
        if (statusMsg) statusMsg.textContent = 'Hardware Ready';
        
        // Update Modal UI
        if (modalIcon) modalIcon.style.color = '#10b981';
        if (modalDesc) modalDesc.textContent = "The biometric scanner device is connected and ready for fingerprint processing. Ensure device is clean for proper use.";
    } else {
        // Swap Classes
        widget.classList.remove('online');
        widget.classList.add('offline');
        
        // Update Header UI
        if (statusLabel) statusLabel.textContent = 'OFFLINE';
        if (statusMsg) statusMsg.textContent = msg || 'Check connection';
        
        // Update Modal UI
        if (modalIcon) modalIcon.style.color = '#ef4444';
        if (modalDesc) modalDesc.textContent = "The scanner service is currently unreachable. Ensure device is properly connected before use.";
    }
}

    try {
        const socket = new WebSocket("ws://127.0.0.1:8080");
        socket.onopen = () => setStatus(true);
        socket.onclose = () => { setStatus(false, "Device Not Detected"); setTimeout(() => initScannerSocket(widget), 5000); };
        socket.onerror = () => { setStatus(false, "Connection Error"); socket.close(); };
    } catch (e) {
        setStatus(false, "Service Error");
    }
}