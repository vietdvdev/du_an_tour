<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-cart-plus text-success"></i> Tạo Booking Mới</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <form action="<?= route('booking.store') ?>" method="POST" id="bookingForm">
                <div class="row">
                    <!-- Cột Trái: Thông tin người đặt -->
                    <div class="col-md-4">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin Đặt chỗ</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Chọn Đợt Khởi Hành <span class="text-danger">*</span></label>
                                    <select name="departure_id" class="form-control select2" required>
                                        <option value="">-- Chọn lịch --</option>
                                        <?php foreach ($departures as $dep): ?>
                                            <option value="<?= $dep['id'] ?>" 
                                                <?= (isset($old['departure_id']) && $old['departure_id'] == $dep['id']) ? 'selected' : '' ?>>
                                                [<?= date('d/m/Y', strtotime($dep['start_date'])) ?>] 
                                                <?= htmlspecialchars($dep['tour_name']) ?> 
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Người liên hệ</label>
                                    <input type="text" name="contact_name" class="form-control" required value="<?= $old['contact_name'] ?? '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="contact_phone" class="form-control" required value="<?= $old['contact_phone'] ?? '' ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= $old['contact_email'] ?? '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="3"><?= $old['note'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải: Danh sách khách -->
                    <div class="col-md-8">
                        <div class="card card-outline card-primary h-100">
                            <!-- [CẬP NHẬT] Thêm nút Import Excel vào Header -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Danh sách Khách hàng</h3>
                                <div class="ml-auto">
                                    <!-- Input file ẩn để chọn Excel -->
                                    <input type="file" id="importExcelInput" accept=".xlsx, .xls" style="display: none;" />
                                    
                                    <!-- Nút kích hoạt chọn file -->
                                    <button type="button" class="btn btn-success btn-sm mr-2" onclick="document.getElementById('importExcelInput').click()">
                                        <i class="fas fa-file-excel"></i> Nhập từ Excel
                                    </button>

                                    <button type="button" class="btn btn-sm btn-primary" id="btnAddTraveler">
                                        <i class="fas fa-user-plus"></i> Thêm khách
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card-body table-responsive p-0" style="height: 500px;">
                                <table class="table table-head-fixed text-nowrap">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 40%">Họ tên <span class="text-danger">*</span></th>
                                            <th style="width: 20%">Giới tính</th>
                                            <th style="width: 25%">Ngày sinh</th>
                                            <th style="width: 10%">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="travelerContainer">
                                        <!-- Dòng khách hàng sẽ được JS thêm vào đây -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light text-right">
                                <div class="mb-2">Tổng số khách: <b id="paxCountDisplay">0</b></div>
                                <small class="text-muted d-block mb-2">Hệ thống sẽ tự động tính giá dựa trên ngày sinh khi bạn bấm Lưu.</small>
                                <button type="submit" class="btn btn-success btn-lg font-weight-bold">
                                    <i class="fas fa-check-circle"></i> XÁC NHẬN ĐẶT CHỖ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- [CẬP NHẬT] Thêm thư viện SheetJS để đọc Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    let travelerIndex = 0;

    // Template dòng khách đơn giản
    function createTravelerRow(index, data = {}) {
        const fullName = data.full_name || '';
        const dob = data.dob || '';
        const gender = data.gender || 'OTHER'; // Mặc định là Khác nếu không có
        
        return `
            <tr id="row-${index}">
                <td class="align-middle text-center row-number"></td>
                <td>
                    <input type="text" name="travelers[${index}][full_name]" class="form-control" placeholder="Nhập họ tên" required value="${fullName}">
                </td>
                <td>
                    <select name="travelers[${index}][gender]" class="form-control">
                        <option value="MALE" ${gender === 'MALE' ? 'selected' : ''}>Nam</option>
                        <option value="FEMALE" ${gender === 'FEMALE' ? 'selected' : ''}>Nữ</option>
                        <option value="OTHER" ${gender === 'OTHER' ? 'selected' : ''}>Khác</option>
                    </select>
                </td>
                <td>
                    <input type="date" name="travelers[${index}][dob]" class="form-control" value="${dob}">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // Các hàm hỗ trợ thêm/xóa dòng
    $('#btnAddTraveler').click(function() {
        $('#travelerContainer').append(createTravelerRow(travelerIndex));
        travelerIndex++;
        updateCount();
    });

    window.removeRow = function(index) {
        $(`#row-${index}`).remove();
        updateCount();
    }

    function updateCount() {
        let count = 0;
        $('#travelerContainer tr').each(function(idx) {
            $(this).find('.row-number').text(idx + 1);
            count++;
        });
        $('#paxCountDisplay').text(count);
    }

    // --- [CẬP NHẬT] LOGIC IMPORT EXCEL ---
    $('#importExcelInput').change(function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            
            // Lấy sheet đầu tiên
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            
            // Chuyển đổi sang JSON
            const jsonData = XLSX.utils.sheet_to_json(worksheet);

            if (jsonData.length > 0) {
                let importedCount = 0;
                // Lặp qua từng dòng trong Excel
                jsonData.forEach(row => {
                    // Mapping cột Excel (Tiếng Việt hoặc Tiếng Anh) sang dữ liệu form
                    const mappedData = {
                        full_name: row['Họ tên'] || row['Full Name'] || '', 
                        
                        // Convert Giới tính
                        gender: convertGender(row['Giới tính'] || row['Gender']),
                        
                        // Convert Ngày sinh
                        dob: convertExcelDate(row['Ngày sinh'] || row['DOB'])
                    };

                    // Chỉ thêm nếu có tên
                    if(mappedData.full_name) {
                        $('#travelerContainer').append(createTravelerRow(travelerIndex, mappedData));
                        travelerIndex++;
                        importedCount++;
                    }
                });
                
                updateCount(); // Cập nhật lại số thứ tự và tổng
                if(importedCount > 0) {
                    alert(`Đã nhập thành công ${importedCount} khách từ Excel!`);
                } else {
                    alert('Không tìm thấy cột "Họ tên" hoặc dữ liệu trong file Excel.');
                }
            } else {
                alert('File Excel không có dữ liệu!');
            }
            
            // Reset input để chọn lại file cũ được nếu muốn
            $('#importExcelInput').val('');
        };
        
        reader.readAsArrayBuffer(file);
    });

    // Hàm phụ trợ: Chuyển đổi giới tính từ text sang mã
    function convertGender(text) {
        if (!text) return 'OTHER';
        text = text.toString().toLowerCase().trim();
        if (text === 'nam' || text === 'male' || text === 'm') return 'MALE';
        if (text === 'nữ' || text === 'nu' || text === 'female' || text === 'f') return 'FEMALE';
        return 'OTHER';
    }

    // Hàm phụ trợ: Chuyển đổi ngày tháng Excel
    function convertExcelDate(excelDate) {
        if (!excelDate) return '';
        
        // Nếu là text dạng 'YYYY-MM-DD' hoặc 'DD/MM/YYYY'
        if (typeof excelDate === 'string') {
            // Nếu chuỗi có dạng DD/MM/YYYY, chuyển thành YYYY-MM-DD
            if (excelDate.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/)) {
                const parts = excelDate.split('/');
                return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
            return excelDate; 
        }
        
        // Nếu Excel trả về số serial (ví dụ 45260)
        if (typeof excelDate === 'number') {
            const date = new Date(Math.round((excelDate - 25569) * 86400 * 1000));
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }
        
        return '';
    }

    // Khởi tạo mặc định
    $(document).ready(function() {
        <?php if (!empty($old_travelers)): ?>
            const oldData = <?= json_encode($old_travelers) ?>;
            oldData.forEach(item => {
                $('#travelerContainer').append(createTravelerRow(travelerIndex, item));
                travelerIndex++;
            });
        <?php else: ?>
            // Mặc định thêm 1 dòng trống nếu chưa có khách nào
            if(travelerIndex === 0) {
                $('#btnAddTraveler').click();
            }
        <?php endif; ?>
        updateCount();
    });
</script>