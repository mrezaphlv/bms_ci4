<?php

namespace App\Models;

use CodeIgniter\Model;

class Mundangan extends Model
{
    protected $table      = 't_agreement';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'updated_user',
        'updated_date',
        'approved_user',
        'approved_date',
        'status',
        'tgl_bayar',
        'tgl_ppjb',
        'no_ppjb',
        'file_ppjb',
        'notes',
    ];

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    protected function listBuilder()
    {
        return $this->db->table('t_agreement a')
            ->select("
                a.id,
                a.no_undangan,
                a.tgl_undangan,
                a.status,
                a.file_ppjb,
                a.notes,
                mt.nama AS nama_owner,
                COALESCE(NULLIF(mt.email1, ''), NULLIF(mt.email2, '')) AS email_owner,
                mt.jenis_tenant AS tipe_tenant,
                mu.kode_unit,
                mb.nama AS nama_building,
                ms.nama AS nama_sales
            ")
            ->join('m_tenant mt', 'a.id_owner = mt.id', 'left')
            ->join('m_unit mu', 'a.id_unit = mu.id', 'left')
            ->join('m_building mb', 'mu.id_building = mb.id', 'left')
            ->join('m_sales ms', 'a.id_sales = ms.id', 'left');
    }

    public function grid(array $inp): array
    {
        $start      = (int) ($inp['start'] ?? 0);
        $length     = (int) ($inp['length'] ?? 10);
        $search     = trim((string) ($inp['search']['value'] ?? ''));
        $statuses   = $inp['tb_checkbox'] ?? ['NEW', 'APPROVED', 'REJECTED'];
        $orderDir   = strtolower((string) ($inp['order']['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderKey   = (string) ($inp['order']['column'] ?? 'id');
        $orderMap   = [
            'id'            => 'a.id',
            'no_undangan'   => 'a.no_undangan',
            'tgl_undangan'  => 'a.tgl_undangan',
            'nama_owner'    => 'mt.nama',
            'tipe_tenant'   => 'mt.jenis_tenant',
            'kode_unit'     => 'mu.kode_unit',
            'nama_building' => 'mb.nama',
            'nama_sales'    => 'ms.nama',
            'status'        => 'a.status',
        ];
        $orderColumn = $orderMap[$orderKey] ?? 'a.id';

        $builderCount = $this->listBuilder();

        if (! empty($statuses)) {
            $builderCount->whereIn('a.status', $statuses);
        }

        if ($search !== '') {
            $searchLower = strtolower($search);
            $builderCount->groupStart()
                ->like('LOWER(a.no_undangan)', $searchLower)
                ->orLike('LOWER(mt.nama)', $searchLower)
                ->orLike('LOWER(mu.kode_unit)', $searchLower)
                ->orLike('LOWER(mb.nama)', $searchLower)
                ->orLike('LOWER(ms.nama)', $searchLower)
                ->orWhere("LOWER(CAST(mt.jenis_tenant AS TEXT)) LIKE " . $this->db->escape('%' . $searchLower . '%'), null, false)
                ->orWhere("LOWER(CAST(a.status AS TEXT)) LIKE " . $this->db->escape('%' . $searchLower . '%'), null, false)
                ->groupEnd();
        }

        $countAll = $builderCount->countAllResults(false);

        $builderCount->orderBy($orderColumn, $orderDir);
        $builderCount->limit($length, $start);

        $rows = $builderCount->get()->getResult();

        foreach ($rows as $row) {
            $row->tgl_undangan = ! empty($row->tgl_undangan) ? date('d-m-Y', strtotime((string) $row->tgl_undangan)) : '';
        }

        return [
            'status'    => true,
            'msg'       => 'Data Found',
            'data'      => $rows,
            'count_all' => $countAll,
        ];
    }

    public function getDetail(int $id): ?object
    {
        $head = $this->listBuilder()
            ->select("
                a.id,
                a.no_undangan,
                a.accept_date,
                a.order_date,
                a.tgl_bayar,
                a.tgl_ppjb,
                a.no_ppjb,
                a.approved_date,
                a.approved_user,
                a.status,
                a.file_ppjb,
                a.notes,
                a.waktu_hadir,
                a.diwakilkan
            ", false)
            ->where('a.id', $id)
            ->get()
            ->getRow();

        if (! $head) {
            return null;
        }

        $detail          = new \stdClass();
        $detail->head    = $head;
        $detail->util    = $this->db->table('td_agreement_utilities du')
            ->select('
                du.id,
                du.id_agreement,
                mu.nama AS nama_utilities,
                rt.nama AS nama_rangetype
            ')
            ->join('m_utilities mu', 'du.id_utilities = mu.id', 'left')
            ->join('m_meterrange mr', 'du.id_meterrange = mr.id', 'left')
            ->join('m_rangetype rt', 'mr.id_rangetype = rt.id', 'left')
            ->where('du.id_agreement', $id)
            ->where('du.flag_id', true)
            ->orderBy('du.id', 'asc')
            ->get()
            ->getResult();
        $detail->charge  = $this->db->table('td_agreement_charge dc')
            ->select('
                dc.id,
                dc.id_agreement,
                sc.nama AS nama_service,
                pj.nama_pajak,
                dc.periode,
                dc.fee,
                dc.amount
            ')
            ->join('m_service_charge sc', 'dc.id_servicecharge = sc.id', 'left')
            ->join('m_pajak pj', 'dc.id_pajak = pj.id', 'left')
            ->where('dc.id_agreement', $id)
            ->where('dc.flag_id', true)
            ->orderBy('dc.id', 'asc')
            ->get()
            ->getResult();

        return $detail;
    }

    public function getAgreement(int $id): ?object
    {
        return $this->db->table('t_agreement')
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    public function approveUndangan(int $id, array $data): bool
    {
        return (bool) $this->db->table('t_agreement')
            ->where('id', $id)
            ->where('status', 'NEW')
            ->update($data);
    }

    public function rejectUndangan(int $id, array $data): bool
    {
        return (bool) $this->db->table('t_agreement')
            ->where('id', $id)
            ->where('status', 'NEW')
            ->update($data);
    }

    public function submitPpjb(int $id, array $data): bool
    {
        return (bool) $this->db->table('t_agreement')
            ->where('id', $id)
            ->update($data);
    }
}
