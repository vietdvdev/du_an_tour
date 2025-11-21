<?php include __DIR__ . '/../layout/header.php'; ?>
<style>
    .remove-row { cursor: pointer; color: red; }
    .tour-image-card { position: relative; }
    .tour-image-card .btn-delete { position: absolute; top: 5px; right: 5px; }
    .tour-image-card .badge-cover { position: absolute; top: 5px; left: 5px; }
</style>

<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// 1. Chuẩn bị dữ liệu an toàn
$tour = $tour ?? [];
$itinerary = $itinerary ?? [];
$prices = $prices ?? [];
$policy = $policy ?? [];
$suppliers = $suppliers ?? [];
$images = $images ?? [];
$categories = $categories ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

$tourId = $tour['id'] ?? 0;
$tourName = $tour['name'] ?? 'Tour Mới';
$tourState = $tour['state'] ?? 'DRAFT';

// 2. Xác định Tab hiện tại dựa trên URL (để active lại sau khi reload)
// Mặc định là 'basic'
$currentTab = $_GET['tab'] ?? 'basic'; 

// Hàm helper hiển thị trạng thái
function getTourStateLabel(string $state): string {
    return match ($state) {
        'DRAFT' => '<span class="badge badge-warning">Bản Nháp</span>',
        'PUBLISHED' => '<span class="badge badge-success">Đã Công Bố</span>',
        default => '<span class="badge badge-secondary">Không rõ</span>',
    };
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cấu Hình Tour: <strong><?= htmlspecialchars($tourName) ?></strong></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <?php if ($tourState === 'DRAFT'): ?>
                        <button type="button" class="btn btn-success" onclick="confirmPublish(<?= $tourId ?>, '<?= htmlspecialchars($tourName) ?>')">
                            <i class="fas fa-check-circle"></i> Công Bố Tour
                        </button>
                    <?php endif; ?>
                    <a href="<?= route('tour.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <div>
                <span class="badge badge-info">ID: <?= $tourId ?></span>
                <span class="badge badge-primary">Code: <?= htmlspecialchars($tour['code'] ?? 'N/A') ?></span>
                <?= getTourStateLabel($tourState) ?>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="tour-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentTab == 'basic' ? 'active' : '' ?>" href="#tab-basic" data-toggle="pill" role="tab">1. Thông tin cơ bản</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentTab == 'itinerary' ? 'active' : '' ?>" href="#tab-itinerary" data-toggle="pill" role="tab">2. Lịch trình & Ảnh</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentTab == 'price' ? 'active' : '' ?>" href="#tab-price" data-toggle="pill" role="tab">3. Giá Tour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentTab == 'policy' ? 'active' : '' ?>" href="#tab-policy" data-toggle="pill" role="tab">4. Chính sách & NCC</a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade <?= $currentTab == 'basic' ? 'show active' : '' ?>" id="tab-basic" role="tabpanel">
                            <?php include __DIR__ . '/tour_sections/basic_info.php'; ?>
                        </div>
                        
                        <div class="tab-pane fade <?= $currentTab == 'itinerary' ? 'show active' : '' ?>" id="tab-itinerary" role="tabpanel">
                            <?php include __DIR__ . '/tour_sections/itinerary_images.php'; ?>
                        </div>

                        <div class="tab-pane fade <?= $currentTab == 'price' ? 'show active' : '' ?>" id="tab-price" role="tabpanel">
                             <?php include __DIR__ . '/tour_sections/price.php'; ?>
                        </div>

                        <div class="tab-pane fade <?= $currentTab == 'policy' ? 'show active' : '' ?>" id="tab-policy" role="tabpanel">
                            <?php include __DIR__ . '/tour_sections/policy_suppliers.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<form id="publish-form-<?= $tourId ?>" method="POST" action="<?= route('tour.publish', ['id' => $tourId]) ?>" style="display:none;"></form>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<script>
    function confirmPublish(id, name) {
        if (confirm(`Xác nhận CÔNG BỐ tour "${name}"?`)) {
            document.getElementById("publish-form-" + id).submit();
        }
    }
</script>