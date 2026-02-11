<?php require_once __DIR__ . '/partials/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
.info-card-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.info-guide-wrapper {
    margin-bottom: 2.5rem;
    padding: 0 5px;
}

.info-guide-content {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
</style>


<div class="main-body">
    <div class="info-card-header">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Profile Management</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">
                Manage your personal information, contact details, and system notification preferences.
            </p>
        </div>
    </div>

    <div class="info-guide-wrapper">
        <div class="info-guide-content">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Security Tip:</span> 
                    Keep your contact email and phone number updated to ensure you receive 
                    <span style="color: #6366f1; font-weight: 600;">Real-Time Attendance Alerts</span> 
                    and system notifications regarding your duty logs.
                </p>
            </div>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start;">
        
        <div class="card" style="border-top: 6px solid #10b981; overflow: hidden;">
            <div class="card-body" style="text-align: center; padding: 40px 25px;">
                <div class="profile-pic-container" style="position: relative; width: 150px; height: 150px; margin: 0 auto 25px;">
                    <div style="width: 150px; height: 150px; background: #f1f5f9; border-radius: 50%; overflow: hidden; border: 5px solid #fff; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; justify-content: center; font-size: 58px; color: #1e293b; font-weight: 800;">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= $user['profile_image'] ?>?v=<?= time() ?>" id="currentAvatar" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="openPhotoModal()" class="btn-avatar-edit" style="position: absolute; bottom: 8px; right: 8px; background: #6366f1; color: white; border: 4px solid white; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.3s;">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>

                <h3 style="margin-bottom: 8px; font-size: 1.4rem; font-weight: 800; color: #1e293b; letter-spacing: -0.025em;">
                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                </h3>
                
                <div style="display: inline-block; background: #f1f5f9; padding: 6px 16px; border-radius: 50px; margin-bottom: 20px;">
                    <span style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 5px;">Role:</span>
                    <span style="color: #1e293b; font-size: 0.85rem; font-weight: 700;"><?= htmlspecialchars($user['role']) ?></span>
                </div>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 20px; margin-top: 10px;">
                    <span style="display: block; color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Faculty Identification</span>
                    <span style="font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; color: #6366f1; font-weight: 700;">
                        <?= htmlspecialchars($user['faculty_id']) ?>
                    </span>
                </div>

                <div style="margin-top: 30px;">
                    <a href="change_password.php" class="btn btn-secondary btn-block" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-weight: 600; padding: 12px;">
                        <i class="fa-solid fa-key" style="margin-right: 8px; font-size: 0.85rem;"></i> Change Password
                    </a>
                </div>
            </div>
        </div>

        <div class="card" style="border-top: 6px solid #6366f1;">
            <div class="card-header" style="background: white; padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9;">
                <div>
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b !important;">
                Account Information
            </h3>
            <p style="margin: 3px 0 0; font-size: 0.85rem; color: #64748b !important;">
                Manage your personal and contact details
            </p>
        </div>
                <button type="button" id="editProfileBtn" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700;">
                    <i class="fa-solid fa-pen-to-square" style="margin-right: 8px;"></i> Edit Details
                </button>
            </div>
            
            <div class="card-body" style="padding: 35px 30px;">
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px;">
                        <div class="form-group">
                            <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">First Name</label>
                            <input type="text" name="first_name" id="firstNameInput" class="form-control profile-input" value="<?= htmlspecialchars($user['first_name']) ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Last Name</label>
                            <input type="text" name="last_name" id="lastNameInput" class="form-control profile-input" value="<?= htmlspecialchars($user['last_name']) ?>" required readonly>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Middle Name</label>
                            <input type="text" name="middle_name" id="middleNameInput" class="form-control profile-input" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" placeholder="Optional" readonly>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Email Address</label>
                            <input type="email" name="email" id="emailInput" class="form-control profile-input" value="<?= htmlspecialchars($user['email']) ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Phone Number</label>
                            <input type="tel" name="phone" id="phoneInput" class="form-control profile-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" readonly>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin: 35px 0 0; padding: 25px;">
                        <h4 style="margin-bottom: 20px; font-size: 0.9rem; font-weight: 800; text-transform: uppercase; color: #1e293b; letter-spacing: 0.05em;">Notification Preferences</h4>
                        
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <label class="custom-checkbox-container" style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                <input type="checkbox" name="email_notifications" id="emailNotifInput" <?= ($user['email_notifications_enabled']) ? 'checked' : '' ?> disabled style="width: 18px; height: 18px; accent-color: #6366f1;">
                                <div>
                                    <span style="display: block; font-weight: 700; color: #1e293b; font-size: 0.95rem;">Real-Time Attendance Alerts</span>
                                    <span style="display: block; font-size: 0.8rem; color: #64748b;">Receive emails immediately when you time in or out</span>
                                </div>
                            </label>
                            
                            <label class="custom-checkbox-container" style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                <input type="checkbox" name="weekly_summary" id="weeklySumInput" <?= ($user['weekly_summary_enabled']) ? 'checked' : '' ?> disabled style="width: 18px; height: 18px; accent-color: #6366f1;">
                                <div>
                                    <span style="display: block; font-weight: 700; color: #1e293b; font-size: 0.95rem;">Weekly Performance Summary</span>
                                    <span style="display: block; font-size: 0.8rem; color: #64748b;">Get a comprehensive report of your hours every weekend</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="editModeButtons" style="display: none; gap: 12px; margin-top: 30px;">
                        <button type="submit" name="update_profile" class="btn btn-primary" style="padding: 12px 30px; font-weight: 700;">
                            <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 8px;"></i> Save All Changes
                        </button>
                        <button type="button" onclick="location.reload()" class="btn btn-secondary" style="padding: 12px 20px;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="photoModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 style="margin: 0; font-size: 1.1rem;">Update Profile Picture</h3>
            <span class="close-btn" onclick="closePhotoModal()">&times;</span>
        </div>
        <div class="modal-body" style="text-align: center; padding: 20px;">
            <div id="selectStep">
                <label for="fileInput" class="btn btn-secondary" style="cursor: pointer; padding: 10px 20px;">
                    <i class="fa-solid fa-upload"></i> Select Image
                </label>
                <input type="file" id="fileInput" accept="image/*" style="display: none;">
                <p style="font-size: 12px; color: var(--gray-500); margin-top: 15px;">
                    Maximum file size: 10MB (JPG or PNG)
                </p>
            </div>
            <div id="cropStep" style="display: none;">
                <div style="max-height: 400px; overflow: hidden; margin-bottom: 15px;">
                    <img id="imageToCrop" style="max-width: 100%; display: block;">
                </div>
                <p style="font-size: 12px; color: var(--gray-500);">Drag to position, scroll to zoom</p>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 20px;">
            <button type="button" class="btn btn-secondary" onclick="closePhotoModal()">Cancel</button>
            <button type="button" id="savePhotoBtn" class="btn btn-primary" style="display: none;">Save and Apply</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const editRow = document.getElementById('editModeButtons');
    const inputs = [
        document.getElementById('firstNameInput'), 
        document.getElementById('lastNameInput'), 
        document.getElementById('middleNameInput'), 
        document.getElementById('emailInput'), 
        document.getElementById('phoneInput'), 
        document.getElementById('emailNotifInput'), 
        document.getElementById('weeklySumInput')
    ];

    if (editBtn) {
        editBtn.addEventListener('click', () => {
            inputs.forEach(i => { if(i){ i.removeAttribute('readonly'); i.removeAttribute('disabled'); }});
            editBtn.style.display = 'none';
            editRow.style.display = 'flex';
        });
    }

    let cropper;
    const photoModal = document.getElementById('photoModal');
    const fileInput = document.getElementById('fileInput');
    const imageToCrop = document.getElementById('imageToCrop');
    const saveBtn = document.getElementById('savePhotoBtn');
    const selectStep = document.getElementById('selectStep');
    const cropStep = document.getElementById('cropStep');

    window.openPhotoModal = function() { photoModal.style.display = 'flex'; }
    window.closePhotoModal = function() { 
        photoModal.style.display = 'none'; 
        if(cropper) cropper.destroy(); 
        selectStep.style.display = 'block';
        cropStep.style.display = 'none';
        saveBtn.style.display = 'none';
        fileInput.value = '';
    }

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 10 * 1024 * 1024) { 
            alert('File is too large. Maximum size is 10MB.'); 
            fileInput.value = '';
            return; 
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            imageToCrop.src = event.target.result;
            selectStep.style.display = 'none';
            cropStep.style.display = 'block';
            saveBtn.style.display = 'inline-block';
            
            if (cropper) cropper.destroy();
            cropper = new Cropper(imageToCrop, { 
                aspectRatio: 1, 
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });
        };
        reader.readAsDataURL(file);
    });

    saveBtn.addEventListener('click', () => {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        cropper.getCroppedCanvas({ width: 400, height: 400 }).toBlob((blob) => {
            const formData = new FormData();
            formData.append('croppedImage', blob);

            fetch('profile.php?action=upload_photo', { 
                method: 'POST', 
                body: formData 
            })
            .then(r => r.json())
            .then(data => { 
                if(data.success) {
                    location.reload(); 
                } else {       
                    alert(data.message);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save and Apply';
                } 
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred during upload.');
                saveBtn.disabled = false;
            });
        });
    });
});
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>