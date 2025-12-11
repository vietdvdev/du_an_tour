<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title text-muted m-0">
        <i class="fas fa-history mr-1"></i> Lịch sử thanh toán (<?= count($payments) ?>)
    </h5>
    <button class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#modal-add-payment">
        <i class="fas fa-money-bill mr-1"></i> Thu tiền
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="bg-light">
            <tr>
                <th>Ngày</th>
                <th style="width: 120px;" class="text-right">Số tiền</th>
                <th style="width: 100px;">Phương thức</th>
                <th>Ghi chú</th>
                <th style="width: 50px;" class="text-center">Hủy</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $pm): ?>
                <tr>
                    <td class="align-middle"><?= date('d/m/Y H:i', strtotime($pm['paid_at'])) ?></td>
                    <td class="text-right align-middle font-weight-bold text-success">+<?= number_format($pm['amount']) ?> đ</td>
                    <td class="align-middle"><?= htmlspecialchars($pm['method']) ?></td>
                    <td class="align-middle text-muted small"><?= htmlspecialchars($pm['note'] ?? '') ?></td>
                    <td class="text-center align-middle">
                        <form action="<?= route('booking.payment.delete', ['id' => $booking['id']]) ?>?tab=payments" method="POST" onsubmit="return confirm('Xác nhận hủy phiếu thanh toán này?');" style="display: inline;">
                            <input type="hidden" name="payment_id" value="<?= $pm['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-receipt fa-2x mb-2 text-gray-300"></i><br>
                        Chưa có giao dịch thanh toán nào
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>