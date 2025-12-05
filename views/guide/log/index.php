<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Nhật ký Tour: <span class="text-primary font-weight-bold"><?= htmlspecialchars($departure['tour_code']) ?></span></h1>
                    <small><?= htmlspecialchars($departure['tour_name']) ?></small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('guide.my_tours') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">


            <!-- Thông báo -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
           
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>


            <div class="row">
                <!-- CỘT TRÁI: FORM GHI NHẬT KÝ -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline sticky-top" style="top: 20px; z-index: 1;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-pen mr-1"></i> Viết nhật ký mới</h3>
                        </div>
                        <form action="<?= route('guide.log.store') ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="departure_id" value="<?= $departure['id'] ?>">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Ngày ghi nhận</label>
                                    <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                               
                                <div class="form-group">
                                    <label>Mức độ sự việc</label>
                                    <select name="incident_level" class="form-control">
                                        <option value="NORMAL">🟢 Bình thường</option>
                                        <option value="WARNING">🟡 Có sự cố nhẹ (Warning)</option>
                                        <option value="CRITICAL">🔴 Nghiêm trọng (Critical)</option>
                                    </select>
                                </div>
                               
                                <div class="form-group">
                                    <label>Nội dung chi tiết</label>
                                    <textarea name="content" class="form-control" rows="4" placeholder="Mô tả diễn biến, sự cố hoặc ghi chú..." required></textarea>
                                </div>


                                <div class="form-group">
                                    <label>Đính kèm ảnh (nếu có)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="attachments[]" multiple accept="image/*" id="logAttachments">
                                        <label class="custom-file-label" for="logAttachments">Chọn ảnh...</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane"></i> Gửi Báo Cáo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- CỘT PHẢI: TIMELINE HIỂN THỊ LỊCH SỬ -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Diễn biến Tour</h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php if (empty($logs)): ?>
                                    <div class="time-label">
                                        <span class="bg-gray">Chưa có nhật ký nào được ghi nhận</span>
                                    </div>
                                <?php else: ?>
                                    <?php
                                    $currentDate = '';
                                    foreach ($logs as $log):
                                        // Gom nhóm theo ngày
                                        if ($currentDate != $log['log_date']):
                                            $currentDate = $log['log_date'];
                                    ?>
                                        <div class="time-label">
                                            <span class="bg-olive px-3"><?= date('d/m/Y', strtotime($currentDate)) ?></span>
                                        </div>
                                    <?php endif; ?>


                                    <div>
                                        <?php
                                            // Icon và màu sắc dựa trên mức độ
                                            $icon = 'fas fa-comment-dots';
                                            $bg = 'bg-primary';
                                            $badge = '';
                                           
                                            if ($log['incident_level'] == 'WARNING') {
                                                $icon = 'fas fa-exclamation-triangle';
                                                $bg = 'bg-warning';
                                                $badge = '<span class="badge badge-warning text-dark ml-2">Sự cố nhẹ</span>';
                                            }
                                            if ($log['incident_level'] == 'CRITICAL') {
                                                $icon = 'fas fa-bomb';
                                                $bg = 'bg-danger';
                                                $badge = '<span class="badge badge-danger ml-2">Nghiêm trọng</span>';
                                            }
                                        ?>
                                        <i class="<?= $icon ?> <?= $bg ?>"></i>
                                       
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> <?= date('H:i', strtotime($log['log_time'] ?? $log['created_at'])) ?></span>
                                           
                                            <h3 class="timeline-header">
                                                <span class="text-primary font-weight-bold">Ghi nhận lúc <?= date('H:i', strtotime($log['log_time'])) ?></span>
                                                <?= $badge ?>
                                            </h3>


                                            <div class="timeline-body">
                                                <div class="mb-2" style="white-space: pre-line;"><?= htmlspecialchars($log['content']) ?></div>
                                               
                                                <!-- Hiển thị ảnh đính kèm (Đã sửa dùng public_url) -->
                                                <?php
                                                $images = json_decode($log['attachments'] ?? '[]', true);
                                                if (!empty($images) && is_array($images)):
                                                ?>
                                                    <div class="row mt-2">
                                                        <?php foreach($images as $img): ?>
                                                            <div class="col-4 col-sm-3 mb-2">
                                                                <a href="<?= public_url($img) ?>" target="_blank">
                                                                    <img src="<?= public_url($img) ?>" class="img-fluid rounded border" alt="Attachment"
                                                                         style="height: 100px; width: 100%; object-fit: cover;">
                                                                </a>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                   
                                    <div>
                                        <i class="fas fa-clock bg-gray"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </section>
</div>


<?php include __DIR__ . '/../../layout/footer.php'; ?>


<script>
    // Hiển thị tên file khi upload
    $(".custom-file-input").on("change", function() {
        var files = $(this)[0].files;
        var label = 'Chọn ảnh...';
        if(files.length > 0){
             label = files.length + ' file đã chọn';
             if(files.length === 1) label = files[0].name;
        }
        $(this).siblings(".custom-file-label").addClass("selected").html(label);
    });
</script>


