<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected $userModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('user_role');
            if ($role === 'agent') {
                return redirect()->to(site_url('agent/dashboard'));
            }
            if ($role === 'student') {
                return redirect()->to(site_url('student/dashboard'));
            }
            return redirect()->to(site_url('student/dashboard'));
        }

        return view('auth/login', [
            'validation' => service('validation'),
        ]);
    }

    public function attempt()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return view('auth/login', [
                'validation' => $this->validator,
            ]);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $user = $this->userModel->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user->password)) {
            return view('auth/login', [
                'validation' => service('validation'),
                'loginError' => 'Invalid email or password.',
            ]);
        }

        $session = session();
        $session->set([
            'isLoggedIn' => true,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'office_id' => $user->office_id,
            'user_name' => $user->name,
        ]);

        return redirect()->to(site_url('/'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'You have been logged out.');
    }
}
