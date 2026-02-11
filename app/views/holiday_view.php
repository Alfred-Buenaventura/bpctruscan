<?php 
require_once __DIR__ . '/partials/header.php'; 
$success = $success ?? null;
$error = $error ?? null;
?>

<style>
.holiday-management {
    position: relative;
    z-index: 1;
}

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    z-index: 9999 !important;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
    padding: 20px;
}

.modal-overlay.active {
    display: flex !important;
}

.modal {
    display: none;
    position: fixed; 
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(3px);
    z-index: 9999 !important;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #ffffff;
    width: 90%;
    max-width: 450px;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideUp 0.3s ease-out;
}

@keyframes modalSlideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.holiday-management h3, 
.holiday-management table td, 
.holiday-management label {
    color: #1e293b !important;
}

.btn i, .btn-sm i {
    pointer-events: none;
}

.btn, .btn-sm {
    position: relative;
    z-index: 5;
    cursor: pointer !important;
}

.status-modal-overlay {
    display: <?= ($success || $error) ? 'flex' : 'none' ?>;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
</style>

<div class="main-body holiday-management">
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-file-signature"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">DTR Management</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Manage holidays and dynamic signatory settings.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Notice:</span> 
                    <span style="color: #6366f1; font-weight: 600;">The DTR Management</span> page is used to manage the DTR for holidays and signatures. Use the 
                    forms below to adjust <span style="color: #6366f1; font-weight: 600;">Holidays</span> 
                    according to the calendar year or change the DTR <span style="color: #6366f1; font-weight: 600;">Signatory</span> section.
                </p>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start;">
        
        <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="padding: 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-calendar-alt" style="color: #059669;"></i> Holiday Records
                </h3>
                <button type="button" class="btn-sm" onclick="toggleModal('filterHolidaysModal', true)" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-filter"></i> Filters
                </button>
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left;">
                        <th style="padding: 1rem; font-size: 0.85rem; color: #64748b;">DATE</th>
                        <th style="padding: 1rem; font-size: 0.85rem; color: #64748b;">EVENT</th>
                        <th style="padding: 1rem; font-size: 0.85rem; color: #64748b;">TYPE</th>
                        <th style="padding: 1rem; text-align: right; font-size: 0.85rem; color: #64748b;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays as $h): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; font-weight: 700; color: #059669;"><?= date('M d, Y', strtotime($h['holiday_date'])) ?></td>
                            <td style="padding: 1rem;"><?= htmlspecialchars($h['description']) ?></td>
                            <td style="padding: 1rem;">
                                <span style="background: <?= $h['type'] === 'Regular' ? '#dcfce7' : '#fef3c7' ?>; color: <?= $h['type'] === 'Regular' ? '#166534' : '#92400e' ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                    <?= $h['type'] ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <button type="button" class="btn-sm" style="color: #ef4444; background: none; border: none; font-size: 1.1rem; cursor: pointer;" 
                                        onclick="prepDelete(<?= $h['id'] ?>, '<?= htmlspecialchars($h['description']) ?>')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 1.5rem; font-weight: 700;"><i class="fa-solid fa-plus-circle" style="color: #059669; margin-right: 10px;"></i> Add New Holiday</h3>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Date</label>
                        <input type="date" name="holiday_date" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Independence Day" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Type</label>
                        <select name="type" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <option value="Regular">Regular Holiday</option>
                            <option value="Special">Special Non-Working Day</option>
                        </select>
                    </div>
                    <button type="submit" name="add_holiday" style="width: 100%; background: #059669; color: white; border: none; padding: 0.85rem; border-radius: 8px; font-weight: 700; cursor: pointer;">Save Holiday</button>
                </form>
            </div>

            <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 1.5rem; font-weight: 700;"><i class="fa-solid fa-pen-nib" style="color: #3b82f6; margin-right: 10px;"></i> DTR Signatory</h3>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">In-Charge Name</label>
                        <input type="text" name="in_charge_name" class="form-control" value="<?= htmlspecialchars($settings['dtr_in_charge_name'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Designation/Title</label>
                        <input type="text" name="in_charge_title" class="form-control" value="<?= htmlspecialchars($settings['dtr_in_charge_title'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                    <button type="submit" name="update_signatory" style="width: 100%; background: #334155; color: white; border: none; padding: 0.85rem; border-radius: 8px; font-weight: 700; cursor: pointer;">Update DTR Signature</button>
                </form>
            </div>
            
        </div>
    </div>
</div>

<div id="filterHolidaysModal" class="modal">
    <div class="modal-content">
        <div style="padding: 0 0 1rem; border-bottom: 1px solid #f1f5f9; margin-bottom: 1.5rem;">
            <h3 style="margin: 0;"><i class="fa-solid fa-filter" style="color: #059669; margin-right: 8px;"></i> Filter Records</h3>
        </div>
        <form method="GET" action="holiday_management.php">
            <?php csrf_field(); ?>
            <div style="margin-bottom: 1rem;">
                <label>Keyword Search</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search']) ?>" style="width:100%; padding:0.75rem; border:1px solid #e2e8f0; border-radius:8px;">
            </div>
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="flex:1;">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>" style="width:100%; padding:0.75rem; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
                <div style="flex:1;">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>" style="width:100%; padding:0.75rem; border:1px solid #e2e8f0; border-radius:8px;">
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" style="width: 100%; background: #059669; color: white; border: none; padding: 0.85rem; border-radius: 10px; font-weight: 700;">Apply Filters</button>
                <button type="button" onclick="toggleModal('filterHolidaysModal', false)" style="background: none; border: none; color: #64748b; font-weight: 600; cursor: pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
#filterHolidaysModal {
    display: none; 
}

#filterHolidaysModal[style*="display: flex"] {
    display: flex !important;
}
</style>

<div id="deleteHolidayModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:12px; width:400px; text-align:center;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:3rem; color:#ef4444; margin-bottom:1rem;"></i>
        <h3>Delete Holiday?</h3>
        <p id="del_name" style="color:#64748b; margin:1rem 0;"></p>
        <form method="POST">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" id="del_id">
            <button type="submit" name="delete_holiday" style="width:100%; background:#ef4444; color:white; padding:0.75rem; border:none; border-radius:8px;">Yes, Delete</button>
            <button type="button" onclick="toggleModal('deleteHolidayModal', false)" style="width:100%; margin-top:0.5rem; background:none; border:none; color:#64748b;">Cancel</button>
        </form>
    </div>
</div>

<?php if ($success || $error): ?>
<div id="statusPopupModal" class="modal-overlay active">
    <div class="modal-content" style="background: white; width: 100%; max-width: 400px; border-radius: 24px; padding: 2.5rem; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.2);">
        <div style="background: <?= $success ? '#ecfdf5' : '#fee2e2' ?>; color: <?= $success ? '#059669' : '#ef4444' ?>; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
            <i class="fa-solid <?= $success ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
        </div>
        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;"><?= $success ? 'Success!' : 'Oops!' ?></h3>
        <p style="color: #64748b; margin-bottom: 2rem;"><?= htmlspecialchars(($success ?? $error) ?? '') ?></p>
        <button onclick="this.closest('.modal-overlay').classList.remove('active')" style="width: 100%; background: #1e293b; color: white; border: none; padding: 1rem; border-radius: 12px; font-weight: 700; cursor: pointer;">Continue</button>
    </div>
</div>
<?php endif; ?>

<script>
function toggleModal(id, show) {
    const el = document.getElementById(id);
    if (el) el.style.display = show ? 'flex' : 'none';
}

function prepDelete(id, name) {
    document.getElementById('del_id').value = id;
    document.getElementById('del_name').textContent = "Are you sure you want to remove '" + name + "'?";
    toggleModal('deleteHolidayModal', true);
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>