<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Support\Validator;


class HomeController extends BaseController
{
    public function index(Request $request): Response
    {
        // Gọi Model để lấy toàn bộ dữ liệu
        $users = (new User())->all();
        // dd($users);
        return $this->render('home.index', [
            'title' => 'Trang chủ123432VÉDCEDFDC',
            'users' => $users
        ]);
    }

        public function test(Request $request): Response
        {
            return $this->render('home.test');
        }

        public function test2(Request $request): Response
        {
            return $this->render('home.test1');
        }


        public function layout(Request $request): Response
        {
            return $this->render('layout.indext');
        }
    
}