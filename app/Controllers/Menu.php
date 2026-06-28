<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use stdClass;

class Menu extends Controller
{
    public function get()
    {
        $session = session();

        $menu = apiwebsec('POST', 'menu/get3lvmenu', [
            'id_user' => $session->get('id_user'),
            'id_apps' => ID_APPS,
        ]);

        $ret = [];

        if (isset($menu->data) && is_array($menu->data)) {

            foreach ($menu->data as $m) {

                if ($m->segmen == 1 && ($m->has_access != 0 || $m->sub_menu != 0)) {

                    $parent = new stdClass();
                    $parent->text = $m->nama_menu;
                    $parent->children = [];

                    foreach ($menu->data as $m2) {

                        if (
                            $m2->segmen == 2 &&
                            $m2->header_id == $m->id &&
                            $m2->has_access != 0
                        ) {

                            $child = new stdClass();
                            $child->text = $m2->nama_menu;

                            if ($m2->controller !== '#') {
                                $child->url = site_url($m2->controller);
                            }

                            $child->children = [];

                            foreach ($menu->data as $m3) {

                                if (
                                    $m3->segmen == 3 &&
                                    $m3->header_id == $m2->id &&
                                    $m3->has_access != 0
                                ) {

                                    $sub = new stdClass();
                                    $sub->text = $m3->nama_menu;
                                    $sub->url = site_url($m3->controller);

                                    $child->children[] = $sub;
                                }
                            }

                            $parent->children[] = $child;
                        }
                    }

                    if (!empty($parent->children)) {
                        $ret[] = $parent;
                    }
                }
            }
        }

        return $this->response->setJSON($ret);
    }
}