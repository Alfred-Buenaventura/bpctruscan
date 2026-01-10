/* =========================================
   1. GLOBAL MODAL FUNCTIONS
   ========================================= */

// Function to open a modal by ID
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    } else {
        console.error("Modal not found: " + modalId);
    }
}

// Function to close a modal by ID
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
}

// Global Logout Confirmation Function
// This is called by the "Log out" button in the sidebar/footer
window.showLogoutConfirm = function() {
    const modal = document.getElementById('logoutConfirmModal');
    if (modal) {
        openModal('logoutConfirmModal');
    } else {
        // Fallback: If modal is missing, use standard browser confirm
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = 'logout.php';
        }
    }
};

// Close any modal if the user clicks on the dark overlay (outside the modal content)
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};


/* =========================================
   UPDATED SIDEBAR TOGGLE LOGIC
   Replace the sidebar toggle section in js/main.js
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

    // --- C. SIDEBAR TOGGLE (Enhanced for Desktop & Mobile) ---
    const sidebarToggle = e.target.closest('#sidebarToggle');
    if (sidebarToggle) {
        const sidebar = document.getElementById('sidebar');
        const dashboardContainer = document.getElementById('dashboardContainer');
        
        if (sidebar && dashboardContainer) {
            // Check if we are on mobile (screen < 768px)
            if (window.innerWidth <= 768) {
                // Toggle mobile-specific class
                dashboardContainer.classList.toggle('sidebar-mobile-open');
            } else {
                // Standard desktop collapse
                const isCurrentlyCollapsed = dashboardContainer.classList.contains('sidebar-collapsed');
                
                // Toggle the collapsed state
                dashboardContainer.classList.toggle('sidebar-collapsed');
                
                // Save state to localStorage
                const newState = !isCurrentlyCollapsed;
                localStorage.setItem('sidebarCollapsed', newState ? 'true' : 'false');
                
                // Force a small delay to ensure CSS transitions complete
                setTimeout(() => {
                    // Trigger window resize event to help any charts/tables adjust
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            }
        }
    }

    // --- D. CLOSE SIDEBAR ON MOBILE WHEN CLICKING OUTSIDE ---
    if (window.innerWidth <= 768) {
        const dashboardContainer = document.getElementById('dashboardContainer');
        const sidebar = document.getElementById('sidebar');
        
        if (dashboardContainer.classList.contains('sidebar-mobile-open') && 
            !sidebar.contains(e.target) && 
            !e.target.closest('#sidebarToggle') &&
            !e.target.closest('#mobileMenuBtn')) {
            
            dashboardContainer.classList.remove('sidebar-mobile-open');
        }
    }
});

// --- MOBILE MENU BUTTON LOGIC ---
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const dbContainer = document.getElementById('dashboardContainer');
            dbContainer.classList.toggle('sidebar-mobile-open');
        });
    }

    // --- RESTORE SIDEBAR STATE ON PAGE LOAD ---
    try {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const sidebar = document.getElementById('sidebar');
        const dashboardContainer = document.getElementById('dashboardContainer');
        
        // Only apply saved state on desktop
        if (isCollapsed && sidebar && dashboardContainer && window.innerWidth > 768) {
            dashboardContainer.classList.add('sidebar-collapsed');
        }
    } catch (err) {
        console.error("Sidebar restore error:", err);
    }
});

// --- HANDLE WINDOW RESIZE ---
window.addEventListener('resize', function() {
    const dashboardContainer = document.getElementById('dashboardContainer');
    
    // If resizing to desktop, remove mobile class
    if (window.innerWidth > 768) {
        dashboardContainer.classList.remove('sidebar-mobile-open');
    }
    
    // If resizing to mobile, remove collapsed class
    if (window.innerWidth <= 768) {
        dashboardContainer.classList.remove('sidebar-collapsed');
    }
});
// --- MOBILE MENU BUTTON LOGIC ---
    const mobileBtn = document.getElementById('mobileMenuBtn');
    
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Stop click from bubbling to document
            const dbContainer = document.getElementById('dashboardContainer');
            dbContainer.classList.toggle('sidebar-mobile-open');
        });
    }

    // --- ROBUST MOBILE SIDEBAR HANDLER ---
document.addEventListener('click', function(e) {
    const dbContainer = document.getElementById('dashboardContainer');
    const sidebar = document.getElementById('sidebar');
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const sidebarToggle = document.getElementById('sidebarToggle');

    // 1. If clicking the Mobile Toggle Button
    if (mobileBtn && mobileBtn.contains(e.target)) {
        e.stopPropagation();
        dbContainer.classList.toggle('sidebar-mobile-open');
        return;
    }

    // 2. If Sidebar is OPEN and we click OUTSIDE sidebar and OUTSIDE toggle buttons
    if (dbContainer.classList.contains('sidebar-mobile-open')) {
        // If click is NOT inside sidebar
        if (sidebar && !sidebar.contains(e.target)) {
            // Close the sidebar
            dbContainer.classList.remove('sidebar-mobile-open');
        }
    }
});

// Reset sidebar state on window resize to prevent layout bugs
window.addEventListener('resize', function() {
    const dbContainer = document.getElementById('dashboardContainer');
    // If we switch to desktop view (> 1024px), clean up mobile classes
    if (window.innerWidth > 1024) {
        dbContainer.classList.remove('sidebar-mobile-open');
    }
});


/* =========================================
   3. PAGE INITIALIZATION
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- A. RESTORE SIDEBAR STATE ---
    try {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const sidebar = document.getElementById('sidebar');
        const dashboardContainer = document.getElementById('dashboardContainer');
        
        if (isCollapsed && sidebar && dashboardContainer) {
            sidebar.classList.add('collapsed');
            dashboardContainer.classList.add('sidebar-collapsed');
        }
    } catch (err) {
        console.error("Sidebar restore error:", err);
    }

    // --- B. LIVE CLOCK (HEADER) ---
    try {
        const liveTimeEl = document.getElementById('live-time');
        const liveDateEl = document.getElementById('live-date');
        
        if (liveTimeEl && liveDateEl) {
            const updateTime = () => {
                const now = new Date();
                // 12-hour format with AM/PM
                liveTimeEl.textContent = now.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit', 
                    hour12: true 
                });
                // Full Date format
                liveDateEl.textContent = now.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    month: 'long', 
                    day: 'numeric' 
                });
            };
            
            updateTime(); // Run immediately
            setInterval(updateTime, 30000); // Update every 30 seconds
        }
    } catch (err) {
        console.error("Clock init error:", err);
    }

    // --- C. FINGERPRINT SCANNER STATUS WIDGET ---
    // Wrapped in try-catch so it doesn't break the rest of the page if missing
    try {
        const scannerWidget = document.getElementById('scanner-status-widget');
        if (scannerWidget) {
            initScannerSocket(scannerWidget);
        }
    } catch (err) {
        // Scanner widget might not exist on all pages (e.g., user dashboard), which is fine.
        // console.log("Scanner widget not active.");
    }
});


/* =========================================
   4. SCANNER WEBSOCKET LOGIC
   ========================================= */

function initScannerSocket(widget) {
    const statusText = widget.querySelector('.scanner-status-text-sub');
    const statusBadge = widget.querySelector('.scanner-status-badge');
    const statusAction = widget.querySelector('.scanner-status-action');
    const iconBadge = widget.querySelector('.scanner-icon-badge');
    
    function setStatus(online, msg) {
        if (online) {
            // Device is Connected
            widget.classList.add('online');
            widget.classList.remove('offline');
            if (statusText) statusText.textContent = 'Device Connected';
            if (statusBadge) statusBadge.textContent = 'ONLINE';
            if (statusAction) statusAction.textContent = 'Ready to scan';
            if (iconBadge) iconBadge.style.display = 'none';
        } else {
            // Device is Offline / Error
            widget.classList.remove('online');
            widget.classList.add('offline');
            if (statusText) statusText.textContent = msg || 'Check connection';
            if (statusBadge) statusBadge.textContent = 'OFFLINE';
            if (statusAction) statusAction.textContent = 'Check connection';
            if (iconBadge) iconBadge.style.display = 'flex';
        }
    }

    // Attempt to connect to the local WebSocket Bridge (C# App)
    try {
        const socket = new WebSocket("ws://127.0.0.1:8080");
        
        socket.onopen = () => {
            setStatus(true);
        };
        
        socket.onclose = () => {
            setStatus(false, "Device Not Detected");
            // Try to reconnect after 5 seconds
            setTimeout(() => initScannerSocket(widget), 5000);
        };
        
        socket.onerror = () => {
            setStatus(false, "Connection Error");
            socket.close();
        };

    } catch (e) {
        setStatus(false, "Service Error");
        // Retry logic is handled in onclose
    }
}