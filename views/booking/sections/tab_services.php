<div class="text-right mb-2">
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-service">
        <i class="fas fa-plus"></i> Thêm
    </button>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Dịch vụ</th>
            <th>SL</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
            <th>Xóa</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($services as $svc): ?>
        <tr>
            <td><span class="badge badge-info"><?= $svc['type'] ?></span> <?= htmlspecialchars($svc['name']) ?></td>
            <td class="text-center"><?= $svc['qty'] ?></td>
            <td class="text-right"><?= number_format($svc['unit_price']) ?></td>
            <td class="text-right font-weight-bold"><?= number_format($svc['amount']) ?></td>
            <td class="text-center">
                <form action="<?= route('booking.service.delete', ['id' => $booking['id']]) ?>?tab=services" method="POST" onsubmit="return confirm('Xóa?')">
                    <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($services)): ?>
            <tr><td colspan="5" class="text-center text-muted">Chưa có dịch vụ thêm</td></tr>
        <?php endif; ?>
    </tbody>
</table>