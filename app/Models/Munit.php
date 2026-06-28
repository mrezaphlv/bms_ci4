<?php

namespace App\Models;

use CodeIgniter\Model;

class Munit extends Model
{
    protected $table      = 'm_unit';
    protected $primaryKey = 'id';

    protected $returnType = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'created_user',
        'created_date',
        'updated_user',
        'updated_date',
        'kode_unit',
        'id_building',
        'id_balkon',
        'id_view',
        'no_urut',
        'daya',
        'deskripsi',
        'lantai',
        'luas',
        'twobr',
        'is_excaption',
        'flag_id',
    ];

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function grid($inp)
    {
        $start  = (int) ($inp['start'] ?? 0);
        $length = (int) ($inp['length'] ?? 10);

        $search = $inp['search']['value'] ?? '';

        $orderColumn = $inp['order']['column'] ?? 'kode_unit';
        $orderDir    = $inp['order']['dir'] ?? 'asc';

        $allowedOrderColumns = [
            'id',
            'kode_unit',
            'daya',
            'nama_building',
            'balkon',
            'nama_tenant',
            'jenis_view',
            'no_agreement',
            'nik_tenant',
            'lantai',
            'luas',
            'no_urut',
            'excaption',
        ];

        if (!in_array($orderColumn, $allowedOrderColumns)) {
            $orderColumn = 'kode_unit';
        }

        $orderDir = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';

        $builderCount = $this->db->table('m_unit a');
        $builderCount->select('COUNT(a.id) AS ctr');
        $builderCount->join('m_building b', 'a.id_building = b.id', 'left');
        $builderCount->join('m_balkon c', 'a.id_balkon = c.id', 'left');
        $builderCount->join('v_handover_agreement ab', 'a.id = ab.id_unit', 'left');
        $builderCount->join('m_tenant d', 'ab.id_owner = d.id', 'left');
        $builderCount->join('m_view e', 'a.id_view = e.id', 'left');
        $builderCount->where('a.flag_id', true);

        if (!empty($search)) {
            $builderCount->groupStart()
                ->like('LOWER(a.kode_unit)', strtolower($search))
                ->orLike('LOWER(a.daya)', strtolower($search))
                ->orLike('LOWER(d.nama)', strtolower($search))
                ->orLike('LOWER(c.balkon)', strtolower($search))
                ->orLike('LOWER(e.jenis_view)', strtolower($search))
                ->groupEnd();
        }

        $queryCountAll = $builderCount->get()->getRow()->ctr ?? 0;

        $builder = $this->db->table('m_unit a');
        $builder->select("
            a.*,
            b.nama AS nama_building,
            c.balkon,
            d.nama AS nama_tenant,
            e.jenis_view,
            ab.id_owner AS id_owner,
            ab.id AS id_bast,
            ab.no_agreement,
            d.nik AS nik_tenant,
            CASE WHEN a.is_excaption = true THEN 'Yes' ELSE 'No' END AS excaption
        ");
        $builder->join('m_building b', 'a.id_building = b.id', 'left');
        $builder->join('m_balkon c', 'a.id_balkon = c.id', 'left');
        $builder->join('v_handover_agreement ab', 'a.id = ab.id_unit', 'left');
        $builder->join('m_tenant d', 'ab.id_owner = d.id', 'left');
        $builder->join('m_view e', 'a.id_view = e.id', 'left');
        $builder->where('a.flag_id', true);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('LOWER(a.kode_unit)', strtolower($search))
                ->orLike('LOWER(a.daya)', strtolower($search))
                ->orLike('LOWER(d.nama)', strtolower($search))
                ->orLike('LOWER(c.balkon)', strtolower($search))
                ->orLike('LOWER(e.jenis_view)', strtolower($search))
                ->groupEnd();
        }

        $builder->orderBy($orderColumn, $orderDir);
        $builder->limit($length, $start);

        $query = $builder->get();

        if ($query) {
            return [
                'status'    => true,
                'msg'       => 'Data Found',
                'data'      => $query->getResult(),
                'count_all' => $queryCountAll,
            ];
        }

        return [
            'status'    => false,
            'msg'       => 'Data Not Found',
            'data'      => [],
            'count_all' => 0,
        ];
    }

    public function grid_bast($inp)
    {
        $start  = (int) ($inp['start'] ?? 0);
        $length = (int) ($inp['length'] ?? 10);

        $search = $inp['search']['value'] ?? '';

        $orderColumn = $inp['order']['column'] ?? 'kode_unit';
        $orderDir    = $inp['order']['dir'] ?? 'asc';

        $allowedOrderColumns = [
            'id',
            'kode_unit',
            'daya',
            'nama_building',
            'balkon',
            'nama_tenant',
            'jenis_view',
            'no_agreement',
            'lantai',
            'luas',
            'no_urut',
        ];

        if (!in_array($orderColumn, $allowedOrderColumns)) {
            $orderColumn = 'kode_unit';
        }

        $orderDir = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';

        $builderCount = $this->db->table('m_unit a');
        $builderCount->select('COUNT(a.id) AS ctr');
        $builderCount->join('m_building b', 'a.id_building = b.id', 'left');
        $builderCount->join('m_balkon c', 'a.id_balkon = c.id', 'left');
        $builderCount->join('v_handover_agreement ab', 'a.id = ab.id_unit');
        $builderCount->join('m_tenant d', 'ab.id_owner = d.id', 'left');
        $builderCount->join('m_view e', 'a.id_view = e.id', 'left');
        $builderCount->where('a.flag_id', true);

        if (!empty($search)) {
            $builderCount->groupStart()
                ->like('LOWER(a.kode_unit)', strtolower($search))
                ->orLike('LOWER(a.daya)', strtolower($search))
                ->orLike('LOWER(d.nama)', strtolower($search))
                ->orLike('LOWER(c.balkon)', strtolower($search))
                ->orLike('LOWER(e.jenis_view)', strtolower($search))
                ->groupEnd();
        }

        $queryCountAll = $builderCount->get()->getRow()->ctr ?? 0;

        $builder = $this->db->table('m_unit a');
        $builder->select("
            a.*,
            b.nama AS nama_building,
            c.balkon,
            d.nama AS nama_tenant,
            e.jenis_view,
            ab.id_owner AS id_owner,
            ab.id AS id_bast,
            ab.no_agreement
        ");
        $builder->join('m_building b', 'a.id_building = b.id', 'left');
        $builder->join('m_balkon c', 'a.id_balkon = c.id', 'left');
        $builder->join('v_handover_agreement ab', 'a.id = ab.id_unit');
        $builder->join('m_tenant d', 'ab.id_owner = d.id', 'left');
        $builder->join('m_view e', 'a.id_view = e.id', 'left');
        $builder->where('a.flag_id', true);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('LOWER(a.kode_unit)', strtolower($search))
                ->orLike('LOWER(a.daya)', strtolower($search))
                ->orLike('LOWER(d.nama)', strtolower($search))
                ->orLike('LOWER(c.balkon)', strtolower($search))
                ->orLike('LOWER(e.jenis_view)', strtolower($search))
                ->groupEnd();
        }

        $builder->orderBy($orderColumn, $orderDir);
        $builder->limit($length, $start);

        $query = $builder->get();

        if ($query) {
            return [
                'status'    => true,
                'msg'       => 'Data Found',
                'data'      => $query->getResult(),
                'count_all' => $queryCountAll,
            ];
        }

        return [
            'status'    => false,
            'msg'       => 'Data Not Found',
            'data'      => [],
            'count_all' => 0,
        ];
    }

    public function getEdit($id)
    {
        $ret = new \stdClass();

        $query = $this->db->table('m_unit')
            ->select("*, CASE WHEN is_excaption = true THEN 1 ELSE 0 END AS excaption")
            ->where('id', $id)
            ->get();

        $row = $query->getRow();

        if ($row) {
            $ret->status = true;
            $ret->msg    = 'Data Found';
            $ret->data   = $row;
        } else {
            $ret->status = false;
            $ret->msg    = 'Data Not Found';
            $ret->data   = null;
        }

        return $ret;
    }

    public function updateData($id, $data)
    {
        $ret = new \stdClass();

        $query = $this->db->table('m_unit')
            ->where('id', $id)
            ->update($data);

        if ($query) {
            $ret->status = true;
            $ret->msg    = 'Update data berhasil';
        } else {
            $ret->status = false;
            $ret->msg    = 'Update data gagal';
        }

        return $ret;
    }

    public function saveNewData($inp)
    {
        $session = session();

        $data = [
            'created_user' => $session->get('id_user'),
            'created_date' => date('Y-m-d H:i:s'),

            'kode_unit'    => $inp['kode_unit'] ?? null,
            'id_building'  => $inp['id_building'] ?? null,
            'id_balkon'    => !empty($inp['id_balkon']) ? $inp['id_balkon'] : null,
            'id_view'      => !empty($inp['id_view']) ? $inp['id_view'] : null,
            'no_urut'      => $inp['no_urut'] ?? null,
            'daya'         => $inp['daya'] ?? null,
            'deskripsi'    => !empty($inp['deskripsi']) ? $inp['deskripsi'] : null,
            'lantai'       => $inp['lantai'] ?? null,
            'luas'         => $inp['luas'] ?? null,
            'twobr'        => $inp['twobr'] ?? null,
            'is_excaption' => $inp['is_excaption'] ?? false,
        ];

        $ret = new \stdClass();

        $query = $this->db->table('m_unit')->insert($data);

        if ($query) {
            $ret->status = true;
            $ret->msg    = 'Simpan data berhasil';
        } else {
            $ret->status = false;
            $ret->msg    = 'Simpan data gagal';
        }

        return $ret;
    }

    public function unit_list()
    {
        $query = $this->db->table('m_unit')
            ->where('flag_id', true)
            ->where('is_excaption', false)
            ->get();

        return $query->getNumRows() > 0 ? $query->getResult() : [];
    }
}