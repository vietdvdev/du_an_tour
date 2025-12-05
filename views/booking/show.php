<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <!-- SECTION HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Đơn hàng: <strong><?= htmlspecialchars($booking['code'] ?? 'N/A') ?></strong></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('booking.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    
                    <?php if (($booking['state'] ?? '') !== 'CANCELLED'): ?>
                        <form action="<?= route('booking.cancel', ['id' => $booking['id']]) ?>" method="POST" style="display:inline" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này? Hành động không thể hoàn tác và sẽ trả lại chỗ trống.')">
                            <button type="submit" class="btn btn-danger ml-2">
                                <i class="fas fa-times-circle"></i> Hủy Đơn
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION CONTENT -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Thông báo Flash -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="row">
                
                <!-- CỘT TRÁI (INCLUDE FILE) -->
                <div class="col-md-4">
                    <?php include __DIR__ . '/sections/left_info.php'; ?>
                </div>

                <!-- CỘT PHẢI (INCLUDE TABS) -->
                <div class="col-md-8">
                    <?php include __DIR__ . '/sections/right_tabs.php'; ?>
                    
                </div>
                
            </div>
        </div>
    </section>
</div>

<!-- INCLUDE MODALS -->
<?php include __DIR__ . '/sections/modals.php'; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- SCRIPT XỬ LÝ TAB KHI CÓ URL HASH -->
<script>
$(document).ready(function() {
    var hash = window.location.hash;
    if (hash) {
        var link = $('.nav-tabs a[href="' + hash + '"]');
        if (link.length > 0) {
            $('.nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            link.addClass('active');
            $(hash).addClass('show active');
        }
    }
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        history.pushState(null, null, e.target.hash);
    });
});
</script>