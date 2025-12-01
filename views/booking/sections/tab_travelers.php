<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Họ tên</th>
            <th>Giới tính</th>
            <th>Ngày sinh</th>
            <th>Ghi chú</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($travelers as $i => $t): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td class="font-weight-bold"><?= htmlspecialchars($t['full_name']) ?></td>
            <td><?= ($t['gender']=='MALE')?'Nam':(($t['gender']=='FEMALE')?'Nữ':'Khác') ?></td>
            <td><?= !empty($t['dob']) ? date('d/m/Y', strtotime($t['dob'])) : '-' ?></td>
            <td><?= htmlspecialchars($t['note'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>