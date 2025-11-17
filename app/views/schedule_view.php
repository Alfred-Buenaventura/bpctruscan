<?php 
// FIX: Use __DIR__ to locate the partials folder correctly
require_once __DIR__ . '/partials/header.php'; 
?>
<div class="main-body">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="stats-grid schedule-stats-grid">
        <?php if (!empty($selectedUserInfo) && isset($userStats)): ?>
            <div class="stat-card stat-card-small">
                <div class="stat-icon emerald"><i class="fa-solid fa-user"></i></div>
                <div class="stat-details">
                    <p>Viewing Schedule For</p>
                    <div class="stat-value-name"><?= htmlspecialchars($selectedUserInfo['first_name'] . ' ' . $selectedUserInfo['last_name']) ?></div>
                    <p class="stat-value-subtext"><?= htmlspecialchars($selectedUserInfo['faculty_id']) ?></p>
                </div>
            </div>
            <div class="stat-card stat-card-small">
                <div class="stat-icon emerald"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-details">
                    <p>Scheduled Hours</p>
                    <div class="stat-value emerald"><?= number_format($userStats['total_hours'], 1) ?>h</div>
                </div>
            </div>
            <div class="stat-card stat-card-small">
                <div class="stat-icon" style="color:var(--blue-600); background:var(--blue-100);"><i class="fa-solid fa-hourglass"></i></div>
                <div class="stat-details">
                    <p>Vacant Hours</p>
                    <div class="stat-value" style="color:var(--blue-700);"><?= number_format($userStats['vacant_hours'], 1) ?>h</div>
                </div>
            </div>
            <div class="stat-card stat-card-small">
                <div class="stat-icon" style="color:var(--indigo-600); background:var(--indigo-100);"><i class="fa-solid fa-business-time"></i></div>
                <div class="stat-details">
                    <p>Duty Span</p>
                    <div class="stat-value" style="color:var(--indigo-700);"><?= number_format($userStats['duty_span'], 1) ?>h</div>
                </div>
            </div>
        <?php elseif ($isAdmin): ?>
            <div class="stat-card stat-card-small">
                <div class="stat-icon emerald"><i class="fa-solid fa-users"></i></div>
                <div class="stat-details">
                    <p>Viewing</p>
                    <div class="stat-value-name">All Users</div>
                </div>
            </div>
            <div class="stat-card stat-card-small">
                <div class="stat-icon emerald"><i class="fa-solid fa-list-check"></i></div>
                <div class="stat-details">
                    <p>Total Approved</p>
                    <div class="stat-value emerald"><?= $stats['total_schedules'] ?? 0 ?></div>
                </div>
            </div>
             <div class="stat-card stat-card-small">
                <div class="stat-icon emerald"><i class="fa-solid fa-user-check"></i></div>
                <div class="stat-details">
                    <p>Users with Schedules</p>
                    <div class="stat-value emerald"><?= $stats['total_users_with_schedules'] ?? 0 ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card" id="schedule-card">
        <div class="card-header card-header-flex">
            <div>
                <h3>Manage Schedules</h3>
                <p><?= $isAdmin ? 'Approve pending schedules or manage approved ones' : 'View your approved schedules or pending submissions' ?></p>
            </div>
            <div class="card-header-actions">
                <?php if (!$isAdmin): ?>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Add New Schedule(s)
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tabs" style="padding: 0 1.5rem; background: var(--gray-50);">
            <button class="tab-btn <?= $activeTab === 'manage' ? 'active' : '' ?>" onclick="showScheduleTab(event, 'manage')">
                <i class="fa-solid fa-check-circle"></i> Approved Schedules
            </button>
            <button class="tab-btn <?= $activeTab === 'pending' ? 'active' : '' ?>" onclick="showScheduleTab(event, 'pending')">
                <i class="fa-solid fa-clock"></i> Pending Approval 
                <?php if (count($pendingSchedules) > 0): ?>
                    <span class="notification-count-badge"><?= count($pendingSchedules) ?></span>
                <?php endif; ?>
            </button>
        </div>
        
        <div id="manageTab" class="tab-content <?= $activeTab === 'manage' ? 'active' : '' ?>">
            <div class="card-body">
                <form method="GET" class="schedule-filter-form">
                    <div class="schedule-filter-grid">
                        <?php if ($isAdmin): ?>
                        <div class="form-group">
                            <label>Select User</label>
                            <select name="user_id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- All Users --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $selectedUserId == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Day of Week</label>
                            <select name="day_of_week" class="form-control" onchange="this.form.submit()">
                                <option value="">All Days</option>
                                <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $d): ?>
                                    <option value="<?= $d ?>" <?= ($filters['day_of_week'] ?? '') == $d ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>

                <?php if ($isAdmin && empty($filters['user_id'])): ?>
                    <?php if (empty($groupedApprovedSchedules)): ?>
                        <p class="empty-schedule-message">No approved schedules found.</p>
                    <?php else: ?>
                        <div class="user-schedule-accordion">
                            <?php foreach ($groupedApprovedSchedules as $uid => $userData): ?>
                                <div class="user-schedule-group">
                                    <button class="user-schedule-header" onclick="toggleScheduleGroup(this)">
                                        <div class="user-schedule-info">
                                            <span class="user-name"><?= htmlspecialchars($userData['user_info']['first_name'] . ' ' . $userData['user_info']['last_name']) ?></span>
                                            <span class="user-id"><?= htmlspecialchars($userData['user_info']['faculty_id']) ?></span>
                                        </div>
                                        <div class="user-schedule-stats">
                                            <span>Sched: <strong><?= number_format($userData['stats']['total_hours'], 1) ?>h</strong></span>
                                            <i class="fa-solid fa-chevron-down schedule-group-icon"></i>
                                        </div>
                                    </button>
                                    <div class="user-schedule-body">
                                        <?php renderScheduleTable($userData['schedules'], true); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <?php if (empty($approvedSchedules)): ?>
                        <p class="empty-schedule-message">No schedules found.</p>
                    <?php else: ?>
                        <?php renderScheduleTable($approvedSchedules, false); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="pendingTab" class="tab-content <?= $activeTab === 'pending' ? 'active' : '' ?>">
            <div class="card-body">
                <?php if (empty($pendingSchedules)): ?>
                    <p class="empty-schedule-message">No pending schedules.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <?php if ($isAdmin): ?><th>User</th><?php endif; ?>
                                <th>Day</th><th>Subject</th><th>Time</th><th>Room</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSchedules as $sched): ?>
                            <tr>
                                <?php if ($isAdmin): ?>
                                    <td><?= htmlspecialchars($sched['first_name'] . ' ' . $sched['last_name']) ?></td>
                                <?php endif; ?>
                                <td class="table-day-highlight"><?= $sched['day_of_week'] ?></td>
                                <td><?= htmlspecialchars($sched['subject']) ?></td>
                                <td><?= date('g:i A', strtotime($sched['start_time'])) ?> - <?= date('g:i A', strtotime($sched['end_time'])) ?></td>
                                <td><?= htmlspecialchars($sched['room']) ?></td>
                                <td style="text-align: right;">
                                    <?php if ($isAdmin): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="schedule_id" value="<?= $sched['id'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $sched['user_id'] ?>">
                                            <input type="hidden" name="subject" value="<?= htmlspecialchars($sched['subject']) ?>">
                                            <button type="submit" name="approve_schedule" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i></button>
                                            <button type="submit" name="decline_schedule" class="btn btn-sm btn-danger"><i class="fa-solid fa-times"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-primary" onclick="openEditModal(<?= $sched['id'] ?>, <?= $sched['user_id'] ?>, '<?= $sched['day_of_week'] ?>', '<?= htmlspecialchars($sched['subject']) ?>', '<?= $sched['start_time'] ?>', '<?= $sched['end_time'] ?>', '<?= htmlspecialchars($sched['room']) ?>')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= $sched['id'] ?>, <?= $sched['user_id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                    <?php endif; ?>
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

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<?php
// Helper to render table rows
function renderScheduleTable($schedules, $nested) {
    echo '<table class="schedule-table-inner"><thead><tr><th>Day</th><th>Subject</th><th>Time</th><th>Duration</th><th>Room</th><th>Actions</th></tr></thead><tbody>';
    foreach ($schedules as $sched) {
        $hours = (strtotime($sched['end_time']) - strtotime($sched['start_time'])) / 3600;
        echo '<tr>';
        echo '<td class="table-day-highlight">' . $sched['day_of_week'] . '</td>';
        echo '<td>' . htmlspecialchars($sched['subject']) . '</td>';
        echo '<td>' . date('g:i A', strtotime($sched['start_time'])) . ' - ' . date('g:i A', strtotime($sched['end_time'])) . '</td>';
        echo '<td>' . number_format($hours, 1) . 'h</td>';
        echo '<td>' . htmlspecialchars($sched['room']) . '</td>';
        echo '<td>';
        echo '<button class="btn btn-sm btn-primary" onclick="openEditModal(' . $sched['id'] . ', ' . $sched['user_id'] . ', \'' . $sched['day_of_week'] . '\', \'' . htmlspecialchars($sched['subject']) . '\', \'' . $sched['start_time'] . '\', \'' . $sched['end_time'] . '\', \'' . htmlspecialchars($sched['room']) . '\')"><i class="fa-solid fa-pen"></i></button> ';
        echo '<button class="btn btn-sm btn-danger" onclick="openDeleteModal(' . $sched['id'] . ', ' . $sched['user_id'] . ')"><i class="fa-solid fa-trash"></i></button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>

<div id="addScheduleModal" class="modal">
    <div class="modal-content modal-lg">
        <form method="POST">
            <div class="modal-header"><h3>Add Schedule</h3><button type="button" class="modal-close" onclick="closeModal('addScheduleModal')">&times;</button></div>
            <div class="modal-body">
                <div id="schedule-entry-list"></div> <button type="button" class="btn btn-secondary" onclick="addScheduleRow()">+ Add Row</button>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_schedule" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>

<div id="editScheduleModal" class="modal">
    <div class="modal-content modal-small">
        <form method="POST">
            <div class="modal-header"><h3>Edit Schedule</h3><button type="button" class="modal-close" onclick="closeModal('editScheduleModal')">&times;</button></div>
            <div class="modal-body">
                <input type="hidden" name="schedule_id" id="editScheduleId">
                <input type="hidden" name="user_id_edit" id="editUserId">
                <div class="form-group"><label>Day</label><select name="day_of_week" id="editDayOfWeek" class="form-control"><option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option></select></div>
                <div class="form-group"><label>Subject</label><input type="text" name="subject" id="editSubject" class="form-control"></div>
                <div class="form-group"><label>Start</label><input type="time" name="start_time" id="editStartTime" class="form-control"></div>
                <div class="form-group"><label>End</label><input type="time" name="end_time" id="editEndTime" class="form-control"></div>
                <div class="form-group"><label>Room</label><input type="text" name="room" id="editRoom" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="submit" name="edit_schedule" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div>

<div id="deleteScheduleModal" class="modal">
    <div class="modal-content modal-small">
        <form method="POST">
            <div class="modal-header" style="background:var(--red-50);"><h3 style="color:var(--red-700);">Delete?</h3><button type="button" class="modal-close" onclick="closeModal('deleteScheduleModal')">&times;</button></div>
            <div class="modal-body"><p>Are you sure?</p>
                <input type="hidden" name="schedule_id_delete" id="deleteScheduleId">
                <input type="hidden" name="user_id_delete" id="deleteUserId">
            </div>
            <div class="modal-footer"><button type="submit" name="delete_schedule" class="btn btn-danger">Delete</button></div>
        </form>
    </div>
</div>

<script>
function showScheduleTab(e, tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.getElementById(tab + 'Tab').style.display = 'block';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    e.currentTarget.classList.add('active');
}
document.getElementById('<?= $activeTab ?>Tab').style.display = 'block'; // Init

function toggleScheduleGroup(btn) {
    const body = btn.nextElementSibling;
    if (body.style.maxHeight) { body.style.maxHeight = null; } else { body.style.maxHeight = body.scrollHeight + "px"; }
}

function openEditModal(id, uid, day, sub, start, end, room) {
    document.getElementById('editScheduleId').value = id;
    document.getElementById('editUserId').value = uid;
    document.getElementById('editDayOfWeek').value = day;
    document.getElementById('editSubject').value = sub;
    document.getElementById('editStartTime').value = start;
    document.getElementById('editEndTime').value = end;
    document.getElementById('editRoom').value = room;
    openModal('editScheduleModal');
}
function openDeleteModal(id, uid) {
    document.getElementById('deleteScheduleId').value = id;
    document.getElementById('deleteUserId').value = uid;
    openModal('deleteScheduleModal');
}
function openAddModal() {
    const list = document.getElementById('schedule-entry-list');
    list.innerHTML = ''; 
    addScheduleRow(); 
    openModal('addScheduleModal');
}
function addScheduleRow() {
    const list = document.getElementById('schedule-entry-list');
    const div = document.createElement('div');
    div.className = 'schedule-entry-row';
    div.innerHTML = `
        <select name="day_of_week[]" class="form-control"><option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option></select>
        <input type="text" name="subject[]" placeholder="Subject" class="form-control">
        <input type="time" name="start_time[]" class="form-control">
        <input type="time" name="end_time[]" class="form-control">
        <input type="text" name="room[]" placeholder="Room" class="form-control">
    `;
    list.appendChild(div);
}
</script>
