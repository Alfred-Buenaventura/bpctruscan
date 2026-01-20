<?php require_once __DIR__ . '/partials/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<div class="main-body">
    <?php if (!empty($success)): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
    <?php if (!empty($error)): ?> <div class="alert alert-error"><?= $error ?></div> <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 30px 20px;">
                <div class="profile-pic-container" style="position: relative; width: 140px; height: 140px; margin: 0 auto 20px;">
                    <div style="width: 140px; height: 140px; background: var(--emerald-500); border-radius: 50%; overflow: hidden; border: 4px solid white; display: flex; align-items: center; justify-content: center; font-size: 52px; color: white; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= $user['profile_image'] ?>?v=<?= time() ?>" id="currentAvatar" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="openPhotoModal()" style="position: absolute; bottom: 5px; right: 5px; background: #4285f4; color: white; border: 3px solid white; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>

                <h3 style="margin-bottom: 4px; font-size: 1.25rem; color: #111827;">
                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                </h3>
                <p style="color: var(--gray-600); font-size: 0.95rem; margin-bottom: 2px;">
                    <?= htmlspecialchars($user['role']) ?>
                </p>
                <p style="color: var(--gray-500); font-size: 0.85rem; margin-bottom: 20px;">
                    ID: <?= htmlspecialchars($user['faculty_id']) ?>
                </p>

                <a href="change_password.php" class="btn btn-secondary btn-block" style="background: #e5e7eb; color: #374151; border: none;">
                    Change Password
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Account Settings</h3>
                <button type="button" id="editProfileBtn" class="btn btn-primary" style="font-size: 0.85rem;">
                    <i class="fa-solid fa-pen" style="font-size: 0.75rem;"></i> Edit Profile
                </button>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="firstNameInput" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="lastNameInput" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required readonly>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" id="middleNameInput" class="form-control" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" id="emailInput" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" id="phoneInput" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" readonly>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--gray-100); margin: 24px 0; padding-top: 24px;">
                        <h4 style="margin-bottom: 16px; font-size: 1rem;">Preferences</h4>
                        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                            <input type="checkbox" name="email_notifications" id="emailNotifInput" <?= ($user['email_notifications_enabled']) ? 'checked' : '' ?> disabled>
                            <div><label for="emailNotifInput" style="font-weight: 600; cursor: pointer;">Real-Time Attendance Alerts</label></div>
                        </div>
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="checkbox" name="weekly_summary" id="weeklySumInput" <?= ($user['weekly_summary_enabled']) ? 'checked' : '' ?> disabled>
                            <div><label for="weeklySumInput" style="font-weight: 600; cursor: pointer;">Weekly Attendance Summary</label></div>
                        </div>
                    </div>

                    <div id="editModeButtons" style="display: none; gap: 12px; margin-top: 24px;">
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        <button type="button" onclick="location.reload()" class="btn btn-secondary">Cancel</button>
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
    // --- Edit Profile Toggle ---
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

    // --- Photo Modal & Cropper Logic ---
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

        // 10MB Limit Check
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