<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

class MUtilityRecord extends Model
{
    protected $DBGroup = 'default';

    public function dropListUtilities(): stdClass
    {
        $ret = new stdClass();

        $query = $this->db->query(
            "select * from m_utilities where flag_id = true and id != 1"
        );

        if ($query->getNumRows() > 0) {
            $ret->status = true;
            $ret->data   = $query->getResult();
        } else {
            $ret->status = false;
            $ret->data   = null;
            $ret->msg    = 'Data Not Found';
        }

        return $ret;
    }

    public function update_data($id, array $data): bool
    {
        return (bool) $this->db
            ->table('th_schedule_tagihan')
            ->where('id', $id)
            ->update($data);
    }

    public function saveNewUR_old(array $data): array
    {
        $query = $this->db
            ->table('th_schedule_tagihan')
            ->insert($data);

        if ($query) {
            return [
                'status' => true,
                'msg'    => 'Simpan Data Berhasil',
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Gagal simpan data',
        ];
    }

    public function saveNewUR($id, array $data): array
    {
        $query = $this->db
            ->table('th_schedule_tagihan')
            ->where('id', $id)
            ->update($data);

        if ($query) {
            return [
                'status' => true,
                'msg'    => 'Simpan Data Berhasil',
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Gagal simpan data',
        ];
    }

    public function updateData($id, array $data): array
    {
        $query = $this->db
            ->table('th_schedule_tagihan')
            ->where('id', $id)
            ->update($data);

        if ($query) {
            return [
                'status' => true,
                'msg'    => 'Update Data Berhasil',
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Gagal Update data',
        ];
    }

    public function getEdit($id): stdClass
    {
        $query = $this->db->query(
            "
            select
                a.*,
                mu.kode_unit,
                a.id_service as id_utilities,
                coalesce(mm.kode, a.kode_meter) as kode_meter
            from th_schedule_tagihan a
            left join m_unit mu on a.id_unit = mu.id
            left join m_meter mm on a.id_meter = mm.id
            where a.id = ?
            ",
            [$id]
        );

        $ret  = new stdClass();
        $data = $query->getRow();

        if ($data) {
            $ret->status = true;
            $ret->msg    = 'Data Found';
            $ret->data   = $data;
        } else {
            $ret->status = false;
            $ret->msg    = 'Data Not Found';
            $ret->data   = [];
        }

        return $ret;
    }
}
