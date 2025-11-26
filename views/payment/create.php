<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-invoice-dollar text-success"></i> Lập Phiếu Thu Mới</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('payment.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại Sổ quỹ
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Hiển thị lỗi nếu có -->
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin phiếu thu</h3>
                        </div>
                        
                        <form action="<?= route('payment.store') ?>" method="POST">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Chọn Đơn hàng (Booking) <span class="text-danger">*</span></label>
                                    <select name="booking_id" class="form-control select2" required id="select-booking">
                                        <option value="">-- Chọn khách hàng nộp tiền --</option>
                                        <?php foreach ($bookings as $bk): 
                                            $paid = (float)($bk['paid_amount'] ?? 0);
                                            $total = (float)($bk['total_amount'] ?? 0);
                                            $remain = $total - $paid;
                                        ?>
                                            <option value="<?= $bk['id'] ?>" data-remain="<?= $remain ?>">
                                                [<?= $bk['code'] ?>] <?= $bk['contact_name'] ?> 
                                                (Còn thiếu: <?= number_format($remain) ?> đ)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Chỉ hiển thị các đơn hàng chưa thanh toán đủ hoặc chưa hủy.</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Số tiền thu (VNĐ) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="amount" id="input-amount" class="form-control" required min="1000" placeholder="Nhập số tiền...">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">VNĐ</span>
                                                </div>
                                            </div>
                                            <!-- Nút gợi ý điền nhanh số tiền còn thiếu -->
                                            <button type="button" class="btn btn-xs btn-outline-info mt-1" id="btn-fill-remain" style="display:none;">
                                                Điền số tiền còn thiếu
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phương thức thanh toán</label>
                                            <select name="method" class="form-control">
                                                <option value="CASH">Tiền mặt</option>
                                                <option value="TRANSFER" selected>Chuyển khoản ngân hàng</option>
                                                <option value="CARD">Thẻ tín dụng / POS</option>
                                                <option value="QR">Cổng thanh toán QR</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Mã chứng từ / Số tham chiếu</label>
                                    <input type="text" name="receipt_no" class="form-control" placeholder="VD: FT2311001, Mã giao dịch NH...">
                                </div>

                                <div class="form-group">
                                    <label>Ghi chú / Lý do thu</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="VD: Thu tiền cọc lần 1..."></textarea>
                                </div>
                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-success font-weight-bold btn-lg">
                                    <i class="fas fa-check"></i> Xác Nhận Thu Tiền
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Logic gợi ý số tiền còn thiếu khi chọn booking
        $('#select-booking').change(function() {
            const selectedOption = $(this).find(':selected');
            const remain = selectedOption.data('remain');
            
            if (remain > 0) {
                $('#btn-fill-remain').text('Điền nhanh: ' + new Intl.NumberFormat('vi-VN').format(remain) + ' đ').show();
                
                // Gắn sự kiện click cho nút gợi ý này
                $('#btn-fill-remain').off('click').on('click', function() {
                    $('#input-amount').val(remain);
                });
            } else {
                $('#btn-fill-remain').hide();
            }
        });
    });
</script>