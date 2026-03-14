<!-- views/tour/edit.php -->
<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/form.css') ?>">
<!-- Thư viện Tab/Nav CSS/JS cần thiết (giả sử dùng Bootstrap tabs) -->

<!-- Phần Navbar & Sidebar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Dữ liệu từ Controller::edit()
$tour = $tour ?? [];
$itinerary = $itinerary ?? [];
$prices = $prices ?? [];
$policy = $policy ?? [];
$suppliers = $suppliers ?? [];
$images = $images ?? [];
$categories = $categories ?? [];
$errors = $errors ?? [];
$old = $old ?? []; // Dữ liệu cũ từ form update

$tourId = $tour['id'] ?? 0;
$tourName = $tour['name'] ?? 'Tour Mới';
$tourState = $tour['state'] ?? 'DRAFT';

// Hàm hỗ trợ hiển thị trạng thái (Tạm thời đặt ở đây cho View)
function getTourStateLabel(string $state): string
{
    return match ($state) {
        'DRAFT' => '<span class="badge bg-warning">Bản Nháp</span>',
        'PUBLISHED' => '<span class="badge bg-success">Đã Công Bố</span>',
        default => '<span class="badge bg-secondary">Không rõ</span>',
    };
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cấu Hình Tour: <?= htmlspecialchars($tourName) ?></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <!-- Nút Công bố chỉ hiển thị khi Tour là DRAFT -->
                    <?php if ($tourState === 'DRAFT'): ?>
                        <button type="button" class="btn btn-success" 
                                onclick="confirmPublish(<?= $tourId ?>, '<?= htmlspecialchars($tourName) ?>')">
                            <i class="bi bi-check-circle"></i> Công Bố Tour
                        </button>
                    <?php endif; ?>
                    <a href="<?= route('tour.index') ?>" class="btn btn-default">
                        <i class="bi bi-list"></i> Quay lại Danh sách Tour
                    </a>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12">
                    <span class="badge bg-primary">Mã Tour: <?= htmlspecialchars($tour['code'] ?? 'N/A') ?></span>
                    <?= getTourStateLabel($tourState) ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header p-0">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills p-2" id="tour-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="tab-basic-link" data-toggle="pill" href="#tab-basic" role="tab">1. Thông tin cơ bản</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-itinerary-link" data-toggle="pill" href="#tab-itinerary" role="tab">2. Lịch trình & Ảnh</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-price-link" data-toggle="pill" href="#tab-price" role="tab">3. Giá Tour</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-policy-link" data-toggle="pill" href="#tab-policy" role="tab">4. Chính sách & NCC</a></li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        
                        <!-- TAB 1: Thông tin cơ bản (Code, Name, Category, Description) -->
                    <!-- TAB 1: Thông tin cơ bản (Code, Name, Category, Description) -->
                    <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                        <?php include __DIR__ . '/tour_sections/basic_info.php'; ?>  <!-- form thay đổi thông tin cơ bản  -->
                    </div>

                        <!-- TAB 2: Lịch trình & Ảnh -->
                        <div class="tab-pane fade" id="tab-itinerary" role="tabpanel">
<?php include __DIR__ . '/tour_sections/itinerary_images.php'; ?> 
                        </div>

                        <!-- TAB 3: Giá Tour -->
                        <div class="tab-pane fade" id="tab-price" role="tabpanel">
                            <h2>Bảng giá (tour_price)</h2>
                            <p>Quản lý giá theo loại khách (ADULT/CHILD/INFANT) và khoảng thời gian (effective_from/to).</p>
                            <p class="text-info">Hiện có <strong><?= count($prices) ?></strong> khoảng giá.</p>
                            <p class="text-danger">Lưu ý: Bạn cần phải viết logic để kiểm tra **ràng buộc không chồng chéo** giữa các khoảng giá (trigger `fn_tour_price_no_overlap`).</p>
                        </div>

                        <!-- TAB 4: Chính sách & NCC -->
                        <div class="tab-pane fade" id="tab-policy" role="tabpanel">
                            <h2>Chính sách Tour (tour_policy)</h2>
                            <p>Chỉnh sửa các quy tắc hủy (`cancel_rules`) và hoàn tiền (`refund_rules`).</p>
                            <p class="text-info">Trạng thái hiện tại: <?= $policy ? 'Đã có Chính sách' : 'Chưa có Chính sách' ?></p>

                            <h2 class="mt-4">Nhà cung cấp (tour_supplier)</h2>
                            <p>Giao diện chọn và gán các NCC (`supplier`) đã có vào Tour này kèm vai trò (`role`).</p>
                            <p class="text-info">Hiện có <strong><?= count($suppliers) ?></strong> NCC được gán.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- Page specific script -->
<form id="publish-form-<?= $tourId ?>" method="POST"
      action="<?= route('tour.publish', ['id' => $tourId]) ?>"
      style="display:none;"></form>
      
<script>
    /**
     * Hàm xác nhận và gửi yêu cầu công bố Tour
     */
    function confirmPublish(id, name) {
        if (!confirm(`Bạn có chắc chắn muốn CÔNG BỐ Tour "${name}"? Hệ thống sẽ kiểm tra các điều kiện bắt buộc (ảnh bìa, lịch trình) trước khi công bố.`)) return;
        
        const form = document.getElementById("publish-form-" + id);
        if (form) form.submit();
    }
</script>