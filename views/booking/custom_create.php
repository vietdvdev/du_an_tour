<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<?php
$old = $old ?? ($_SESSION['old'] ?? []);
$errors = $errors ?? ($_SESSION['errors'] ?? []);
unset($_SESSION['old'], $_SESSION['errors']);
?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-magic text-primary"></i> Tạo Tour Thiết Kế Riêng</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('booking.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="container-fluid">
           
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>
           
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['general'][0] ?>
                </div>
            <?php endif; ?>


            <form action="<?= route('booking.custom.store') ?>" method="POST" id="customBookingForm">
                <div class="row">
                   
                    <!-- CỘT TRÁI: THÔNG TIN TOUR & LIÊN HỆ -->
                    <div class="col-md-5">
                       
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">1. Thông tin Tour & Tài chính</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Tên Tour / Hành trình <span class="text-danger">*</span></label>
                                    <input type="text" name="custom_tour_name" class="form-control"
                                           placeholder="VD: Tour Gia đình anh Nam - Phú Quốc 3N2Đ"
                                           required value="<?= htmlspecialchars($old['custom_tour_name'] ?? '') ?>">
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày đi <span class="text-danger">*</span></label>
                                            <input type="date" name="custom_start_date" class="form-control" required value="<?= $old['custom_start_date'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày về <span class="text-danger">*</span></label>
                                            <input type="date" name="custom_end_date" class="form-control" required value="<?= $old['custom_end_date'] ?? '' ?>">
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <label>Tổng giá trị hợp đồng (VNĐ) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="custom_total_amount" id="total_amount" class="form-control font-weight-bold text-success"
                                               placeholder="Nhập tổng tiền chốt với khách" required min="0"
                                               value="<?= $old['custom_total_amount'] ?? '' ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                    </div>
                                </div>


                                <!-- [MỚI] Ô NHẬP TIỀN CỌC -->
                                <div class="form-group">
                                    <label>Số tiền cọc trước (VNĐ) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="custom_deposit_amount" id="deposit_amount" class="form-control font-weight-bold text-warning"
                                               placeholder="Nhập số tiền khách đã cọc" required min="0"
                                               value="<?= $old['custom_deposit_amount'] ?? '' ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                    </div>
                                    <small class="text-danger mt-1 d-block">
                                        <i class="fas fa-exclamation-circle"></i> Yêu cầu cọc tối thiểu 10% giá trị hợp đồng.
                                    </small>
                                </div>


                            </div>
                        </div>


                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">2. Thông tin Người liên hệ</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" class="form-control" required value="<?= htmlspecialchars($old['contact_name'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_phone" class="form-control" required value="<?= htmlspecialchars($old['contact_phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($old['contact_email'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="2"><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- CỘT PHẢI: DANH SÁCH KHÁCH -->
                    <div class="col-md-7">
                        <div class="card card-outline card-info h-100">
                            <!-- [UPDATE] Thêm nút nhập từ Excel vào Header -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">3. Danh sách Đoàn</h3>
                                <div class="ml-auto">
                                    <!-- Input file ẩn -->
                                    <input type="file" id="importExcelInput" accept=".xlsx, .xls" style="display: none;" />
                                    
                                    <!-- Nút Import -->
                                    <button type="button" class="btn btn-success btn-sm mr-2" onclick="document.getElementById('importExcelInput').click()">
                                        <i class="fas fa-file-excel"></i> Nhập từ Excel
                                    </button>

                                    <button type="button" class="btn btn-sm btn-info" id="btnAddTraveler">
                                        <i class="fas fa-user-plus"></i> Thêm khách
                                    </button>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0" style="height: 500px;">
                                <table class="table table-head-fixed text-nowrap">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 45%">Họ tên</th>
                                            <th style="width: 20%">Giới tính</th>
                                            <th style="width: 20%">Ngày sinh</th>
                                            <th style="width: 10%">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="travelerContainer"></tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light text-right">
                                <div class="mb-2">Tổng số khách: <b id="paxCountDisplay">0</b></div>
                                <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5">
                                    <i class="fas fa-paper-plane"></i> TẠO TOUR & BOOKING
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

<!-- [MỚI] Thêm thư viện SheetJS để đọc Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    // JS kiểm tra nhanh (Client-side validation)
    document.getElementById('customBookingForm').addEventListener('submit', function(e) {
        const total = parseFloat(document.getElementById('total_amount').value) || 0;
        const deposit = parseFloat(document.getElementById('deposit_amount').value) || 0;
       
        if (total > 0 && deposit < (total * 0.1)) {
            e.preventDefault(); // Chặn submit
            alert('Lỗi: Số tiền cọc phải lớn hơn hoặc bằng 10% tổng giá trị (' + new Intl.NumberFormat('vi-VN').format(total * 0.1) + ' VNĐ).');
            document.getElementById('deposit_amount').focus();
        }
    });


    // ... (Phần script thêm dòng khách giữ nguyên và bổ sung logic Excel) ...
    let travelerIndex = 0;
    
    // Hàm tạo dòng khách (nhận thêm data để điền nếu có)
    function createTravelerRow(index, data = {}) {
        const fullName = data.full_name || '';
        const dob = data.dob || '';
        const gender = data.gender || 'OTHER';

        return `
            <tr id="row-${index}">
                <td class="align-middle text-center row-number"></td>
                <td><input type="text" name="travelers[${index}][full_name]" class="form-control" placeholder="Nhập họ tên" required value="${fullName}"></td>
                <td>
                    <select name="travelers[${index}][gender]" class="form-control">
                        <option value="MALE" ${gender === 'MALE' ? 'selected' : ''}>Nam</option>
                        <option value="FEMALE" ${gender === 'FEMALE' ? 'selected' : ''}>Nữ</option>
                        <option value="OTHER" ${gender === 'OTHER' ? 'selected' : ''}>Khác</option>
                    </select>
                </td>
                <td><input type="date" name="travelers[${index}][dob]" class="form-control" value="${dob}"></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${index})"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
    }

    $('#btnAddTraveler').click(function() {
        $('#travelerContainer').append(createTravelerRow(travelerIndex++));
        updateCount();
    });

    window.removeRow = function(index) { $(`#row-${index}`).remove(); updateCount(); }
    
    function updateCount() {
        let count = 0;
        $('#travelerContainer tr').each(function(idx) {
            $(this).find('.row-number').text(idx + 1);
            count++;
        });
        $('#paxCountDisplay').text(count);
    }

    // --- [MỚI] LOGIC IMPORT EXCEL ---
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
                    // Mapping cột Excel sang dữ liệu form
                    const mappedData = {
                        full_name: row['Họ tên'] || row['Full Name'] || '', 
                        gender: convertGender(row['Giới tính'] || row['Gender']),
                        dob: convertExcelDate(row['Ngày sinh'] || row['DOB'])
                    };

                    // Chỉ thêm nếu có tên
                    if(mappedData.full_name) {
                        $('#travelerContainer').append(createTravelerRow(travelerIndex++, mappedData));
                        importedCount++;
                    }
                });
                
                updateCount(); // Cập nhật lại số thứ tự và tổng
                if(importedCount > 0) {
                    alert(`Đã nhập thành công ${importedCount} khách từ Excel!`);
                } else {
                    alert('Không tìm thấy cột "Họ tên" hoặc dữ liệu hợp lệ trong file Excel.');
                }
            } else {
                alert('File Excel không có dữ liệu!');
            }
            
            // Reset input
            $('#importExcelInput').val('');
        };
        
        reader.readAsArrayBuffer(file);
    });

    // Hàm phụ trợ: Chuyển đổi giới tính
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
            if (excelDate.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/)) {
                const parts = excelDate.split('/');
                return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
            return excelDate; 
        }
        
        // Nếu Excel trả về số serial
        if (typeof excelDate === 'number') {
            const date = new Date(Math.round((excelDate - 25569) * 86400 * 1000));
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }
        
        return '';
    }

    $(document).ready(function() { 
        // Nếu không có dữ liệu cũ, thêm 1 dòng trống
        if(travelerIndex === 0) {
            $('#btnAddTraveler').click(); 
        }
        updateCount(); 
    });
</script>