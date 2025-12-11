<?php
// Xác định đây là Tour Custom hay không
$isCustom = isset($booking['is_custom']) && $booking['is_custom'] == 1;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title text-muted m-0">
        <i class="fas fa-users mr-1"></i> Danh sách đoàn (<?= count($travelers) ?>)
    </h5>
    
    <!-- Nút Thêm Khách (Luôn hiện cho cả Tour Hệ thống và Tour Custom) -->
    <button type="button" class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#modalAddTraveler">
        <i class="fas fa-user-plus mr-1"></i>
        <?= $isCustom ? 'Thêm khách & Tính phí' : 'Thêm khách vào đoàn' ?>
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover text-nowrap">
        <thead class="bg-light">
            <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Họ tên</th>
                <th>Giới tính</th>
                <th>Ngày sinh</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($travelers)): ?>
                <?php foreach ($travelers as $key => $t): ?>
                <tr>
                    <td class="text-center align-middle"><?= $key + 1 ?></td>
                    <td class="align-middle">
                        <span class="font-weight-bold text-primary"><?= htmlspecialchars($t['full_name']) ?></span>
                    </td>
                    <td class="align-middle">
                        <?php if($t['gender'] == 'MALE'): ?>
                            <span class="badge badge-light text-primary"><i class="fas fa-mars"></i> Nam</span>
                        <?php elseif($t['gender'] == 'FEMALE'): ?>
                            <span class="badge badge-light text-pink"><i class="fas fa-venus"></i> Nữ</span>
                        <?php else: ?>
                            <span class="badge badge-light">Khác</span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle">
                        <?php if(!empty($t['dob'])): ?>
                            <?= date('d/m/Y', strtotime($t['dob'])) ?>
                            <small class="text-muted">(<?= date('Y') - date('Y', strtotime($t['dob'])) ?> tuổi)</small>
                        <?php else: ?>
                            ---
                        <?php endif; ?>
                    </td>
                    <td class="align-middle text-muted small">
                        <?= htmlspecialchars($t['note']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-user-slash fa-2x mb-2 text-gray-300"></i><br>
                        Chưa có thông tin khách hàng.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>