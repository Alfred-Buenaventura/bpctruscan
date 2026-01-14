<?php require_once __DIR__ . '/partials/header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Animations and States */
.scan-step { transition: all 0.3s ease; }
.scan-step.active-step { 
    transform: scale(1.2); 
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.6); 
    background-color: #10b981 !important; 
    color: white !important; 
    border-color: #10b981 !important; 
}
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.05); opacity: 0.7; } }
.pulse { animation: pulse 1s infinite ease-in-out; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Custom Modal Backdrop */
.custom-modal-backdrop {
    display: none; 
    position: fixed; 
    top: 0; left: 0; 
    width: 100%; height: 100%; 
    background: rgba(0,0,0,0.5); 
    z-index: 1050;
}
</style>

<div class="main-body flex items-center justify-center">
  <div class="w-full max-w-2xl bg-white p-8 rounded-2xl shadow-lg border border-gray-200" style="margin: 0 auto;">
    
    <h2 class="text-center text-2xl font-bold text-gray-800 mb-2">Fingerprint Enrollment</h2>
    <p class="text-center text-gray-600 mb-6">
      Target User: <span class="font-semibold text-emerald-700"><?= htmlspecialchars($targetUser['first_name'] . ' ' . $targetUser['last_name']) ?></span>
    </p>

    <div id="successMsg" style="display:none; background-color:#ecfdf5; border:1px solid #6ee7b7; color:#065f46; padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem; text-align:center;"></div>

    <div id="deviceStatusContainer" class="border border-gray-200 bg-gray-50 text-gray-600 py-3 px-4 rounded-lg flex items-center justify-center gap-3 mb-6" style="display:flex; align-items:center; justify-content:center; gap:12px; padding:12px; margin-bottom:1.5rem;">
      <i id="deviceStatusIcon" class="fa fa-spinner fa-spin"></i>
      <span id="deviceStatusText" class="font-medium">Connecting to device...</span>
    </div>

    <div class="flex flex-col items-center text-center border border-emerald-100 rounded-xl p-8 mb-6" style="display:flex; flex-direction:column; align-items:center; border:1px solid #d1fae5; border-radius:12px; padding:2rem; margin-bottom:1.5rem;">
      <div class="w-40 h-40 rounded-full border-4 border-emerald-100 flex items-center justify-center mb-4" style="width:10rem; height:10rem; border-radius:50%; border:4px solid #d1fae5; display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
        <i class="fa fa-fingerprint fa-4x text-emerald-600" id="fingerIcon" style="font-size:4em; color:#059669;"></i>
      </div>
      
      <h3 class="text-xl font-bold text-gray-800 mb-2" id="currentFingerName">---</h3>
      <p class="text-gray-700 font-medium mb-4" id="scanStatus">Waiting for device...</p>
      
      <div class="flex gap-3 mb-6" style="display:flex; gap:0.75rem; margin-bottom:1.5rem;">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <div id="scanStep<?= $i ?>" class="scan-step w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center font-bold text-gray-500" style="width:2rem; height:2rem; border-radius:50%; border:1px solid #d1d5db; display:flex; align-items:center; justify-content:center;">
              <?= $i ?>
          </div>
        <?php endfor; ?>
      </div>

      <button type="button" id="openModalBtn" class="btn btn-primary" disabled>
        <i class="fa fa-plus-circle"></i> Select Finger & Scan
      </button>
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-weight:bold; margin:0;">Registered Fingers</h3>
        </div>

        <ul id="registeredList" style="list-style:none; padding:0;">
            <?php if (!empty($registeredFingers)): ?>
                <?php foreach ($registeredFingers as $fp): ?>
                    <li style="padding:0.5rem; background:#ecfdf5; margin-bottom:5px; border-radius:4px; color:#065f46;">
                        <i class="fa fa-check-circle"></i> <?= htmlspecialchars($fp['finger_name']) ?>
                        <span style="font-size:0.8em; color:#666; float:right;"><?= date('M d, Y', strtotime($fp['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li style="padding:0.5rem; color:#666; font-style:italic;">No fingers registered yet.</li>
            <?php endif; ?>
        </ul>
        
        <div style="margin-top:2rem; text-align:center;">
             <a href="complete_registration.php" class="btn btn-secondary" style="display:inline-block; width:100%;">Return to User List</a>
        </div>
    </div>
  </div>
</div>

<div id="instructionModal" class="custom-modal-backdrop">
    <div class="modal-dialog modal-dialog-centered" style="margin: 10% auto; max-width: 500px; padding: 0;">
        <div class="modal-content" style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 15px 20px; background: #f0fdf4;">
                <h5 class="modal-title" style="font-weight:bold; font-size:1.1rem; margin:0; color: #166534;">
                    <i class="fa-solid fa-info-circle"></i> Enrollment Instructions
                </h5>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="margin-bottom: 10px; font-weight: bold;">Before starting:</p>
                <ol style="margin-left: 20px; color: #444; line-height: 1.6;">
                    <li>Ensure the user's finger is clean and dry.</li>
                    <li>Guide the user to place their finger flat on the sensor.</li>
                    <li>The system will require <strong>3 scans</strong> of the same finger.</li>
                    <li>Please wait for the "Lift Finger" prompt between scans.</li>
                </ol>
            </div>
            <div class="modal-footer" style="padding: 15px 20px; border-top: 1px solid #eee; text-align:right; background: #fff;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('instructionModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="showSelectionModal()">Next <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>

<div id="fingerSelectModal" class="custom-modal-backdrop">
    <div class="modal-dialog modal-dialog-centered" style="margin: 10% auto; max-width: 400px; padding: 0;">
        <div class="modal-content" style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 15px 20px; background: #f8f9fa;">
                <h5 class="modal-title" style="font-weight:bold; font-size:1.1rem; margin:0;">Select Finger to Enroll</h5>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#444;">Choose Finger:</label>
                    <select id="fingerSelector" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        <option value="Right Thumb">Right Thumb</option>
                        <option value="Right Index" selected>Right Index</option>
                        <option value="Right Middle">Right Middle</option>
                        <option value="Right Ring">Right Ring</option>
                        <option value="Right Little">Right Little</option>
                        <option disabled>──────────</option>
                        <option value="Left Thumb">Left Thumb</option>
                        <option value="Left Index">Left Index</option>
                        <option value="Left Middle">Left Middle</option>
                        <option value="Left Ring">Left Ring</option>
                        <option value="Left Little">Left Little</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 20px; border-top: 1px solid #eee; text-align:right; background: #fff;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('fingerSelectModal')">Back</button>
                <button type="button" class="btn btn-primary" onclick="confirmStart()">Proceed & Scan</button>
            </div>
        </div>
    </div>
</div>

<script>
let socket;
let isDeviceConnected = false;
const userId = <?= json_encode($targetUser['id']) ?>; 

// UI References
const openModalBtn = document.getElementById('openModalBtn');
const instructionModal = document.getElementById('instructionModal');
const selectModal = document.getElementById('fingerSelectModal');
const scanStatus = document.getElementById('scanStatus');
const fingerIcon = document.getElementById('fingerIcon');
const currentFingerDisplay = document.getElementById('currentFingerName');
const registeredList = document.getElementById('registeredList');
const deviceStatusText = document.getElementById('deviceStatusText');
const deviceStatusContainer = document.getElementById('deviceStatusContainer');

// --- MODAL FUNCTIONS ---

function openInstructionModal() {
    if (!isDeviceConnected) {
        alert("Cannot start: Device is not connected. Please start the C# Bridge App.");
        return;
    }
    instructionModal.style.display = 'block';
}

function showSelectionModal() {
    instructionModal.style.display = 'none';
    selectModal.style.display = 'block';
}

function closeModal(modalId) {
    const m = document.getElementById(modalId);
    if(m) m.style.display = 'none';
}

function confirmStart() {
    const selectedFinger = document.getElementById('fingerSelector').value;
    currentFingerDisplay.textContent = selectedFinger;
    
    closeModal('fingerSelectModal');
    startEnrollment(selectedFinger);
}

// --- SCANNING LOGIC ---
function resetScanUI() {
    for (let i = 1; i <= 3; i++) {
        const step = document.getElementById(`scanStep${i}`);
        step.classList.remove('active-step');
    }
    fingerIcon.classList.remove('pulse');
    scanStatus.textContent = "Ready to scan...";
    openModalBtn.disabled = false; 
    openModalBtn.innerHTML = '<i class="fa fa-plus-circle"></i> Select Finger & Scan';
}

function startEnrollment(fingerName) {
    if (!socket || socket.readyState !== WebSocket.OPEN) {
        alert("Device disconnected! Check C# Bridge.");
        return;
    }
    
    resetScanUI();
    scanStatus.textContent = `Place ${fingerName} (Scan 1 of 3)...`;
    fingerIcon.classList.add('pulse');
    openModalBtn.disabled = true; 
    
    // Send command to C# Bridge
    socket.send(JSON.stringify({ command: "enroll_start" }));
}

function connectWebSocket() {
    socket = new WebSocket("ws://127.0.0.1:8080");

    socket.onopen = () => {
        isDeviceConnected = true;
        deviceStatusText.textContent = "Device Connected";
        deviceStatusContainer.style.backgroundColor = '#ecfdf5';
        scanStatus.textContent = "Ready to scan...";
        
        // ENABLE BUTTON ON CONNECT
        if(openModalBtn) openModalBtn.disabled = false;
    };

    socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        
        if (data.status === "progress") {
            const stepEl = document.getElementById(`scanStep${data.step}`);
            if(stepEl) stepEl.classList.add('active-step');
            scanStatus.textContent = data.message;
        } 
        else if (data.status === "success") {
            saveToServer(data.template);
        } 
        else if (data.status === "error") {
            scanStatus.textContent = "Error: " + data.message;
            fingerIcon.classList.remove('pulse');
            openModalBtn.disabled = false;
        }
    };

    socket.onclose = () => {
        isDeviceConnected = false;
        deviceStatusText.textContent = "Device Disconnected (Start C# App)";
        deviceStatusContainer.style.backgroundColor = '#fef2f2';
        if(openModalBtn) openModalBtn.disabled = true;
    };
    
    socket.onerror = (err) => {
        console.error("WebSocket Error:", err);
    };
}

async function saveToServer(template) {
    const fingerName = document.getElementById('fingerSelector').value;
    scanStatus.textContent = "Saving to database...";

    try {
        const res = await fetch('api/register_finger.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                position: fingerName,
                template: template
            })
        });
        const result = await res.json();

        if (result.status === "success") {
            scanStatus.textContent = "Enrolled Successfully!";
            fingerIcon.classList.remove('pulse');
            openModalBtn.disabled = false;
            openModalBtn.innerHTML = "Enroll Another Finger";

            // Add to list visually
            const li = document.createElement("li");
            li.style.cssText = "padding:0.5rem; background:#ecfdf5; margin-bottom:5px; border-radius:4px; color:#065f46;";
            li.innerHTML = `<i class="fa fa-check-circle"></i> ${fingerName} <span style="float:right; font-size:0.8em;">Just now</span>`;
            registeredList.prepend(li);
        } else {
            alert("Database Error: " + result.message);
            resetScanUI();
        }
    } catch (err) {
        console.error(err);
        alert("Network Error");
    }
}

document.addEventListener('DOMContentLoaded', () => {
    connectWebSocket();
    if(openModalBtn) openModalBtn.addEventListener('click', openInstructionModal);
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>