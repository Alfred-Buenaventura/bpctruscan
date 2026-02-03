<table class="attendance-table">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Method</th> <th>Date/Time</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($history as $row): ?>
        <tr>
            <td><?= $row['student_id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <span class="badge <?= $row['method'] === 'Manual' ? 'bg-warning' : 'bg-success' ?>">
                    <?= $row['method'] ?>
                </span>
            </td>
            <td><?= $row['log_date'] ?> <?= $row['log_time'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>