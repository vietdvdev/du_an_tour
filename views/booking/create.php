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
                    <!-- Cột Trái: Thông tin chung -->
                    <div class="col-md-4">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin Đặt chỗ</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Chọn Đợt Khởi Hành <span class="text-danger">*</span></label>
                                    <select name="departure_id" class="form-control" required>
                                        <option value="">-- Chọn lịch --</option>
                                        <?php foreach ($departures as $dep): ?>
                                            <option value="<?= $dep['id'] ?>" 
                                                <?= (isset($old['departure_id']) && $old['departure_id'] == $dep['id']) ? 'selected' : '' ?>>
                                                [<?= date('d/m/Y', strtotime($dep['start_date'])) ?>] 
                                                <?= htmlspecialchars($dep['tour_name']) ?> 
                                                (Capacity: <?= $dep['capacity'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if(isset($errors['departure_id'])): ?><div class="text-danger small"><?= $errors['departure_id'] ?></div><?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Người liên hệ <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" class="form-control" placeholder="Họ tên người đặt" required value="<?= $old['contact_name'] ?? '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_phone" class="form-control" required value="<?= $old['contact_phone'] ?? '' ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= $old['contact_email'] ?? '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Ghi chú booking</label>
                                    <textarea name="note" class="form-control" rows="3"><?= $old['note'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải: Danh sách khách -->
                    <div class="col-md-8">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Danh sách Khách hàng (Travelers)</h3>
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
                                        <!-- JS sẽ render vào đây -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light text-right">
                                <div class="mb-2 font-weight-bold">Tổng số khách: <span id="paxCountDisplay" class="text-primary h4">0</span></div>
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

    // Template dòng khách
    function createTravelerRow(index, data = {}) {
        const fullName = data.full_name || '';
        const dob = data.dob || '';
        const gender = data.gender || 'MALE';
        
        return `
            <tr id="row-${index}">
                <td class="align-middle text-center row-number"></td>
                <td>
                    <input type="text" name="travelers[${index}][full_name]" class="form-control" placeholder="Nhập họ tên" required value="${fullName}">
                </td>
                <td>
                    <select name="travelers[${index}][gender]" class="form-control">
                        <option value="MALE" ${gender === 'MALE' ? 'selected' : ''}>Nam</option>
                        <option value="FEMALE" ${gender === 'FEMALE' ? 'selected' : ''}>Nữ</option>
                        <option value="OTHER" ${gender === 'OTHER' ? 'selected' : ''}>Khác</option>
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

    // Logic thêm/xóa dòng
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

    // Init
    $(document).ready(function() {
        <?php if (!empty($old_travelers)): ?>
            const oldData = <?= json_encode($old_travelers) ?>;
            oldData.forEach(item => {
                $('#travelerContainer').append(createTravelerRow(travelerIndex, item));
                travelerIndex++;
            });
        <?php else: ?>
            // Mặc định 1 dòng
            $('#btnAddTraveler').click();
        <?php endif; ?>
        updateCount();
    });
</script>