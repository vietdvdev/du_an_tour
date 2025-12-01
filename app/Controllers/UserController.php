<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\GuideProfile;
use App\Support\Validator;

class UserController extends BaseController
{
    /**
     * 1. DANH SÁCH QUẢN TRỊ VIÊN (ROLE = 1)
     */
    // --- 1. DANH SÁCH ADMIN (Role=1) ---
    public function indexAdmin()
    {
      
        // Lấy list admin
        $ListUsers = (new User())->where('role', 0);

        // Render view cũ: tai-khoan/admin/index.php
        return $this->render('tai-khoan/admin/index', [
            'ListUsers' => $ListUsers,
            'pageTitle' => 'Danh sách Quản trị viên'
        ]);
    }

    // --- 2. DANH SÁCH HƯỚNG DẪN VIÊN (Role=0) ---
    public function indexGuide()
    {
        
        // Lấy list HDV
        $ListUsers = (new User())->where('role', 1);

        // Render view MỚI: tai-khoan/guide/index.php
        return $this->render('tai-khoan/guide/index', [
            'ListUsers' => $ListUsers,
            'pageTitle' => 'Danh sách Hướng dẫn viên'
        ]);
    }
   
public function toggleStatus(Request $req): Response
{
    // 1. Lấy ID
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;
    
    // 2. Tìm User
    $userModel = new User();
    $user = $userModel->find($id);

    if (!$user) {
        $_SESSION['flash_error'] = 'Không tìm thấy tài khoản.';
        // Quay lại trang danh sách tương ứng (ở đây ví dụ quay lại trang guide)
        return $this->redirect(route('guide.index')); 
    }

    // 3. Đảo trạng thái
    $newStatus = $user['is_active'] == 1 ? 0 : 1;

    try {
        $userModel->update($id, ['is_active' => $newStatus]);
        
        // Thông báo thành công
        $msg = $newStatus == 1 ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.';
        $_SESSION['flash_success'] = $msg;
        
    } catch (\Throwable $e) {
        $_SESSION['flash_error'] = 'Lỗi hệ thống: ' . $e->getMessage();
    }

    // 4. Chuyển hướng quay lại trang danh sách (Reload trang)
    // Bạn có thể logic kiểm tra role để redirect về admin hay guide index tùy ý
    return $this->redirect(route('guide.index'));
}
    /**
     * FORM THÊM MỚI (Dùng chung)
     */
    public function create(Request $req): Response
    {
        return $this->render('tai-khoan.admin.create');
    }

    /**
     * XỬ LÝ LƯU (STORE) - CÓ CHỌN ROLE
     */
    public function store(Request $req): Response
    {
        $data = [
            'username'              => trim((string)$req->input('username')),
            'password'              => (string)$req->input('password'),
            'password_confirmation' => (string)$req->input('password_confirmation'),
            'full_name'             => trim((string)$req->input('full_name')),
            'email'                 => trim((string)$req->input('email')),
            'phone'                 => trim((string)$req->input('phone')),
            'role'                  => (int)$req->input('role'), // <--- Lấy Role từ form
        ];

        // Luật kiểm tra
        $rules = [
            'username'  => 'required|string|min:3|max:100|regex:/^[A-Za-z0-9_.-]+$/|unique:users,username',
            'password'  => 'required|min:6|confirmed',
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'phone'     => 'nullable|string|max:30|regex:/^[0-9+()\\-\\s]{6,30}$/',
            'role'      => 'integer', // Đảm bảo role là số
        ];

        $messages = [
            'username.required'  => 'Vui lòng nhập tên đăng nhập.',
            'username.unique'    => 'Tên đăng nhập đã được sử dụng.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'email.required'     => 'Vui lòng điền email.',
            'full_name.required' => 'Vui lòng điền họ tên.',
        ];

        // Validate
        $v = new Validator($data, $rules, $messages);
        if ($v->fails()) {
            return $this->render('tai-khoan.admin.create', [
                'errors' => $v->errors(),
                'old'    => $data
            ]);
        }

        // Mặc định Active
        $isActive = 1;

        try {
            (new User())->create([
                'username'      => $data['username'],
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
                'full_name'     => $data['full_name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] !== '' ? $data['phone'] : null,
                'role'          => $data['role'], // Lưu đúng role đã chọn
                'is_active'     => $isActive,
            ]);

            $_SESSION['flash_success'] = 'Thêm tài khoản thành công.';
            return $this->redirect(route('admin.create'));
        } catch (\Throwable $e) {
            return $this->render('tai-khoan.admin.create', [
                'errors' => [
                    'general' => ['Lỗi hệ thống: ' . $e->getMessage()]
                ],
                'old' => $data
            ]);
        }
    }

    /**
     * FORM CHỈNH SỬA
     */
  public function edit(Request $req): Response
    {
        
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Lấy ID từ URL
        $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID không hợp lệ.";
            return $this->redirect(route('admin.index'));
        }

        // 2. Tìm User trong bảng users
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $_SESSION['flash_error'] = "Không tìm thấy người dùng #{$id}.";
            return $this->redirect(route('admin.index'));
        }

        // 3. Chuẩn bị dữ liệu để đổ ra form ($old)
        // Mặc định $old chính là thông tin từ bảng users
        $mergedData = $user; 
        
        // 4. Nếu là Hướng dẫn viên (role = 1), lấy thêm thông tin từ bảng guide_profile
        if ((int)$user['role'] === 1) {
            $guideProfileModel = new GuideProfile();
            // Tìm bản ghi profile theo user_id
            $profile = $guideProfileModel->firstWhere('user_id', $id);
            
            if ($profile) {
                // Gộp mảng profile vào mảng user
                // Khi gộp, các key như 'dob', 'license_number'... của profile sẽ được thêm vào $mergedData
                // View chỉ cần gọi $old['license_number'] là có dữ liệu
                $mergedData = array_merge($user, $profile);
            }
        }

        // 5. Trả về view với dữ liệu đã gộp
        return $this->render('tai-khoan.admin.edit', [
            'user'   => $user,        // Dữ liệu gốc bảng users (để dùng cho các logic kiểm tra id, role gốc)
            'old'    => $mergedData,  // Dữ liệu đầy đủ (User + Profile) để điền vào form
            'errors' => []
        ]);
    }

    /**
     * XỬ LÝ CẬP NHẬT (UPDATE)
     */
       public function update(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        
        // 1. Lấy dữ liệu user
        $data = [
            'username'  => trim((string)$req->input('username')),
            'full_name' => trim((string)$req->input('full_name')),
            'email'     => trim((string)$req->input('email')),
            'phone'     => trim((string)$req->input('phone')),
            'role'      => (int)$req->input('role'),
        ];
        
        // Rules (unique ngoại trừ ID hiện tại)
        $rules = [
            'username'  => 'required|string|min:3|max:100|regex:/^[A-Za-z0-9_.-]+$/|unique:users,username,' . $id,
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $id,
            'phone'     => 'nullable|integer|max:30',
            'role'      => 'integer',
        ];

        // Nếu nhập mật khẩu thì mới validate password
        if ($req->input('password') !== '') {
            $rules['password'] = 'min:6|confirmed';
        }

        // Thông điệp lỗi tùy chỉnh
        $messages = [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.min'      => 'Tên đăng nhập phải có ít nhất 3 ký tự.',
            'username.max'      => 'Tên đăng nhập tối đa 100 ký tự.',
            'username.regex'    => 'Tên đăng nhập không được chứa ký tự đặc biệt.',
            'username.unique'   => 'Tên đăng nhập này đã được sử dụng.',

            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max'      => 'Họ tên tối đa 255 ký tự.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email không hợp lệ.',
            'email.max'      => 'Email tối đa 255 ký tự.',
            'email.unique'   => 'Email này đã được sử dụng bởi tài khoản khác.',

            'phone.integer' => 'Số điện thoại chỉ được chứa số.',
            'phone.max'     => 'Số điện thoại quá dài.',

            'password.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];

        // Truyền $messages vào Validator (tham số thứ 3)
        $v = new Validator($data, $rules, $messages);
        
        if ($v->fails()) {
            $dataWithId = $data;
            $dataWithId['id'] = $id;
            // Merge lại profile cũ để form không bị mất dữ liệu
            if ($data['role'] == 1) {
                $oldProfile = (new GuideProfile())->firstWhere('user_id', $id) ?? [];
                $dataWithId = array_merge($dataWithId, $oldProfile);
            }
            return $this->render('tai-khoan.admin.edit', [
                'errors' => $v->errors(),
                'old'    => $dataWithId
            ]);
        }

        try {
            // 2. Cập nhật bảng Users
            $updateData = $data;
            if ($req->input('password')) {
                $updateData['password_hash'] = password_hash($req->input('password'), PASSWORD_BCRYPT);
            }
            (new User())->update($id, $updateData);

            // 3. Cập nhật Profile nếu là HDV
            if ($data['role'] == 1) {
                $profileModel = new GuideProfile();
                $exists = $profileModel->firstWhere('user_id', $id);

                // --- XỬ LÝ UPLOAD ẢNH ---
                $avatarUrl = null;
                
                // Kiểm tra xem có file upload không
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    
                    // Sử dụng đường dẫn tuyệt đối theo yêu cầu của bạn
                    $uploadDir = __DIR__ . '/../../public/uploads/users/';
                    
                    // Tạo thư mục nếu chưa có
                    if (!is_dir($uploadDir)) {
                        if (!mkdir($uploadDir, 0755, true)) {
                            throw new \Exception('Không thể tạo thư mục lưu ảnh. Vui lòng kiểm tra quyền ghi (permissions).');
                        }
                    }

                    $fileName = $_FILES['avatar']['name'];
                    $fileTmp = $_FILES['avatar']['tmp_name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Chỉ cho phép ảnh
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($fileExt, $allowed)) {
                        // Đặt tên file duy nhất: avatar_{userid}_{timestamp}.ext
                        $newFileName = "avatar_{$id}_" . time() . ".{$fileExt}";
                        $destPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmp, $destPath)) {
                            // Lưu đường dẫn web (tương đối) để hiển thị trên trình duyệt
                            $avatarUrl = '/uploads/users/' . $newFileName;
                        } else {
                            throw new \Exception('Lỗi khi lưu file ảnh. Vui lòng thử lại.');
                        }
                    } else {
                        throw new \Exception('Định dạng file không hợp lệ. Vui lòng chỉ chọn ảnh (JPG, PNG, GIF, WEBP).');
                    }
                } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                    // Trường hợp có file nhưng bị lỗi khác (ví dụ file quá lớn)
                    throw new \Exception('Lỗi tải lên ảnh: Mã lỗi ' . $_FILES['avatar']['error']);
                }
                // -------------------------

                $profileData = [
                    'dob'              => $req->input('dob') ?: null,
                    'gender'           => $req->input('gender') ?: 'OTHER',
                    'license_number'   => $req->input('license_number'),
                    'license_expiry'   => $req->input('license_expiry') ?: null,
                    'experience_years' => (int)$req->input('experience_years'),
                    'languages'        => $req->input('languages'),
                    'bio'              => $req->input('bio'),
                ];

                // Nếu có upload ảnh mới thành công thì cập nhật trường avatar_url
                if ($avatarUrl) {
                    $profileData['avatar_url'] = $avatarUrl;
                }

                if ($exists) {
                    $profileModel->builder()->where('user_id', $id)->update($profileData);
                } else {
                    $profileData['user_id'] = $id;
                    $profileModel->create($profileData);
                }
            }

            $_SESSION['flash_success'] = 'Cập nhật thành công.';
            return $this->redirect(route('admin.edit', ['id' => $id]));

        } catch (\Throwable $e) {
            return $this->render('tai-khoan.admin.edit', [
                'errors' => ['general' => ['Lỗi: ' . $e->getMessage()]],
                'old'    => $_POST
            ]);
        }
    }

    /**
     * XÓA USER
     */
    public function delete(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID không hợp lệ.";
            return $this->redirect(route('admin.index'));
        }

        try {
            $userModel = new User();
            $user = $userModel->find($id);

            if (!$user) {
                $_SESSION['flash_error'] = "Không tìm thấy người dùng.";
                return $this->redirect(route('admin.index'));
            }

            // Lưu lại role cũ để redirect về đúng trang danh sách
            $oldRole = (int)$user['role'];

            // Thực hiện xóa
            $deleted = $userModel->delete($id);

            if ($deleted) {
                $_SESSION['flash_success'] = "Đã xóa tài khoản: <strong>{$user['username']}</strong>";
            } else {
                $_SESSION['flash_error'] = "Không thể xóa. Vui lòng thử lại.";
            }

            // Điều hướng về trang danh sách tương ứng
            if ($oldRole === 1) {
                return $this->redirect(route('admin.index')); // Về list admin
            } else {
                return $this->redirect(route('guide.index')); // Về list hướng dẫn viên
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống khi xóa.";
            return $this->redirect(route('admin.index'));
        }
    }

    /**
     * XEM CHI TIẾT (READ ONLY)
     */
    public function detail(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        
        // 1. Tìm User
        $user = (new User())->find($id);

        if (!$user) {
            $_SESSION['flash_error'] = 'Không tìm thấy tài khoản.';
            return $this->redirect(route('admin.index'));
        }

        // 2. Nếu là HDV, tìm Profile
        $profile = [];
        if ($user['role'] == 1) {
            $profile = (new GuideProfile())->firstWhere('user_id', $id);
        }

        return $this->render('tai-khoan.admin.detail', [
            'user'    => $user,
            'profile' => $profile // Có thể null hoặc mảng rỗng nếu chưa có profile
        ]);
    }
}
