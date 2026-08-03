<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

class Mundangan extends Model
{
    protected $table         = 't_agreement';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'no_undangan',
        'id_owner',
        'id_unit',
        'id_sales',
        'tgl_undangan',
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
            ->join('m_sales ms', 'a.id_sales = ms.id', 'left')
            ->where('a.flag_id', true);
    }

    public function grid(array $inp): array
    {
        $start    = (int) ($inp['start'] ?? 0);
        $length   = (int) ($inp['length'] ?? 10);
        $search   = trim((string) ($inp['search']['value'] ?? ''));
        $statuses = $inp['tb_checkbox'] ?? ['NEW', 'APPROVED', 'REJECTED'];
        $orderDir = strtolower((string) ($inp['order']['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderKey = (string) ($inp['order']['column'] ?? 'id');
        $orderMap = [
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
                a.id_owner,
                a.id_unit,
                a.id_sales,
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

        $detail        = new \stdClass();
        $detail->head  = $head;
        $detail->util  = $this->db->table('td_agreement_utilities du')
            ->select('
                du.id,
                du.id_agreement,
                du.id_meterrange,
                du.id_meter,
                du.id_utilities,
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
        $detail->charge = $this->db->table('td_agreement_charge dc')
            ->select('
                dc.id,
                dc.id_agreement,
                dc.id_servicecharge,
                dc.id_pajak,
                dc.periode,
                dc.fee,
                dc.amount,
                dc.nilai_pajak,
                sc.nama AS nama_service,
                pj.nama_pajak
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
            ->where('flag_id', true)
            ->get()
            ->getRow();
    }

    public function getFormData(?int $id = null): array
    {
        $data = [
            'dthead'         => null,
            'dtutil'         => [],
            'dtcharge'       => [],
            'droplist_pajak' => $this->getPajakList(),
            'droplist_util'  => $this->getUtilitiesList(),
            'droplist_charge'=> $this->getServiceChargeList(),
        ];

        if ($id === null) {
            return $data;
        }

        $detail = $this->getDetail($id);
        if (! $detail) {
            return $data;
        }

        $detail->head->tgl_undangan = ! empty($detail->head->tgl_undangan) ? date('d-m-Y', strtotime((string) $detail->head->tgl_undangan)) : '';
        $detail->head->jam_undangan = ! empty($detail->head->tgl_undangan) ? date('H:i', strtotime((string) $detail->head->tgl_undangan)) : '';

        $data['dthead']   = $detail->head;
        $data['dtutil']   = $detail->util;
        $data['dtcharge'] = $detail->charge;

        return $data;
    }

    public function getPajakList(): array
    {
        return $this->db->table('m_pajak')
            ->where('flag_id', true)
            ->like('LOWER(nama_pajak)', 'ppn')
            ->orderBy('nama_pajak', 'asc')
            ->get()
            ->getResult();
    }

    public function getUtilitiesList(): array
    {
        return $this->db->table('m_utilities mu')
            ->distinct()
            ->select('mu.id, mu.nama')
            ->join('m_meterrange mr', 'mr.id_utilities = mu.id AND mr.flag_id IS TRUE', 'inner', false)
            ->where('mu.flag_id', true)
            ->orderBy('nama', 'asc')
            ->get()
            ->getResult();
    }

    public function getServiceChargeList(): array
    {
        return $this->db->table('m_service_charge')
            ->select('id, nama')
            ->where('flag_id', true)
            ->orderBy('nama', 'asc')
            ->get()
            ->getResult();
    }

    public function getOwnerGrid(array $inp): array
    {
        $builder = $this->db->table('m_tenant');
        $builder->select('id, nama, nik, email1');
        $builder->where('flag_id', true);

        return $this->buildLookupGrid($builder, $inp, [
            'id'     => 'id',
            'nama'   => 'nama',
            'nik'    => 'nik',
            'email1' => 'email1',
        ], ['nama', 'nik', 'email1', 'email2']);
    }

    public function getSalesGrid(array $inp): array
    {
        $builder = $this->db->table('m_sales');
        $builder->select("id, nama, COALESCE(email, '') AS email, COALESCE(nohp, '') AS nohp", false);
        $builder->where('flag_id', true);

        return $this->buildLookupGrid($builder, $inp, [
            'id'    => 'id',
            'nama'  => 'nama',
            'email' => 'email',
            'nohp'  => 'nohp',
        ], ['nama', 'email', 'nohp']);
    }

    public function getUnitGrid(array $inp): array
    {
        $builder = $this->db->table('m_unit a');
        $builder->select('a.id, a.kode_unit, a.lantai, a.luas, mb.nama AS nama_building');
        $builder->join('m_building mb', 'a.id_building = mb.id', 'left');
        $builder->where('a.flag_id', true);

        return $this->buildLookupGrid($builder, $inp, [
            'id'            => 'a.id',
            'kode_unit'     => 'a.kode_unit',
            'lantai'        => 'a.lantai',
            'luas'          => 'a.luas',
            'nama_building' => 'mb.nama',
        ], ['a.kode_unit', 'mb.nama']);
    }

    protected function buildLookupGrid($baseBuilder, array $inp, array $orderMap, array $searchColumns): array
    {
        $postSearch  = trim((string) ($inp['search']['value'] ?? ''));
        $start       = (int) ($inp['start'] ?? 0);
        $length      = (int) ($inp['length'] ?? 10);
        $orderKey    = (string) ($inp['order']['column'] ?? array_key_first($orderMap));
        $orderColumn = $orderMap[$orderKey] ?? reset($orderMap);
        $orderDir    = strtolower((string) ($inp['order']['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $builderCount = clone $baseBuilder;
        if ($postSearch !== '') {
            $this->applyLookupSearch($builderCount, $postSearch, $searchColumns);
        }

        $countAll = $builderCount->countAllResults(false);
        $builderCount->orderBy($orderColumn, $orderDir);
        $builderCount->limit($length, $start);

        return [
            'count_all' => $countAll,
            'data'      => $builderCount->get()->getResult(),
        ];
    }

    protected function applyLookupSearch($builder, string $search, array $columns): void
    {
        $builder->groupStart();
        foreach ($columns as $index => $column) {
            if ($index === 0) {
                $builder->like("LOWER({$column})", strtolower($search));
                continue;
            }

            $builder->orLike("LOWER({$column})", strtolower($search));
        }
        $builder->groupEnd();
    }

    public function getMeterRangeByUtility(int $utilityId): array
    {
        $meterrange = $this->db->table('m_meterrange mr')
            ->select('mr.id AS id_meterrange, rt.nama')
            ->join('m_rangetype rt', 'mr.id_rangetype = rt.id', 'left')
            ->where('mr.id_utilities', $utilityId)
            ->where('mr.flag_id', true)
            ->orderBy('rt.nama', 'asc')
            ->get()
            ->getResult();

        $meterid = $this->db->table('m_meterid')
            ->select('id, kode')
            ->where('id_utilities', $utilityId)
            ->where('flag_id', true)
            ->orderBy('kode', 'asc')
            ->get()
            ->getResult();

        return [
            'status'     => true,
            'meterrange' => $meterrange,
            'meterid'    => $meterid,
        ];
    }

    public function getMeterRangeGrid(int $utilityId): array
    {
        $rows = $this->db->table('m_meterrange mr')
            ->select('mr.id AS id_meterrange, rt.nama')
            ->join('m_rangetype rt', 'mr.id_rangetype = rt.id', 'left')
            ->where('mr.id_utilities', $utilityId)
            ->where('mr.flag_id', true)
            ->orderBy('rt.nama', 'asc')
            ->get()
            ->getResult();

        return [
            'count_all' => count($rows),
            'data'      => $rows,
        ];
    }

    public function getChargeCalculation(int $serviceChargeId, int $unitId): ?object
    {
        $service = $this->db->table('m_service_charge')
            ->select('id, nama, nominal, flag_luasunit')
            ->where('id', $serviceChargeId)
            ->where('flag_id', true)
            ->get()
            ->getRow();

        if (! $service) {
            return null;
        }

        $service->unit = $this->db->table('m_unit')
            ->select('id, luas')
            ->where('id', $unitId)
            ->where('flag_id', true)
            ->get()
            ->getRow();

        return $service;
    }

    public function getPajakTarif(int $id, float $hargaJual): ?object
    {
        return $this->db->query(
            "SELECT *, fn_hitung_dpp_ppn((?)::numeric(20,2), (nilai_pajak)::numeric(20,2), tax_fn) AS dpp_ppn, fn_hitung_ppn_nominal((?)::numeric(20,2), (nilai_pajak)::numeric(20,2), tax_fn) AS ppn_nominal FROM m_pajak WHERE id = ?",
            [$hargaJual, $hargaJual, $id]
        )->getRow();
    }

    public function generateNoUndangan(string $tglUndangan, int $unitId): ?string
    {
        $row = $this->db->query('SELECT gen_noundangan(?, ?) AS no_undangan', [$tglUndangan, $unitId])->getRow();

        return $row->no_undangan ?? null;
    }

    public function saveUndangan(array $header, array $utilities, array $charges, int $userId): int
    {
        $this->db->transBegin();

        try {
            $this->db->table('t_agreement')->insert($header);
            $agreementId = (int) $this->db->insertID();

            $this->saveUtilities($agreementId, $utilities, $userId, true);
            $this->saveCharges($agreementId, $charges, $userId, true);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal menyimpan data undangan.');
        }

        $this->db->transCommit();

        return $agreementId;
    }

    public function updateUndangan(int $id, array $header, array $utilities, array $charges, array $deletedUtilIds, array $deletedChargeIds, int $userId): bool
    {
        $this->db->transBegin();

        try {
            $this->db->table('t_agreement')
                ->where('id', $id)
                ->where('flag_id', true)
                ->update($header);

            if (! empty($deletedUtilIds)) {
                $this->db->table('td_agreement_utilities')
                    ->whereIn('id', $deletedUtilIds)
                    ->where('id_agreement', $id)
                    ->update([
                        'flag_id'      => false,
                        'updated_date' => date('Y-m-d H:i:s'),
                        'updated_user' => $userId,
                    ]);
            }

            if (! empty($deletedChargeIds)) {
                $this->db->table('td_agreement_charge')
                    ->whereIn('id', $deletedChargeIds)
                    ->where('id_agreement', $id)
                    ->update([
                        'flag_id'      => false,
                        'updated_date' => date('Y-m-d H:i:s'),
                        'updated_user' => $userId,
                    ]);
            }

            $this->saveUtilities($id, $utilities, $userId, false);
            $this->saveCharges($id, $charges, $userId, false);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();

        return true;
    }

    protected function saveUtilities(int $agreementId, array $utilities, int $userId, bool $isCreate): void
    {
        foreach ($utilities as $utility) {
            $idUtilities  = (int) ($utility['s_util'] ?? 0);
            $idMeterrange = (int) ($utility['s_meterrange'] ?? 0);
            $detailId     = (int) ($utility['id_dutil'] ?? 0);

            if ($idUtilities <= 0 || $idMeterrange <= 0) {
                throw new RuntimeException('Data utilities belum lengkap.');
            }

            $payload = [
                'id_agreement'  => $agreementId,
                'id_utilities'  => $idUtilities,
                'id_meterrange' => $idMeterrange,
                'id_meter'      => ! empty($utility['s_meterid']) ? (int) $utility['s_meterid'] : null,
                'flag_id'       => true,
            ];

            if ($isCreate || $detailId <= 0) {
                $payload['created_date'] = date('Y-m-d H:i:s');
                $payload['created_user'] = $userId;
                $this->db->table('td_agreement_utilities')->insert($payload);
                continue;
            }

            $payload['updated_date'] = date('Y-m-d H:i:s');
            $payload['updated_user'] = $userId;
            $this->db->table('td_agreement_utilities')
                ->where('id', $detailId)
                ->where('id_agreement', $agreementId)
                ->update($payload);
        }
    }

    protected function saveCharges(int $agreementId, array $charges, int $userId, bool $isCreate): void
    {
        foreach ($charges as $charge) {
            $idServiceCharge = (int) ($charge['s_scharge'] ?? 0);
            $idPajak         = (int) ($charge['s_pajak'] ?? 0);
            $periode         = (int) ($charge['s_periode'] ?? 0);
            $detailId        = (int) ($charge['id_dcharge'] ?? 0);

            if ($idServiceCharge <= 0 || $idPajak <= 0 || $periode <= 0) {
                throw new RuntimeException('Data charge belum lengkap.');
            }

            $payload = [
                'id_agreement'     => $agreementId,
                'id_servicecharge' => $idServiceCharge,
                'id_pajak'         => $idPajak,
                'periode'          => $periode,
                'fee'              => $this->normalizeNumber($charge['fee'] ?? 0),
                'amount'           => $this->normalizeNumber($charge['amount'] ?? 0),
                'nilai_pajak'      => isset($charge['nilai_pajak']) ? (float) $charge['nilai_pajak'] : null,
                'flag_id'          => true,
            ];

            if ($isCreate || $detailId <= 0) {
                $payload['created_date'] = date('Y-m-d H:i:s');
                $payload['created_user'] = $userId;
                $this->db->table('td_agreement_charge')->insert($payload);
                continue;
            }

            $payload['updated_date'] = date('Y-m-d H:i:s');
            $payload['updated_user'] = $userId;
            $this->db->table('td_agreement_charge')
                ->where('id', $detailId)
                ->where('id_agreement', $agreementId)
                ->update($payload);
        }
    }

    protected function normalizeNumber($value): float
    {
        $value = str_replace('.', '', (string) $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
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
