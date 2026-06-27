<?php

namespace App\Controllers;

abstract class MyController extends BaseController
{
    protected function template(string $view, array $data = []): string
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
