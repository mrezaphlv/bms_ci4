<?php

namespace App\Models;

use CodeIgniter\Model;
use stdClass;

class MLmInvoice extends Model
{
    protected $DBGroup = 'default';

    public function getView($id): array
    {
        $q1 = "
            select
                a.*,
                mu.kode_unit,
                a.deskripsi,
                mt.nama as nama_owner,
                mtt.nama as tipe_tenant,
                mt.email1 as email_owner,
                mt.npwp as npwp,
                mt.hp1 as hp_owner,
                coalesce(tot.total_amount, 0) as total_amount
            from th_invoice a
            left join m_unit mu on a.id_unit = mu.id
            left join v_handover_agreement b on a.id_unit = b.id_unit
            left join m_tenant mt on a.id_tenant = mt.id
            left join m_tipe_tenant mtt on mt.id_tipe = mtt.id
            left join (
                select
                    id_header,
                    sum(total) as total_amount
                from td_invoice
                group by id_header
            ) tot on a.id = tot.id_header
            where a.id = ?
        ";

        $header = $this->db->query($q1, [$id])->getRow();

        if (! $header) {
            return [
                'status' => false,
                'msg'    => 'Data Not Found',
            ];
        }

        $q2 = "
            select
                a.*,
                msc.nama as nm_service,
                mp.nama_pajak,
                mtu.nama as nm_utilities,
                case
                    when a.nilai_pajak < 0
                    then '(' || a.nilai_pajak::text || ')'
                    else a.nilai_pajak::text
                end as nilai_pajak
            from td_invoice a
            left join m_service_charge msc
                on a.id_service = msc.id
                and a.tipe = 'SERVICE'
            left join m_utilities mtu
                on a.id_service = mtu.id
                and a.tipe = 'UTILITIES'
            left join m_pajak mp
                on a.id_pajak = mp.id
            where a.id_header = ?
        ";

        $detail = $this->db->query($q2, [$id])->getResult();

        $qJurnal = "
            select
                a.*,
                mc.keterangan as nama_coa
            from td_jurnal a
            left join m_coa mc
                on a.kode_coa = mc.kode_account
            join th_jurnal th
                on a.id_header = th.id
                and th.flag_id = true
            left join td_invoice ti
                on th.id_invoice = ti.id
                and ti.flag_id = true
            where a.flag_id = true
            and ti.id_header = ?
        ";

        $jurnal = $this->db->query($qJurnal, [$id])->getResult();

        return [
            'status'    => true,
            'msg'       => 'Data Found',
            'dthead'    => $header,
            'dt_detail' => $detail,
            'dtjurnal'  => $jurnal,
        ];
    }

    public function getViewEdit($id): array
    {
        $q2 = "
            select
                a.*,
                msc.nama as nm_service,
                mp.nama_pajak,
                mtu.nama as nm_utilities,
                case
                    when a.nilai_pajak < 0
                    then '(' || a.nilai_pajak::text || ')'
                    else a.nilai_pajak::text
                end as nilai_pajak
            from td_invoice a
            left join m_service_charge msc
                on a.id_service = msc.id
                and a.tipe = 'SERVICE'
            left join m_utilities mtu
                on a.id_service = mtu.id
                and a.tipe = 'UTILITIES'
            left join m_pajak mp
                on a.id_pajak = mp.id
            where a.id = ?
        ";

        return [
            'status'    => true,
            'msg'       => 'Data Found',
            'dt_detail' => $this->db->query($q2, [$id])->getResult(),
        ];
    }

    public function getInvoice($id): array
    {
        $row = $this->db
            ->query(
                "select * from td_invoice a where a.id = ?",
                [$id]
            )
            ->getRow();

        if ($row) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'inv'    => $row,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function getJurnalByIdInvoice($id): array
    {
        $q1 = "
            select a.*
            from td_jurnal a
            left join th_jurnal b on b.id = a.id_header
            left join th_invoice c on c.id = b.id_invoice
            left join td_invoice d on d.id_header = c.id
            where d.id = ?
        ";

        $rows = $this->db->query($q1, [$id])->getResult();

        if ($rows) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'jurnal' => $rows,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
            'jurnal' => [],
        ];
    }

    public function getHeadJurnalByIdInvoice($id): array
    {
        $q1 = "
            select a.id
            from th_jurnal a
            left join td_invoice c on c.id = a.id_invoice
            where c.id = ?
            and a.id_voucher is null
        ";

        $row = $this->db->query($q1, [$id])->getRow();

        if ($row) {
            return [
                'status'       => true,
                'msg'          => 'Data Found',
                'head_jurnal'  => $row,
            ];
        }

        return [
            'status'      => false,
            'msg'         => 'Data Not Found',
            'head_jurnal' => null,
        ];
    }

    public function getInvoiceReq($id): array
    {
        $q1 = "
            select
                a.*,
                msc.nama as nm_service,
                mp.nama_pajak,
                mtu.nama as nm_utilities,
                case
                    when a.nilai_pajak < 0
                    then '(' || a.nilai_pajak::text || ')'
                    else a.nilai_pajak::text
                end as nilai_pajak
            from td_invoice_req_edit a
            left join m_service_charge msc
                on a.id_service = msc.id
                and a.tipe = 'SERVICE'
            left join m_utilities mtu
                on a.id_service = mtu.id
                and a.tipe = 'UTILITIES'
            left join m_pajak mp
                on a.id_pajak = mp.id
            where a.id = ?
        ";

        $row = $this->db->query($q1, [$id])->getRow();

        if ($row) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'inv'    => $row,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function getIdInvoice($id): array
    {
        $row = $this->db
            ->query(
                "select id from td_invoice where id_header = ?",
                [$id]
            )
            ->getRow();

        if ($row) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'id'     => $row->id,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function getIdInvoiceBaru($id): array
    {
        $q1 = "
            select b.id
            from td_invoice_req_edit a
            left join td_invoice b on b.id = a.id_primary
            left join th_invoice c on c.id = b.id_header
            where a.id = ?
        ";

        $row = $this->db->query($q1, [$id])->getRow();

        if ($row) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'id'     => $row->id,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function getIdPrimary($id): array
    {
        $q1 = "
            select a.id_primary
            from td_invoice_req_edit a
            left join td_invoice b on b.id = a.id_primary
            left join th_invoice c on c.id = b.id_header
            where a.id = ?
        ";

        $row = $this->db->query($q1, [$id])->getRow();

        if ($row) {
            return [
                'status'     => true,
                'msg'        => 'Data Found',
                'id_primary' => $row->id_primary,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function getPPN($id): array
    {
        $row = $this->db
            ->query(
                "select nilai_pajak from m_pajak where id = ?",
                [$id]
            )
            ->getRow();

        if ($row) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'pajak'  => $row->nilai_pajak,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function getViewPrint($id): array
    {
        $q2 = "
            select
                a.*,
                msc.nama as nm_service,
                mp.nama_pajak,
                mp.tax_fn,
                mt.nama as nama_owner,
                mu.kode_unit,
                mu.luas,
                mt.npwp,
                mtu.nama as nm_utilities,
                ab.deskripsi,
                mp1.nama_pajak as nama_pph
            from td_invoice a
            left join m_service_charge msc
                on a.id_service = msc.id
                and a.tipe = 'SERVICE'
            left join m_utilities mtu
                on a.id_service = mtu.id
                and a.tipe = 'UTILITIES'
            left join m_pajak mp
                on a.id_pajak = mp.id
            left join m_tenant mt
                on mt.id = a.id_tenant
            join th_invoice ab
                on a.id_header = ab.id
            left join m_unit mu
                on ab.id_unit = mu.id
            left join m_pajak mp1
                on a.id_pph = mp1.id
            where a.id = ?
        ";

        $row = $this->db->query($q2, [$id])->getRow();

        if ($row) {
            return [
                'status' => true,
                'msg'    => 'Data Found',
                'data'   => $row,
            ];
        }

        return [
            'status' => false,
            'msg'    => 'Data Not Found',
        ];
    }

    public function saveNewInvoice(array $p): array
    {
        $inp = (object) $p;

        $periode         = ! empty($inp->periode) ? '01-' . $inp->periode : null;
        $noGroupInvoice  = new_nogroup_invoice($periode);

        $sequenceRow = $this->db
            ->query("select nextval('th_invoice_id_seq') as newid")
            ->getRow();

        if (! $sequenceRow) {
            return [
                'status'    => false,
                'msg'       => 'Failed',
                'header_id' => null,
            ];
        }

        $newIdHeader = $sequenceRow->newid;

        $insertHeader = [
            'id'               => $newIdHeader,
            'id_unit'          => $inp->id_unit,
            'id_tenant'        => $inp->id_tenant,
            'id_bast'          => $inp->id_bast,
            'no_group_invoice' => $noGroupInvoice,
            'jatuh_tempo'      => date('Y-m-d', strtotime($inp->due_date)),
            'periode'          => date('Y-m-d', strtotime($periode)),
            'deskripsi'        => $inp->deskripsi,
            'created_user'     => $inp->created_user,
            'created_date'     => $inp->created_date,
        ];

        $insertHeaderOk = $this->db
            ->table('th_invoice')
            ->insert($insertHeader);

        if (! $insertHeaderOk) {
            return [
                'status'    => false,
                'msg'       => 'Failed',
                'header_id' => null,
            ];
        }

        $this->db->transStart();

        $idUnit = $inp->id_unit;

        $qVa = "
            select
                mu.kode_unit,
                ctr.id as ct_handover
            from m_unit mu
            left join (
                select
                    count(a.id) as id,
                    a.id_unit
                from th_handover_agreement a
                group by a.id_unit
            ) ctr on mu.id = ctr.id_unit
            where mu.id = ?
        ";

        $dtVa = $this->db->query($qVa, [$idUnit])->getRow();

        if (! $dtVa) {
            $this->db->transRollback();

            return [
                'status'    => false,
                'msg'       => 'Data unit tidak ditemukan',
                'header_id' => null,
            ];
        }

        $expKodeUnit = explode('/', (string) $dtVa->kode_unit);

        $virtualAcc = isset($expKodeUnit[1])
            ? gen_va_bykodeunit($dtVa->kode_unit, $dtVa->ct_handover, 'LM')
            : '';

        foreach ($inp->dcharge as $pa) {
            $va = (object) $pa;

            if ($va->id_service != 1) {
                $tipe  = 'SERVICE';
                $dserv = $this->db
                    ->query(
                        'select nama as nama_service from m_service_charge where id = ?',
                        [$va->id_service]
                    )
                    ->getRow();
            } else {
                $tipe  = 'UTILITIES';
                $dserv = $this->db
                    ->query(
                        'select nama as nama_service from m_utilities where id = ?',
                        [$va->id_service]
                    )
                    ->getRow();
            }

            $idService      = $va->id_service;
            $prefixNo       = cariPrefixInvoice($idService, $tipe);
            $noInvoice      = new_noinvoice($prefixNo, $periode);
            $amount         = ! empty($va->amount) ? sep_to_decimal($va->amount) : 0;
            $tarifPajak     = ! empty($va->id_pajak) ? cariTarifPajak($va->id_pajak) : 0;
            $tarifPajakPph  = ! empty($va->id_pph) ? cariTarifPajak($va->id_pph) : 0;

            $pajakAmount = ! empty($va->ppn_amount)
                ? sep_to_decimal($va->ppn_amount)
                : (floatval($amount) * floatval($tarifPajak)) / 100;

            $pphAmount = (floatval($amount) * floatval($tarifPajakPph)) / 100;

            $total = ! empty($va->total_amount)
                ? sep_to_decimal($va->total_amount)
                : 0;

            $data = [
                'id_service'      => $idService,
                'id_header'       => $newIdHeader,
                'id_unit'         => $inp->id_unit,
                'id_tenant'       => $inp->id_tenant,
                'id_bast'         => $inp->id_bast,
                'tipe'            => $tipe,
                'no_invoice'      => $noInvoice,
                'periode'         => date('Y-m-d', strtotime($inp->due_date)),
                'created_user'    => $inp->created_user,
                'created_date'    => $inp->created_date,
                'amount'          => ! empty($va->amount) ? sep_to_decimal($va->amount) : 0,
                'fee'             => ! empty($va->fee) ? sep_to_decimal($va->fee) : 0,
                'total'           => $total,
                'tax_amount'      => round(floatval($pajakAmount)),
                'pph_amount'      => round(floatval($pphAmount)),
                'virtual_account' => $virtualAcc,
                'jatuh_tempo'     => date('Y-m-d', strtotime($inp->due_date)),
                'nilai_pajak'     => ! empty($va->id_pajak) ? cariTarifPajak($va->id_pajak) : null,
                'tax_base_ppn'    => ! empty($va->tax_base_ppn) ? sep_to_decimal($va->tax_base_ppn) : null,
                'pph_tarif'       => $tarifPajakPph,
            ];

            if (! empty($va->id_pajak)) {
                $data['id_pajak'] = $va->id_pajak;
            }

            if (! empty($va->id_pph)) {
                $data['id_pph'] = $va->id_pph;
            }

            $this->db
                ->table('td_invoice')
                ->insert($data);

            // Dipertahankan untuk kompatibilitas jika nantinya block jurnal
            // diaktifkan kembali.
            $newidInvoice = $this->db->insertID();
            unset($dserv, $newidInvoice);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return [
                'status'    => false,
                'msg'       => 'Failed',
                'header_id' => null,
            ];
        }

        return [
            'status'    => true,
            'msg'       => 'Success',
            'header_id' => $newIdHeader,
        ];
    }

    public function updateInvoice(array $p): stdClass
    {
        $ret = new stdClass();
        $inp = json_decode(json_encode($p));

        $periode = ! empty($inp->periode)
            ? '01-' . $inp->periode
            : null;

        $insertHeader = [
            'jatuh_tempo'  => date('Y-m-d', strtotime($inp->due_date)),
            'periode'      => date('Y-m-d', strtotime($periode)),
            'deskripsi'    => $inp->deskripsi,
            'updated_user' => $inp->updated_user,
            'updated_date' => $inp->updated_date,
        ];

        if (! empty($inp->id_unit)) {
            $insertHeader['id_unit'] = $inp->id_unit;
        }

        if (! empty($inp->id_tenant)) {
            $insertHeader['id_tenant'] = $inp->id_tenant;
        }

        if (! empty($inp->id_bast)) {
            $insertHeader['id_bast'] = $inp->id_bast;
        }

        $headerUpdated = $this->db
            ->table('th_invoice')
            ->where('id', $inp->id_thinvoice)
            ->update($insertHeader);

        if (! $headerUpdated) {
            $ret->status = false;
            $ret->msg    = 'Failed';

            return $ret;
        }

        $idUnit = $inp->id_unit;

        $qVa = "
            select
                mu.kode_unit,
                ctr.id as ct_handover
            from m_unit mu
            left join (
                select
                    count(a.id) as id,
                    b.id_unit
                from th_handover_agreement a
                join t_agreement b
                    on a.id_agreement = b.id
                group by b.id_unit
            ) ctr on mu.id = ctr.id_unit
            where mu.id = ?
        ";

        $dtVa = $this->db->query($qVa, [$idUnit])->getRow();

        if (! $dtVa) {
            $ret->status = false;
            $ret->msg    = 'Data unit tidak ditemukan';

            return $ret;
        }

        $virtualAcc = gen_va_bykodeunit(
            $dtVa->kode_unit,
            $dtVa->ct_handover,
            'LM'
        );

        for ($i = 1; $i < count($inp->dcharge); $i++) {
            $va = $inp->dcharge[$i];

            $amount        = ! empty($va->amount) ? sep_to_decimal($va->amount) : 0;
            $totalAmount   = ! empty($va->total_amount) ? sep_to_decimal($va->total_amount) : 0;
            $tarifPajak    = ! empty($va->id_pajak) ? cariTarifPajak($va->id_pajak) : 0;
            $tarifPajakPph = ! empty($va->id_pph) ? cariTarifPajak($va->id_pph) : 0;

            $pajakAmount = ! empty($va->ppn_amount)
                ? sep_to_decimal($va->ppn_amount)
                : (floatval($amount) * floatval($tarifPajak)) / 100;

            $pphAmount = (floatval($amount) * floatval($tarifPajakPph)) / 100;

            if (! empty($va->id_tdinvoice)) {
                $idService = explode('#', $va->id_service);

                $data = [
                    'id_service'   => $idService[0],
                    'tipe'         => $idService[1],
                    'periode'      => date('Y-m-d', strtotime($periode)),
                    'updated_user' => $inp->updated_user,
                    'updated_date' => $inp->updated_date,
                    'amount'       => ! empty($va->amount) ? sep_to_decimal($va->amount) : 0,
                    'fee'          => ! empty($va->fee) ? sep_to_decimal($va->fee) : 0,
                    'id_pajak'     => ! empty($va->id_pajak) ? $va->id_pajak : 0,
                    'tax_amount'   => $pajakAmount,
                    'nilai_pajak'  => $tarifPajak,
                    'pph_tarif'    => $tarifPajakPph,
                    'tax_base_ppn' => ! empty($va->tax_base_ppn) ? sep_to_decimal($va->tax_base_ppn) : null,
                    'pph_amount'   => $pphAmount,
                    'jatuh_tempo'  => date('Y-m-d', strtotime($inp->due_date)),
                    'total'        => $totalAmount,
                    'id_pph'       => ! empty($va->id_pph) ? $va->id_pph : 0,
                ];

                $this->update_detail($data, $va->id_tdinvoice);
            } else {
                $idService = explode('#', $va->id_service);
                $prefixNo  = cariPrefixInvoice($idService[0], $idService[1]);
                $noInvoice = new_noinvoice($prefixNo);

                $data = [
                    'id_service'      => $idService[0],
                    'id_header'       => $inp->id_thinvoice,
                    'id_bast'         => $inp->id_bast,
                    'id_unit'         => $inp->id_unit,
                    'id_tenant'       => $inp->id_tenant,
                    'tipe'            => $idService[1],
                    'no_invoice'      => $noInvoice,
                    'periode'         => date('Y-m-d', strtotime($periode)),
                    'created_user'    => $inp->updated_user,
                    'created_date'    => $inp->updated_date,
                    'amount'          => $amount,
                    'fee'             => ! empty($va->fee) ? sep_to_decimal($va->fee) : 0,
                    'id_pajak'        => ! empty($va->id_pajak) ? $va->id_pajak : 0,
                    'tax_amount'      => $pajakAmount,
                    'nilai_pajak'     => $tarifPajak,
                    'pph_tarif'       => $tarifPajakPph,
                    'pph_amount'      => $pphAmount,
                    'jatuh_tempo'     => date('Y-m-d', strtotime($inp->due_date)),
                    'total'           => $totalAmount,
                    'virtual_account' => $virtualAcc,
                    'tax_base_ppn'    => ! empty($va->tax_base_ppn) ? sep_to_decimal($va->tax_base_ppn) : null,
                ];

                if (! empty($va->id_pph)) {
                    $data['id_pph'] = $va->id_pph;
                }

                $this->db
                    ->table('td_invoice')
                    ->insert($data);
            }
        }

        $ret->status = true;
        $ret->msg    = 'Success';

        return $ret;
    }

    public function update_detail(array $data, $id): bool
    {
        return (bool) $this->db
            ->table('td_invoice')
            ->where('id', $id)
            ->update($data);
    }
}
