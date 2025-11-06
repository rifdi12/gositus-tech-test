<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Redirect to dashboard if logged in, otherwise to login
        $session = \Config\Services::session();
        
        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        } else {
            return redirect()->to('/login');
        }
    }
}
