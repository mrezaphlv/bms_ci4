<?php

namespace App\Controllers;

class Login extends BaseController
{
    public function index()
    {
        return redirect()->to(site_url('login'));
    }
}
