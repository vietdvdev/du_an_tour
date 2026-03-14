<?php
namespace App\Controllers;
use App\Core\Request;
use App\Core\Response;
use App\Models\Supplier;
use App\Support\Validator;



class SupplierController extends BaseController
{
    public function index(Request $request): Response
    {
        // Gọi Model để lấy toàn bộ dữ liệu
        $ListSupplier = (new Supplier())->all();
        // dd($Danh_Muc_Tour);
        return $this->render('nha-cung-cap.index', [
            'title' => 'Quản lý nhà cung cấp',
            'ListSupplier' => $ListSupplier
        ]);
    }

    public function create(Request $req): Response
    {
        return $this->render('nha-cung-cap.create');
    }
    
public function store(Request $req): Response
{
    // Gom dữ liệu: Giữ nguyên, logic trim và cast to string là tốt
    $data = [
        'name'    => trim((string)$req->input('name')),
        'type'    => trim((string)$req->input('type')),
        'contact' => trim((string)$req->input('contact')),
        'phone'   => trim((string)$req->input('phone')),
        'email'   => trim((string)$req->input('email')),
        'address' => trim((string)$req->input('address')),
        'note'    => trim((string)$req->input('note')),
    ];

    // Rule: Giữ nguyên
    $rules = [
        'name'  => 'required|max:255|unique:supplier,name',
        'type'  => 'required|in:HOTEL,TRANSPORT,RESTAURANT,OTHER',
        // Phone regex: cho phép số, dấu cộng, ngoặc, gạch ngang, khoảng trắng, min 9, max 21
        'phone' => 'required|min:9|max:21|regex:/^[0-9+()\\-\\s]{6,30}$/', 
        'email' => 'nullable|email|max:255',
        'contact' => 'nullable|max:255', // Thêm validation cho contact, address, note
        'address' => 'nullable|max:255',
        'note'    => 'nullable',
    ];

    // Message: Giữ nguyên (bổ sung type.required)
    $messages = [
        'name.required' => 'Vui lòng nhập tên nhà cung cấp.',
        'name.max'      => 'Tên tối đa 255 ký tự.',
        'name.unique'   => 'Tên nhà cung cấp đã tồn tại.',

        'type.required' => 'Vui lòng chọn loại nhà cung cấp.', // Bổ sung
        'type.in'       => 'Loại nhà cung cấp không hợp lệ.',

        'phone.required' => 'Vui lòng nhập số điện thoại nhà cung cấp.',
        'phone.max'      => 'Số điện thoại tối đa 21 ký tự.', // Sửa max về 21 cho đồng bộ với rule
        'phone.min'      => 'Số điện thoại tối thiểu 9 ký tự.',
        'phone.regex'    => 'Số điện thoại không hợp lệ.',

        'email.email' => 'Email không hợp lệ.',
        'email.max'   => 'Email tối đa 255 ký tự.',
    ];

    // Validate
    // Giả sử Validator là class bạn đang sử dụng (ví dụ: Illuminate\Validation\Validator)
    $v = new Validator($data, $rules, $messages); 

    if ($v->fails()) {
        // Trả về view tạo Nhà cung cấp, kèm lỗi và dữ liệu cũ
        return $this->render('nha-cung-cap.create', [ 
            'errors' => $v->errors(),
            'old'    => $data
        ]);
    }

    // Lưu vào DB
    try {
        // Giả sử (new Supplier()) là cách bạn khởi tạo Model
        (new Supplier())->create($data); 

        // Sau khi thành công, chuyển hướng đến danh sách Nhà cung cấp
        $_SESSION['flash_success'] = 'Thêm nhà cung cấp thành công.';
        return $this->redirect(route('supplier.index'));
        
    } catch (\Throwable $e) {
        // Xử lý lỗi DB
        // Log $e (lỗi) ở đây nếu cần thiết
        return $this->render('nha-cung-cap.create', [
            'errors' => [
                // Hiển thị lỗi chung (general) trên form
                'general' => ['Không thể lưu dữ liệu. Vui lòng thử lại sau.'] 
            ],
            'old' => $data
        ]);
    }
}


public function edit(Request $req): Response
{
    // Lấy ID từ URL
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['flash_error'] = "ID Nhà cung cấp không hợp lệ.";
        return $this->redirect(route('supplier.index'));
    }

    $supplierModel = new Supplier();
    $supplier = $supplierModel->find($id); // Tìm nhà cung cấp theo ID

    if (!$supplier) {
        $_SESSION['flash_error'] = "Không tìm thấy Nhà cung cấp để chỉnh sửa.";
        return $this->redirect(route('supplier.index'));
    }

    // Truyền dữ liệu tìm được sang View
    return $this->render('nha-cung-cap.edit', [
        'supplier' => $supplier,
        'errors'   => [], // Mặc định không có lỗi
        'old'      => $supplier, // Dữ liệu cũ là dữ liệu hiện tại
    ]);
}


public function update(Request $req): Response
{
    // Lấy ID từ URL
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

    // Gom dữ liệu từ form
  $data = [
        'name'    => trim((string)$req->input('name')),
        'type'    => trim((string)$req->input('type')),
        'contact' => trim((string)$req->input('contact')),
        'phone'   => trim((string)$req->input('phone')),
        'email'   => trim((string)$req->input('email')),
        'address' => trim((string)$req->input('address')),
        'note'    => trim((string)$req->input('note')),
    ];

    // Quy tắc validation: UNIQUE cần loại trừ ID hiện tại
   $rules = [
        'name'  => 'required|max:255|unique:supplier,name',
        'type'  => 'required|in:HOTEL,TRANSPORT,RESTAURANT,OTHER',
        // Phone regex: cho phép số, dấu cộng, ngoặc, gạch ngang, khoảng trắng, min 9, max 21
        'phone' => 'required|min:9|max:21|regex:/^[0-9+()\\-\\s]{6,30}$/', 
        'email' => 'nullable|email|max:255',
        'contact' => 'nullable|max:255', // Thêm validation cho contact, address, note
        'address' => 'nullable|max:255',
        'note'    => 'nullable',
    ];

    // Message: Giữ nguyên (bổ sung type.required)
    $messages = [
        'name.required' => 'Vui lòng nhập tên nhà cung cấp.',
        'name.max'      => 'Tên tối đa 255 ký tự.',
        'name.unique'   => 'Tên nhà cung cấp đã tồn tại.',

        'type.required' => 'Vui lòng chọn loại nhà cung cấp.', // Bổ sung
        'type.in'       => 'Loại nhà cung cấp không hợp lệ.',

        'phone.required' => 'Vui lòng nhập số điện thoại nhà cung cấp.',
        'phone.max'      => 'Số điện thoại tối đa 21 ký tự.', // Sửa max về 21 cho đồng bộ với rule
        'phone.min'      => 'Số điện thoại tối thiểu 9 ký tự.',
        'phone.regex'    => 'Số điện thoại không hợp lệ.',

        'email.email' => 'Email không hợp lệ.',
        'email.max'   => 'Email tối đa 255 ký tự.',
    ];


    // Validate
    // ...
     $v = new Validator($data, $rules, $messages); 

    if ($v->fails()) {
        // Trả về view edit, kèm lỗi và dữ liệu người dùng vừa gửi ($data)
        return $this->render('nha-cung-cap.edit', [
            'id'       => $id,
            'errors'   => $v->errors(),
            'old'      => $data
        ]);
    }

    // Lưu vào DB (sử dụng $id)
    try {
        (new Supplier())->update($id, $data); 

        $_SESSION['flash_success'] = 'Cập nhật nhà cung cấp thành công.';
        return $this->redirect(route('supplier.index'));
    } catch (\Throwable $e) {
        // ... (Xử lý lỗi DB)
        return $this->render('nha-cung-cap.edit', [
            'id'       => $id,
            'errors'   => ['general' => ['Không thể lưu dữ liệu. Vui lòng thử lại sau.']],
            'old'      => $data
        ]);
    }
}




    public function delete(Request $req): Response
    {
      
        // 1. Đảm bảo session được bắt đầu để sử dụng flash messages
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 2. Lấy ID từ route params (Giả định router đặt ID vào $req->params['id'])
        $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID Nhà cung cấp không hợp lệ.";
            return $this->redirect(route('supplier.index'));
        }

        try {
            $supplierModel = new Supplier();
            // 3. Tìm Nhà cung cấp trước khi xóa để lấy tên và kiểm tra sự tồn tại
            $supplier = $supplierModel->find($id);
       

            if (!$supplier) {
                $_SESSION['flash_error'] = "Không tìm thấy Nhà cung cấp có ID #{$id}.";
                return $this->redirect(route('supplier.index'));
            }

            $name = $supplier['name'] ?? 'ID ' . $id;
            
            // 4. Thực hiện xóa
            if ($supplierModel->delete($id)) {
                $_SESSION['flash_success'] = "Xóa nhà cung cấp <strong>{$name}</strong> thành công.";
            } else {
                // Trường hợp Model trả về false mà không throw Exception
                $_SESSION['flash_error'] = "Không thể xóa nhà cung cấp <strong>{$name}</strong>. Lỗi xóa không xác định.";
            }

            return $this->redirect(route('supplier.index'));
            
        } catch (\Throwable $e) {
            // 5. Xử lý lỗi hệ thống hoặc lỗi Ràng buộc FK (ON DELETE RESTRICT)
            
            // Log lỗi để debug (rất quan trọng)
            error_log("[Supplier.delete] Lỗi khi xóa ID={$id}: " . $e->getMessage());
            
            // Thông báo chung cho người dùng, gợi ý về ràng buộc
            $_SESSION['flash_error'] = "Không thể xóa nhà cung cấp. Dữ liệu này có thể đang được sử dụng trong Tour hoặc Dịch vụ (ràng buộc FK).";
            return $this->redirect(route('supplier.index'));
        }
    }


 
    
}