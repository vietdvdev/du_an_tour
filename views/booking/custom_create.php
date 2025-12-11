<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<?php
$old = $old ?? ($_SESSION['old'] ?? []);
$errors = $errors ?? ($_SESSION['errors'] ?? []);
unset($_SESSION['old'], $_SESSION['errors']);
?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-magic text-primary"></i> Tạo Tour Thiết Kế Riêng</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('booking.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="container-fluid">
           
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>
           
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['general'][0] ?>
                </div>
            <?php endif; ?>


            <form action="<?= route('booking.custom.store') ?>" method="POST" id="customBookingForm">
                <div class="row">
                   
                    <!-- CỘT TRÁI: THÔNG TIN TOUR & LIÊN HỆ -->
                    <div class="col-md-5">
                       
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">1. Thông tin Tour & Tài chính</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Tên Tour / Hành trình <span class="text-danger">*</span></label>
                                    <input type="text" name="custom_tour_name" class="form-control"
                                           placeholder="VD: Tour Gia đình anh Nam - Phú Quốc 3N2Đ"
                                           required value="<?= htmlspecialchars($old['custom_tour_name'] ?? '') ?>">
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày đi <span class="text-danger">*</span></label>
                                            <input type="date" name="custom_start_date" class="form-control" required value="<?= $old['custom_start_date'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày về <span class="text-danger">*</span></label>
                                            <input type="date" name="custom_end_date" class="form-control" required value="<?= $old['custom_end_date'] ?? '' ?>">
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <label>Tổng giá trị hợp đồng (VNĐ) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="custom_total_amount" id="total_amount" class="form-control font-weight-bold text-success"
                                               placeholder="Nhập tổng tiền chốt với khách" required min="0"
                                               value="<?= $old['custom_total_amount'] ?? '' ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                    </div>
                                </div>


                                <!-- [MỚI] Ô NHẬP TIỀN CỌC -->
                                <div class="form-group">
                                    <label>Số tiền cọc trước (VNĐ) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="custom_deposit_amount" id="deposit_amount" class="form-control font-weight-bold text-warning"
                                               placeholder="Nhập số tiền khách đã cọc" required min="0"
                                               value="<?= $old['custom_deposit_amount'] ?? '' ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                    </div>
                                    <small class="text-danger mt-1 d-block">
                                        <i class="fas fa-exclamation-circle"></i> Yêu cầu cọc tối thiểu 10% giá trị hợp đồng.
                                    </small>
                                </div>


                            </div>
                        </div>


                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">2. Thông tin Người liên hệ</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" class="form-control" required value="<?= htmlspecialchars($old['contact_name'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_phone" class="form-control" required value="<?= htmlspecialchars($old['contact_phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($old['contact_email'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="2"><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- CỘT PHẢI: DANH SÁCH KHÁCH -->
                    <div class="col-md-7">
                        <div class="card card-outline card-info h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">3. Danh sách Đoàn</h3>
                                <button type="button" class="btn btn-sm btn-info ml-auto" id="btnAddTraveler">
                                    <i class="fas fa-user-plus"></i> Thêm khách
                                </button>
                            </div>
                            <div class="card-body table-responsive p-0" style="height: 500px;">
                                <table class="table table-head-fixed text-nowrap">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 45%">Họ tên</th>
                                            <th style="width: 20%">Giới tính</th>
                                            <th style="width: 20%">Ngày sinh</th>
                                            <th style="width: 10%">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="travelerContainer"></tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light text-right">
                                <div class="mb-2">Tổng số khách: <b id="paxCountDisplay">0</b></div>
                                <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5">
                                    <i class="fas fa-paper-plane"></i> TẠO TOUR & BOOKING
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>


<script>
    // JS kiểm tra nhanh (Client-side validation)
    document.getElementById('customBookingForm').addEventListener('submit', function(e) {
        const total = parseFloat(document.getElementById('total_amount').value) || 0;
        const deposit = parseFloat(document.getElementById('deposit_amount').value) || 0;
       
        if (total > 0 && deposit < (total * 0.1)) {
            e.preventDefault(); // Chặn submit
            alert('Lỗi: Số tiền cọc phải lớn hơn hoặc bằng 10% tổng giá trị (' + new Intl.NumberFormat('vi-VN').format(total * 0.1) + ' VNĐ).');
            document.getElementById('deposit_amount').focus();
        }
    });


    // ... (Phần script thêm dòng khách giữ nguyên) ...
    let travelerIndex = 0;
    function createTravelerRow(index) {
        return `
            <tr id="row-${index}">
                <td class="align-middle text-center row-number"></td>
                <td><input type="text" name="travelers[${index}][full_name]" class="form-control" placeholder="Nhập họ tên" required></td>
                <td>
                    <select name="travelers[${index}][gender]" class="form-control">
                        <option value="MALE">Nam</option>
                        <option value="FEMALE">Nữ</option>
                    </select>
                </td>
                <td><input type="date" name="travelers[${index}][dob]" class="form-control"></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${index})"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
    }
    $('#btnAddTraveler').click(function() {
        $('#travelerContainer').append(createTravelerRow(travelerIndex++));
        updateCount();
    });
    window.removeRow = function(index) { $(`#row-${index}`).remove(); updateCount(); }
    function updateCount() {
        let count = 0;
        $('#travelerContainer tr').each(function(idx) {
            $(this).find('.row-number').text(idx + 1);
            count++;
        });
        $('#paxCountDisplay').text(count);
    }
    $(document).ready(function() { $('#btnAddTraveler').click(); updateCount(); });
</script>

