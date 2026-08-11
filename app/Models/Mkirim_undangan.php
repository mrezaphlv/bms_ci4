<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

class Mkirim_undangan extends Model
{
    protected $DBGroup = 'default';

    public function grid($inp)
    {
        $start  = $inp['start'];
        $length = $inp['length'];
        $where  = '';

        if (!empty($inp['search']['value'])) {
            $search    = $inp['search']['value'];
            $lowsearch = strtolower($search);
            $upsearch  = strtoupper($search);

            // Dipertahankan mengikuti logic CI3 existing.
            // Escape sederhana untuk menghindari quote memutus query.
            $searchEsc    = str_replace("'", "''", $search);
            $lowsearchEsc = str_replace("'", "''", $lowsearch);
            $upsearchEsc  = str_replace("'", "''", $upsearch);

            $where .= " and (
                a.no_undangan like '%{$searchEsc}%'
                OR lower(b.nama) like '%{$lowsearchEsc}%'
                OR lower(b.nama) like '%{$lowsearchEsc}%'
                OR upper(mu.kode_unit) like '%{$upsearchEsc}%'
            ) ";
        }

        if (!empty($inp['tb_checkbox'])) {
            $whereCheckbox = [];

            for ($i = 0; $i < count($inp['tb_checkbox']); $i++) {
                $isiCheckbox = $inp['tb_checkbox'][$i];

                if ($isiCheckbox === 'SENT') {
                    $whereCheckbox[] = "ba.id_agreement is null";
                }

                if ($isiCheckbox === 'CONFIRM') {
                    $whereCheckbox[] = "(ba.id_agreement is not null and a.waktu_hadir is null)";
                }

                if ($isiCheckbox === 'REVIEW') {
                    $whereCheckbox[] = "(ba.id_agreement is not null and a.waktu_hadir is not null)";
                }
            }

            if (!empty($whereCheckbox)) {
                $impWhereCheckbox = implode(' or ', $whereCheckbox);
                $where .= " and ({$impWhereCheckbox})";
            }
        } else {
            $where .= " and a.id::character = 'a'";
        }

        $qCountAll = "
            SELECT count(*) as ctr
            from t_checklist aa
            join t_agreement a on aa.id_agreement = a.id
            left join th_handover_agreement tha
                on a.id = tha.id_agreement
                and tha.id_parent = 0
                and tha.flag_id = true
            left join (
                select id_checklist
                from td_checklist_item
                group by id_checklist
            ) ci on aa.id = ci.id_checklist
            left join m_tenant b on a.id_owner = b.id
            left join m_tipe_tenant bb on b.id_tipe = bb.id
            left join m_unit mu on a.id_unit = mu.id
            left join m_building mb on mu.id_building = mb.id
            left join (
                select id_agreement
                from t_email_send_hist_agreement
                group by id_agreement
            ) ba on a.id = ba.id_agreement
            where aa.flag_id = true
            and aa.status = 'APPROVED'
            {$where}
        ";

        $rowCount = $this->db->query($qCountAll)->getRow();
        $queryCountAll = $rowCount ? (int) $rowCount->ctr : 0;

        $order       = $inp['order'];
        $orderColumn = $order['column'];
        $orderDir    = strtolower($order['dir']) === 'desc' ? 'desc' : 'asc';

        // Tetap mengikuti mekanisme existing.
        // Untuk order column diasumsikan berasal dari konfigurasi DataTables yang valid.
        $q1 = "
            SELECT
                md5(aa.id::character varying) as mid,
                aa.id as id_checklist,
                a.id,
                a.no_undangan,
                CASE
                    WHEN aa.tipe_checklist = 'CLOSED AGREEMENT'
                    THEN to_char(tca.created_date, 'dd-mm-yyyy')
                    ELSE to_char(a.tgl_undangan, 'dd-mm-yyyy')
                END AS tgl_undangan,
                aa.status,
                mu.kode_unit,
                b.nama as nama_owner,
                ci.id_checklist,
                bb.nama as tipe_tenant,
                ba.id_agreement as id_agreement_email,
                a.waktu_hadir,
                case
                    when tha.no_agreement = '-'
                    then tha.no_pinjam_pakai
                    else tha.no_agreement
                end as no_agreement,
                CASE
                    WHEN aa.tipe_checklist = 'CLOSED AGREEMENT'
                    THEN tca.tgl_rencana
                    ELSE a.waktu_hadir
                END AS waktu_hadir,
                case
                    when a.waktu_hadir is not null
                    and a.diwakilkan = true
                    then 'Ya'
                    when a.waktu_hadir is not null
                    and a.diwakilkan = false
                    then 'Tidak'
                    else '-'
                end as diwakilkan,
                aa.tipe_checklist
            from t_checklist aa
            join t_agreement a on aa.id_agreement = a.id
            left join th_handover_agreement tha
                on a.id = tha.id_agreement
                and tha.id_parent = 0
                and tha.flag_id = true
            left join t_closed_agreement tca
                on tca.id_handover_agreement = tha.id
            left join (
                select id_checklist
                from td_checklist_item
                group by id_checklist
            ) ci on aa.id = ci.id_checklist
            left join m_tenant b on a.id_owner = b.id
            left join m_tipe_tenant bb on b.id_tipe = bb.id
            left join m_unit mu on a.id_unit = mu.id
            left join m_building mb on mu.id_building = mb.id
            left join (
                select id_agreement
                from t_email_send_hist_agreement
                group by id_agreement
            ) ba on a.id = ba.id_agreement
            where aa.flag_id = true
            and aa.status = 'APPROVED'
            {$where}
        ";

        $qq = "
            select *
            from ({$q1}) as data
            order by {$orderColumn} {$orderDir}
            limit {$length}
            offset {$start}
        ";

        $query = $this->db->query($qq);

        $ret = new stdClass();

        if ($query->getNumRows() > 0) {
            $data = [];

            foreach ($query->getResult() as $key => $r) {
                $data[$key] = $r;
                $data[$key]->waktu_hadir = !empty($r->waktu_hadir)
                    ? date('d-m-Y H:i', strtotime($r->waktu_hadir))
                    : null;
            }

            $ret->status    = true;
            $ret->msg       = 'Data Found';
            $ret->count_all = $queryCountAll;
            $ret->data      = $data;
        } else {
            $ret->status    = false;
            $ret->msg       = 'Data Not Found';
            $ret->count_all = $queryCountAll;
            $ret->data      = [];
        }

        return $ret;
    }

    public function submitConfirm($p)
    {
        $diwakilkan = $p['diwakilkan'];
        $waktuHadir = $p['tgl_hadir'] . ' ' . $p['jam_hadir'] . ':00';

        $data = [
            'updated_date'       => $p['updated_date'],
            'updated_user'       => $p['updated_user'],
            'waktu_hadir'        => date('Y-m-d H:i:s', strtotime($waktuHadir)),
            'diwakilkan'         => $p['diwakilkan'] == 1,
            'nama_wakil'         => $diwakilkan == 1 ? $p['nama_wakil'] : null,
            'tempat_lahir_wakil' => $diwakilkan == 1 ? $p['tempat_lahir_wakil'] : null,
            'tgl_lahir_wakil'    => $diwakilkan == 1 ? $p['tgl_lahir_wakil'] : null,
            'nik_wakil'          => $diwakilkan == 1 ? $p['nik_wakil'] : null,
            'alamat_wakil'       => $diwakilkan == 1 ? $p['alamat_wakil'] : null,
            'npwp_wakil'         => $diwakilkan == 1 ? $p['npwp_wakil'] : null,
            'pekerjaan_wakil'    => $diwakilkan == 1 ? $p['pekerjaan_wakil'] : null,
        ];

        $query = $this->db
            ->table('t_agreement')
            ->where('id', $p['id_agreement'])
            ->update($data);

        $ret = new stdClass();

        if ($query) {
            $ret->status = true;
            $ret->msg    = 'Success';
        } else {
            $ret->status = false;
            $ret->msg    = 'Failed';
        }

        return $ret;
    }

    public function getDetail($id)
    {
        $ret = new stdClass();

        $qq = "
            select
                a.id,
                aa.id as id_checklist,
                a.no_undangan,
                a.diwakilkan,
                to_char(a.tgl_undangan, 'dd-mm-yy') as tgl_undangan,
                a.status,
                a.id_unit,
                mu.kode_unit,
                a.id_owner,
                b.nama as nama_owner,
                a.id_sales,
                c.nama as nama_sales,
                mb.nama as nama_building,
                b.email1 as email_owner,
                d.nama as tipe_tenant,
                a.notes,
                a.waktu_hadir,
                sem.id_agreement as id_agreement_email,
                a.file_ppjb,
                a.nama_wakil,
                a.nik_wakil,
                a.tempat_lahir_wakil,
                a.tgl_lahir_wakil,
                a.nik_wakil,
                a.alamat_wakil,
                a.npwp_wakil,
                a.pekerjaan_wakil
            from t_agreement a
            left join m_tenant b
                on a.id_owner = b.id
            left join m_sales c
                on a.id_sales = c.id
            left join m_unit mu
                on a.id_unit = mu.id
            left join m_building mb
                on mu.id_building = mb.id
            left join m_tipe_tenant d
                on b.id_tipe = d.id
            join t_checklist aa
                on a.id = aa.id_agreement
            left join (
                select id_agreement
                from t_email_send_hist_agreement
                group by id_agreement
            ) sem on a.id = sem.id_agreement
            where a.flag_id = true
            and a.id = ?
        ";

        $query = $this->db->query($qq, [$id]);
        $head  = $query->getRow();

        if ($head) {
            $idChecklist = $head->id_checklist;

            $qUtil = "
                select
                    a.*,
                    d.nama as nama_utilities,
                    e.nama as nama_rangetype,
                    c.kode as kode_meter
                from td_agreement_utilities a
                left join m_meterrange b
                    on a.id_meterrange = b.id
                left join m_meter c
                    on a.id_meter = c.id
                left join m_utilities d
                    on a.id_utilities = d.id
                left join m_rangetype e
                    on b.id_rangetype = e.id
                where a.flag_id = true
                and a.id_agreement = ?
            ";

            $util = $this->db->query($qUtil, [$id]);

            $qCharge = "
                select
                    b.nama as nama_service,
                    c.nama_pajak,
                    a.*
                from td_agreement_charge a
                left join m_service_charge b
                    on a.id_servicecharge = b.id
                left join m_pajak c
                    on a.id_pajak = c.id
                where a.flag_id = true
                and a.id_agreement = ?
            ";

            $charge = $this->db->query($qCharge, [$id]);

            $qItc = "
                select *
                from (
                    select
                        1 as segmen,
                        a.id as id_kategori,
                        a.nama as nama_kategori,
                        a.no_urut as urut_kategori,
                        a.nama as nama_item,
                        0 as id_item,
                        '' as keterangan,
                        0 as qty,
                        true as tenant_check,
                        '' as kondisi,
                        '' as foto
                    from m_kategori_item a
                    join td_checklist_item b
                        on a.id = b.kategori_item_id
                    where b.id_checklist = ?
                    group by a.id, a.nama, a.no_urut

                    union all

                    select
                        2 as segmen,
                        a.id as id_kategori,
                        a.nama as nama_kategori,
                        a.no_urut as urut_kategori,
                        b.nama as nama_item,
                        c.id as id_item,
                        c.keterangan,
                        c.qty,
                        c.tenant_check,
                        c.kondisi,
                        c.foto
                    from m_kategori_item a
                    join m_item b
                        on a.id = b.kategori_item_id
                    join td_checklist_item c
                        on b.id = c.id_item
                    where c.id_checklist = ?
                ) as hh
                order by urut_kategori, segmen
            ";

            $queItc = $this->db->query($qItc, [$idChecklist, $idChecklist]);

            $qEmail = $this->db->query(
                "select * from t_email_send_hist_agreement where id_agreement = ?",
                [$id]
            );

            $ret->status = true;
            $ret->msg    = 'Data Found';
            $ret->data   = new stdClass();

            $ret->data->head   = $head;
            $ret->data->util   = $util->getResult();
            $ret->data->charge = $charge->getResult();
            $ret->data->citem  = $queItc->getResult();
            $ret->demail       = $qEmail->getResult();
        } else {
            $ret->status = false;
            $ret->msg    = 'Data Not Found';
            $ret->data   = null;
            $ret->demail = [];
        }

        return $ret;
    }
}
