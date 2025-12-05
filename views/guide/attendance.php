<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Tính toán số ngày của tour để hiển thị dropdown
$start = strtotime($departure['start_date']);
$end   = strtotime($departure['end_date']);
$datediff = $end - $start;
$numDays = round($datediff / (60 * 60 * 24)) + 1;

// Kiểm tra biến quyền sửa (được truyền từ Controller)
$isEditable = $isEditable ?? false;
$statusMessage = $statusMessage ?? '';
?>

<div class="content-wrapper">
    <!-- Header Thông tin Tour -->
    <div class="content-header pb-1">
        <div class="container-fluid">
            
            <!-- Cảnh báo nếu không được điểm danh -->
            <?php if (!$isEditable): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-lock"></i> <?= htmlspecialchars($statusMessage) ?> (Chế độ chỉ xem)
                </div>
            <?php endif; ?>

            <div class="card bg-light">
                <div class="card-body pt-2 pb-2">
                    <h5 class="text-primary font-weight-bold m-0">
                        <?= htmlspecialchars($departure['tour_name']) ?>
                        <span class="badge badge-warning text-white" style="font-size: 0.6em; vertical-align: middle;">
                            <?= $numDays ?> Ngày
                        </span>
                    </h5>
                    <small class="text-muted">
                        <i class="far fa-calendar-alt"></i> 
                        <?= date('d/m/Y', $start) ?> - <?= date('d/m/Y', $end) ?>
                    </small>
                    
                    <div class="mt-3">
                        <label class="mr-2 text-dark"><i class="fas fa-map-marker-alt text-danger"></i> Điểm danh lúc:</label>
                        
                        <!-- Dropdown chọn điểm điểm danh -->
                        <select class="form-control form-control-sm d-inline-block w-auto font-weight-bold border-primary shadow-sm" 
                                id="select-checkpoint" 
                                onchange="changeCheckpoint(this)"
                                style="max-width: 100%;">
                            
                            <?php 
                            // Vòng lặp tạo option cho từng ngày
                            for ($i = 1; $i <= $numDays; $i++): 
                                $currentDate = date('d/m', strtotime("+" . ($i - 1) . " days", $start));
                            ?>
                                <optgroup label="📅 Ngày <?= $i ?> (<?= $currentDate ?>)">
                                    <?php if($i == 1): ?>
                                        <option value="D<?= $i ?>_PICKUP" <?= $checkpoint == "D{$i}_PICKUP" || $checkpoint == 'PICKUP' ? 'selected' : '' ?>>
                                            📍 Ngày <?= $i ?>: Đón khách (Lên xe)
                                        </option>
                                    <?php else: ?>
                                        <option value="D<?= $i ?>_START" <?= $checkpoint == "D{$i}_START" ? 'selected' : '' ?>>
                                            🚩 Ngày <?= $i ?>: Tập trung sáng
                                        </option>
                                    <?php endif; ?>

                                    <option value="D<?= $i ?>_LUNCH" <?= $checkpoint == "D{$i}_LUNCH" ? 'selected' : '' ?>>
                                        🍽️ Ngày <?= $i ?>: Ăn trưa
                                    </option>
                                    
                                    <option value="D<?= $i ?>_DINNER" <?= $checkpoint == "D{$i}_DINNER" ? 'selected' : '' ?>>
                                        🍲 Ngày <?= $i ?>: Ăn tối
                                    </option>

                                    <option value="D<?= $i ?>_HOTEL" <?= $checkpoint == "D{$i}_HOTEL" ? 'selected' : '' ?>>
                                        🏨 Ngày <?= $i ?>: Về khách sạn
                                    </option>
                                </optgroup>
                            <?php endfor; ?>

                            <optgroup label="Khác">
                                <option value="OTHER" <?= $checkpoint == 'OTHER' ? 'selected' : '' ?>>❓ Phát sinh</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách khách -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Thống kê nhanh -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center bg-white p-2 rounded shadow-sm border">
                    <span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Có: <span id="count-present">0</span></span>
                    <span class="text-warning font-weight-bold"><i class="fas fa-exclamation-circle"></i> Trễ: <span id="count-late">0</span></span>
                    <span class="text-danger font-weight-bold"><i class="fas fa-times-circle"></i> Vắng: <span id="count-absent">0</span></span>
                    <span class="text-secondary font-weight-bold">Tổng: <?= count($travelers) ?></span>
                </div>
            </div>

            <div class="row">
                <?php foreach ($travelers as $t): 
                    $currentStatus = $statusMap[$t['id']] ?? ''; 
                    
                    // Nếu không được sửa, thêm class disabled để làm mờ nút
                    $disabledAttr = $isEditable ? '' : 'disabled';
                    $disabledClass = $isEditable ? '' : 'disabled';
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card card-outline card-primary mb-2 shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Thông tin khách -->
                                <div style="width: 55%;">
                                    <h6 class="font-weight-bold mb-1 text-truncate">
                                        <?= htmlspecialchars($t['full_name']) ?>
                                    </h6>
                                    <small class="text-muted d-block">
                                        Năm sinh: <?= date('Y', strtotime($t['dob'])) ?> 
                                        (<?= $t['gender'] == 'MALE' ? 'Nam' : 'Nữ' ?>)
                                    </small>
                                    <small class="text-muted text-truncate d-block">
                                        Code: <b><?= $t['booking_code'] ?></b>
                                    </small>
                                </div>

                                <!-- Nút điểm danh -->
                                <div class="text-right" style="width: 45%;">
                                    <div class="btn-group btn-group-sm btn-group-toggle w-100" data-toggle="buttons">
                                        <!-- Vắng -->
                                        <label class="btn btn-outline-danger px-1 <?= $currentStatus == 'ABSENT' ? 'active' : '' ?> <?= $disabledClass ?>" 
                                               <?php if($isEditable): ?>onclick="submitCheckIn(<?= $t['id'] ?>, 'ABSENT')"<?php endif; ?>>
                                            <input type="radio" autocomplete="off" <?= $disabledAttr ?>> Vắng
                                        </label>
                                        
                                        <!-- Trễ -->
                                        <label class="btn btn-outline-warning px-1 <?= $currentStatus == 'LATE' ? 'active' : '' ?> <?= $disabledClass ?>"
                                               <?php if($isEditable): ?>onclick="submitCheckIn(<?= $t['id'] ?>, 'LATE')"<?php endif; ?>>
                                            <input type="radio" autocomplete="off" <?= $disabledAttr ?>> Trễ
                                        </label>

                                        <!-- Có mặt -->
                                        <label class="btn btn-outline-success px-1 <?= $currentStatus == 'PRESENT' ? 'active' : '' ?> <?= $disabledClass ?>"
                                               <?php if($isEditable): ?>onclick="submitCheckIn(<?= $t['id'] ?>, 'PRESENT')"<?php endif; ?>>
                                            <input type="radio" autocomplete="off" <?= $disabledAttr ?>> Có
                                        </label>
                                    </div>
                                    
                                    <!-- Trạng thái đã check -->
                                    <div class="mt-1 text-right">
                                        <small class="status-time font-weight-bold" id="time-<?= $t['id'] ?>" style="font-size: 0.75rem;">
                                            <?= !empty($currentStatus) ? '<span class="text-success"><i class="fas fa-check"></i> Đã check</span>' : '<span class="text-secondary">--:--</span>' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($travelers)): ?>
                <div class="alert alert-info text-center mt-3">
                    <i class="fas fa-search"></i> Chưa có hành khách nào trong danh sách.
                </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<script>
    // Hàm cập nhật số lượng thống kê
    function updateStats() {
        $('#count-present').text($('.btn-outline-success.active').length);
        $('#count-late').text($('.btn-outline-warning.active').length);
        $('#count-absent').text($('.btn-outline-danger.active').length);
    }

    // Hàm chuyển đổi địa điểm (reload trang với tham số mới)
    function changeCheckpoint(select) {
        const val = select.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('checkpoint', val);
        
        // Giữ nguyên departure_id
        if (!currentUrl.searchParams.has('departure_id')) {
            currentUrl.searchParams.set('departure_id', <?= $departure['id'] ?>);
        }
        
        // Hiệu ứng mờ màn hình để biết đang tải
        $('body').css('opacity', '0.6');
        window.location.href = currentUrl.toString();
    }

    // Hàm gửi Ajax Check-in
    function submitCheckIn(travelerId, status) {
        // Chặn ở phía client nếu không được phép sửa
        <?php if(!$isEditable): ?>
            alert('Tour này không trong thời gian diễn ra nên không thể điểm danh.');
            return;
        <?php endif; ?>

        const departureId = <?= $departure['id'] ?>;
        const checkpoint = $('#select-checkpoint').val();

        $.ajax({
            url: '<?= route('guide.attendance.check') ?>',
            type: 'POST',
            data: {
                departure_id: departureId,
                traveler_id: travelerId,
                status: status,
                checkpoint: checkpoint
            },
            success: function(res) {
                if(res.success) {
                    // Cập nhật giao diện khi thành công
                    $('#time-' + travelerId).html('<span class="text-success">' + res.time + '</span>');
                    updateStats();
                    
                    // Rung nhẹ điện thoại (nếu hỗ trợ)
                    if (navigator.vibrate) navigator.vibrate(50);
                } else {
                    alert('Lỗi: ' + res.message);
                }
            },
            error: function() {
                alert('Mất kết nối mạng! Vui lòng kiểm tra lại 3G/Wifi.');
            }
        });
    }

    // Chạy thống kê khi tải trang
    $(document).ready(function() {
        updateStats();
    });
</script>