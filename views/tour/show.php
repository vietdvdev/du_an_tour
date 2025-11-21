<!-- views/tour/show.php -->
<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/tour-show.css') ?>"> 
<style>
    /* CSS Tùy chỉnh cho màn hình Show (Dùng Bootstrap 5/AdminLTE) */
    .tour-detail-card { margin-bottom: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .tour-header-section { padding: 20px; background: #fff; border-bottom: 1px solid #eee; }
    .tour-section-title { font-size: 1.5rem; margin-top: 15px; margin-bottom: 10px; border-left: 5px solid #007bff; padding-left: 10px; }
    .itinerary-day { border-left: 3px solid #17a2b8; padding-left: 15px; margin-bottom: 15px; }
    .itinerary-day h4 { color: #17a2b8; font-size: 1.25rem; }
    .price-table th { background-color: #f8f9fa; }
    .image-gallery img { width: 100%; height: 200px; object-fit: cover; border-radius: 4px; }
    .cover-image-badge { position: absolute; top: 10px; right: 20px; z-index: 10; font-size: 0.9rem;}
</style>

<!-- Phần Navbar & Sidebar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Dữ liệu từ Controller::show()
$tour = $tour ?? [];
$itinerary = $itinerary ?? [];
$prices = $prices ?? [];
$policy = $policy ?? [];
$images = $images ?? [];
$suppliers = $suppliers ?? []; // Cần được tải trong Controller::show()

$tourId = $tour['id'] ?? 0;
$tourName = $tour['name'] ?? 'Tour Không Xác Định';
$tourState = $tour['state'] ?? 'N/A';

// Hàm hỗ trợ hiển thị trạng thái
function getTourStateLabel(string $state): string
{
    return match ($state) {
        'DRAFT' => '<span class="badge bg-warning">Bản Nháp (Nội bộ)</span>',
        'PUBLISHED' => '<span class="badge bg-success">Đã Công Bố</span>',
        default => '<span class="badge bg-secondary">Không rõ</span>',
    };
}

// Hàm lấy tên loại khách
function getPaxTypeLabel(string $type): string
{
    return match ($type) {
        'ADULT' => 'Người lớn',
        'CHILD' => 'Trẻ em',
        'INFANT' => 'Em bé',
        default => 'Khác',
    };
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1>Chi Tiết Tour: <?= htmlspecialchars($tourName) ?></h1>
                </div>
                <div class="col-sm-4 text-right">
                    <!-- Nút chuyển sang chế độ chỉnh sửa -->
                    <a href="<?= route('tour.edit', ['id' => $tourId]) ?>" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Chỉnh Sửa Cấu Hình
                    </a>
                    <a href="<?= route('tour.index') ?>" class="btn btn-default">
                        <i class="bi bi-list"></i> Danh sách Tour
                    </a>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12">
                    <p>Mã Tour: <strong><?= htmlspecialchars($tour['code'] ?? 'N/A') ?></strong> | 
                       Trạng thái: <?= getTourStateLabel($tourState) ?> | 
                       Ngày tạo: <?= date('d/m/Y', strtotime($tour['created_at'] ?? date('Y-m-d'))) ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <!-- Mô tả Tổng quan -->
                <div class="col-12">
                    <div class="card tour-detail-card">
                        <div class="card-header"><h3 class="tour-section-title">Tổng quan Tour</h3></div>
                        <div class="card-body">
                            <p><?= nl2br(htmlspecialchars($tour['description'] ?? 'Chưa có mô tả chi tiết.')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- 1. HÌNH ẢNH TOUR -->
                <div class="col-12">
                    <div class="card tour-detail-card">
                        <div class="card-header"><h3 class="tour-section-title">1. Thư viện Hình Ảnh</h3></div>
                        <div class="card-body">
                            <?php if (!empty($images)): ?>
                                <div class="row image-gallery">
                                    <?php foreach ($images as $img): ?>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3 position-relative">
                                            <!-- Giả định URL ảnh hợp lệ -->
                                            <img src="<?= public_url($img['url']) ?>" alt="<?= htmlspecialchars($img['caption'] ?? 'Ảnh Tour') ?>" class="img-fluid">
                                            <?php if (($img['is_cover'] ?? 0) == 1): ?>
                                                <span class="badge bg-danger cover-image-badge"><i class="bi bi-star-fill"></i> Ảnh Bìa</span>
                                            <?php endif; ?>
                                            <p class="text-center text-muted mt-1" style="font-size: 0.85rem;"><?= htmlspecialchars($img['caption'] ?? '') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Chưa có hình ảnh minh họa nào được tải lên.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 2. LỊCH TRÌNH -->
                <div class="col-12">
                    <div class="card tour-detail-card">
                        <div class="card-header"><h3 class="tour-section-title">2. Lịch Trình Chi Tiết (<?= count($itinerary) ?> Ngày)</h3></div>
                        <div class="card-body">
                            <?php if (!empty($itinerary)): ?>
                                <?php foreach ($itinerary as $day): ?>
                                    <div class="itinerary-day">
                                        <h4>Ngày <?= htmlspecialchars($day['day_no'] ?? 'N/A') ?>: <?= htmlspecialchars($day['title'] ?? 'Không tiêu đề') ?></h4>
                                        <div class="text-secondary"><?= nl2br(htmlspecialchars($day['content'] ?? '')) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Lịch trình chi tiết chưa được thiết lập.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 3. BẢNG GIÁ -->
                <div class="col-12">
                    <div class="card tour-detail-card">
                        <div class="card-header"><h3 class="tour-section-title">3. Bảng Giá Áp Dụng</h3></div>
                        <div class="card-body">
                            <?php if (!empty($prices)): ?>
                                <table class="table table-bordered table-striped price-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Loại Khách</th>
                                            <th style="width: 30%;">Giá Cơ Bản</th>
                                            <th style="width: 25%;">Hiệu Lực Từ</th>
                                            <th style="width: 25%;">Hiệu Lực Đến</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prices as $price): ?>
                                            <tr>
                                                <td><?= getPaxTypeLabel($price['pax_type'] ?? 'OTHER') ?></td>
                                                <td><strong><?= number_format($price['base_price'] ?? 0, 0, ',', '.') ?> VNĐ</strong></td>
                                                <td><?= date('d/m/Y', strtotime($price['effective_from'])) ?></td>
                                                <td><?= date('d/m/Y', strtotime($price['effective_to'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">Bảng giá chưa được thiết lập.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 4. CHÍNH SÁCH & NCC -->
                <div class="col-12">
                    <div class="card tour-detail-card">
                        <div class="card-header"><h3 class="tour-section-title">4. Chính Sách và Nhà Cung Cấp</h3></div>
                        <div class="card-body row">
                            
                            <div class="col-md-6 border-right">
                                <h4>Chính Sách Hoàn/Hủy (tour_policy)</h4>
                                <?php if (!empty($policy)): ?>
                                    <strong class="text-primary">Quy tắc Hủy:</strong>
                                    <div class="p-2 border rounded bg-light"><?= nl2br(htmlspecialchars($policy['cancel_rules'] ?? 'Không có quy tắc hủy.')) ?></div>
                                    <strong class="text-primary mt-3 d-block">Quy tắc Hoàn Tiền:</strong>
                                    <div class="p-2 border rounded bg-light"><?= nl2br(htmlspecialchars($policy['refund_rules'] ?? 'Không có quy tắc hoàn tiền.')) ?></div>
                                <?php else: ?>
                                    <p class="text-muted">Chính sách chưa được thiết lập.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Nhà Cung Cấp Liên Kết (tour_supplier)</h4>
                                <?php if (!empty($suppliers)): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($supplier['role'] ?? 'NCC Dịch vụ') ?></span>
                                                <span class="badge bg-info">Supplier ID: <?= htmlspecialchars($supplier['supplier_id']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">Chưa gán nhà cung cấp nào cho Tour này.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>