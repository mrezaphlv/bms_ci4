<?php

namespace App\Controllers;

use Config\Services;

class Root extends BaseController
{
    protected $session;
    protected $agent;

    public function __construct()
    {
        $this->session = session();
        $this->agent   = Services::userAgent();
    }

    public function index()
    {
        if ($this->session->get('login')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('pages/login/vwelcome');
    }

    public function login()
    {
        $url_forgot = URL_IPI_SSO_FORGOT;
        $id_apps = encrypt(ID_APPS);

        $data = [
            'url_forgot' => $url_forgot . '/?app=' . $id_apps
        ];

        if ($this->session->get('login')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('pages/login/vlogin', $data);
    }

    public function login_direct()
    {
        if ($this->session->get('login')) {
            return redirect()->to(site_url('dashboard'));
        }

        $uri = service('uri');

        $data = [
            'page' => $uri->getSegment(3) . '/' . $uri->getSegment(4),
            'param' => $this->request->getGet('p')
        ];

        return view('pages/login/vlogin_direct', $data);
    }

    public function logout()
    {
        if ($this->session->get('token') && $this->session->get('id_apps')) {

            $dataLogout = [
                'id_apps' => $this->session->get('id_apps'),
                'token'   => $this->session->get('token')
            ];

            $res = apiwebsec('POST', 'user/logout', $dataLogout);

            if ($res->status) {

                $this->session->destroy();

                return redirect()->to(site_url('login'));
            }

            return redirect()->to(site_url('dashboard'));
        }

        return redirect()->to(site_url('login'));
    }

    public function login_check()
    {
        $email = $this->request->getPost('email');
        $passwd = $this->request->getPost('passwd');

        if (!empty($email) && !empty($passwd)) {

            $dataLogin = [
                'id_apps'    => ID_APPS,
                'email'      => esc($email),
                'password'   => esc($passwd),
                'ip_address' => $this->request->getIPAddress()
            ];

            if ($this->agent->getPlatform()) {
                $dataLogin['platform'] = $this->agent->getPlatform();
            }

            if ($this->agent->getBrowser()) {
                $dataLogin['browser'] = $this->agent->getBrowser();
            }

            $res = apiwebsec('POST', 'login', $dataLogin);

            if ($res->status) {

                if ($res->data->homepage != null) {

                    $this->session->set([
                        'login' => true,
                        'nama' => $res->data->nama,
                        'username' => $res->data->username,
                        'email' => $res->data->email,
                        'id_user' => $res->data->id,
                        'role' => $res->data->role,
                        'pin' => $res->data->pin,
                        'id_apps' => $res->data->id_apps,
                        'id_departemen' => $res->data->id_departemen,
                        'nama_departemen' => $res->data->nama_departemen,
                        'homepage' => $res->data->homepage,
                        'token' => $res->token
                    ]);

                    return redirect()->to(site_url('dashboard'));

                }

                return redirect()->back()->with('error', 'Empty Default Homepage!');
            }

            return redirect()->back()->with('error', $res->msg);

        }

        return redirect()->back()->with('error', 'Empty Email & Password!');
    }

    public function login_check_direct()
    {
        $uri = service('uri');

        $page = $uri->getSegment(3) . '/' . $uri->getSegment(4);
        $param = $this->request->getGet('p');

        $url_login = site_url('root/login_direct/' . $page . '?p=' . $param);

        if ($this->request->getPost('email') && $this->request->getPost('passwd')) {

            $dataLogin = [
                'id_apps' => 9,
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('passwd')
            ];

            $res = apiwebsec('POST', 'login', $dataLogin);

            if ($res->status) {

                if ($res->data->homepage != null) {

                    $this->session->set([
                        'login' => true,
                        'nama' => $res->data->nama,
                        'username' => $res->data->username,
                        'email' => $res->data->email,
                        'id_user' => $res->data->id,
                        'role' => $res->data->role,
                        'pin' => $res->data->pin,
                        'id_apps' => $res->data->id_apps,
                        'id_departemen' => $res->data->id_departemen
                    ]);

                    return redirect()->to(site_url($page . '?p=' . $param));

                }

                return redirect()->to($url_login)->with('error', 'Empty Default Homepage!');
            }

            return redirect()->to($url_login)->with('error', $res->msg);
        }

        return redirect()->to($url_login)->with('error', 'Empty Email & Password!');
    }

    public function cekPin()
    {
        $status = md5($this->request->getPost('pin')) == $this->session->get('pin');

        return $this->response->setJSON([
            'status' => $status,
            'msg' => $status ? 'Pin Benar' : 'Pin Anda Salah'
        ]);
    }

    public function cron_invoice()
    {
        api('GET', 'cron_invoice/generate', null);

        return $this->response->setBody('OK');
    }

    public function cek_addr()
    {
        return $this->response->setBody($this->request->getIPAddress());
    }
}