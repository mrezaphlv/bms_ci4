<?php

namespace App\Controllers;

use App\Controllers\MyController;
use App\Models\Munit;

class Unit extends MyController
{
    protected $munit;
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->munit   = new Munit();
        $this->db      = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        if (!empty($this->akses) && $this->akses->can_view == 1) {
            return view('pages/unit/vunit', []);
        }

        return redirect()->to('/');
    }

    public function grid()
    {
        $post = $this->request->getPost();

        $fp = [
            'draw'   => $post['draw'] ?? 0,
            'start'  => $post['start'] ?? 0,
            'search' => $post['search'] ?? [],
            'length' => $post['length'] ?? 10,
        ];

        $indexColOrder = $post['order'][0]['column'] ?? 0;

        $fp['order'] = [
            'dir'    => $post['order'][0]['dir'] ?? 'asc',
            'column' => $post['columns'][$indexColOrder]['data'] ?? 'id',
        ];

        $result = $this->munit->grid($fp);

        return $this->response->setJSON([
            'draw'            => $post['draw'] ?? 0,
            'recordsTotal'    => $result['count_all'] ?? 0,
            'recordsFiltered' => $result['count_all'] ?? 0,
            'data'            => $result['data'] ?? [],
        ]);
    }

    public function grid_dlg_bck()
    {
        $post = $this->request->getPost();

        $fp = [
            'draw'   => $post['draw'] ?? 0,
            'start'  => $post['start'] ?? 0,
            'search' => $post['search'] ?? [],
            'length' => $post['length'] ?? 10,
        ];

        $indexColOrder = $post['order'][0]['column'] ?? 0;

        $fp['order'] = [
            'dir'    => $post['order'][0]['dir'] ?? 'asc',
            'column' => $post['columns'][$indexColOrder]['name'] ?? 'id',
        ];

        $result = api_json('POST', 'unit/grid_dlg', $fp);

        return $this->response->setJSON([
            'draw'            => $post['draw'] ?? 0,
            'recordsTotal'    => $result->count_all ?? 0,
            'recordsFiltered' => $result->count_all ?? 0,
            'data'            => $result->data ?? [],
        ]);
    }

    public function grid_dlg()
    {
        $post = $this->request->getPost();

        $start  = (int) ($post['start'] ?? 0);
        $length = (int) ($post['length'] ?? 10);

        $search = $post['search']['value'] ?? '';

        $indexColOrder = $post['order'][0]['column'] ?? 0;
        $orderColumn   = $post['columns'][$indexColOrder]['name'] ?? 'kode_unit';
        $orderDir      = $post['order'][0]['dir'] ?? 'asc';

        $allowedOrderColumns = [
            'id',
            'kode_unit',
            'nama_building',
            'nama_owner',
            'lantai',
            'luas',
            'no_urut',
        ];

        if (!in_array($orderColumn, $allowedOrderColumns)) {
            $orderColumn = 'kode_unit';
        }

        $orderDir = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';

        $builderCount = $this->db->table('m_unit a');
        $builderCount->select('COUNT(*) AS ctr');
        $builderCount->join('m_building b', 'a.id_building = b.id', 'left');
        $builderCount->where('a.flag_id', true);

        if (!empty($search)) {
            $builderCount->like('a.kode_unit', $search);
        }

        $queryCountAll = $builderCount->get()->getRow()->ctr ?? 0;

        $builder = $this->db->table('m_unit a');
        $builder->select('
            a.*,
            b.nama AS nama_building,
            mt.nama AS nama_owner,
            mt.id AS id_owner
        ');
        $builder->join('m_building b', 'a.id_building = b.id', 'left');
        $builder->join('v_handover_agreement vha', 'a.id = vha.id_unit', 'left');
        $builder->join('m_tenant mt', 'vha.id_owner = mt.id', 'left');
        $builder->where('a.flag_id', true);

        if (!empty($search)) {
            $builder->like('a.kode_unit', $search);
        }

        $builder->orderBy($orderColumn, $orderDir);
        $builder->limit($length, $start);

        $query = $builder->get();

        return $this->response->setJSON([
            'draw'            => $post['draw'] ?? 0,
            'recordsTotal'    => $queryCountAll,
            'recordsFiltered' => $queryCountAll,
            'data'            => $query->getResult(),
        ]);
    }

    public function grid_unit_bast()
    {
        $post = $this->request->getPost();

        $fp = [
            'draw'   => $post['draw'] ?? 0,
            'start'  => $post['start'] ?? 0,
            'search' => $post['search'] ?? [],
            'length' => $post['length'] ?? 10,
        ];

        $indexColOrder = $post['order'][0]['column'] ?? 0;

        $fp['order'] = [
            'dir'    => $post['order'][0]['dir'] ?? 'asc',
            'column' => $post['columns'][$indexColOrder]['data'] ?? 'id',
        ];

        $res = $this->munit->grid($fp);

        return $this->response->setJSON([
            'draw'            => $post['draw'] ?? 0,
            'recordsTotal'    => $res['count_all'] ?? 0,
            'recordsFiltered' => $res['count_all'] ?? 0,
            'data'            => $res['data'] ?? [],
        ]);
    }

    public function softDelete()
    {
        $id = $this->request->getPost('id');

        $fp = [
            'id'           => $id,
            'updated_user' => $this->session->get('id_user'),
            'updated_date' => date('Y-m-d H:i:s'),
        ];

        $response = api('POST', 'unit/softDelete', $fp);

        return $this->response->setJSON($response);
    }

    public function input()
    {
        if (!empty($this->akses) && $this->akses->can_create == 1) {
            return $this->template('pages/unit/input');
        }

        return redirect()->to('/');
    }

    public function edit($id)
    {
        if (!empty($this->akses) && $this->akses->can_edit == 1) {
            $res = $this->munit->getEdit($id);

            $data = [
                'idh' => $id,
                'dth' => $res->data ?? null,
            ];

            return $this->template('pages/unit/edit', $data);
        }

        return redirect()->to('/');
    }

    public function saveData()
    {
        $kodeUnit = $this->request->getPost('kode_unit');

        $fp = [
            'kode_unit'    => $kodeUnit,
            'id_building'  => $this->request->getPost('id_building'),
            'id_balkon'    => $this->request->getPost('id_balkon'),
            'id_view'      => $this->request->getPost('id_view'),
            'no_urut'      => $this->request->getPost('no_urut'),
            'daya'         => $this->request->getPost('daya'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'lantai'       => $this->request->getPost('lantai'),
            'luas'         => $this->request->getPost('luas'),
            'twobr'        => $this->request->getPost('twobr'),
            'is_excaption' => $this->request->getPost('is_excaption') == 1,
        ];

        $exist = $this->db->table('m_unit')
            ->where('kode_unit', $kodeUnit)
            ->where('flag_id', true)
            ->countAllResults();

        if ($exist == 0) {
            $res = $this->munit->saveNewData($fp);
            return $this->response->setJSON($res);
        }

        return $this->response->setJSON([
            'status' => false,
            'msg'    => 'Kode unit sudah ada',
        ]);
    }

    public function updateData()
    {
        $id       = $this->request->getPost('id');
        $kodeUnit = $this->request->getPost('kode_unit');

        $fp = [
            'updated_date' => date('Y-m-d H:i:s'),
            'updated_user' => $this->session->get('id_user'),

            'kode_unit'    => $kodeUnit,
            'id_building'  => !empty($this->request->getPost('id_building')) ? $this->request->getPost('id_building') : 0,
            'id_balkon'    => !empty($this->request->getPost('id_balkon')) ? $this->request->getPost('id_balkon') : 0,
            'id_view'      => !empty($this->request->getPost('id_view')) ? $this->request->getPost('id_view') : 0,
            'no_urut'      => $this->request->getPost('no_urut'),
            'daya'         => $this->request->getPost('daya'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'lantai'       => $this->request->getPost('lantai'),
            'luas'         => $this->request->getPost('luas'),
            'twobr'        => $this->request->getPost('twobr'),
            'is_excaption' => $this->request->getPost('is_excaption') == 1,
        ];

        $exist = $this->db->table('m_unit')
            ->where('id !=', $id)
            ->where('flag_id', true)
            ->where('kode_unit', $kodeUnit)
            ->countAllResults();

        if ($exist == 0) {
            $res = $this->munit->updateData($id, $fp);
            return $this->response->setJSON($res);
        }

        return $this->response->setJSON([
            'status' => false,
            'msg'    => 'Kode unit sudah ada',
        ]);
    }

    public function getDetail()
    {
        $id = $this->request->getPost('id');

        $fp = [
            'id' => $id,
        ];

        $res = api('POST', 'unit/getDetail', $fp);

        return $this->response->setJSON($res);
    }

    public function tenantList()
    {
        $post = $this->request->getPost();

        $fp = [
            'draw'   => $post['draw'] ?? 0,
            'start'  => $post['start'] ?? 0,
            'search' => $post['search'] ?? [],
            'length' => $post['length'] ?? 10,
        ];

        $indexColOrder = $post['order'][0]['column'] ?? 0;

        $fp['order'] = [
            'dir'    => $post['order'][0]['dir'] ?? 'asc',
            'column' => $post['columns'][$indexColOrder]['name'] ?? 'id',
        ];

        $result = api_json('POST', 'unit/lookup_tenant', $fp);

        return $this->response->setJSON([
            'draw'            => $post['draw'] ?? 0,
            'recordsTotal'    => $result->count_all ?? 0,
            'recordsFiltered' => $result->count_all ?? 0,
            'data'            => $result->data ?? [],
        ]);
    }
}