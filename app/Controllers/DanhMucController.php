<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Tour_category;
use App\Support\Validator;


class DanhMucController extends BaseController
{
    public function index(Request $request): Response
    {
        // Gọi Model để lấy toàn bộ dữ liệu
        $Danh_Muc_Tour = (new Tour_category())->all();
        // dd($Danh_Muc_Tour);
        return $this->render('danh-muc.index', [
            'title' => 'Trang chủ123432VÉDCEDFDC',
            'Danh_Muc_Tour' => $Danh_Muc_Tour
        ]);
    }

    public function create(Request $req): Response
    {
        return $this->render('danh-muc.create');
    }
    

    public function store(Request $req): Response
{
    // Lấy dữ liệu từ request và normalize
    $data = [
        'name'        => trim((string)$req->input('name')),
        'description' => trim((string)$req->input('description')),
        // checkbox thường gửi 'on' khi checked; nếu không gửi thì là null
        'is_active'   => $req->input('is_active') !== null ? 1 : 0,
    ];
  

    // Luật kiểm tra
    $rules = [
        'name'        => 'required|string|min:5|max:255|unique:tour_category,name',
        'description' => 'nullable|string',
        'is_active'   => 'required|in:0,1', // hoặc 'boolean' nếu validator hỗ trợ
    ];

    // Thông điệp lỗi
    $messages = [
        'name.required' => 'Vui lòng nhập tên danh mục.',
        'name.max'      => 'Tên danh mục tối đa 255 ký tự.',
        'name.min'      => 'Tên danh mục tối thiểu 5  ký tự.',
        'name.unique'   => 'Tên danh mục đã tồn tại. Vui lòng chọn tên khác.',

        'description.string' => 'Mô tả không hợp lệ.',

        'is_active.required' => 'Trạng thái danh mục không hợp lệ.',
        'is_active.in'       => 'Trạng thái danh mục không hợp lệ.',
    ];

    // Validate
    $v = new Validator($data, $rules, $messages);
    if ($v->fails()) {
        return $this->render('danh-muc.create', [
            'errors' => $v->errors(),
            'old'    => $data
        ]);
    }

    try {
        // Tạo category mới
        (new Tour_category())->create([
            'name'        => $data['name'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'is_active'   => (int)$data['is_active'],
        ]);
      
        $_SESSION['flash_success'] = 'Thêm danh mục thành công.';
        // Chuyển hướng về danh sách (hoặc trang bạn muốn)
        return $this->redirect(route('danhMuc.index'));
    } catch (\Throwable $e) {
        // Log lỗi ở đây nếu cần: error_log($e->getMessage());
        return $this->render('danh-muc.create', [
            'errors' => [
                'general' => ['Không thể thêm danh mục. Vui lòng thử lại sau.']
            ],
            'old' => $data
        ]);
    }
}


public function edit(Request $req): Response
{
    // đảm bảo session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Lấy id từ route params (chuẩn hóa)
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;
    if ($id <= 0) {
        $_SESSION['flash_error'] = "ID không hợp lệ.";
        return $this->redirect(route('danhMuc.index'));
    }

    try {
        // Tìm danh mục theo id (model Tour_category cần có phương thức find)
        $category = (new Tour_category())->find($id);

        if (!$category) {
            $_SESSION['flash_error'] = "Không tìm thấy danh mục có ID #{$id}.";
            return $this->redirect(route('danhMuc.index'));
        }

        // Render view edit, truyền biến 'item' để nhất quán với form
        return $this->render('danh-muc.edit', [
            'item'   => $category,
            'errors' => []    // view mong đợi $errors khi validation thất bại
        ]);
    } catch (\Throwable $e) {
        // Log để debug (file log hoặc error_log)
        error_log('[danhMuc.edit] Lỗi khi load danh mục id=' . $id . ' — ' . $e->getMessage());
        $_SESSION['flash_error'] = "Lỗi khi tải dữ liệu danh mục. Vui lòng thử lại sau.";
        return $this->redirect(route('danhMuc.index'));
    }
}


public function update(Request $req): Response
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Lấy id từ params (Router đã đưa vào $req->params)
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

    if ($id <= 0) {
        $_SESSION['flash_error'] = "ID không hợp lệ.";
        return $this->redirect(route('danhMuc.index'));
    }


    // Lấy dữ liệu từ form
    $data = [
        'name'        => trim((string)$req->input('name')),
        'description' => trim((string)$req->input('description')),
        'is_active'   => $req->input('is_active') !== null ? 1 : 0,
    ];

    // Rule kiểm tra (unique bỏ chính nó)
    $rules = [
        'name'        => "required|string|min:5|max:255|unique:tour_category,name,{$id},id",
        'description' => 'nullable|string',
      
    ];

    // Thông điệp lỗi
    $messages = [
        'name.required' => 'Vui lòng nhập tên danh mục.',
        'name.max'      => 'Tên danh mục tối đa 255 ký tự.',
        'name.min'      => 'Tên danh mục tối thiểu 5 ký tự.',
        'name.unique'   => 'Tên danh mục đã tồn tại.',

        'description.string' => 'Mô tả không hợp lệ.',
    ];

    // Validate
    $v = new Validator($data, $rules, $messages);
    if ($v->fails()) {
        return $this->render('danh-muc.edit', [
            'errors' => $v->errors(),
            'old'    => $data,
        ]);
    }

    try {
        // Cập nhật
        (new Tour_category())->update($id, [
            'name'        => $data['name'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
        ]);

        $_SESSION['flash_success'] = 'Cập nhật danh mục thành công.';
        return $this->redirect(route('danhMuc.index'));
    } catch (\Throwable $e) {
        return $this->render('danh-muc.edit', [
            'errors' => [
                'general' => ['Không thể cập nhật danh mục. Vui lòng thử lại sau.']
            ],
            'old'  => $data,
        ]);
    }
}


public function updateActive(Request $req): Response
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Lấy id từ URL
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

    if ($id <= 0) {
        $_SESSION['flash_error'] = "ID không hợp lệ.";
        return $this->redirect(route('danhMuc.index'));
    }

    try {
        $model = new Tour_category();

        // Lấy bản ghi hiện tại
        $item = $model->find($id);
        if (!$item) {
            $_SESSION['flash_error'] = "Không tìm thấy danh mục có ID #{$id}.";
            return $this->redirect(route('danhMuc.index'));
        }

        // Toggle hoặc cập nhật trạng thái
        $newStatus = $item['is_active'] ? 0 : 1;

        $model->update($id, [
            'is_active' => $newStatus
        ]);

        $_SESSION['flash_success'] = "Cập nhật trạng thái thành công.";
        return $this->redirect(route('danhMuc.index'));

    } catch (\Throwable $e) {
        error_log('[updateActive] ' . $e->getMessage());
        $_SESSION['flash_error'] = "Lỗi khi cập nhật trạng thái.";
        return $this->redirect(route('danhMuc.index'));
    }
}


   public function delete(Request $req): Response
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Lấy ID từ URL
    // Giả định ID được truyền qua route parameter (ví dụ: /danh-muc/delete/{id})
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

    if ($id <= 0) {
        $_SESSION['flash_error'] = "ID danh mục không hợp lệ.";
        return $this->redirect(route('danhMuc.index'));
    }

    try {
        // Khởi tạo Model Danh mục Tour
        $Tour_category = new Tour_category();

        // 2. Tìm Danh mục trước khi xóa để lấy tên (và kiểm tra sự tồn tại)
        // Lưu ý: Tên biến $Tour nên đổi thành $category hoặc $danhMuc
        $category = $Tour_category->find($id);

        if (!$category) {
            $_SESSION['flash_error'] = "Không tìm thấy danh mục để xóa (ID: {$id}).";
            return $this->redirect(route('danhMuc.index'));
        }

        // Lấy tên danh mục để hiển thị trong thông báo
        $categoryName = $category['name'] ?? 'có ID là ' . $id;

        // 3. Xóa
        // Nên kiểm tra logic ràng buộc (Foreign Key Constraints) ở đây.
        // Nếu việc xóa danh mục bị chặn vì các tour đang tham chiếu,
        // Model của bạn nên trả về false hoặc throw exception.
        $deleted = $Tour_category->delete($id);

        if ($deleted) {
            // Sửa thông báo thành công: dùng tên danh mục thay vì username
            $_SESSION['flash_success'] = "Xóa danh mục <strong>{$categoryName}</strong> thành công.";
        } else {
            // Sửa thông báo lỗi: nếu Model không xóa được (có thể do ràng buộc FK)
            $_SESSION['flash_error'] = "Không thể xóa danh mục <strong>{$categoryName}</strong>. Danh mục có thể đang được sử dụng.";
        }

        return $this->redirect(route('danhMuc.index'));
        
    } catch (\Throwable $e) {
        // Lỗi hệ thống/Lỗi DB không mong muốn (ví dụ: kết nối, cú pháp SQL)
        
        // Bạn nên log $e ở đây để theo dõi lỗi chi tiết
        // error_log("Lỗi xóa danh mục: " . $e->getMessage()); 
        
        $_SESSION['flash_error'] = "Lỗi hệ thống khi xóa danh mục. Vui lòng kiểm tra log.";
        return $this->redirect(route('danhMuc.index'));
    }
}

    
}