<div class="text-right mb-2">
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-add-payment">
        <i class="fas fa-money-bill"></i> Thu tiền
    </button>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Ngày</th>
            <th>Số tiền</th>
            <th>PT</th>
            <th>Ghi chú</th>
            <th>Hủy</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($payments as $pm): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($pm['paid_at'])) ?></td>
            <td class="text-success font-weight-bold">+<?= number_format($pm['amount']) ?></td>
            <td><?= htmlspecialchars($pm['method']) ?></td>
            <td><?= htmlspecialchars($pm['note'] ?? '') ?></td>
            <td class="text-center">
                <form action="<?= route('booking.payment.delete', ['id' => $booking['id']]) ?>?tab=payments" method="POST" onsubmit="return confirm('Hủy phiếu?')">
                    <input type="hidden" name="payment_id" value="<?= $pm['id'] ?>">
                    <button class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($payments)): ?>
            <tr><td colspan="5" class="text-center text-muted">Chưa có giao dịch</td></tr>
        <?php endif; ?>
    </tbody>
</table>