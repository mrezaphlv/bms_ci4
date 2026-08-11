<?php

namespace App\Models;

use CodeIgniter\Model;

class MChecklistEngineer extends Model
{
    protected $table      = 't_checklist';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function grid(array $inp): array
    {
        $start    = (int) ($inp['start'] ?? 0);
        $length   = (int) ($inp['length'] ?? 10);
        $search   = trim((string) ($inp['search']['value'] ?? ''));
        $statuses = $inp['tb_checkbox'] ?? ['NEW', 'APPROVED', 'REJECTED', 'EDITED'];
        $orderKey = (string) ($inp['order']['column'] ?? 'id');
        $orderDir = strtolower((string) ($inp['order']['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $orderMap = [
            'id'            => 'aa.id',
            'no_undangan'   => 'a.no_undangan',
            'tgl_undangan'  => 'a.tgl_undangan',
            'nama_owner'    => 'b.nama',
            'tipe_tenant'   => 'bb.nama',
            'kode_unit'     => 'mu.kode_unit',
            'tipe_checklist'=> 'aa.tipe_checklist',
            'status'        => 'aa.status',
        ];
        $orderColumn = $orderMap[$orderKey] ?? 'aa.id';

        $builder = $this->db->table('t_checklist aa')
            ->select("
                aa.id,
                md5(CAST(aa.id AS varchar)) AS mid,
                aa.id_agreement,
                aa.tipe_checklist,
                a.no_undangan,
                TO_CHAR(a.order_date, 'dd-mm-yyyy') AS order_date,
                TO_CHAR(a.accept_date, 'dd-mm-yyyy') AS accept_date,
                TO_CHAR(a.tgl_undangan, 'dd-mm-yyyy') AS tgl_undangan,
                aa.status,
                mu.kode_unit,
                b.nama AS nama_owner,
                ci.id_checklist,
                bb.nama AS tipe_tenant
            ", false)
            ->join('t_agreement a', 'aa.id_agreement = a.id')
            ->join('(SELECT id_checklist FROM td_checklist_item WHERE flag_id IS TRUE GROUP BY id_checklist) ci', 'aa.id = ci.id_checklist', 'left', false)
            ->join('m_tenant b', 'a.id_owner = b.id', 'left')
            ->join('m_tipe_tenant bb', 'b.id_tipe = bb.id', 'left')
            ->join('m_unit mu', 'a.id_unit = mu.id', 'left')
            ->join('m_building mb', 'mu.id_building = mb.id', 'left')
            ->where('aa.tipe', 'ENGINEERING')
            ->where('aa.flag_id', true);

        if ($search !== '') {
            $builder->groupStart()
                ->like('a.no_undangan', $search)
                ->orLike('LOWER(b.nama)', strtolower($search))
                ->orLike('LOWER(bb.nama)', strtolower($search))
                ->orLike('LOWER(mu.kode_unit)', strtolower($search))
                ->groupEnd();
        }

        if (! empty($statuses) && is_array($statuses)) {
            $builder->whereIn('aa.status', $statuses);
        } else {
            $builder->groupStart()
                ->where('aa.status', 'NEW')
                ->orWhere('aa.status', 'APPROVED')
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $countAll     = $countBuilder->countAllResults(false);

        $builder->orderBy($orderColumn, $orderDir);
        $builder->limit($length, $start);

        return [
            'count_all' => $countAll,
            'data'      => $builder->get()->getResult(),
        ];
    }

    public function getInputData(int $id): ?array
    {
        $header = $this->db->table('t_checklist aa')
            ->select('mu.kode_unit')
            ->join('t_agreement a', 'aa.id_agreement = a.id')
            ->join('m_unit mu', 'a.id_unit = mu.id', 'left')
            ->where('aa.flag_id', true)
            ->where('aa.tipe', 'ENGINEERING')
            ->where('aa.id', $id)
            ->get()
            ->getRow();

        if (! $header) {
            return null;
        }

        $items = $this->db->query("
            SELECT * FROM (
                SELECT 1 AS segmen, a.id AS id_kategori, a.nama AS nama_kategori, a.no_urut AS urut_kategori,
                    0 AS no_urut, a.nama AS nama_item, 0 AS id_item
                FROM m_kategori_item a
                JOIN m_item b ON a.id = b.kategori_item_id
                WHERE b.flag = TRUE AND a.flag_id = TRUE
                GROUP BY a.id, a.nama, a.no_urut
                UNION ALL
                SELECT 2 AS segmen, a.id AS id_kategori, a.nama AS nama_kategori, a.no_urut AS urut_kategori,
                    b.no_urut, b.nama AS nama_item, b.id AS id_item
                FROM m_kategori_item a
                JOIN m_item b ON a.id = b.kategori_item_id
                WHERE b.flag = TRUE AND a.flag_id = TRUE
            ) hh
            ORDER BY urut_kategori, segmen, no_urut
        ")->getResult();

        return [
            'data' => $header,
            'item' => $items,
        ];
    }

    public function getEditData(int $id): ?array
    {
        $header = $this->db->table('t_checklist aa')
            ->select('mu.kode_unit, aa.status')
            ->join('t_agreement a', 'aa.id_agreement = a.id')
            ->join('m_unit mu', 'a.id_unit = mu.id', 'left')
            ->where('aa.flag_id', true)
            ->where('aa.id', $id)
            ->get()
            ->getRow();

        if (! $header) {
            return null;
        }

        $items = $this->db->query("
            SELECT * FROM (
                SELECT 1 AS segmen, a.id AS id_kategori, a.nama AS nama_kategori, a.no_urut AS urut_kategori,
                    a.nama AS nama_item, 0 AS id_item, '' AS keterangan, 0 AS qty, TRUE AS tenant_check, '' AS kondisi, '' AS foto
                FROM m_kategori_item a
                JOIN td_checklist_item b ON a.id = b.kategori_item_id
                WHERE b.id_checklist = ?
                GROUP BY a.id, a.nama, a.no_urut
                UNION ALL
                SELECT 2 AS segmen, a.id AS id_kategori, a.nama AS nama_kategori, a.no_urut AS urut_kategori,
                    b.nama AS nama_item, c.id AS id_item, c.keterangan, c.qty, c.tenant_check, c.kondisi, c.foto
                FROM m_kategori_item a
                JOIN m_item b ON a.id = b.kategori_item_id
                JOIN td_checklist_item c ON b.id = c.id_item
                WHERE c.id_checklist = ? AND c.flag_id IS TRUE
            ) hh
            ORDER BY urut_kategori, segmen
        ", [$id, $id])->getResult();

        return [
            'data' => $header,
            'item' => $items,
        ];
    }

    public function getDetailUndangan(int $id): ?array
    {
        $head = $this->db->table('t_checklist aa')
            ->select("
                a.id,
                a.no_undangan,
                a.order_date,
                a.accept_date,
                a.status,
                a.id_unit,
                mu.kode_unit,
                a.id_owner,
                b.nama AS nama_owner,
                a.id_sales,
                c.nama AS nama_sales,
                mb.nama AS nama_building,
                COALESCE(NULLIF(b.email1, ''), NULLIF(b.email2, '')) AS email_owner,
                d.nama AS tipe_tenant,
                aa.notes,
                aa.status AS status_checklist
            ", false)
            ->join('t_agreement a', 'aa.id_agreement = a.id')
            ->join('m_tenant b', 'a.id_owner = b.id', 'left')
            ->join('m_sales c', 'a.id_sales = c.id', 'left')
            ->join('m_unit mu', 'a.id_unit = mu.id', 'left')
            ->join('m_building mb', 'mu.id_building = mb.id', 'left')
            ->join('m_tipe_tenant d', 'b.id_tipe = d.id', 'left')
            ->where('a.flag_id', true)
            ->where('aa.id', $id)
            ->get()
            ->getRow();

        if (! $head) {
            return null;
        }

        $agreementId = (int) $head->id;

        $util = $this->db->table('td_agreement_utilities a')
            ->select('a.*, d.nama AS nama_utilities, e.nama AS nama_rangetype, c.kode AS kode_meter')
            ->join('m_meterrange b', 'a.id_meterrange = b.id', 'left')
            ->join('m_meter c', 'a.id_meter = c.id', 'left')
            ->join('m_utilities d', 'a.id_utilities = d.id', 'left')
            ->join('m_rangetype e', 'b.id_rangetype = e.id', 'left')
            ->where('a.flag_id', true)
            ->where('a.id_agreement', $agreementId)
            ->get()
            ->getResult();

        $charge = $this->db->table('td_agreement_charge a')
            ->select('b.nama AS nama_service, c.nama_pajak, a.*')
            ->join('m_service_charge b', 'a.id_servicecharge = b.id', 'left')
            ->join('m_pajak c', 'a.id_pajak = c.id', 'left')
            ->where('a.flag_id', true)
            ->where('a.id_agreement', $agreementId)
            ->get()
            ->getResult();

        $citem = $this->db->query("
            SELECT * FROM (
                SELECT 1 AS segmen, a.id AS id_kategori, a.nama AS nama_kategori, a.no_urut AS urut_kategori,
                    a.nama AS nama_item, 0 AS id_item, '' AS keterangan, 0 AS qty, TRUE AS tenant_check, '' AS kondisi, '' AS foto
                FROM m_kategori_item a
                JOIN td_checklist_item b ON a.id = b.kategori_item_id
                WHERE b.id_checklist = ?
                GROUP BY a.id, a.nama, a.no_urut
                UNION ALL
                SELECT 2 AS segmen, a.id AS id_kategori, a.nama AS nama_kategori, a.no_urut AS urut_kategori,
                    b.nama AS nama_item, c.id AS id_item, c.keterangan, c.qty, c.tenant_check, c.kondisi, c.foto
                FROM m_kategori_item a
                JOIN m_item b ON a.id = b.kategori_item_id
                JOIN td_checklist_item c ON b.id = c.id_item
                WHERE c.id_checklist = ? AND c.flag_id IS TRUE
            ) hh
            ORDER BY urut_kategori, segmen
        ", [$id, $id])->getResult();

        return [
            'head'   => $head,
            'util'   => $util,
            'charge' => $charge,
            'citem'  => $citem,
        ];
    }

    public function getKategoriItemList(): array
    {
        return $this->db->table('m_kategori_item')
            ->where('flag_id', true)
            ->orderBy('no_urut', 'asc')
            ->orderBy('nama', 'asc')
            ->get()
            ->getResult();
    }

    public function saveChecklist(int $idChecklist, array $items, array $files, int $userId): array
    {
        if ($idChecklist <= 0 || empty($items)) {
            return ['status' => false, 'msg' => 'Item checklist belum lengkap.'];
        }

        $now = date('Y-m-d H:i:s');
        $dir = $this->ensureChecklistDirectory($idChecklist);

        $this->db->transBegin();

        try {
            foreach ($items as $index => $item) {
                $itemId = (int) ($item['id_mitem'] ?? 0);
                if ($itemId <= 0) {
                    continue;
                }

                $master = $this->db->table('m_item')
                    ->select('id, nama, kategori_item_id, tenant_check, no_urut')
                    ->where('id', $itemId)
                    ->get()
                    ->getRow();

                if (! $master) {
                    continue;
                }

                $photoName = $this->storeChecklistPhoto($dir, $idChecklist, $index, $files[$index]['fotoitem'] ?? null);

                $payload = [
                    'id_checklist'      => $idChecklist,
                    'id_item'           => $master->id,
                    'item'              => $master->nama,
                    'kategori_item_id'  => $master->kategori_item_id,
                    'keterangan'        => trim((string) ($item['keterangan'] ?? '')),
                    'qty'               => max(0, (int) ($item['qty'] ?? 0)),
                    'tenant_check'      => $this->toBool($master->tenant_check),
                    'urut_item'         => (int) ($master->no_urut ?? 0),
                    'kondisi'           => trim((string) ($item['s_kondisi'] ?? 'GOOD')),
                    'created_date'      => $now,
                    'created_user'      => $userId,
                    'flag_id'           => true,
                    'foto'              => $photoName,
                ];

                $this->db->table('td_checklist_item')->insert($payload);
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['status' => false, 'msg' => $e->getMessage()];
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return ['status' => false, 'msg' => 'Gagal simpan data checklist.'];
        }

        $this->db->transCommit();

        return ['status' => true, 'msg' => 'Berhasil simpan data'];
    }

    public function updateChecklist(int $idChecklist, array $items, array $files, int $userId): array
    {
        if ($idChecklist <= 0 || empty($items)) {
            return ['status' => false, 'msg' => 'Item checklist belum lengkap.'];
        }

        $now = date('Y-m-d H:i:s');
        $dir = $this->ensureChecklistDirectory($idChecklist);
        $oldFilesToDelete = [];

        $this->db->transBegin();

        try {
            foreach ($items as $index => $item) {
                $detailId = (int) ($item['id_mitem'] ?? 0);
                if ($detailId <= 0) {
                    continue;
                }

                $payload = [
                    'qty'          => max(0, (int) ($item['qty'] ?? 0)),
                    'kondisi'      => trim((string) ($item['s_kondisi'] ?? 'GOOD')),
                    'keterangan'   => trim((string) ($item['keterangan'] ?? '')),
                    'updated_date' => $now,
                    'updated_user' => $userId,
                ];

                $newPhoto = $this->storeChecklistPhoto($dir, $idChecklist, $index, $files[$index]['fotoitem'] ?? null);
                if ($newPhoto !== '') {
                    $oldPhoto = trim((string) ($item['fotonow'] ?? ''));
                    if ($oldPhoto !== '') {
                        $oldFilesToDelete[] = $oldPhoto;
                    }
                    $payload['foto'] = $newPhoto;
                }

                $this->db->table('td_checklist_item')
                    ->where('id', $detailId)
                    ->where('id_checklist', $idChecklist)
                    ->update($payload);
            }

            $this->cekStatus($idChecklist);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['status' => false, 'msg' => $e->getMessage()];
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return ['status' => false, 'msg' => 'Gagal update data checklist.'];
        }

        $this->db->transCommit();

        foreach ($oldFilesToDelete as $oldFile) {
            $path = $dir . DIRECTORY_SEPARATOR . $oldFile;
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return ['status' => true, 'msg' => 'Update Data Berhasil'];
    }

    public function approveChecklist(int $id, int $userId): array
    {
        $now = date('Y-m-d H:i:s');
        $saved = $this->db->table('t_checklist')
            ->where('id', $id)
            ->update([
                'updated_date'  => $now,
                'updated_user'  => $userId,
                'approved_date' => $now,
                'approved_user' => $userId,
                'status'        => 'APPROVED',
            ]);

        return [
            'status' => (bool) $saved,
            'msg'    => $saved ? 'Approve Berhasil' : 'Approve Gagal',
        ];
    }

    public function rejectChecklist(int $id, string $notes, int $userId): array
    {
        $now = date('Y-m-d H:i:s');
        $saved = $this->db->table('t_checklist')
            ->where('id', $id)
            ->update([
                'rejected_date' => $now,
                'rejected_user' => $userId,
                'notes'         => $notes,
                'status'        => 'REJECTED',
            ]);

        return [
            'status' => (bool) $saved,
            'msg'    => $saved ? 'Reject Berhasil' : 'Reject Gagal',
        ];
    }

    public function addNewItem(int $idChecklist, array $data, int $userId): array
    {
        if ($idChecklist <= 0 || $data['nama'] === '' || $data['kategori_item_id'] <= 0 || $data['no_urut'] <= 0) {
            return ['status' => false, 'msg' => 'Data item baru belum lengkap.'];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();

        try {
            $this->db->table('m_item')->insert([
                'created_user'     => $userId,
                'created_date'     => $now,
                'nama'             => $data['nama'],
                'no_urut'          => $data['no_urut'],
                'kategori_item_id' => $data['kategori_item_id'],
                'nilai'            => $data['nilai'],
                'tenant_check'     => $this->toBool($data['tenant_check']),
                'flag'             => true,
            ]);

            $itemId = (int) $this->db->insertID();

            $master = $this->db->table('m_item')
                ->select('id, nama, kategori_item_id, tenant_check, no_urut')
                ->where('id', $itemId)
                ->get()
                ->getRow();

            $this->db->table('td_checklist_item')->insert([
                'id_checklist'      => $idChecklist,
                'id_item'           => $master->id,
                'item'              => $master->nama,
                'kategori_item_id'  => $master->kategori_item_id,
                'qty'               => 1,
                'tenant_check'      => $this->toBool($master->tenant_check),
                'urut_item'         => (int) ($master->no_urut ?? 0),
                'created_date'      => $now,
                'created_user'      => $userId,
                'flag_id'           => true,
            ]);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['status' => false, 'msg' => $e->getMessage()];
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return ['status' => false, 'msg' => 'Tambah Item Gagal'];
        }

        $this->db->transCommit();

        return ['status' => true, 'msg' => 'Tambah Item Berhasil'];
    }

    public function ensureChecklistForAgreement(int $agreementId, int $userId): bool
    {
        if ($agreementId <= 0) {
            return false;
        }

        $exists = $this->db->table('t_checklist')
            ->where('id_agreement', $agreementId)
            ->where('tipe', 'ENGINEERING')
            ->where('flag_id', true)
            ->countAllResults();

        if ($exists > 0) {
            return true;
        }

        return (bool) $this->db->table('t_checklist')->insert([
            'id_agreement' => $agreementId,
            'tipe'         => 'ENGINEERING',
            'status'       => 'NEW',
            'created_date' => date('Y-m-d H:i:s'),
            'created_user' => $userId,
            'flag_id'      => true,
        ]);
    }

    protected function cekStatus(int $idChecklist): void
    {
        $row = $this->db->table('t_checklist')
            ->select('status')
            ->where('id', $idChecklist)
            ->get()
            ->getRow();

        if (($row->status ?? '') === 'REJECTED') {
            $this->db->table('t_checklist')
                ->where('id', $idChecklist)
                ->update(['status' => 'EDITED']);
        }
    }

    protected function ensureChecklistDirectory(int $idChecklist): string
    {
        $dir = FCPATH . 'dokumen/checklist/engineering/' . $idChecklist;
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    protected function storeChecklistPhoto(string $dir, int $idChecklist, int $index, $file): string
    {
        if (! $file || ! method_exists($file, 'isValid') || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        $name = $index . 'fotoitem_' . $idChecklist . $index . '_' . date('YmdHis') . '.' . $file->getExtension();
        $file->move($dir, $name, true);

        return $name;
    }

    protected function toBool($value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 't' || $value === 'true';
    }
}
