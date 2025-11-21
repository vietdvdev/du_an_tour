<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Support\Validator;

class UserController extends BaseController
{

    public function index()
    {
        $ListUsers = (new User())->all();
        return $this->render('tai-khoan/admin/index', ['ListUsers' => $ListUsers]);
    }

    public function create(Request $req): Response
    {
        return $this->render('tai-khoan.admin.create');
    }
    
    public function store(Request $req): Response
    {

        $data = [
            'username'              => trim((string)$req->input('username')),
            'password'              => (string)$req->input('password'),
            'password_confirmation' => (string)$req->input('password_confirmation'),
            'full_name'             => trim((string)$req->input('full_name')),
            'email'                 => trim((string)$req->input('email')),
            'phone'                 => trim((string)$req->input('phone')),
        ];

        // Luật kiểm tra
        $rules = [
            'username'  => 'required|string|min:3|max:100|regex:/^[A-Za-z0-9_.-]+$/|unique:users,username',
            'password'  => 'required|min:6|confirmed',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone'     => 'nullable|string|max:30|regex:/^[0-9+()\\-\\s]{6,30}$/',
        ];

        // Thông điệp lỗi
        $messages = [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.min'      => 'Tên đăng nhập phải có ít nhất 3 ký tự.',
            'username.max'      => 'Tên đăng nhập tối đa 100 ký tự.',
            'username.regex'    => 'Tên đăng nhập chỉ gồm chữ, số, dấu gạch dưới, chấm hoặc gạch ngang.',
            'username.unique'   => 'Tên đăng nhập đã được sử dụng.',

            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

            'full_name.string' => 'Họ tên không hợp lệ.',
            'full_name.max'    => 'Họ tên tối đa 255 ký tự.',
            'full_name.required'    => 'Vui lòng điền Đầy đủ họ tên.',

            'email.email' => 'Email không hợp lệ.',
            'email.max'   => 'Email tối đa 255 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
            'email.required' => 'Điền email của tài khoản.',

            'phone.max'   => 'Số điện thoại tối đa 30 ký tự.',
            'phone.regex' => 'Số điện thoại chỉ được chứa số, khoảng trắng, +, -, (), và dài tối thiểu 6 ký tự.',
        ];

        // Kiểm tra hợp lệ
        $v = new Validator($data, $rules, $messages);
        if ($v->fails()) {
            return $this->render('tai-khoan.admin.create', [
                'errors' => $v->errors(),
                'old'    => $data
            ]);
        }

        // Gán mặc định
        $role = 0;
        $isActive = 1;

        try {
            // Tạo user mới
            (new User())->create([
                'username'      => $data['username'],
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
                'full_name'     => $data['full_name'],
                'email'         => $data['email'],
                'phone'         => $data['phone']     !== '' ? $data['phone']     : null,
                'role'          => $role,
                'is_active'     => $isActive,
            ]);

            $_SESSION['flash_success'] = 'Thêm người dùng thành công.';
            // Thành công → chuyển hướng danh sách
            return $this->redirect(route('admin.create'));
        } catch (\Throwable $e) {
            // Trả về render với lỗi chung (không redirect) => view giữ old + hiển thị lỗi
            return $this->render('tai-khoan.admin.create', [
                'errors' => [
                    'general' => ['Không thể thêm người dùng. Vui lòng thử lại sau.']
                ],
                'old' => $data
            ]);
        }
    }

    public function edit(Request $req): Response
    {
        dd('duantua');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Lấy id từ request params
        $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID không hợp lệ.";
            return $this->redirect(route('admin.index'));
        }

        try {
            $user = (new User())->find($id);
            //    dd($user);
            if (!$user) {
                $_SESSION['flash_error'] = "Không tìm thấy người dùng có ID #{$id}.";
                return $this->redirect(route('admin.index'));
            }

            return $this->render('tai-khoan.admin.edit', [
                'user'   => $user,
                'errors' => []
            ]);
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi khi tải dữ liệu người dùng.";
            return $this->redirect(route('admin.index'));
        }
    }


    public function update(Request $req): Response
{
    // đảm bảo session (flash)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Lấy id từ params (router của bạn đặt id trong $req->params['id'])
    $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;
    if ($id <= 0) {
        $_SESSION['flash_error'] = 'ID không hợp lệ.';
        return $this->redirect(route('admin.index'));
    }

    // Thu thập dữ liệu từ form
    $data = [
        'username'              => trim((string)$req->input('username')),
        'password'              => (string)$req->input('password'),
        'password_confirmation' => (string)$req->input('password_confirmation'),
        'full_name'             => trim((string)$req->input('full_name')),
        'email'                 => trim((string)$req->input('email')),
        'phone'                 => trim((string)$req->input('phone')),
    ];

    // Luật kiểm tra (giống store nhưng unique bỏ qua chính record hiện tại)
    $rules = [
        'username'  => 'required|string|min:3|max:100|regex:/^[A-Za-z0-9_.-]+$/|unique:users,username,' . $id,
        // password không bắt buộc khi edit; chỉ validate nếu nhập
        'full_name' => 'required|string|max:255',
        'email'     => 'required|email|max:255|unique:users,email,' . $id,
        // giữ regex giống store (cho phép +, (), -, space, 6-30 ký tự)
        'phone'     => 'nullable|integer|max:30|unique:users,phone,' . $id,
    ];

    // Nếu nhập mật khẩu thì kiểm tra min + confirmed
    if ($data['password'] !== '') {
        $rules['password'] = 'min:6|confirmed';
    }

    // Thông điệp lỗi (theo style của store)
    $messages = [
        'username.required' => 'Vui lòng nhập tên đăng nhập.',
        'username.min'      => 'Tên đăng nhập phải có ít nhất 3 ký tự.',
        'username.max'      => 'Tên đăng nhập tối đa 100 ký tự.',
        'username.regex'    => 'Tên đăng nhập chỉ gồm chữ, số, dấu gạch dưới, chấm hoặc gạch ngang.',
        'username.unique'   => 'Tên đăng nhập đã được sử dụng.',

        'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

        'full_name.string' => 'Họ tên không hợp lệ.',
        'full_name.max'    => 'Họ tên tối đa 255 ký tự.',
        'full_name.required' => 'Vui lòng điền họ tên.',

        'email.email' => 'Email không hợp lệ.',
        'email.max'   => 'Email tối đa 255 ký tự.',
        'email.unique' => 'Email này đã được sử dụng.',
        'email.required' => 'Vui lòng điền email của tài khoản.',

        'phone.max'   => 'Số điện thoại tối đa 30 ký tự.',
        'phone.integer'   => 'Chỉ được điền số trong mục điện thoại',
        'phone.unique' => 'Số điện thoại đã được sử dụng.',
    ];

    // Validate
    $v = new Validator($data, $rules, $messages);
   if ($v->fails()) {
    // đảm bảo old có id để form action và các field khác vẫn hoạt động
    $dataWithId = $data;
    $dataWithId['id'] = $id;

    return $this->render('tai-khoan.admin.edit', [
        'errors' => $v->errors(),
        'old'    => $dataWithId
    ]);
}

    // Chuẩn bị dữ liệu cập nhật
    $updateData = [
        'username'  => $data['username'],
        'full_name' => $data['full_name'] !== '' ? $data['full_name'] : null,
        'email'     => $data['email'] !== '' ? $data['email'] : null,
        'phone'     => $data['phone'] !== '' ? $data['phone'] : null,
    ];

    if ($data['password'] !== '') {
        $updateData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    try {
        $userModel = new User();
        $ok = $userModel->update($id, $updateData);

        if ($ok) {
            $_SESSION['flash_success'] = 'Cập nhật người dùng thành công.';
            return $this->redirect(route('admin.edit', ['id' => $id]));
        } else {
            // Nếu không có row affected (có thể data không thay đổi), vẫn redirect với info
            $_SESSION['flash_info'] = 'Không có thay đổi nào được lưu.';
            return $this->redirect(route('admin.edit', ['id' => $id]));
        }
    } catch (\Throwable $e) {
        // Trả về view edit với lỗi chung và giữ dữ liệu cũ để user sửa
        return $this->render('tai-khoan.admin.edit', [
            'errors' => [
                'general' => ['Không thể cập nhật người dùng. Vui lòng thử lại sau.']
            ],
            'old' => $data
        ]);
    }
}



    public function delete(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Lấy ID từ URL
        $id = isset($req->params['id']) ? (int)$req->params['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID không hợp lệ.";
            return $this->redirect(route('admin.index'));
        }

        try {
            $userModel = new User();

            // Tìm user trước khi xóa
            $user = $userModel->find($id);

            if (!$user) {
                $_SESSION['flash_error'] = "Không tìm thấy người dùng để xóa.";
                return $this->redirect(route('admin.index'));
            }

            // Xóa
            $deleted = $userModel->delete($id);

            if ($deleted) {
                $_SESSION['flash_success'] = "Xóa người dùng <strong>{$user['username']}</strong> thành công.";
            } else {
                $_SESSION['flash_error'] = "Không thể xóa người dùng. Vui lòng thử lại.";
            }

            return $this->redirect(route('admin.index'));
        } catch (\Throwable $e) {

            $_SESSION['flash_error'] = "Lỗi hệ thống khi xóa người dùng.";
            return $this->redirect(route('admin.index'));
        }
    }


}
