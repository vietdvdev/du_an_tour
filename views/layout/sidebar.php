  <aside class="main-sidebar sidebar-light-success elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="assets/dist/img/logo2.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light"> Viet DV</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="assets/dist/img/logo1.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Quản Lý Tour</a>
        </div>
      </div>


      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->


          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Trang chủ

              </p>
            </a>
          </li>



          <li class="nav-item">
            <a href="<?= route('danhMuc.index') ?>" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Danh mục

              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?= route('supplier.index') ?>" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Nhà cung câp dịch vụ
              </p>
            </a>
          </li>

          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user"></i>2
              <p>
                Quản lý tài khoản
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= route('admin.index') ?>" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Tài khoản Admin</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Tài khoản cá nhân</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user"></i>
              <p>
                Quản lý Tour
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= route('tour.index') ?>" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Danh Sách Tour</p>
                </a>
              </li>


            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= route('departure.index') ?>" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Lịch khởi hành tour</p>
                </a>
              </li>
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= route('booking.index') ?>" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Đặt Lịch tour</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= route('payment.index') ?>" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Quản lý Thanh toán</p>
                </a>
              </li>
            </ul>


            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= route('assignment.index') ?>" class="nav-link">
                  <i class="nav-icon far fa-user"></i>
                  <p>Phân công Hướng dẫn viên</p>
                </a>
              </li>
            </ul>            

          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>