<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title text-muted m-0">
        <i class="fas fa-concierge-bell mr-1"></i> Danh sách dịch vụ & phụ thu (<?= count($services) ?>)
    </h5>
    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#modal-add-service">
        <i class="fas fa-plus mr-1"></i> Thêm dịch vụ
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="bg-light">
            <tr>
                <th>Dịch vụ</th>
                <th style="width: 60px;" class="text-center">SL</th>
                <th style="width: 100px;" class="text-right">Đơn giá</th>
                <th style="width: 100px;" class="text-right">Thành tiền</th>
                <th style="width: 50px;" class="text-center">Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $svc): ?>
                <tr>
                    <td>
                        <span class="badge badge-info"><?= htmlspecialchars($svc['type']) ?></span> 
                        <?= htmlspecialchars($svc['name']) ?>
                    </td>
                    <td class="text-center align-middle"><?= $svc['qty'] ?></td>
                    <td class="text-right align-middle"><?= number_format($svc['unit_price']) ?></td>
                    <td class="text-right align-middle font-weight-bold text-success"><?= number_format($svc['amount']) ?></td>
                    <td class="text-center align-middle">
                        <form action="<?= route('booking.service.delete', ['id' => $booking['id']]) ?>?tab=services" method="POST" onsubmit="return confirm('Xác nhận xóa dịch vụ này?');" style="display: inline;">
                            <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 text-gray-300"></i><br>
                        Chưa có dịch vụ nào được thêm
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>