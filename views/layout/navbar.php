<!-- Thay đổi class từ navbar-primary navbar-dark thành navbar-white navbar-light -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= route('home.index') ?>" class="nav-link font-weight-bold">Trang chủ Website</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        
        <!-- Fullscreen Button -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <!-- Logout Button -->
        <li class="nav-item">
            <a class="nav-link text-danger" href="<?= route('logout') ?>" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?')" role="button">
                <i class="fas fa-sign-out-alt mr-1"></i> Đăng xuất
            </a>
        </li>
    </ul>
</nav>