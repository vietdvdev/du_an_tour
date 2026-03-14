<?php
// File này được gọi từ views/tour/edit.php
// Các biến $tour, $itinerary, $images đã được truyền vào

$tourId = $tour['id'] ?? 0;
$itineraryList = $itinerary ?? [];
$imageList = $images ?? [];

// Giả định ngày tiếp theo là max(day_no) + 1
$nextDayNo = 1;
if (!empty($itineraryList)) {
    $lastDay = end($itineraryList);
    $nextDayNo = ($lastDay['day_no'] ?? 0) + 1;
}
?>

<div class="row">
    <!-- Phần 1: QUẢN LÝ LỊCH TRÌNH (tour_itinerary) -->
    <div class="col-md-7">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Quản lý Lịch Trình (<?= count($itineraryList) ?> Ngày)</h3>
            </div>
            <div class="card-body p-0">
                
                <!-- Hiển thị Lịch trình đã có -->
                <?php if (!empty($itineraryList)): ?>
                    <ul class="list-group list-group-flush" id="itinerary-list">
                        <?php foreach ($itineraryList as $day): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Ngày <?= htmlspecialchars($day['day_no']) ?>:</strong> <?= htmlspecialchars($day['title']) ?>
                                    <p class="mb-0 text-muted" style="font-size: 0.85rem;"><?= htmlspecialchars(mb_strimwidth($day['content'], 0, 100, '...')) ?></p>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-warning btn-edit-day" data-id="<?= $day['id'] ?>">Sửa</button>
                                    <button type="button" class="btn btn-outline-danger btn-delete-day" data-id="<?= $day['id'] ?>">Xóa</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">Chưa có lịch trình nào được thiết lập.</div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Form Thêm/Sửa Ngày Mới -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title" id="itinerary-form-title">Thêm Ngày Mới</h3>
            </div>
            <form id="itinerary-form" method="POST" action="<?= route('tour.update.itinerary', ['id' => $tourId]) ?>">
                <input type="hidden" name="day_id" id="day_id" value="">
                <input type="hidden" name="action" id="day_action" value="add">
                
                <div class="card-body">
                    <div class="row">
                        <!-- DAY NO -->
                        <div class="form-group col-md-3">
                            <label for="day_no">Ngày số <span class="text-danger">*</span></label>
                            <input type="number" name="day_no" id="day_no" class="form-control" 
                                value="<?= $nextDayNo ?>" min="1" required readonly>
                            <small class="form-text text-muted">Hệ thống tự động đánh số.</small>
                        </div>
                        
                        <!-- TITLE -->
                        <div class="form-group col-md-9">
                            <label for="day_title">Tiêu đề ngày</label>
                            <input type="text" name="title" id="day_title" class="form-control" maxlength="255" required>
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div class="form-group">
                        <label for="day_content">Chi tiết hoạt động <span class="text-danger">*</span></label>
                        <textarea name="content" id="day_content" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Lưu Lịch Trình</button>
                    <button type="button" class="btn btn-default" onclick="resetItineraryForm()">Hủy</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Phần 2: QUẢN LÝ HÌNH ẢNH (tour_image) -->
    <div class="col-md-5">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Quản lý Hình Ảnh (<?= count($imageList) ?> Ảnh)</h3>
            </div>
            <div class="card-body">
                
                <!-- Form Upload -->
                <form id="image-upload-form" method="POST" enctype="multipart/form-data" 
                    action="<?= route('tour.update.images', ['id' => $tourId]) ?>">
                    <div class="form-group">
                        <label for="image_file">Tải lên Ảnh mới</label>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="image_caption">Chú thích ảnh</label>
                        <input type="text" name="caption" id="image_caption" class="form-control" maxlength="255">
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-upload"></i> Tải Ảnh lên
                    </button>
                </form>

                <hr>

                <!-- Thư viện Ảnh đã có -->
                <h5 class="mt-4">Thư viện (Kéo xuống để xem)</h5>
                <div id="image-gallery-preview" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($imageList)): ?>
                        <div class="row">
                            <?php foreach ($imageList as $img): ?>
                                <div class="col-6 mb-3 position-relative">
                                    <img src="<?= htmlspecialchars($img['url']) ?>" 
                                         alt="<?= htmlspecialchars($img['caption'] ?? 'Ảnh') ?>" 
                                         class="img-fluid rounded" style="height: 100px; object-fit: cover;">
                                         
                                    <?php if (($img['is_cover'] ?? 0) == 1): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-1">Bìa</span>
                                    <?php endif; ?>
                                    
                                    <div class="mt-1 d-flex justify-content-between">
                                        <!-- Nút đặt ảnh bìa -->
                                        <button type="button" class="btn btn-sm <?= ($img['is_cover'] ?? 0) == 1 ? 'btn-danger' : 'btn-outline-primary' ?> btn-set-cover" data-id="<?= $img['id'] ?>">
                                            <i class="bi bi-star"></i> Bìa
                                        </button>
                                        <!-- Nút xóa ảnh -->
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-image" data-id="<?= $img['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted p-3">Chưa có ảnh nào.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Giả lập hàm AJAX để xử lý sự kiện
    
    // Khởi tạo form Thêm/Sửa Lịch trình
    function resetItineraryForm() {
        document.getElementById('itinerary-form-title').innerText = 'Thêm Ngày Mới';
        document.getElementById('itinerary-form').reset();
        document.getElementById('day_id').value = '';
        document.getElementById('day_action').value = 'add';
        // Đặt lại day_no tự động
        let lastDayNo = <?= $nextDayNo - 1 ?>;
        document.getElementById('day_no').value = lastDayNo + 1;
        document.getElementById('day_no').readOnly = true; // Chỉ chỉnh sửa khi thêm mới
    }
    
    // Hàm xử lý nút Sửa (Giả lập)
    document.querySelectorAll('.btn-edit-day').forEach(button => {
        button.addEventListener('click', function() {
            const dayId = this.getAttribute('data-id');
            // GIẢ LẬP: Lấy dữ liệu ngày theo AJAX (cần code backend)
            const mockData = {
                id: dayId,
                day_no: 3, // Giả sử
                title: 'Tham quan Vịnh Hạ Long',
                content: 'Lên du thuyền, ăn trưa, chèo kayak.'
            }; 
            
            document.getElementById('itinerary-form-title').innerText = 'Sửa Ngày: ' + mockData.title;
            document.getElementById('day_id').value = mockData.id;
            document.getElementById('day_action').value = 'update';
            document.getElementById('day_no').value = mockData.day_no;
            document.getElementById('day_title').value = mockData.title;
            document.getElementById('day_content').value = mockData.content;
            document.getElementById('day_no').readOnly = false; // Cho phép sửa số ngày khi sửa
        });
    });

    // Hàm xử lý Xóa Ngày (Giả lập)
    document.querySelectorAll('.btn-delete-day').forEach(button => {
        button.addEventListener('click', function() {
            const dayId = this.getAttribute('data-id');
            if (confirm(`Bạn có chắc chắn muốn xóa Ngày số ${dayId}?`)) {
                // THỰC HIỆN AJAX/POST XÓA (Cần code backend cho TourController::deleteItinerary)
                console.log('Đã gửi yêu cầu xóa ngày ID:', dayId);
            }
        });
    });
    
    // Hàm xử lý Đặt ảnh bìa (Giả lập)
    document.querySelectorAll('.btn-set-cover').forEach(button => {
        button.addEventListener('click', function() {
            const imageId = this.getAttribute('data-id');
            if (confirm('Đặt ảnh này làm ảnh bìa chính?')) {
                // THỰC HIỆN AJAX/POST ĐẶT BÌA (Cần code backend cho TourController::setCoverImage)
                console.log('Đã gửi yêu cầu đặt ảnh ID:', imageId, 'làm bìa');
            }
        });
    });

    // Hàm xử lý Xóa ảnh (Giả lập)
    document.querySelectorAll('.btn-delete-image').forEach(button => {
        button.addEventListener('click', function() {
            const imageId = this.getAttribute('data-id');
            if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
                // THỰC HIỆN AJAX/POST XÓA ẢNH (Cần code backend cho TourController::deleteImage)
                console.log('Đã gửi yêu cầu xóa ảnh ID:', imageId);
            }
        });
    });
</script>