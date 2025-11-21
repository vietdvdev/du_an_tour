<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h3 class="card-title"><i class="fas fa-images"></i> Thư viện ảnh</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= route('tour.update.images', ['id' => $tourId]) ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tải lên ảnh mới</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="images[]" class="custom-file-input" id="tourImages" multiple accept="image/*">
                        <label class="custom-file-label" for="tourImages">Chọn file...</label>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit">Upload</button>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <?php foreach ($images as $img): ?>
                <div class="col-md-3 mb-3">
                    <div class="card tour-image-card border">
                        <div style="height: 150px; overflow: hidden;">
                            <img src="<?= public_url($img['url']) ?>" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="card-body p-2 text-center">
                            <?php if($img['is_cover']): ?>
                                <span class="badge badge-success">Ảnh bìa</span>
                            <?php else: ?>
                                <button type="submit" name="set_cover_id" value="<?= $img['id'] ?>" class="btn btn-xs btn-outline-primary">Đặt bìa</button>
                            <?php endif; ?>
                            
                            <button type="submit" name="delete_image_id" value="<?= $img['id'] ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Xóa ảnh này?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title"><i class="fas fa-list-ol"></i> Lịch trình chi tiết</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= route('tour.update.itinerary', ['id' => $tourId]) ?>">
            
            <div id="itinerary-container">
                <?php 
                $displayItinerary = !empty($itinerary) ? $itinerary : [];
                ?>

                <?php foreach ($displayItinerary as $k => $day): $dayNo = $k + 1; ?>
                <div class="itinerary-item border p-3 mb-3 bg-light rounded">
                    <input type="hidden" name="itineraries[<?= $k ?>][id]" value="<?= $day['id'] ?>">
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="m-0 text-primary">Ngày <span class="day-number"><?= $dayNo ?></span></h5>
                        
                        <?php if (isset($day['id']) && $day['id'] > 0): ?>
                            <button type="submit" 
                                    formaction="<?= route('tour.itinerary.delete_item', ['id' => $day['id']]) ?>" 
                                    formnovalidate
                                    onclick="return confirm('Xác nhận xóa ngày này khỏi Database ngay lập tức?')"
                                    class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Xóa (Server)
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="this.closest('.itinerary-item').remove()">
                                <i class="fas fa-times"></i> Bỏ dòng này
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Tiêu đề</label>
                        <input type="text" name="itineraries[<?= $k ?>][title]" class="form-control" 
                               value="<?= htmlspecialchars($day['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Nội dung</label>
                        <textarea name="itineraries[<?= $k ?>][content]" class="form-control" rows="3"><?= htmlspecialchars($day['content'] ?? '') ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-info" id="btn-add-day"><i class="fas fa-plus"></i> Thêm dòng nhập</button>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Lưu Tất Cả</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('btn-add-day').addEventListener('click', function() {
    const container = document.getElementById('itinerary-container');
    const index = new Date().getTime(); // Index ngẫu nhiên
    const html = `
        <div class="itinerary-item border p-3 mb-3 bg-white rounded border-info">
            <input type="hidden" name="itineraries[${index}][id]" value="0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="m-0 text-info">Ngày Mới (Chưa lưu)</h5>
                <button type="button" class="btn btn-sm btn-secondary" onclick="this.closest('.itinerary-item').remove()">
                    <i class="fas fa-times"></i> Bỏ dòng này
                </button>
            </div>
            <div class="form-group">
                <label>Tiêu đề</label>
                <input type="text" name="itineraries[${index}][title]" class="form-control" placeholder="Nhập tiêu đề..." required>
            </div>
            <div class="form-group">
                <textarea name="itineraries[${index}][content]" class="form-control" rows="3" placeholder="Nội dung..."></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
});
</script>

<script>
    // Xử lý hiển thị tên file khi chọn ảnh
    document.getElementById('tourImages').addEventListener('change', function(e) {
        // 1. Lấy danh sách các file đã chọn
        var files = e.target.files;
        var label = e.target.nextElementSibling; // Thẻ label ngay kế bên input

        // 2. Xử lý logic hiển thị
        if (files && files.length > 1) {
            // Nếu chọn nhiều file -> Hiển thị số lượng
            label.innerText = files.length + ' file đã được chọn';
        } else if (files && files.length === 1) {
            // Nếu chọn 1 file -> Hiển thị tên file đó
            label.innerText = files[0].name;
        } else {
            // Không chọn gì -> Trả về mặc định
            label.innerText = 'Chọn file...';
        }
    });
</script>