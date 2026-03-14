# PHP MVC OOP Base — PRO (Laragon + MySQL)

Đầy đủ hàm & hằng cơ bản cho dự án sinh viên: PSR-4 autoload, Router có group/middleware,
Request/Response, View + layout, Database (PDO), Session, CSRF, Validator, Logger, ErrorHandler,
BaseModel CRUD, assets (CSS/JS/Images).

## Quick start
1) Giải nén vào `C:\laragon\www\your-project`
2) `composer dump-autoload`
3) Sao chép `.env.example` → `.env` và cập nhật DB
4) Tự tạo database + bảng `users` (hoặc sửa Model/Controller theo DB của bạn)
5) Trỏ Document Root đến `public/` ➜ truy cập `http://localhost/your-project/public`

## Cấu trúc chính
- `public/` — index.php, .htaccess, assets (css/js/images)
- `app/Core/` — Env, Router, Request, Response, View, Database, Session, Validator, Logger, ErrorHandler
- `app/Middleware/` — MiddlewareInterface, CsrfMiddleware, ExampleMiddleware
- `app/Controllers/` — BaseController, HomeController, UserController
- `app/Models/` — BaseModel (CRUD), User
- `app/Support/` — helpers.php (dd, env, str_random, redirect, old(), csrf_field(), ...)
- `app/Config/` — constants.php, config.php
- `app/routes/web.php` — routes + group + middleware ví dụ
- `views/` — layouts/main.php, home/, users/
