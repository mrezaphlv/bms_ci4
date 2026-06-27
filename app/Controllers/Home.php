<?php

namespace App\Controllers;

class Home extends MyController
{
    public function index(): string
    {
        $data = $this->pageData('Home', 'Dashboard');
        $data['url_sso'] = URL_IPI_SSO;
        $data['nama'] = (string) $this->session->get('nama');
        $data['username'] = (string) ($this->session->get('username') ?: $this->session->get('email'));
        $data['email'] = (string) $this->session->get('email');
        $data['token'] = (string) $this->session->get('token');
        $data['id_apps'] = (string) $this->session->get('id_apps');

        return $this->template('V_home', $data);
    }
}
