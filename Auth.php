<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel->getUserByUsername($username);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        if ($user['status'] != 1) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda tidak aktif.');
        }

        $sessionData = [
            'id'           => $user['id'],
            'username'     => $user['username'],
            'email'        => $user['email'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role'         => $user['role'],
            'isLoggedIn'   => true,
        ];

        session()->set($sessionData);

        return redirect()->to('/admin')->with('success', 'Selamat datang, ' . $user['nama_lengkap']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }
}
