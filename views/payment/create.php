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
            <!-- Thông báo lỗi chung (nếu có) -->
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>


            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin phiếu thu</h3>
                        </div>
                       
                        <form action="<?= route('payment.store') ?>" method="POST" id="form-payment" novalidate>
                            <div class="card-body">
                               
                                <!-- 1. Chọn Booking -->
                                <div class="form-group">
                                    <label>Chọn Đơn hàng (Booking) <span class="text-danger">*</span></label>
                                    <select name="booking_id" class="form-control select2 <?= isset($errors['booking_id']) ? 'is-invalid' : '' ?>" required id="select-booking">
                                        <option value="" data-remain="0">-- Chọn khách hàng nộp tiền --</option>
                                        <?php foreach ($bookings as $bk):
                                            $paid = (float)($bk['paid_amount'] ?? 0);
                                            $total = (float)($bk['total_amount'] ?? 0);
                                            $remain = $total - $paid;
                                            if ($remain <= 0) continue;
                                           
                                            // Logic giữ lại giá trị cũ khi có lỗi
                                            $selected = (isset($old['booking_id']) && $old['booking_id'] == $bk['id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $bk['id'] ?>" data-remain="<?= $remain ?>" <?= $selected ?>>
                                                [<?= $bk['code'] ?>] <?= $bk['contact_name'] ?>
                                                (Còn thiếu: <?= number_format($remain) ?> đ)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- Hiển thị lỗi -->
                                    <?php if (isset($errors['booking_id'])): ?>
                                        <span class="error invalid-feedback d-block"><?= $errors['booking_id'] ?></span>
                                    <?php endif; ?>
                                </div>


                                <div class="row">
                                    <!-- 2. Số tiền thu -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Số tiền thu (VNĐ) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="amount" id="input-amount"
                                                       class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>"
                                                       required min="1" placeholder="Nhập số tiền..."
                                                       value="<?= $old['amount'] ?? '' ?>">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">VNĐ</span>
                                                </div>
                                            </div>
                                           
                                            <!-- Hiển thị lỗi -->
                                            <?php if (isset($errors['amount'])): ?>
                                                <span class="error invalid-feedback d-block"><?= $errors['amount'] ?></span>
                                            <?php endif; ?>
                                           
                                            <!-- Client-side Error -->
                                            <small class="text-danger" id="client-error-msg" style="display:none;"></small>


                                            <button type="button" class="btn btn-xs btn-outline-info mt-1" id="btn-fill-remain" style="display:none;">
                                                Điền số tiền còn thiếu
                                            </button>
                                        </div>
                                    </div>


                                    <!-- 3. Phương thức -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phương thức thanh toán</label>
                                            <select name="method" class="form-control">
                                                <option value="CASH" <?= (isset($old['method']) && $old['method'] == 'CASH') ? 'selected' : '' ?>>Tiền mặt</option>
                                                <option value="TRANSFER" <?= (isset($old['method']) && $old['method'] == 'TRANSFER') ? 'selected' : '' ?>>Chuyển khoản ngân hàng</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <!-- 4. Mã chứng từ (Bắt buộc) -->
                                <div class="form-group">
                                    <label>Mã chứng từ / Số tham chiếu <span class="text-danger">*</span></label>
                                    <input type="text" name="receipt_no"
                                           class="form-control <?= isset($errors['receipt_no']) ? 'is-invalid' : '' ?>"
                                           placeholder="VD: FT2311001, Mã giao dịch NH..."
                                           value="<?= htmlspecialchars($old['receipt_no'] ?? '') ?>">
                                   
                                    <!-- Hiển thị lỗi -->
                                    <?php if (isset($errors['receipt_no'])): ?>
                                        <span class="error invalid-feedback d-block"><?= $errors['receipt_no'] ?></span>
                                    <?php endif; ?>
                                </div>


                                <!-- 5. Ghi chú -->
                                <div class="form-group">
                                    <label>Ghi chú / Lý do thu</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="VD: Thu tiền cọc lần 1..."><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
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
        const inputAmount = $('#input-amount');
        const clientErrorMsg = $('#client-error-msg');
        let maxAmount = 0;


        // Nếu đã có chọn booking từ trước (trường hợp validate lỗi trả về), cần kích hoạt lại logic maxAmount
        if ($('#select-booking').val()) {
             const selectedOption = $('#select-booking').find(':selected');
             const remain = parseFloat(selectedOption.data('remain')) || 0;
             if (remain > 0) {
                 maxAmount = remain;
                 inputAmount.attr('max', remain);
                 $('#btn-fill-remain').text('Điền nhanh: ' + new Intl.NumberFormat('vi-VN').format(remain) + ' đ').show();
                 $('#btn-fill-remain').off('click').on('click', function() {
                    inputAmount.val(remain);
                    inputAmount.trigger('input');
                });
             }
        }


        // 1. Khi chọn booking -> Cập nhật giới hạn tiền (max)
        $('#select-booking').change(function() {
            const selectedOption = $(this).find(':selected');
            const remain = parseFloat(selectedOption.data('remain')) || 0;
            maxAmount = remain;


            // Reset input nếu người dùng chọn booking khác
            inputAmount.val('');
            clientErrorMsg.hide();
            inputAmount.attr('max', remain);


            if (remain > 0) {
                $('#btn-fill-remain').text('Điền nhanh: ' + new Intl.NumberFormat('vi-VN').format(remain) + ' đ').show();
               
                $('#btn-fill-remain').off('click').on('click', function() {
                    inputAmount.val(remain);
                    inputAmount.trigger('input');
                });
            } else {
                $('#btn-fill-remain').hide();
            }
        });


        // 2. Validate ngay khi nhập liệu (Client side)
        inputAmount.on('input', function() {
            const currentVal = parseFloat($(this).val());
           
            if (currentVal < 0) {
                $(this).val(0);
            }


            if (maxAmount > 0 && currentVal > maxAmount) {
                clientErrorMsg.text('Số tiền vượt quá số nợ còn lại (' + new Intl.NumberFormat('vi-VN').format(maxAmount) + ' đ)').show();
                $(this).addClass('is-invalid');
            } else {
                clientErrorMsg.hide();
                // Chỉ xóa class invalid nếu không có lỗi từ server đang hiển thị
                <?php if (!isset($errors['amount'])): ?>
                    $(this).removeClass('is-invalid');
                <?php endif; ?>
            }
        });
    });
</script>

