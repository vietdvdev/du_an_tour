<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Chuyển hướng tất cả request vào thư mục public/
header("Location: public/");
exit;
