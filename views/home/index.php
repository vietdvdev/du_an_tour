<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>

<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<!-- Phần nỗi dung -->

<!-- /.content-wrapper -->
<!-- <footer> -->
<?php include __DIR__ . '/../layout/footer.php'; ?>
<!-- endforeach -->
<!-- Page specific script -->
<script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
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
<!-- Code injected by live-server -->
</body>

</html>

<!-- Phần Footer -->