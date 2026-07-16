<?php

namespace App\Controllers;

abstract class MyController extends BaseController
{
    protected object $akses;

    public function __construct()
    {
        $this->akses = (object) [
            'can_view'    => 0,
            'can_create'  => 0,
            'can_edit'    => 0,
            'can_delete'  => 0,
            'can_approve' => 0,
            'nama_menu'   => '',
        ];

        if (! session()->get('login')) {
            return;
        }

        $controller = strtolower(service('router')->controllerName() ?? '');
        $controller = $controller !== '' ? strtolower(class_basename($controller)) : '';

        if ($controller === '') {
            return;
        }

        if (service('request')->isAJAX()) {
            $this->akses = (object) [
                'can_view'    => 1,
                'can_create'  => 1,
                'can_edit'    => 1,
                'can_delete'  => 1,
                'can_approve' => 1,
                'nama_menu'   => ucfirst($controller),
            ];

            return;
        }

        $res = apiwebsec('POST', 'akses', [
            'id_user'    => session()->get('id_user'),
            'id_apps'    => session()->get('id_apps') ?: ID_APPS,
            'controller' => $controller,
        ]);

        if (($res->status ?? false) && isset($res->data)) {
            $this->akses = $res->data;
        }
    }

    protected function template(string $view, array $data = []): string
    {
        return view('templates/include', $data). view($view, $data);
    }

     protected function template_menu(string $view, array $data = []): string
    {
        return view('templates/_head', $data)
            . view('templates/include', $data)
            . view($view, $data)
            . view('templates/_foot', $data);
    }

    protected function pageData(string $title, ?string $menu = null): array
    {
        return [
            'title' => $title,
            'menu'  => $menu,
        ];
    }
}
