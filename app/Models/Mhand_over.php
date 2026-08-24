<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Mhand_over extends Model
{
    protected $DBGroup = 'default';

    public function grid(array $inp): array
    {
        $offset = max(0, (int) ($inp['offset'] ?? 0));
        $limit  = max(1, (int) ($inp['limit'] ?? 20));
        $status = strtoupper((string) ($inp['status'] ?? ''));
        $search = trim((string) ($inp['search'] ?? ''));

        $where = '';
        if ($search !== '') {
            $searchEsc = str_replace("'", "''", $search);
            $lowSearchEsc = str_replace("'", "''", strtolower($search));
            $upSearchEsc = str_replace("'", "''", strtoupper($search));
            $where .= " and (
                lower(a.no_undangan) like '%{$lowSearchEsc}%'
                OR lower(b.nama) like '%{$lowSearchEsc}%'
                OR lower(aa.no_pinjam_pakai) like '%{$lowSearchEsc}%'
                OR lower(mu.kode_unit) like '%{$lowSearchEsc}%'
                OR lower(aa.no_agreement) like '%{$lowSearchEsc}%'
                OR upper(aa.status_bayar::text) like '%{$upSearchEsc}%'
                OR TO_CHAR(aa.handover_date, 'DD-MM-YYYY') like '%{$searchEsc}%'
            ) ";
        }

        $whereFilter = '';
        if ($status === 'NEW') {
            $whereFilter = " WHERE (aa.id_agreement is null) ";
        } elseif ($status === 'SETUP_UTILITIES') {
            $whereFilter = " WHERE (aa.id_agreement is not null and aa.header_utilities is null) ";
        } elseif ($status === 'SETUP_CHARGE') {
            $whereFilter = " WHERE (aa.header_utilities is not null and aa.header_charge is null) ";
        } elseif ($status === 'DONE') {
            $whereFilter = " WHERE (aa.id_agreement is not null AND aa.header_utilities is not null AND aa.header_charge is not null) ";
        } elseif ($status === 'DIALIHKAN') {
            $whereFilter = " WHERE (aa.id_agreement is not null AND aa.header_utilities is not null AND aa.header_charge is not null and aa.id_bast_new is not null) ";
        }

        $allowedSort = [
            'id' => 'id',
            'no_agreement' => 'no_agreement',
            'no_pinjam_pakai' => 'no_pinjam_pakai',
            'no_undangan' => 'no_undangan',
            'handover_date' => 'handover_date_sort',
            'nama_owner' => 'nama_owner',
            'tipe_tenant' => 'tipe_tenant',
            'kode_unit' => 'kode_unit',
            'status_bayar' => 'status_bayar',
            'diwakilkan' => 'diwakilkan',
        ];

        $sort = (string) ($inp['sort'] ?? 'id');
        $orderColumn = $allowedSort[$sort] ?? 'id';
        $orderDir = strtolower((string) ($inp['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $baseQuery = "SELECT
                md5(a.id::character varying) as mid,
                a.id,
                aa.id as id_handover_check,
                thu.id_header as dutil_check,
                thc.id_header as dcharge_check,
                aa.no_agreement,
                aa.id_agreement,
                aa.step,
                tca.id as id_closed_agreement,
                thu.id_header as header_utilities,
                thc.id_header as header_charge,
                a.no_undangan,
                coalesce(to_char(aa.handover_date,'dd-mm-yyyy'),'-') as handover_date,
                aa.handover_date as handover_date_sort,
                a.status,
                mu.kode_unit,
                b.nama as nama_owner,
                a.file_ppjb,
                aa.status_bayar,
                aa.id_parent,
                ab.id as id_checklist,
                ab.status as status_checklist,
                a.status as status_agreement,
                case when a.diwakilkan = true then 'Diwakilkan' else 'Tidak Diwakilkan' end as diwakilkan,
                mtt.nama as tipe_tenant,
                aa.no_pinjam_pakai,
                tha2.id as id_insentive,
                lbn.id_bast_new,
                a.waktu_hadir
            from t_agreement a
            left join t_checklist ab on a.id=ab.id_agreement and ab.tipe = 'ENGINEERING' and ab.flag_id = true
            left join th_handover_agreement aa on a.id=aa.id_agreement and aa.flag_id = true and aa.id_owner > 0
            left join t_closed_agreement tca on tca.id_handover_agreement = aa.id and tca.flag_id = true and tca.status = 'DONE'
            left join m_tenant b on a.id_owner=b.id
            left join m_sales c on a.id_sales=c.id
            left join m_unit mu on a.id_unit=mu.id
            left join m_building mb on mu.id_building=mb.id
            left join (select id_header from td_handover_utilities where flag_id=true group by id_header) thu on aa.id=thu.id_header
            left join (select id_header from td_handover_charge where flag_id=true group by id_header) thc on aa.id=thc.id_header
            left join m_tipe_tenant mtt on b.id_tipe = mtt.id
            left join th_handover_agreement tha2 on aa.id=tha2.id_parent and tha2.flag_id = true
            left join log_bast_nonaktif lbn on aa.id = lbn.id_bast
            where a.flag_id=true
            and a.status='APPROVED'
            {$where}";

        $rowCount = $this->db->query("select count(*) as ctr from ({$baseQuery}) as aa {$whereFilter}")->getRow();
        $total = $rowCount ? (int) $rowCount->ctr : 0;

        $rows = $this->db
            ->query("select * from ({$baseQuery}) as aa {$whereFilter} order by {$orderColumn} {$orderDir} limit ? offset ?", [$limit, $offset])
            ->getResult();

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }

    function addAgreement($id_undangan){
        $qq = "select a.id as id_undangan, a.no_undangan, a.id_unit,mu.kode_unit, a.order_date, a.accept_date, mt.nama as nama_owner, mb.nama as nama_building, ms.nama as nama_sales, a.waktu_hadir from t_agreement a left join m_unit mu on a.id_unit=mu.id left join m_tenant mt on a.id_owner=mt.id left join m_sales ms on a.id_sales=ms.id left join m_building mb on mu.id_building=mb.id where a.id=$id_undangan";
        $x1 = $this->db->query($qq);
        if($x1){
            $head = $x1->getRow();
            $qutil = "select a.* from td_agreement_utilities a join m_meterrange b on (a.id_meterrange=b.id and b.flag_id=true) left join m_meter c on (a.id_meter=c.id and c.flag_id=true) join m_utilities d on (a.id_utilities=d.id and d.flag_id=true) where a.flag_id=true and a.id_agreement=$id_undangan";
            $util = $this->db->query($qutil);
            $qcharge = "select a.* from td_agreement_charge a where a.flag_id= true and a.id_agreement=$id_undangan";
            $charge = $this->db->query($qcharge);
            return ['resp' => ['status' => true, 'msg' => 'Data Found', 'data' => $x1->getRow(),'util' => $util->getResult(),'charge' => $charge->getResult()], 'code' => ResponseInterface::HTTP_OK];
        }
        return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => null],'code' => ResponseInterface::HTTP_NOT_FOUND];
    }
    function cek_schedule_tagihan_unit($id_unit){
        $q1 = $this->db->query("select count(*) as ctr from th_schedule_tagihan where id_unit=$id_unit")->getRow()->ctr;
        return $q1;
    }
    function saveNewTransaksi($inp){
        $kodeagreement = kode_agreement();
        $ambil_max_agreement = $this->db->query("select coalesce(max(left(no_agreement ,4)::int),0) as max_noagreement from th_handover_agreement where no_agreement like '%$kodeagreement'")->getRow()->max_noagreement;
        $new_urut_agreement = intval($ambil_max_agreement) + 1;
        $new_noagreement = str_pad($new_urut_agreement,4,"0",STR_PAD_LEFT).'/'.$kodeagreement;
        
        $in_head = array('id_agreement'=> $inp->id_agreement,'no_agreement' => $new_noagreement,'created_date' => $inp->created_date,'created_user' => $inp->created_user,'handover_date' => $inp->handover_date,'status_bayar' => $inp->status_bayar,'fito_date' => $inp->fito_date);
        $in_head['id_unit'] = $inp->id_unit;
        $in_head['id_owner'] = $inp->id_owner;
        $query = $this->db->table('th_handover_agreement')->insert($in_head);
        $dtu = $inp->utilities;
        $dtc = $inp->charge;
        if($query){
            $newidh = $this->db->insertID();
            foreach ($dtu as $key => $u) {
                if(!empty($u->id_dutil)){
                    $id_dutil = $u->id_dutil;
                    $que = "insert into td_handover_utilities (id_header, created_date, created_user, id_meterrange, id_meter, id_utilities, meter_start, start_date)";
                    $que .= "select $newidh as id_header, TO_TIMESTAMP('".$inp->created_date."','YYYY-MM-DD HH24:MI:SS') as created_date, ".$inp->created_user." as created_user, ".$u->s_meterrange." as meterrange, ".$u->s_meterid." as meterid, ".$u->s_util." as utilities, ".$u->meter_start." as meter_start, TO_TIMESTAMP('".$u->start_date."','YYYY-MM-DD HH24:MI:SS') as start_date from td_agreement_utilities where id=$id_dutil";
                    $this->db->query($que);
                //     $insert = array('id_header' => $newidh,'id_utilities' => $u->s_util, 'id_meterrange' => $u->s_meterrange,'id_meter' => $u->s_meterid,'created_date' => $inp->created_date,'created_user' => $inp->created_user,'meter_start' => $u->meter_start,'start_date' => $u->start_date);
                // $this->db->table('td_handover_utilities')->insert($insert);
                }else{
                 $insert1 = array('id_header' => $newidh,'id_utilities' => $u->s_util, 'id_meterrange' => $u->s_meterrange,'id_meter' => $u->s_meterid,'created_date' => $inp->created_date,'created_user' => $inp->created_user,'meter_start' => $u->meter_start,'start_date' => $u->start_date);
                $this->db->table('td_handover_utilities')->insert($insert1);   
                }
                
            }
            foreach ($dtc as $key => $c) {
                if($key > 0){
                    $amount = round($c->amount);
                    if(!empty($c->id_dcharge)){
                    $id_dcharge = $c->id_dcharge;
                    $que = "insert into td_handover_charge (id_header, created_date, created_user, id_servicecharge, id_pajak, periode, fee, amount, nilai_pajak, start_date, end_date)";
                    $que .= "select $newidh as id_header, TO_TIMESTAMP('".$inp->created_date."','YYYY-MM-DD HH24:MI:SS') as created_date, ".$inp->created_user." as created_user, id_servicecharge, id_pajak, periode, fee, amount, nilai_pajak, TO_TIMESTAMP('".$c->start_date."','YYYY-MM-DD HH24:MI:SS') as start_date, TO_TIMESTAMP('".$c->end_date."','YYYY-MM-DD HH24:MI:SS') as end_date from td_agreement_charge where id=$id_dcharge";
                    $this->db->query($que);
                    }else{
                    $insert2 = array('id_header' => $newidh,'created_date' => $inp->created_date,'created_user' => $inp->created_user,'id_servicecharge' => $c->s_scharge,'id_pajak' => $c->s_pajak, 'periode' => $c->s_periode,'fee' => $c->fee,'amount' => $amount,'nilai_pajak' => cariTarifPajak($c->s_pajak),'start_date' => $c->start_date,'end_date' => $c->end_date);
                    $this->db->table('td_handover_charge')->insert($insert2);
                    }
                }
                
            }
            return ['new_idh' => $newidh,'resp' => ['status' => true, 'msg' => 'Berhasil simpan data'], 'code' => ResponseInterface::HTTP_OK];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Gagal simpan Data'], 'code' => ResponseInterface::HTTP_NOT_FOUND];
        }
    }
    function buat_termin($jarak){
        // $jarak = 3;
        $jml = 12;
        $termin= $jml/$jarak;
        $bulan = array();
        for($i = 1;$i <=12 ; $i++){
            $fmod = fmod($i, $jarak);
            if($fmod == 0){
                $bln_tagih = ($i-$jarak)+1;
                array_push($bulan, $bln_tagih);
            }
        
        }
        return $bulan;
    }
    function schedule_tagihan_kedua($handover_date, $jarak){
        for ($i = 0; $i < intval($jarak); $i++) {
          echo date('Y-m-d', strtotime($handover_date. ' + '.$i.' months')).'<br>';
        }
    }

    function gen_schedule_tagihan($id, $created_user){
        $this->db->transStart();
        $tgl_cutoff = con_tglcutoff();
        // $handover_date = $this->db->query("select handover_date from th_handover_agreement where id=$id")->getRow()->handover_date;
        $dt_bast = $this->db->query("select * from th_handover_agreement where id=$id")->getRow();
        $id_tenant = $dt_bast->id_owner;
        $id_materai = get_idmaterai();
        $q1 = "select a.id,'SERVICE' as tipe, ta.id_unit , ha.no_agreement as no_bast,ha.id as id_bast, a.id_servicecharge as id_service, a.periode, a.id_pajak ,a.fee, a.amount, a.nilai_pajak, ha.handover_date,  a.start_date , a.end_date from td_handover_charge a join th_handover_agreement ha on a.id_header=ha.id join t_agreement ta on (ha.id_agreement=ta.id and ta.flag_id=true) where a.flag_id=true and a.id_header=$id and a.id_servicecharge != $id_materai";
        $q = $this->db->query($q1);

        $scid = get_id_ipl();
        $sfid = get_id_sf();
        $tgl_jtempo = get_jtempo_tagihan();
        if($q){
            $insert_batch = array();
            // $tgperiodea = date('Y-m-d',strtotime($schema_periode[0].'-'.'01'));
            $tgperiodea = date('Y-m-d',strtotime($q->getRow()->start_date.'-'.'01'));
            $jatuh_tempoa = date('Y-m', strtotime($tgperiodea)).'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
            //insert materai
                $fee_materai = $this->db->query("select nama, nominal from m_service_charge where id=$id_materai")->getRow()->nominal;
            $data = array('id_unit' => $q->getRow()->id_unit,
                        'id_bast' => $q->getRow()->id_bast,
                        'periode' => $tgperiodea,
                        'jatuh_tempo' => date('Y-m-d',strtotime($jatuh_tempoa)),
                        'id_service' => $id_materai,
                        'tipe' => 'SERVICE',
                        'jarak_periode' => 1,
                        'created_date' => date('Y-m-d H:i:s'),
                        'status' => 'DONE',
                        'id_bast' => $q->getRow()->id_bast,
                        'periode_end' => $tgperiodea,
                        'id_tenant' => $id_tenant,
                        // 'fee' => $fee_materai,
                        'created_user' => $created_user);
                        array_push($insert_batch, $data);
                        $this->db->table('th_schedule_tagihan')->insert($data);
            // end insert materai
            
            foreach ($q->getResult() as $key => $va) {
                if(date('d',strtotime($va->start_date)) > 20){
                    $tambah = 1;
                    $start_tagihan = date('Y-m-d', strtotime($va->start_date. ' + '.$tambah.' months'));
                }else{
                    $start_tagihan = $va->start_date;
                }
               $schema_periode = array();
                $start_year = intval(date('Y',strtotime($start_tagihan)));
                // $end_year = get_endyear_tagihan();
               $end_year = $start_year + 1; 
                for ($i=$start_year; $i <= $end_year; $i++) {
                    if($i == $start_year){
                        $start_month = intval(date('m',strtotime($start_tagihan)));
                    }else{
                        $start_month = 1;
                    }
                    $jumlah_month = 12;
                        for ($j=intval($start_month); $j <= $jumlah_month; $j++) { 
                            $periode = $i.str_pad($j,2,"0",STR_PAD_LEFT).'01';
                            array_push($schema_periode, date('Y-m',strtotime($periode)));
                        }
                    
                    }
                $termin_tagih = $this->buat_termin($va->periode);
        
                //insert tagihan pertama tidak gantung
                $id_serv = $va->id_service;
                if($va->id_service == $scid['basic']){
                    $id_serv = $scid['dp'];
                }
                if($va->id_service == $sfid['basic']){
                    $id_serv = $sfid['dp'];
                }
                // if($va->id_service == $scid['basic']){
                //     $id_serv = $scid['dp'];
                // }
                $jarak_periode = $va->periode;
                $tgperiodeb = date('Y-m-d',strtotime($schema_periode[0].'-'.'01'));
                $tgperiodeb_end =  date('Y-m-d', strtotime($tgperiodeb. ' + '.($jarak_periode - 1).' months'));
                // $jatuh_tempob = date('Y-m', strtotime($tgperiodeb. ' + '.'6'.' days')).'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
                $jatuh_tempob = date('Y-m-d', strtotime($tgperiodeb. ' + '.'6'.' days'));
                $data = array('id_unit' => $va->id_unit,
                        'periode' => $tgperiodeb,
                        'periode_end' => $tgperiodeb_end,
                        'jatuh_tempo' => date('Y-m-d',strtotime($jatuh_tempob)),
                        'id_service' => $id_serv,
                        'tipe' => $va->tipe,
                        'jarak_periode' => $va->periode,
                        'id_pajak' => $va->id_pajak,
                        'status' => 'DONE',
                        'id_bast' => $va->id_bast,
                        'id_tenant' => $id_tenant,
                        'fee' => $va->fee,
                        'nilai_pajak' => $va->nilai_pajak,
                        'id_bast' => $va->id_bast,
                        'created_date' => date('Y-m-d H:i:s'),
                        'created_user' => $created_user);
                        array_push($insert_batch, $data);
                        $this->db->table('th_schedule_tagihan')->insert($data);
                
                //insert tagihan kedua gantung
                // dari controller schedule saveNewData
                // $dtmonths = [1, 4, 7, 10];
                //         if ($month_1 > 10) {
                //             $nextMonth = 1; 
                //         } else {
                //             $nextMonth = min(array_filter($dtmonths, function ($month) use ($month_1) {
                //                 return $month >= $month_1;
                //             }));
                //         }
                //         $nextMonth = empty($nextMonth) ? min($dtmonths) : $nextMonth;
                //         $selisih_month = ($nextMonth >= $month_1) ? $nextMonth - $month_1 : 12 - $month_1 + $nextMonth;
                //         $periode = $first_schedule . '-01';
                
                if(!in_array(intval(date('m',strtotime($schema_periode[$va->periode].'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT)))), $termin_tagih)){
                    $tgperiodec = date('Y-m-d',strtotime($schema_periode[$va->periode].'-'.'01'));
                    // $jatuh_tempoc = date('Y-m', strtotime($tgperiodec. ' + '.'1'.' months')).'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
                    $tgperiodec_end =  date('Y-m-d', strtotime($tgperiodec. ' + '.($jarak_periode - 1).' months'));
                    $jatuh_tempoc = date('Y-m-d', strtotime($tgperiodec. ' + '.'6'.' days'));
                    $termin_gantung = $va->periode;
                    $month_1 = date('m', strtotime($tgperiodec));
                    $dtmonths = [1, 4, 7, 10];
                        if ($month_1 > 10) {
                            $nextMonth = 1; 
                        } else {
                            $nextMonth = min(array_filter($dtmonths, function ($month) use ($month_1) {
                                return $month >= $month_1;
                            }));
                        }
                        $nextMonth = empty($nextMonth) ? min($dtmonths) : $nextMonth;
                        $termin_gantung = ($nextMonth >= $month_1) ? $nextMonth - $month_1 : 12 - $month_1 + $nextMonth;
                    $data = array('id_unit' => $va->id_unit,
                        'periode' => $tgperiodec,
                        'periode_end' => $tgperiodec_end,
                        'jatuh_tempo' => date('Y-m-d',strtotime($jatuh_tempoc)),
                        'id_service' => $va->id_service,
                        'tipe' => $va->tipe,
                        'jarak_periode' => $termin_gantung,
                        'id_pajak' => $va->id_pajak,
                        'nilai_pajak' => $va->nilai_pajak,
                        'status' => 'DONE',
                        'id_bast' => $va->id_bast,
                        'id_tenant' => $id_tenant,
                        'fee' => $va->fee,
                        'created_date' => date('Y-m-d H:i:s'),
                        'created_user' => $created_user);
                        array_push($insert_batch, $data);
                        $this->db->table('th_schedule_tagihan')->insert($data);
                }
        
                // insert tagihan looping sesuai jalur
                for ($i=intval($va->periode); $i < count($schema_periode); $i++) {
                    $tgperiode = $schema_periode[$i].'-'.'01';
                    // $jatuh_tempo = $schema_periode[$i].'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
                    $tgperiode_end =  date('Y-m-d', strtotime($tgperiode. ' + '.$jarak_periode.' months'));
                    $tgperiode_end =  date('Y-m-d', strtotime($tgperiode_end. ' - 1 days'));
                    $jatuh_tempo = date('Y-m', strtotime($tgperiode)).'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
                    if(in_array(intval(date('m',strtotime($tgperiode))), $termin_tagih)){
                    $data = array('id_unit' => $va->id_unit,
                        'periode' => date('Y-m-d',strtotime($tgperiode)),
                        'periode_end' => date('Y-m-d',strtotime($tgperiode_end)),
                        'jatuh_tempo' => date('Y-m-d',strtotime($jatuh_tempo)),
                        'id_service' => $va->id_service,
                        'tipe' => $va->tipe,
                        'jarak_periode' => $va->periode,
                        'id_pajak' => $va->id_pajak,
                        'id_bast' => $va->id_bast,
                        'status' => 'DONE',
                        'fee' => $va->fee,
                        'id_bast' => $va->id_bast,
                        'nilai_pajak' => $va->nilai_pajak,
                        'id_tenant' => $id_tenant,
                        'created_date' => date('Y-m-d H:i:s'),
                        'created_user' => $created_user);
                        array_push($insert_batch, $data);
                        $this->db->table('th_schedule_tagihan')->insert($data);
                    }
                }
            }
            // print_r($insert_batch);die();
        }
        
        // die();
        $q2 = "select a.id,'UTILITIES' as tipe, ha.id as id_bast, ha.no_agreement as no_bast,ta.id_unit , a.id_header, a.id_utilities as id_service, 1 as periode, a.meter_start, a.id_meterrange, a.start_date FROM td_handover_utilities a join th_handover_agreement ha on a.id_header=ha.id join t_agreement ta on (ha.id_agreement=ta.id and ta.flag_id=true) where a.flag_id=true and a.id_header=$id";
        $x = $this->db->query($q2);
        $batch_util = array();
        if($x){
            foreach ($x->getResult() as $key => $va) {
                if(date('d',strtotime($va->start_date)) > 20){
                    $tambah = 1;
                    $start_tagihan = date('Y-m-d', strtotime($va->start_date. ' + '.$tambah.' months'));
                }else{
                    $start_tagihan = $va->start_date;
                }
               $schema_periode = array();
                $start_year = intval(date('Y',strtotime($start_tagihan)));
                // $end_year = get_endyear_tagihan();
               $end_year = $start_year + 1; 
                for ($i=$start_year; $i <= $end_year; $i++) {
                    if($i == $start_year){
                        $start_month = intval(date('m',strtotime($start_tagihan)));
                    }else{
                        $start_month = 1;
                    }
                    $jumlah_month = 12;
                        for ($j=intval($start_month); $j <= $jumlah_month; $j++) { 
                            $periode = $i.str_pad($j,2,"0",STR_PAD_LEFT).'01';
                            array_push($schema_periode, date('Y-m',strtotime($periode)));
                        }
                    
                    }
               for ($i=0; $i < count($schema_periode); $i++) { 
                $tgperiode = $schema_periode[$i].'-'.'01';
                    // $jatuh_tempo = $schema_periode[$i].'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
                    $jatuh_tempo = date('Y-m', strtotime($tgperiode)).'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
                    $data = array('id_unit' => $va->id_unit,
                    'periode' => date('Y-m-d',strtotime($tgperiode)),
                    'jatuh_tempo' => date('Y-m-d',strtotime($jatuh_tempo)),
                    'id_service' => $va->id_service,
                    'tipe' => $va->tipe,
                    'id_bast' => $va->id_bast,
                    'jarak_periode' => $va->periode,
                    'id_meterrange' => $va->id_meterrange,
                    'status' => 'NEW',
                    // 'start_meter' => $i == intval(date('m',strtotime($start_tagihan))) ? $va->meter_start : NULL,
                    'start_meter' => NULL,
                    'created_date' => date('Y-m-d H:i:s'),
                    'id_bast' => $va->id_bast,
                    'id_tenant' => $id_tenant,
                    'created_user' => $created_user,
                );
                    $data['utility_periode'] = date('Y-m-d', strtotime($data['periode']. ' - 1 months'));
                    $data['periode_end'] = date('Y-m', strtotime($data['utility_periode'])).'-'.$tgl_cutoff;
                    $data['periode_start'] = date('Y-m', strtotime($data['periode_end']. ' - 1 months')).'-'.($tgl_cutoff + 1);
                    // array_push($insert_batch, $data);
                    array_push($batch_util, $data);
                    
               }
            }
            $this->db->table('th_schedule_tagihan')->insertBatch($batch_util);
        }
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            $ret = new stdClass();
            $ret->status = false;
            $ret->msg = 'Simpan data gagal';
            return $ret;
        }

        $ret = new stdClass();
        $ret->status = true;
        $ret->msg = 'Simpan data Berhasil';
        return $ret;
    }

    

    function getDetail($id){
        $qq = "SELECT a.id, aa.id as id_checklist,aa2.id as id_ctenant,a.no_undangan, a.order_date, a.accept_date, a.status, a.id_unit,mu.kode_unit, a.id_owner, b.nama as nama_owner,b.hp1 as no_hp, a.id_sales, c.nama as nama_sales, mb.nama as nama_building, b.email1 as email_owner, d.nama as tipe_tenant, a.notes, a.waktu_hadir,sem.id_agreement as id_agreement_email, ab.no_agreement, a.tgl_bayar, ab.fito_date, ab.handover_date, a.diwakilkan, a.waktu_hadir, ab.status_bayar, aa2.id_agreement as id_agreement_ctenant, ab.id as id_handover, a.nama_wakil, a.nik_wakil, a.alamat_wakil, a.npwp_wakil, a.pekerjaan_wakil, a.tempat_lahir_wakil, a.tgl_lahir_wakil from t_agreement a left join m_tenant b on a.id_owner=b.id left join m_sales c on a.id_sales=c.id left join m_unit mu on a.id_unit=mu.id left join m_building mb on mu.id_building=mb.id left join m_tipe_tenant d on b.id_tipe=d.id join t_checklist aa on (a.id=aa.id_agreement and aa.tipe='ENGINEERING') left join (select id_agreement from t_email_send_hist_agreement group by id_agreement) sem on a.id=sem.id_agreement join th_handover_agreement ab on a.id=ab.id_agreement left join t_checklist aa2 on (a.id=aa2.id_agreement and aa2.tipe = 'TENANT')  where a.flag_id=true and a.id=$id";
        $query=$this->db->query($qq);
        $ret = new stdClass();
        if ($query) {
            $head = $query->getRow();
            $id_checklist = $head->id_checklist;
            $id_ctenant = $head->id_ctenant;
            $qutil = "select a.*, d.nama as nama_utilities, e.nama as nama_rangetype , c.kode as kode_meter from td_handover_utilities a join m_meterrange b on (a.id_meterrange=b.id and b.flag_id=true) join m_meter c on (a.id_meter=c.id and c.flag_id=true) join m_utilities d on (a.id_utilities=d.id and d.flag_id=true) left join m_rangetype e on b.id_rangetype=e.id join th_handover_agreement tha on a.id_header=tha.id join t_agreement ta on tha.id_agreement=ta.id where a.flag_id=true and ta.id=$id and tha.id_parent = 0";
            // print_r($qutil);die();
            $util = $this->db->query($qutil);
            $qcharge = "select b.nama as nama_service, c.nama_pajak ,a.* from td_handover_charge a left join m_service_charge b on a.id_servicecharge=b.id left join m_pajak c on a.id_pajak=c.id join th_handover_agreement tha on a.id_header=tha.id join t_agreement ta on tha.id_agreement=ta.id where a.flag_id= true and ta.id=$id and tha.id_parent = 0";
            $charge = $this->db->query($qcharge);
            // print_r($util->getResult());die();
            $q_itc = "select * from(
        select 1 as segmen, a.id as id_kategori, a.nama as nama_kategori, a.no_urut as urut_kategori,a.nama as nama_item, 0 as id_item, '' as keterangan, 0 as qty, true as tenant_check,'' as kondisi, '' as foto from m_kategori_item a join td_checklist_item  b on a.id=b.kategori_item_id where b.id_checklist=$id_checklist  group by a.id, a.nama, a.no_urut
        union all
        select 2 as segmen, a.id as id_kategori, a.nama as nama_kategori, a.no_urut as urut_kategori,b.nama as nama_item, c.id as id_item, c.keterangan, c.qty, c.tenant_check, c.kondisi, c.foto from m_kategori_item a join m_item b on a.id=b.kategori_item_id join td_checklist_item c on b.id=c.id_item where c.id_checklist=$id_checklist
        ) as hh order by urut_kategori , segmen";
        // print_r($q_itc);die();
            $que_itc = $this->db->query($q_itc);
            if(!empty($id_ctenant)){
                $q_itt = "select * from(
                    select 1 as segmen, a.id as id_kategori, a.nama as nama_kategori, a.no_urut as urut_kategori,a.nama as nama_item, 0 as id_item, '' as keterangan, 0 as qty, true as tenant_check,'' as kondisi, '' as foto from m_kategori_item a join td_checklist_item  b on a.id=b.kategori_item_id where b.id_checklist=$id_ctenant and b.tenant_check=true group by a.id, a.nama, a.no_urut
                    union all
                    select 2 as segmen, a.id as id_kategori, a.nama as nama_kategori, a.no_urut as urut_kategori,b.nama as nama_item, c.id as id_item, c.keterangan, c.qty, c.tenant_check, c.kondisi, c.foto from m_kategori_item a join m_item b on a.id=b.kategori_item_id join td_checklist_item c on b.id=c.id_item where c.id_checklist=$id_ctenant and c.tenant_check=true
                    ) as hh order by urut_kategori , segmen";
                        $que_itt = $this->db->query($q_itt)->getResult();
            }else{
                $que_itt = [];
            }
            
            $q_email = $this->db->query("select * from t_email_send_hist_agreement where id_agreement=$id");
            $ret->status = true;
            $ret->msg = 'Data Found';
            $ret->data = new stdClass();
            $ret->data->head = $head;
            $ret->data->util = $util->getResult();
            $ret->data->charge = $charge->getResult();
            $ret->data->citem = $que_itc->getResult();
            $ret->data->ctenant = $que_itt;
            $ret->demail = ($q_email) ? $q_email->getResult() : array();
            return $ret;
        } else {
            // return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => null],'code' => ResponseInterface::HTTP_NOT_FOUND];
            $ret->status = false;
            $ret->msg = 'Data Not Found';
            $ret->data = null;
            return $ret;
        }
    }
    function print_bast($id){
        $q1 = "select aa.id as id_agreement, a.no_agreement, a.status_bayar, aa.no_undangan, a.no_pinjam_pakai, mt.nama as nama_tenant, mt.hp1 as no_hp, mt.tempat_lahir, mt.tgl_lahir, mt.npwp, mt.nik, mt.alamat1 as alamat_tenant, mu.kode_unit, mu.lantai, mu.no_urut as no_unit, mbl.balkon as tipe_unit, mu.luas as luas_unit, mb.nama as nama_building, a.handover_date,aa.tgl_bayar, aa.tgl_ppjb, aa.no_ppjb from th_handover_agreement a join t_agreement aa on a.id_agreement=aa.id left join m_tenant mt on aa.id_owner=mt.id left join m_unit mu on aa.id_unit=mu.id left join m_balkon mbl on mbl.id=mu.id_balkon left join m_building mb on mu.id_building=mb.id where aa.id=$id";
        $xq1 = $this->db->query($q1);
        // print_r($q1);die;
        if($xq1){
            return ['resp' => ['status' => true, 'msg' => 'Data Found', 'data' => $xq1->getRow(), 'tarif_service' => tarif_service_charge()]];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => $xq1->getRow()]];
        }
    }
    function print_bast_insentive($id){
        $q1 = "select aa.id as id_agreement, a.no_agreement, a.status_bayar, aa.no_undangan, a.no_pinjam_pakai, mt.nama as nama_tenant, mt.hp1 as no_hp, mt.tempat_lahir, mt.tgl_lahir, mt.npwp, mt.nik, mt.alamat1 as alamat_tenant, mu.kode_unit, mu.lantai, mu.no_urut as no_unit, mbl.balkon as tipe_unit, mu.luas as luas_unit, mb.nama as nama_building, a.handover_date,aa.tgl_bayar, aa.tgl_ppjb, aa.no_ppjb, a.no_ppjb as no_ppjb_insentive, a.tgl_lunas as tgl_ppjb_insentive, a.kode_kir as kode_kir from th_handover_agreement a join t_agreement aa on a.id_agreement=aa.id left join m_tenant mt on aa.id_owner=mt.id left join m_unit mu on aa.id_unit=mu.id left join m_balkon mbl on mbl.id=mu.id_balkon left join m_building mb on mu.id_building=mb.id where aa.id=$id and a.no_ppjb is not null";
        $xq1 = $this->db->query($q1);
        // print_r($q1);die;
        if($xq1){
            return ['resp' => ['status' => true, 'msg' => 'Data Found', 'data' => $xq1->getRow(), 'tarif_service' => tarif_service_charge()]];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => $xq1->getRow()]];
        }
    }
    function print_bast_diwakilkan($id){
        $q1 = "select aa.id as id_agreement, a.status_bayar, a.no_agreement,aa.diwakilkan, aa.no_undangan, a.no_pinjam_pakai, a.handover_date,mt.nama as nama_tenant,aa.nama_wakil,aa.tempat_lahir_wakil,aa.tgl_lahir_wakil,aa.nik_wakil,aa.alamat_wakil, aa.npwp_wakil, aa.pekerjaan_wakil, mt.tempat_lahir,mt.tgl_lahir,mt.npwp,mt.nik,mt.alamat1 as alamat_tenant,mu.kode_unit,mu.lantai,mu.no_urut as no_unit,mbl.balkon as tipe_unit,mu.luas as luas_unit,mb.nama as nama_building,a.handover_date, aa.tgl_bayar, aa.tgl_ppjb,aa.no_ppjb from th_handover_agreement a join t_agreement aa on a.id_agreement = aa.id left join m_tenant mt on aa.id_owner = mt.id left join m_unit mu on aa.id_unit = mu.id left join m_balkon mbl on mbl.id = mu.id_balkon left join m_building mb on mu.id_building = mb.id where aa.diwakilkan = true and aa.id = $id";
        $xq1 = $this->db->query($q1);
        // print_r($xq1);die;
        if($xq1){
            return ['resp' => ['status' => true, 'msg' => 'Data Found', 'data' => $xq1->getRow(), 'tarif_service' => tarif_service_charge()]];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => $xq1->getRow()]];
        }
    }

    function print_bast_insentive_diwakilkan($id){
        $q1 = "select aa.id as id_agreement, a.status_bayar, a.no_agreement,aa.diwakilkan, aa.no_undangan, a.no_pinjam_pakai, a.handover_date,mt.nama as nama_tenant,aa.nama_wakil,aa.tempat_lahir_wakil,aa.tgl_lahir_wakil,aa.nik_wakil,aa.alamat_wakil,mt.tempat_lahir,mt.tgl_lahir,mt.npwp,mt.nik,mt.alamat1 as alamat_tenant,mu.kode_unit,mu.lantai,mu.no_urut as no_unit,mbl.balkon as tipe_unit,mu.luas as luas_unit,mb.nama as nama_building,a.handover_date, aa.tgl_bayar, aa.tgl_ppjb,aa.no_ppjb, a.no_ppjb as no_ppjb_insentive, a.tgl_lunas as tgl_ppjb_insentive, a.kode_kir as kode_kir from th_handover_agreement a join t_agreement aa on a.id_agreement = aa.id left join m_tenant mt on aa.id_owner = mt.id left join m_unit mu on aa.id_unit = mu.id left join m_balkon mbl on mbl.id = mu.id_balkon left join m_building mb on mu.id_building = mb.id where aa.diwakilkan = true and aa.id = $id and a.no_ppjb is not null";
        $xq1 = $this->db->query($q1);
        // print_r($xq1);die;
        if($xq1){
            return ['resp' => ['status' => true, 'msg' => 'Data Found', 'data' => $xq1->getRow(), 'tarif_service' => tarif_service_charge()]];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => $xq1->getRow()]];
        }
    }
    function print_bast_util($id){
        $q1 = "select aa.id as id_agreement, a.no_agreement, aa.no_undangan, mt.nama as nama_tenant, mt.tempat_lahir, mt.tgl_lahir, mt.npwp, mt.nik, mt.alamat1 as alamat_tenant, mu.kode_unit, mu.lantai, mu.no_urut as no_unit, mu.luas as luas_unit, mb.nama as nama_building, a.id as id_handover, mt.whatsapp as wa_tenant from th_handover_agreement a join t_agreement aa on a.id_agreement=aa.id left join m_tenant mt on aa.id_owner=mt.id left join m_unit mu on aa.id_unit=mu.id left join m_building mb on mu.id_building=mb.id where aa.id=$id";
        $xq1 = $this->db->query($q1);
        if($xq1){
            $id_util_water = id_util_water();
            $id_util_listrik = id_util_listrik();
            $water = $this->db->query("select mm.kode as kode_meter, a.meter_start from td_handover_utilities a left join m_meter mm on a.id_meter=mm.id  where a.id_header = ".$xq1->getRow()->id_handover." and a.id_utilities =$id_util_water");
            $listrik = $this->db->query("select mm.kode as kode_meter from td_handover_utilities a left join m_meter mm on a.id_meter=mm.id  where a.id_header = ".$xq1->getRow()->id_handover." and a.id_utilities =$id_util_listrik");
            $resp = ['status' => true, 'msg' => 'Data Not Found', 'data' => $xq1->getRow(),'mwater' => $water->getRow()];
            if($listrik){
                $resp['mlistrik'] = $listrik->getRow();
            }
            return ['resp' => $resp,'code' => ResponseInterface::HTTP_OK];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => $xq1->getRow()]];
        }
    }
    function print_tatib($id){
        $q1 = "select aa.id as id_agreement, a.no_agreement, aa.no_undangan, mt.nama as nama_tenant, mt.tempat_lahir, mt.tgl_lahir, mt.npwp, mt.nik, mt.alamat1 as alamat_tenant, mu.kode_unit, mu.lantai, mu.no_urut as no_unit, mu.luas as luas_unit, mb.nama as nama_building, a.handover_date from th_handover_agreement a join t_agreement aa on a.id_agreement=aa.id left join m_tenant mt on aa.id_owner=mt.id left join m_unit mu on aa.id_unit=mu.id left join m_building mb on mu.id_building=mb.id where aa.id=$id";
        $xq1 = $this->db->query($q1);
        if($xq1){
            
            return ['resp' => ['status' => true, 'msg' => 'Data Not Found', 'data' => $xq1->getRow(), 'tarif_service' => tarif_service_charge()]];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => $xq1->getRow()]];
        }
    }
    function pilih_bast($id){
        // $id = $this->post('id_agreement');
        $q = $this->db->query("select aa.status_bayar from t_agreement a join th_handover_agreement aa on a.id=aa.id_agreement where a.id=$id");
        if($q){
            return ['resp' => ['status' => true, 'msg' => 'Data Not Found', 'data' => $q->getRow()->status_bayar]];
        }else{
            return ['resp' => ['status' => false, 'msg' => 'Data Not Found', 'data' => null ]];
        }
    }
    function cari_tagihan_awal($id_unit){
        $q1 ="select count(id) as ctr from th_schedule_tagihan where id_unit = $id_unit and to_char(periode,'yyyymm')=(select to_char(periode,'yyyymm') from th_schedule_tagihan where id_unit =$id_unit group by to_char(periode,'yyyymm') order by to_char(periode,'yyyymm')::int asc limit 1)";
        $x1 = $this->db->query($q1)->getRow()->ctr;
        if($x1 > 0){
        $qq ="select id from th_schedule_tagihan where id_unit = $id_unit and to_char(periode,'yyyymm')=(select to_char(periode,'yyyymm') from th_schedule_tagihan where id_unit =$id_unit group by to_char(periode,'yyyymm') order by to_char(periode,'yyyymm')::int asc limit 1)";
        $xq = $this->db->query($qq);
        $ret = array();
        foreach ($xq->getResult() as $key => $a) {
            array_push($ret, $a->id);
        }
        return $ret;
        }else{
            return false;
        }
        
    }
    function get_for_pengalihan_hak($id_bast){
       $x = $this->db->query("select a.*, mt.nama as nama_owner, mu.kode_unit from th_handover_agreement a left join m_tenant mt on a.id_owner=mt.id left join m_unit mu on a.id_unit=mu.id where a.id = $id_bast");
       return $x->getRow();
    }
}
