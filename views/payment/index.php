<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1><i class="fas fa-coins text-success"></i> Sổ Quỹ (Phiếu Thu)</h1></div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('payment.create') ?>" class="btn btn-success"><i class="fas fa-plus"></i> Tạo Phiếu Thu</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table id="paymentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ngày thu</th>
                                <th>Mã Booking</th>
                                <th>Người nộp</th>
                                <th>Số tiền</th>
                                <th>Phương thức</th>
                                <th>Mã chứng từ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($p['paid_at'])) ?></td>
                                <td><a href="<?= route('booking.show', ['id' => $p['booking_id']]) ?>">
                                    <?= htmlspecialchars($p['booking_code']) ?>
                                </a></td>
                                <td><?= htmlspecialchars($p['contact_name']) ?></td>
                                <td class="font-weight-bold text-success text-right">
                                    <?= number_format($p['amount']) ?> đ
                                </td>
                                <td><?= $p['method'] ?></td>
                                <td><?= htmlspecialchars($p['receipt_no']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
<script>
    $(function () { $("#paymentTable").DataTable({ "order": [[ 0, "desc" ]] }); });
</script>