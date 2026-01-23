<?php require_once __DIR__ . '/partials/header.php'; ?>

<style>
/* Animations and States */
.scan-step { transition: all 0.3s ease; }
.scan-step.active-step { 
    transform: scale(1.15); 
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); 
    background-color: #10b981 !important; 
    color: white !important; 
    border-color: #10b981 !important; 
}
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.05); opacity: 0.7; } }
.pulse { animation: pulse 1.5s infinite ease-in-out; }

/* Professional Modal Styles */
.custom-reg-modal {
    display: none; 
    position: fixed; 
    top: 0; left: 0; 
    width: 100%; height: 100%; 
    background: rgba(15, 23, 42, 0.7); 
    z-index: 2000;
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
}

.reg-modal-content {
    background: white;
    padding: 2.5rem;
    border-radius: 20px;
    width: 90%;
    max-width: 480px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    border: 1px solid #f1f5f9;
}

.reg-modal-header { text-align: center; margin-bottom: 1.5rem; }
.reg-modal-header i { font-size: 3rem; color: #10b981; margin-bottom: 1rem; }
.reg-modal-header h3 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0; }

.reg-step-list { text-align: left; margin: 1.5rem 0; color: #475569; }
.reg-step-item { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding: 10px; background: #f8fafc; border-radius: 10px; }
.reg-step-num { background: #10b981; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; flex-shrink: 0; }

.finger-select-group { margin-bottom: 2rem; }
.finger-select-group label { display: block; font-weight: 700; color: #334155; margin-bottom: 10px; }
</style>

<div class="main-body" style="padding-top: 2rem; padding-bottom: 2rem;">
  <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100" style="width: 100%; max-width: 600px; margin: 0 auto; padding: 2.5rem;">
    
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Biometric Enrollment</h2>
        <div style="background: #f0fdf4; display: inline-block; padding: 5px 15px; border-radius: 50px; border: 1px solid #bbf7d0;">
            <p class="text-emerald-700 font-bold" style="font-size: 0.9rem;">
                <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($targetUser['first_name'] . ' ' . $targetUser['last_name']) ?>
            </p>
        </div>
        <p class="text-gray-500 mt-2" style="font-size: 0.8rem;">Faculty ID: <?= htmlspecialchars($targetUser['faculty_id']) ?></p>
    </div>

    <div id="deviceStatusContainer" class="border border-gray-200 bg-gray-50 text-gray-600 py-3 px-4 rounded-lg flex items-center justify-center gap-3 mb-6" style="display:flex; align-items:center; justify-content:center; gap:12px; padding:12px; margin-bottom:1.5rem;">
      <i id="deviceStatusIcon" class="fa fa-spinner fa-spin"></i>
      <span id="deviceStatusText" class="font-medium">Initializing scanner...</span>
    </div>

    <div class="flex flex-col items-center text-center border border-emerald-50 rounded-xl p-8 mb-6" style="display:flex; flex-direction:column; align-items:center; border:1px solid #d1fae5; border-radius:15px; padding:2rem; margin-bottom:1.5rem; background: #fafafa;">
      <div class="w-40 h-40 rounded-full border-4 border-emerald-100 flex items-center justify-center mb-4" style="width:10rem; height:10rem; border-radius:50%; border:4px solid #d1fae5; display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem; background: white; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);">
        <i class="fa fa-fingerprint fa-4x text-emerald-600" id="fingerIcon" style="font-size:4.5rem; color:#10b981;"></i>
      </div>
      
      <h3 class="text-xl font-bold text-gray-800 mb-2" id="currentFingerName">---</h3>
      <p class="text-gray-700 font-medium mb-4" id="scanStatus">Hardware check in progress...</p>
      
      <div class="flex gap-4 mb-8" style="display:flex; gap:1rem; margin-bottom:2rem;">
        <?php for ($i = 1; $i <= 3; $i++): ?>
          <div id="scanStep<?= $i ?>" class="scan-step w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center font-bold text-gray-400" style="width:2.5rem; height:2.5rem; border-radius:50%; display:flex; align-items:center; justify-content:center; background: white;">
              <?= $i ?>
          </div>
        <?php endfor; ?>
      </div>

      <button type="button" id="openModalBtn" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px; font-weight: 700;" disabled>
        <i class="fa-solid fa-plus-circle"></i> New Registration
      </button>
    </div>

    <div style="border-top:2px solid #f3f4f6; padding-top:1.5rem;">
        <h3 style="font-weight:bold; color: #1e293b; margin-bottom:1rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em;">Current Enrollments</h3>
        <ul id="registeredList" style="list-style:none; padding:0;">
            <?php if (!empty($registeredFingers)): ?>
                <?php foreach ($registeredFingers as $fp): ?>
                    <li style="padding:0.75rem; background:#f0fdf4; margin-bottom:8px; border-radius:8px; color:#166534; border: 1px solid #dcfce7; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600;"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($fp['finger_name']) ?></span>
                        <span style="font-size:0.7rem; font-weight: 700; color:#86efac;"><?= date('M d, Y', strtotime($fp['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li style="padding:1rem; color:#94a3b8; font-style:italic; text-align: center; background: #f8fafc; border-radius: 8px;">No fingers registered yet.</li>
            <?php endif; ?>
        </ul>
        <div style="margin-top:2rem;">
             <a href="complete_registration.php" class="btn btn-secondary" style="display:block; width:100%; text-align: center; border-radius: 12px; padding: 12px;">
                <i class="fa-solid fa-arrow-left"></i> Exit Enrollment
             </a>
        </div>
    </div>
  </div>
</div>

<div id="instructionModal" class="custom-reg-modal">
    <div class="reg-modal-content">
        <div class="reg-modal-header">
            <i class="fa-solid fa-circle-info"></i>
            <h3>Registration Prep</h3>
        </div>
        <div class="reg-step-list">
            <div class="reg-step-item">
                <div class="reg-step-num">1</div>
                <span>Select the specific finger to enroll.</span>
            </div>
            <div class="reg-step-item">
                <div class="reg-step-num">2</div>
                <span>The scanner will prompt for 3 captures.</span>
            </div>
            <div class="reg-step-item">
                <div class="reg-step-num">3</div>
                <span>Hold steady until the light turns green.</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary" onclick="showSelectionModal()" style="width:100%; padding: 14px; border-radius: 12px; font-weight: 700;">Proceed to Selection</button>
    </div>
</div>

<div id="fingerSelectModal" class="custom-reg-modal">
    <div class="reg-modal-content">
        <div class="reg-modal-header">
            <i class="fa-solid fa-hand-pointer"></i>
            <h3>Target Finger</h3>
        </div>
        <div class="finger-select-group">
            <label>Select hand and finger:</label>
            <select id="fingerSelector" class="form-control" style="width:100%; padding:14px; border-radius:12px; background: #f8fafc; border: 2px solid #e2e8f0;">
                <option value="Right Index">Right Index Finger</option>
                <option value="Right Thumb">Right Thumb Finger</option>
                <option value="Right Middle">Right Middle Finger</option>
                <option value="Right Middle">Right Pinky Finger</option>
                <option value="Right Middle">Right Ring Finger</option>
                <option value="Left Index">Left Index Finger</option>
                <option value="Left Thumb">Left Thumb Finger</option>
                <option value="Right Middle">Left Middle Finger</option>
                <option value="Right Middle">Left Pinky Finger</option>
                <option value="Right Middle">Left Ring Finger</option>
                
            </select>
        </div>
        <div style="display:flex; gap:12px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('fingerSelectModal')" style="flex:1; border-radius:12px;">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="confirmStart()" style="flex:2; border-radius:12px; font-weight: 700;">Begin Scanning</button>
        </div>
    </div>
</div>

<script>
let socket;
let isDeviceConnected = false;
const userId = <?= json_encode($targetUser['id']) ?>; 

const openModalBtn = document.getElementById('openModalBtn');
const instructionModal = document.getElementById('instructionModal');
const selectModal = document.getElementById('fingerSelectModal');
const scanStatus = document.getElementById('scanStatus');
const fingerIcon = document.getElementById('fingerIcon');
const currentFingerDisplay = document.getElementById('currentFingerName');
const deviceStatusText = document.getElementById('deviceStatusText');
const deviceStatusContainer = document.getElementById('deviceStatusContainer');

function openInstructionModal() {
    if (!isDeviceConnected) { alert("Cannot start: Biometric scanner is not connected."); return; }
    instructionModal.style.display = 'flex';
}
function showSelectionModal() { instructionModal.style.display = 'none'; selectModal.style.display = 'flex'; }
function closeModal(modalId) { const m = document.getElementById(modalId); if(m) m.style.display = 'none'; }
function confirmStart() {
    const selectedFinger = document.getElementById('fingerSelector').value;
    currentFingerDisplay.textContent = selectedFinger;
    closeModal('fingerSelectModal');
    startEnrollment(selectedFinger);
}
function resetScanUI() {
    for (let i = 1; i <= 3; i++) { document.getElementById(`scanStep${i}`).classList.remove('active-step'); }
    fingerIcon.classList.remove('pulse');
    openModalBtn.disabled = false;
}
function startEnrollment(fingerName) {
    if (!socket || socket.readyState !== WebSocket.OPEN) { alert("Communication lost with hardware."); return; }
    resetScanUI();
    scanStatus.textContent = `Place ${fingerName} (Scan 1 of 3)...`;
    fingerIcon.classList.add('pulse');
    openModalBtn.disabled = true; 
    socket.send(JSON.stringify({ command: "enroll_start" }));
}
function connectWebSocket() {
    socket = new WebSocket("ws://127.0.0.1:8080");
    socket.onopen = () => { 
        isDeviceConnected = true; 
        deviceStatusText.textContent = "Hardware Ready"; 
        deviceStatusContainer.style.backgroundColor = '#f0fdf4'; 
        deviceStatusContainer.style.color = '#15803d';
        if(openModalBtn) openModalBtn.disabled = false; 
    };
    socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        if (data.status === "progress") { 
            document.getElementById(`scanStep${data.step}`).classList.add('active-step'); 
            scanStatus.textContent = data.message; 
        }
        else if (data.status === "success") { 
            saveToServer(data.template); 
        }
    };
    socket.onclose = () => { 
        isDeviceConnected = false; 
        deviceStatusText.textContent = "Hardware Disconnected"; 
        deviceStatusContainer.style.backgroundColor = '#fef2f2'; 
        deviceStatusContainer.style.color = '#b91c1c';
        if(openModalBtn) openModalBtn.disabled = true; 
    };
}
async function saveToServer(template) {
    const fingerName = document.getElementById('fingerSelector').value;
    try {
        const res = await fetch('api/register_finger.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ user_id: userId, position: fingerName, template: template }) });
        const result = await res.json();
        if (result.status === "success") { 
            scanStatus.textContent = "Template Registered!"; 
            fingerIcon.classList.remove('pulse');
            setTimeout(() => location.reload(), 1500);
        }
    } catch (err) { console.error(err); }
}
document.addEventListener('DOMContentLoaded', () => { connectWebSocket(); if(openModalBtn) openModalBtn.addEventListener('click', openInstructionModal); });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>