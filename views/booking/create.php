<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-cart-plus text-success"></i> Tạo Booking Mới</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <form action="<?= route('booking.store') ?>" method="POST" id="bookingForm">
                <div class="row">
                    <!-- Cột Trái: Thông tin người đặt -->
                    <div class="col-md-4">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin Đặt chỗ</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Chọn Đợt Khởi Hành <span class="text-danger">*</span></label>
                                    <select name="departure_id" class="form-control select2" required>
                                        <option value="">-- Chọn lịch --</option>
                                        <?php foreach ($departures as $dep): ?>
                                            <option value="<?= $dep['id'] ?>" 
                                                <?= (isset($old['departure_id']) && $old['departure_id'] == $dep['id']) ? 'selected' : '' ?>>
                                                [<?= date('d/m/Y', strtotime($dep['start_date'])) ?>] 
                                                <?= htmlspecialchars($dep['tour_name']) ?> 
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Người liên hệ</label>
                                    <input type="text" name="contact_name" class="form-control" required value="<?= $old['contact_name'] ?? '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="contact_phone" class="form-control" required value="<?= $old['contact_phone'] ?? '' ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= $old['contact_email'] ?? '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="3"><?= $old['note'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải: Danh sách khách -->
                    <div class="col-md-8">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Danh sách Khách hàng</h3>
                                <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnAddTraveler">
                                    <i class="fas fa-user-plus"></i> Thêm khách
                                </button>
                            </div>
                            <div class="card-body table-responsive p-0" style="height: 500px;">
                                <table class="table table-head-fixed text-nowrap">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 40%">Họ tên <span class="text-danger">*</span></th>
                                            <th style="width: 20%">Giới tính</th>
                                            <th style="width: 25%">Ngày sinh</th>
                                            <th style="width: 10%">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="travelerContainer">
                                        <!-- Dòng khách hàng sẽ được JS thêm vào đây -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light text-right">
                                <div class="mb-2">Tổng số khách: <b id="paxCountDisplay">0</b></div>
                                <small class="text-muted d-block mb-2">Hệ thống sẽ tự động tính giá dựa trên ngày sinh khi bạn bấm Lưu.</small>
                                <button type="submit" class="btn btn-success btn-lg font-weight-bold">
                                    <i class="fas fa-check-circle"></i> XÁC NHẬN ĐẶT CHỖ
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
    let travelerIndex = 0;

    // Template dòng khách đơn giản (Không có logic tính tuổi phức tạp)
    function createTravelerRow(index, data = {}) {
        const fullName = data.full_name || '';
        const dob = data.dob || '';
        
        return `
            <tr id="row-${index}">
                <td class="align-middle text-center row-number"></td>
                <td>
                    <input type="text" name="travelers[${index}][full_name]" class="form-control" placeholder="Nhập họ tên" required value="${fullName}">
                </td>
                <td>
                    <select name="travelers[${index}][gender]" class="form-control">
                        <option value="MALE">Nam</option>
                        <option value="FEMALE">Nữ</option>
                        <option value="OTHER">Khác</option>
                    </select>
                </td>
                <td>
                    <input type="date" name="travelers[${index}][dob]" class="form-control" value="${dob}">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // Các hàm hỗ trợ thêm/xóa dòng (UI Logic bắt buộc phải có JS để dynamic form hoạt động)
    $('#btnAddTraveler').click(function() {
        $('#travelerContainer').append(createTravelerRow(travelerIndex));
        travelerIndex++;
        updateCount();
    });

    window.removeRow = function(index) {
        $(`#row-${index}`).remove();
        updateCount();
    }

    function updateCount() {
        let count = 0;
        $('#travelerContainer tr').each(function(idx) {
            $(this).find('.row-number').text(idx + 1);
            count++;
        });
        $('#paxCountDisplay').text(count);
    }

    // Khởi tạo mặc định
    $(document).ready(function() {
        <?php if (!empty($old_travelers)): ?>
            const oldData = <?= json_encode($old_travelers) ?>;
            oldData.forEach(item => {
                $('#travelerContainer').append(createTravelerRow(travelerIndex, item));
                travelerIndex++;
            });
        <?php else: ?>
            $('#btnAddTraveler').click(); // Mặc định thêm 1 dòng
        <?php endif; ?>
        updateCount();
    });
</script>