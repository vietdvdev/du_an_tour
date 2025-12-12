<!-- Footer -->
<footer class="main-footer text-sm">
    <!-- Dòng 1: Thông tin chính -->
    <div class="row">
        <div class="col-md-6">
            <strong>Bản quyền &copy; <?= date('Y') ?> <a href="#" class="text-primary"> Travel Go</a>.</strong>
            Đã đăng ký bản quyền.
            <div class="mt-1 text-muted">
                <i>"Hành trình vạn dặm bắt đầu từ một bước chân."</i>
            </div>
        </div>
        <div class="col-md-6 text-md-right">
            <div class="float-right d-none d-sm-block">
                <b>Phiên bản</b> 1.0.0 (Beta)
            </div>
        </div>
    </div>

    <hr class="my-2">

    <!-- Dòng 2: Thông tin liên hệ chi tiết (Fake) -->
    <div class="row text-muted" style="font-size: 0.85rem;">
        <div class="col-md-4 mb-2">
            <i class="fas fa-map-marker-alt mr-1 text-danger"></i> 
            <b>Trụ sở chính:</b> Tầng 5, Tòa nhà Vincom Center, 72 Lê Thánh Tông, TP. Hà Nội.
        </div>
        <div class="col-md-4 mb-2">
            <i class="fas fa-phone-alt mr-1 text-success"></i> 
            <b>Hotline:</b> 1900 123 456 - 0987 654 321 (Hỗ trợ 24/7)
        </div>
        <div class="col-md-4 mb-2">
            <i class="fas fa-envelope mr-1 text-info"></i> 
            <b>Email:</b> support@vietdvtravel.com | contact@vietdv.vn
        </div>
    </div>

    <!-- Dòng 3: Liên kết mạng xã hội & Chính sách -->
    <div class="row mt-1">
        <div class="col-12">
            <a href="#" class="mr-3 text-secondary"><i class="fab fa-facebook-square fa-lg"></i> Facebook</a>
            <a href="#" class="mr-3 text-secondary"><i class="fab fa-youtube fa-lg"></i> Youtube</a>
            <a href="#" class="mr-3 text-secondary"><i class="fab fa-instagram fa-lg"></i> Instagram</a>
            <span class="border-left pl-3 ml-1"></span>
            <a href="#" class="text-muted mr-2">Chính sách bảo mật</a> | 
            <a href="#" class="text-muted ml-2">Điều khoản sử dụng</a>
        </div>
    </div>
</footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="<?= asset('plugins/jquery/jquery.min.js') ?>"></script>

<!-- Bootstrap 4 -->
<script src="<?= asset('plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

<!-- DataTables -->
<script src="<?= asset('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?= asset('plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>

<!-- AdminLTE -->
<script src="<?= asset('dist/js/adminlte.min.js') ?>"></script>

<!-- Demo -->
<script src="<?= asset('dist/js/demo.js') ?>"></script>

<!-- Page specific script -->
<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>
</body>
</html>
