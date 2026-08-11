<?php

namespace App\Controllers;

use App\Models\MChecklistEngineer;
use CodeIgniter\Exceptions\PageNotFoundException;

class Checklist_engineer extends MyController
{
    protected MChecklistEngineer $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new MChecklistEngineer();
    }

    public function index()
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('dashboard'));
        }

        return $this->template('pages/checklist_engineer/vchecklist_engineer', [
            'akses' => $this->akses,
        ]);
    }

    public function grid()
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return $this->response->setJSON([
                'total' => 0,
                'rows'  => [],
            ]);
        }

        $post = $this->request->getPost();
        $page = max(1, (int) ($post['page'] ?? 1));
        $rows = max(1, (int) ($post['rows'] ?? 10));

        $params = [
            'start'       => (($page - 1) * $rows),
            'length'      => $rows,
            'search'      => ['value' => (string) ($post['search_value'] ?? '')],
            'tb_checkbox' => $post['tb_checkbox'] ?? [],
            'order'       => [
                'column' => (string) ($post['sort'] ?? 'id'),
                'dir'    => (string) ($post['order'] ?? 'desc'),
            ],
        ];

        $result = $this->model->grid($params);

        return $this->response->setJSON([
            'total' => $result['count_all'] ?? 0,
            'rows'  => $result['data'] ?? [],
        ]);
    }

    public function input(int $id)
    {
        if (($this->akses->can_create ?? 0) != 1) {
            return redirect()->to(site_url('checklist_engineer'));
        }

        $data = $this->model->getInputData($id);
        if (! $data) {
            throw PageNotFoundException::forPageNotFound('Data checklist tidak ditemukan.');
        }

        return $this->template('pages/checklist_engineer/form', [
            'akses'                  => $this->akses,
            'mode'                   => 'create',
            'id_checklist'           => $id,
            'header'                 => $data['data'],
            'items'                  => $data['item'],
            'kategoriItemOptions'    => $this->model->getKategoriItemList(),
            'saveUrl'                => site_url('checklist_engineer/save'),
            'backUrl'                => site_url('checklist_engineer'),
        ]);
    }

    public function edit(int $id)
    {
        if (($this->akses->can_edit ?? 0) != 1) {
            return redirect()->to(site_url('checklist_engineer'));
        }

        $data = $this->model->getEditData($id);
        if (! $data) {
            throw PageNotFoundException::forPageNotFound('Data checklist tidak ditemukan.');
        }

        return $this->template('pages/checklist_engineer/form', [
            'akses'               => $this->akses,
            'mode'                => 'edit',
            'id_checklist'        => $id,
            'header'              => $data['data'],
            'items'               => $data['item'],
            'kategoriItemOptions' => $this->model->getKategoriItemList(),
            'saveUrl'             => site_url('checklist_engineer/update'),
            'backUrl'             => site_url('checklist_engineer'),
        ]);
    }

    public function view(int $id)
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('checklist_engineer'));
        }

        $data = $this->model->getEditData($id);
        if (! $data) {
            throw PageNotFoundException::forPageNotFound('Data checklist tidak ditemukan.');
        }

        return $this->template('pages/checklist_engineer/view', [
            'akses'        => $this->akses,
            'id_checklist' => $id,
            'header'       => $data['data'],
            'items'        => $data['item'],
        ]);
    }

    public function detailUndangan(int $id)
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('checklist_engineer'));
        }

        $detail = $this->model->getDetailUndangan($id);
        if (! $detail) {
            throw PageNotFoundException::forPageNotFound('Data detail checklist tidak ditemukan.');
        }

        return $this->template('pages/checklist_engineer/vdetail_undangan', [
            'akses'      => $this->akses,
            'id'         => $id,
            'dthead'     => $detail['head'],
            'dtutil'     => $detail['util'],
            'dtcharge'   => $detail['charge'],
            'citem'      => $detail['citem'],
            'backUrl'    => site_url('checklist_engineer'),
            'editUrl'    => site_url('checklist_engineer/edit/' . $id),
        ]);
    }

    public function saveChecklist()
    {
        if (($this->akses->can_create ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses menyimpan checklist.',
            ]);
        }

        $idChecklist = (int) $this->request->getPost('id_checklist');
        $items       = $this->request->getPost('chkitem') ?? [];
        $files       = $this->request->getFiles();

        $result = $this->model->saveChecklist(
            $idChecklist,
            is_array($items) ? $items : [],
            $files['chkitem'] ?? [],
            (int) session()->get('id_user')
        );

        return $this->response->setJSON($result);
    }

    public function updateChecklist()
    {
        if (($this->akses->can_edit ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses mengubah checklist.',
            ]);
        }

        $idChecklist = (int) $this->request->getPost('id_checklist');
        $items       = $this->request->getPost('chkitem') ?? [];
        $files       = $this->request->getFiles();

        $result = $this->model->updateChecklist(
            $idChecklist,
            is_array($items) ? $items : [],
            $files['chkitem'] ?? [],
            (int) session()->get('id_user')
        );

        return $this->response->setJSON($result);
    }

    public function cekPin()
    {
        $pin = (string) $this->request->getPost('pin');

        return $this->response->setJSON([
            'status' => md5($pin) === (string) session()->get('pin'),
            'msg'    => md5($pin) === (string) session()->get('pin') ? 'Pin Benar' : 'Pin Anda Salah',
        ]);
    }

    public function approveChecklist()
    {
        if (($this->akses->can_approve ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki hak approve.',
            ]);
        }

        $pin = (string) $this->request->getPost('pin');
        if (md5($pin) !== (string) session()->get('pin')) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Pin Anda Salah',
            ]);
        }

        $result = $this->model->approveChecklist(
            (int) $this->request->getPost('id'),
            (int) session()->get('id_user')
        );

        return $this->response->setJSON($result);
    }

    public function rejectChecklist()
    {
        if (($this->akses->can_approve ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki hak reject.',
            ]);
        }

        $pin = (string) $this->request->getPost('pin');
        if (md5($pin) !== (string) session()->get('pin')) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Pin Anda Salah',
            ]);
        }

        $result = $this->model->rejectChecklist(
            (int) $this->request->getPost('id_reject'),
            trim((string) $this->request->getPost('keterangan')),
            (int) session()->get('id_user')
        );

        return $this->response->setJSON($result);
    }

    public function addnewItem()
    {
        if (($this->akses->can_edit ?? 0) != 1 && ($this->akses->can_create ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses menambah item.',
            ]);
        }

        $result = $this->model->addNewItem(
            (int) $this->request->getPost('id_checklist'),
            [
                'nama'             => trim((string) $this->request->getPost('nama')),
                'kategori_item_id' => (int) $this->request->getPost('kategori_item_id'),
                'no_urut'          => (int) $this->request->getPost('no_urut'),
                'nilai'            => (int) $this->request->getPost('nilai'),
                'tenant_check'     => (string) $this->request->getPost('tenant_check'),
            ],
            (int) session()->get('id_user')
        );

        return $this->response->setJSON($result);
    }
}
