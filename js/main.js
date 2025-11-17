document.addEventListener('DOMContentLoaded', function() {
    
    /* Toggle logic for the sidebar */
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const dashboardContainer = document.getElementById('dashboardContainer');

    if (sidebarToggle && sidebar && dashboardContainer) {
        const setSidebarState = (isCollapsed) => {
            sidebar.classList.toggle('collapsed', isCollapsed);
            dashboardContainer.classList.toggle('sidebar-collapsed', isCollapsed);
            localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
        };

        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        setSidebarState(isCollapsed);

        sidebarToggle.addEventListener('click', () => {
            const wasCollapsed = sidebar.classList.contains('collapsed');
            setSidebarState(!wasCollapsed);
        });
    }

    /* Toggle for the user settings menu */
    const userSettingsBtn = document.getElementById('userSettingsBtn');
    const settingsMenu = document.getElementById('settings-menu');

    if (userSettingsBtn && settingsMenu) {
        userSettingsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            settingsMenu.classList.toggle('active');
        });
    }

    /* Close settings menu if clicked outside */
    document.addEventListener('click', (e) => {
        if (settingsMenu && settingsMenu.classList.contains('active') && 
            !settingsMenu.contains(e.target) && !userSettingsBtn.contains(e.target)) {
            settingsMenu.classList.remove('active');
        }
    });

    /* Global Logout Function */
    window.showLogoutConfirm = function() {
        openModal('logoutConfirmModal');
    };

    /* Date and Time Display */
    const liveTimeEl = document.getElementById('live-time');
    const liveDateEl = document.getElementById('live-date');
    function updateTime() {
        if (liveTimeEl && liveDateEl) {
            const now = new Date();
            liveTimeEl.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            liveDateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        }
    }
    updateTime();
    setInterval(updateTime, 30000);

    /* Scanner Status Widget Logic */
    const scannerStatusWidget = document.getElementById('scanner-status-widget');
    if (scannerStatusWidget) {
        const statusText = scannerStatusWidget.querySelector('.scanner-status-text-sub');
        const statusBadge = scannerStatusWidget.querySelector('.scanner-status-badge');
        const statusAction = scannerStatusWidget.querySelector('.scanner-status-action');
        const iconBadge = scannerStatusWidget.querySelector('.scanner-icon-badge');

        const setScannerStatus = (isConnected, message) => {
            if (isConnected) {
                scannerStatusWidget.classList.add('online');
                scannerStatusWidget.classList.remove('offline');
                statusText.textContent = 'Device Connected';
                statusBadge.textContent = 'ONLINE';
                statusAction.textContent = 'Ready to scan';
                if(iconBadge) iconBadge.style.display = 'none';
            } else {
                scannerStatusWidget.classList.remove('online');
                scannerStatusWidget.classList.add('offline');
                statusText.textContent = message;
                statusBadge.textContent = 'OFFLINE';
                statusAction.textContent = 'Check connection';
                if(iconBadge) iconBadge.style.display = 'flex';
            }
        };

        function connectScannerSocket() {
            try {
                const socket = new WebSocket("ws://127.0.0.1:8080");
                socket.onopen = () => setScannerStatus(true, "Device Connected");
                socket.onclose = () => {
                    setScannerStatus(false, "Device Not Detected");
                    setTimeout(connectScannerSocket, 5000);
                };
                socket.onerror = () => {
                    setScannerStatus(false, "Connection Error");
                    socket.close();
                };
            } catch (err) {
                setScannerStatus(false, "Service Not Running");
                setTimeout(connectScannerSocket, 5000);
            }
        }
        connectScannerSocket();
    }
});

/* --- GLOBAL MODAL FUNCTIONS --- */
/* These need to be outside DOMContentLoaded to be accessible by inline onclick events */

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    } else {
        console.error("Modal not found: " + modalId);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
}

// Close modal if clicking outside content
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};