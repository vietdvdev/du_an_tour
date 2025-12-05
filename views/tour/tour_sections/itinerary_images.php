<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h3 class="card-title"><i class="fas fa-images"></i> Thư viện ảnh</h3>
    </div>
    <div class="card-body">
        <!-- SỬA: Thêm dấu = và dùng $tour['id'] -->
        <form method="POST" action="<?= route('tour.update.images', ['id' => $tour['id']]) ?>" enctype="multipart/form-data">
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
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $img): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card tour-image-card border h-100">
                            <div style="height: 150px; overflow: hidden; background: #eee;">
                                <!-- SỬA: Dùng trực tiếp url nếu đã lưu đường dẫn đầy đủ, hoặc thêm / nếu cần -->
                                <img src="<?= htmlspecialchars(public_url($img['url'])) ?>" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="card-body p-2 text-center">
                                <?php if($img['is_cover']): ?>
                                    <span class="badge badge-success w-100 py-2">Ảnh bìa</span>
                                <?php else: ?>
                                    <button type="submit" name="set_cover_id" value="<?= $img['id'] ?>" class="btn btn-xs btn-outline-primary mb-1">Đặt bìa</button>
                                <?php endif; ?>
                                
                                <button type="submit" name="delete_image_id" value="<?= $img['id'] ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Xóa ảnh này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">Chưa có hình ảnh nào.</div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title"><i class="fas fa-list-ol"></i> Lịch trình chi tiết</h3>
    </div>
    <div class="card-body">
        <!-- SỬA: Thêm dấu = và dùng $tour['id'] -->
        <form method="POST" action="<?= route('tour.update.itinerary', ['id' => $tour['id']]) ?>">
            
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
                            <!-- Nút xóa ngày đã có trong DB -->
                            <button type="submit" 
                                    formaction="<?= route('tour.itinerary.delete_item', ['id' => $day['id']]) ?>" 
                                    formnovalidate
                                    onclick="return confirm('Xác nhận xóa ngày này khỏi Database ngay lập tức?')"
                                    class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Xóa ngày
                            </button>
                        <?php else: ?>
                            <!-- Nút xóa dòng chưa lưu (JS) -->
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
                <button type="button" class="btn btn-info" id="btn-add-day"><i class="fas fa-plus"></i> Thêm ngày mới</button>
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
                <textarea name="itineraries[${index}][content]" class="form-control" rows="3" placeholder="Nội dung hoạt động..."></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
});

// Xử lý hiển thị tên file khi chọn ảnh
const fileInput = document.getElementById('tourImages');
if(fileInput){
    fileInput.addEventListener('change', function(e) {
        var files = e.target.files;
        var label = e.target.nextElementSibling;
        if (files && files.length > 1) {
            label.innerText = files.length + ' file đã được chọn';
        } else if (files && files.length === 1) {
            label.innerText = files[0].name;
        } else {
            label.innerText = 'Chọn file...';
        }
    });
}
</script>