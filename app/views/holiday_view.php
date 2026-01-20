<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="main-body">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success auto-dismiss" style="margin-bottom: 1.5rem;">
            <i class="fa-solid fa-circle-check"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error auto-dismiss" style="margin-bottom: 1.5rem;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-calendar-plus"></i> Add New Holiday</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="holiday_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Independence Day" required>
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="Regular">Regular Holiday</option>
                            <option value="Special">Special Non-Working Day</option>
                        </select>
                    </div>
                    <button type="submit" name="add_holiday" class="btn btn-primary btn-full-width">
                        <i class="fa-solid fa-plus"></i> Add Holiday
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3><i class="fa-solid fa-list-ul"></i> Holiday List</h3>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="openModal('filterHolidaysModal')">
                        <i class="fa-solid fa-filter"></i> Set Filters
                    </button>
                    <?php if (!empty($filters['search']) || !empty($filters['start_date'])): ?>
                        <a href="holiday_management.php" class="btn btn-sm btn-outline" title="Clear Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($holidays)): ?>
                    <div style="text-align: center; color: var(--gray-500); padding: 3rem;">
                        <i class="fa-solid fa-calendar-xmark" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                        <p>No holidays match your current filters.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($holidays as $h): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('F d', strtotime($h['holiday_date'])) ?></strong>
                                        <div style="font-size: 0.75rem; color: var(--gray-500);"><?= date('Y', strtotime($h['holiday_date'])) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($h['description']) ?></td>
                                    <td>
                                        <span class="ud-badge <?= $h['type'] === 'Regular' ? 'completed' : 'pending' ?>">
                                            <?= $h['type'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn btn-sm" style="color: var(--red-600);" 
                                                onclick="confirmDeleteHoliday(<?= $h['id'] ?>, '<?= htmlspecialchars($h['description']) ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="filterHolidaysModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><i class="fa-solid fa-filter"></i> Filter Holidays</h3>
            <button type="button" class="modal-close" onclick="closeModal('filterHolidaysModal')">&times;</button>
        </div>
        <form method="GET">
            <div class="modal-body">
                <div class="form-group">
                    <label>Keyword Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by description..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('filterHolidaysModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteHolidayModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header" style="border-bottom: none;">
            <div style="background: var(--red-100); color: var(--red-600); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; font-size: 1.5rem;">
                <i class="fa-solid fa-exclamation-triangle"></i>
            </div>
            <h3 style="text-align: center; width: 100%;">Delete Holiday?</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteHolidayModal')">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center; padding-top: 0;">
            <p>Are you sure you want to remove <strong id="delete_holiday_name"></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem; margin-top: 0.5rem;">This action cannot be undone and will affect attendance reporting.</p>
        </div>
        <div class="modal-footer" style="border-top: none; justify-content: center; gap: 1rem;">
            <form method="POST">
                <input type="hidden" name="id" id="delete_holiday_id">
                <button type="submit" name="delete_holiday" class="btn" style="background-color: var(--red-600); color: white;">Yes, Delete</button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteHolidayModal')">No, Cancel</button>
        </div>
    </div>
</div>

<script>
/**
 * Auto-dismiss alerts after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            // Start fading out
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            // Remove from DOM after fade
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000); // 5 seconds
    });
});

/**
 * Triggers the styled delete modal
 */
function confirmDeleteHoliday(id, description) {
    document.getElementById('delete_holiday_id').value = id;
    document.getElementById('delete_holiday_name').textContent = description;
    openModal('deleteHolidayModal');
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>