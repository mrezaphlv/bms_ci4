<?php
namespace App\Controllers;

use App\Models\MLmInvoice;
use App\Models\MPajak;
use Config\Database;
use CodeIgniter\Database\BaseConnection;


class Lm_invoice extends MyController
{
    protected MLmInvoice $model;
    protected BaseConnection $db;
    public function __construct()
    {
        parent::__construct();
        $this->nama_menu = "LM Invoice";
        $this->model = new MLmInvoice();
        $this->db = Database::connect();
    }
	public function index()
	{
        return $this->template('pages/lm_invoice/vlm_invoice', ['akses' => $this->akses]);
	}
    public function input()
    {
        if (($this->akses->can_create ?? 0) != 1) {
            return redirect()->to(site_url('lm_invoice'));
        }

        $pajakModel = new MPajak();
        $data['dt_detail'] = [];
        $data['droplist_pajak'] = $pajakModel->droplistppn();
        $data['droplist_pph'] = $pajakModel->droplist_pph();
        // print_r($data);die;
        // echo count()
        $data['akses'] = $this->akses;
        return $this->template('pages/lm_invoice/input', $data);
    }
    public function saveNewInvoice(){
        $fp = $this->request->getPost();
    
        $periode = !empty($this->request->getPost('periode')) ? date('Y-m-d',strtotime('01-'.$this->request->getPost('periode'))) : NULL;
        $jtempo = !empty($this->request->getPost('due_date')) ? date('Y-m-d',strtotime($this->request->getPost('due_date'))) : NULL;
        
        $cek_periode_closing = cek_close_periode($periode);
        $cek_periode_closing2 = cek_close_periode($jtempo);

        // $cek_akses_close_periode = cek_akses_close_periode();
        // $flg_periode_close = 0;
        // $flg_periode_close2 = 0;

        // if($cek_periode_closing > 0){
        //     if($cek_akses_close_periode > 0){
        //         $flg_periode_close = 0;
        //     }else{
        //         $flg_periode_close = 1;
        //     }
        // }else{
        //     $flg_periode_close = 0;
        // }

        // if($cek_periode_closing2 > 0){
        //     if($cek_akses_close_periode > 0){
        //         $flg_periode_close2 = 0;
        //     }else{
        //         $flg_periode_close2 = 1;
        //     }
        // }else{
        //     $flg_periode_close2 = 0;
        // }

        if($cek_periode_closing == 0 &&  $cek_periode_closing2 == 0){
            $data = $this->request->getPost('dcharge');
        foreach ($data as $key => $dcharge) {
            $idservice_exploder = explode('#', $dcharge['id_service']);
            $fp['dcharge'][$key]['id_service'] = $idservice_exploder[0];
        }
        $fp['created_user'] = session()->get('id_user');
        $fp['created_date'] = date('Y-m-d H:i:s');
        // $s = api_json('POST','lm_invoice/saveNewInvoice', $fp);
        $s = $this->model->saveNewInvoice($fp);
        }else{
            $s = ['status' => false,'msg' => 'Periode sudah Closing','header_id' => NULL];
        }
        
        return $this->response->setJSON($s);
    }
    public function lm_invoice_except_id(){
        return '(1,2)';
    }
    public function grid(){
        // print_r($this->request->getPost());
        $post = $this->request->getPost();
        $start = max(0, (int) ($post['start'] ?? 0));
        $length = max(1, (int) ($post['length'] ?? 10));
        $where = '';
        $indexColOrder = (int) ($post['order'][0]['column'] ?? 0);
        $requestedOrder = (string) ($post['columns'][$indexColOrder]['data'] ?? 'id');
        $allowedOrder = ['id', 'no_invoice', 'no_group_invoice', 'kode_unit', 'jatuh_tempo', 'periode', 'nama_owner', 'id_header', 'total', 'amount', 'id_pajak', 'id_service', 'nama_pajak', 'nm_service', 'nm_utilities', 'total_alokasi'];
        $order_column = in_array($requestedOrder, $allowedOrder, true) ? $requestedOrder : 'id';
        $order_dir = strtolower((string) ($post['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        if (!empty($post['gsearch'])) {
            // $upsearch = strtoupper($post['gsearch']);
            $lowsearch = $this->db->escapeString(strtolower((string) $post['gsearch']));
            $upsearch = $this->db->escapeString(strtoupper((string) $post['gsearch']));
            $where .= " and (upper(a.no_invoice) like '%$upsearch%' or lower(mu.kode_unit) like '%$lowsearch%' or upper(th.no_group_invoice) like '%$upsearch%' or lower(msc.nama) like '%$lowsearch%' or lower(mtu.nama) like '%$lowsearch%')";
        }
        
        if(!empty($post['tenant'])){
            $tenant = (string) $post['tenant'];
            $lownama_search = $this->db->escapeString(strtolower($tenant));
            $where .= "  and lower(mt.nama) like '%$lownama_search%'";
        }
        if(!empty($post['duedate'])){
            $duedate = (string) $post['duedate'];
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $duedate)) {
                $where .= " AND date(a.jatuh_tempo) = '{$duedate}'";
            }
        }
		if(!empty($post['periode'])){
            $periode = (string) $post['periode'];
            if (preg_match('/^\d{4}-\d{2}$/', $periode)) {
                $where .= " and to_char(a.periode, 'yyyy-mm') = '{$periode}'";
            }
        }
        $lm_invoice_except_id = $this->lm_invoice_except_id();
        
        $q_count_all = "select count(a.id) as ctr from td_invoice a join th_invoice th on a.id_header=th.id left join m_unit mu on th.id_unit=mu.id left join (select ta.* from th_handover_agreement tha join (select max(a.id) as id, a.id_unit from t_agreement a join th_handover_agreement tha2 on a.id=tha2.id_agreement group by a.id_unit) aa on tha.id_agreement = aa.id join t_agreement ta on aa.id = ta.id) b on th.id_unit=b.id_unit left join m_tenant mt on b.id_owner=mt.id left join m_service_charge msc on msc.id= a.id_service left join m_utilities mtu on mtu.id=a.id_service where a.flag_id = true and a.id_parent = 0 and a.id_service in (9,24,27,1,23,22,20,3) $where";
    
        $query_count_all = $this->db->query($q_count_all)->getRow()->ctr;

        // $q1 = "select a.id, a.no_invoice, th.no_group_invoice, mu.kode_unit, a.jatuh_tempo , a.periode, mt.nama as nama_owner, a.id_header,a.amount,a.total, coalesce(tal.total, 0) as total_alokasi from td_invoice a join th_invoice th on a.id_header=th.id left join m_unit mu on th.id_unit=mu.id left join (select ta.* from th_handover_agreement tha join (select max(a.id) as id, a.id_unit from t_agreement a join th_handover_agreement tha2 on a.id=tha2.id_agreement group by a.id_unit) aa on tha.id_agreement = aa.id join t_agreement ta on aa.id = ta.id) b on th.id_unit=b.id_unit left join m_tenant mt on b.id_owner=mt.id left join (select id_invoice as id_invoice, sum(total_amount) as total from t_alokasi group by id_invoice ) tal on a.id= tal.id_invoice where a.flag_id=true and a.id_service not in $lm_invoice_except_id $where";

        $q1ooo = "select
            a.id,
            a.no_invoice,
            th.no_group_invoice,
            mu.kode_unit,
            a.jatuh_tempo ,
            a.periode,
            mt.nama as nama_owner,
            a.id_header,
            a.total,
            a.amount,
            id_pajak,
            id_service,
            mp.nama_pajak,
            msc.nama as nm_service,
            mtu.nama as nm_utilities,
            coalesce(tal.total,
            0) as total_alokasi
        from
            td_invoice a
        join th_invoice th on
            a.id_header = th.id
        left join m_unit mu on
            th.id_unit = mu.id
        left join m_service_charge msc on
            (a.id_service = msc.id
                and a.tipe = 'SERVICE')
        left join m_utilities mtu on
            (a.id_service = mtu.id
                and a.tipe = 'UTILITIES')
        left join m_pajak mp on
            a.id_pajak = mp.id
        left join v_handover_agreement b on
            th.id_unit = b.id_unit
        left join th_handover_agreement tha on coalesce(a.id_bast,th.id_bast) = tha.id
        left join m_tenant mt on
            a.id_tenant = mt.id
        left join (
            select
                id_invoice as id_invoice,
                sum(total_amount) as total
            from
                t_alokasi
            group by
                id_invoice ) tal on
            a.id = id_invoice
        where
            a.flag_id = true and a.id_parent = 0  $where
            and a.id_service in (9, 24, 27, 1,23,22,20)";
        $q1 = "select
            a.id,
            a.no_invoice,
            th.no_group_invoice,
            mu.kode_unit,
            a.jatuh_tempo ,
            a.periode,
            mt.nama as nama_owner,
            a.id_header,
            a.total,
            a.amount,
            id_pajak,
            id_service,
            mp.nama_pajak,
            msc.nama as nm_service,
            mtu.nama as nm_utilities,
            coalesce(tal.total,
            0) as total_alokasi
        from
            td_invoice a
        join th_invoice th on
            a.id_header = th.id
        left join m_unit mu on
            th.id_unit = mu.id
        left join m_service_charge msc on
            (a.id_service = msc.id
                and a.tipe = 'SERVICE')
        left join m_utilities mtu on
            (a.id_service = mtu.id
                and a.tipe = 'UTILITIES')
        left join m_pajak mp on
            a.id_pajak = mp.id
        left join th_handover_agreement tha on coalesce(a.id_bast,th.id_bast) = tha.id
        left join m_tenant mt on
            a.id_tenant = mt.id
        left join (
            select
                id_invoice as id_invoice,
                sum(total_amount) as total
            from
                t_alokasi
            group by
                id_invoice ) tal on
            a.id = id_invoice
        where
            a.flag_id = true and a.id_parent = 0  $where
            and a.id_service in (9, 24, 27, 1,23,22,20)";
        // dd($q1);
        $qq = "select * from ($q1) as data order by $order_column $order_dir limit $length offset $start";
        $query = $this->db->query($qq);
		$rdata = array();
		foreach($query->getResult()  as $i => $v){
			$remaining = floatval($v->total) - floatval($v->total_alokasi);
			$rdata[$i] = $v;
            $rdata[$i]->remaining = fmt_currency($remaining);
            $rdata[$i]->remaining_amount = $remaining;
			$rdata[$i]->periode = !empty($v->periode) ? date('F-Y',strtotime($v->periode)) : NULL ;
            $rdata[$i]->fmt_amount = fmt_currency($v->amount);
            $rdata[$i]->fmt_total = fmt_currency($v->total);
		}
        
        $callback = array(
            'draw' => $this->request->getPost('draw'), // Ini dari datatablenya    
            'recordsTotal' => $query_count_all,
            'recordsFiltered' => $query_count_all,
            'data' => $rdata,
            // 'akses' => $this->akses,
            'http_req' => $this->request->isAJAX() ? 'XMLHttpRequest' : null,
        );
        return $this->response->setJSON($callback);
    }
    public function view($id)
    {
        $id = (int) $id;
        $req = $this->model->getView($id);
        // dd($req);die;
        $data = (array)$req;
        $data['id_param'] = $id;
        $data['akses'] = $this->akses;
        return $this->template('pages/lm_invoice/view', $data);
    }
    public function edit($id)
    {
        $id = (int) $id;
        $dt = $this->db->query("select * from th_invoice where flag_id = true and id = $id");
        if($dt->getRow()){
            $dtr = $dt->getRow();
            $periode = !empty($dtr->periode) ? date('Y-m-d',strtotime($dtr->periode)) : NULL;
			$cek_periode_closing = cek_close_periode($periode);

            // $cek_akses_close_periode = cek_akses_close_periode();
			// $flg_periode_close = 0;
			// if($cek_periode_closing > 0){
			// 	if($cek_akses_close_periode > 0){
			// 		$flg_periode_close = 0;
			// 	}else{
			// 		$flg_periode_close = 1;
			// 	}
			// }else{
			// 	$flg_periode_close = 0;
			// }

            if($cek_periode_closing == 0){
                $pajakModel = new MPajak();
                $req = api('POST', 'lm_invoice/getEdit', ['id' => $id]);
                // dd($req);
                $data = (array)$req;
                $data['droplist_pajak'] = $pajakModel->droplistppn();
                $data['droplist_pph'] = $pajakModel->droplist_pph();
                $data['periode'] = date('m-Y',strtotime($req->dthead->periode));
                $data['id_param'] = $id;
                $data['akses'] = $this->akses;
                return $this->template('pages/lm_invoice/input', $data);
            }else{
                return redirect()->to(site_url('lm_invoice'));
            }
       
        }
        
    }
    public function select_ppn($id)
    {
        $id = (int) $id;
        $req = $this->model->getPPN($id);
        if ($req['status']) {
            return $this->response->setBody((string) $req['pajak']);
        }

        return $this->response->setBody('');
    }
    public function editDialog($id = null)
    {
        $pajakModel = new MPajak();
        $data['droplist_pajak'] = $pajakModel->droplistppn();
        $data['droplist_pph'] = $pajakModel->droplist_pph();
		$id = $this->request->getPost('id');
		$fp = array('id' => $id);
		$res = api('POST', 'lm_invoice/getEditDialog', $fp);
		$resArray = json_decode(json_encode($res), true);
	
		$resArray['droplist_pajak'] = $data['droplist_pajak'];
		$resArray['droplist_pph'] = $data['droplist_pph'];
		return $this->response->setJSON($resArray);
	}
    public function saveEditLMInvoice()
    {
        if (($this->akses->can_edit ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses edit LM Invoice.',
            ]);
        }

        $post = $this->request->getPost();
        $get = static fn (array $data, string $key, $default = null) =>
            array_key_exists($key, $data) && $data[$key] !== '' ? $data[$key] : $default;

        $amount    = $get($post, 'amount');
        $total     = $get($post, 'total');
        $taxAmount = $get($post, 'tax_amount');

        $insert = [
            'id_primary'      => $get($post, 'id_primary'),
            'id_header'       => $get($post, 'id_header'),
            'id_schedule'     => $get($post, 'id_schedule'),
            'periode'         => $get($post, 'periode'),
            'id_service'      => $get($post, 'id_service'),
            'tipe'            => $get($post, 'tipe'),
            'flag_id'         => $get($post, 'flag_id'),
            'created_date'    => $get($post, 'created_date'),
            'created_user'    => $get($post, 'created_user'),
            'updated_user'    => (int) session()->get('id_user'),
            'updated_date'    => date('Y-m-d H:i:s'),
            'jatuh_tempo'     => $get($post, 'jatuh_tempo'),
            'amount'          => $amount !== null ? ribuan_to_decimal($amount) : null,
            'start_meter'     => $get($post, 'start_meter'),
            'end_meter'       => $get($post, 'end_meter'),
            'no_invoice'      => $get($post, 'no_invoice'),
            'virtual_account' => $get($post, 'virtual_account'),
            'id_pajak'        => $get($post, 'ppn', 0),
            'nilai_pajak'     => $get($post, 'nilai_pajak'),
            'fee'             => $amount !== null ? ribuan_to_decimal($amount) : null,
            'status_bayar'    => $get($post, 'status_bayar'),
            'activated'       => $get($post, 'activated'),
            'total'           => $total !== null ? ribuan_to_decimal($total) : null,
            'tgl_bayar'       => $get($post, 'tgl_bayar'),
            'id_pembelian'    => $get($post, 'id_pembelian'),
            'deskripsi'       => $get($post, 'deskripsi'),
            'cara_bayar'      => $get($post, 'cara_bayar'),
            'bukti_bayar'     => $get($post, 'bukti_bayar'),
            'payment_inquiry' => $get($post, 'payment_inquiry'),
            'inquiry_id'      => $get($post, 'inquiry_id'),
            'tax_amount'      => $taxAmount !== null ? ribuan_to_decimal($taxAmount) : null,
            'novoucher'       => $get($post, 'novoucher'),
            'no_bast'         => $get($post, 'no_bast'),
            'id_bast'         => $get($post, 'id_bast'),
            'id_voucher'      => $get($post, 'id_voucher'),
            'addition'        => $get($post, 'addition'),
            'id_unit'         => $get($post, 'id_unit'),
            'id_tenant'       => $get($post, 'id_tenant'),
            'id_parent'       => $get($post, 'id_parent'),
            'periode_end'     => $get($post, 'periode_end'),
            'deleted_user'    => $get($post, 'deleted_user'),
            'deleted_date'    => $get($post, 'deleted_date'),
            'periode_start'   => $get($post, 'periode_start'),
            'id_pph'          => $get($post, 'pph'),
            'pph_tarif'       => $get($post, 'pph_tarif'),
            'pph_amount'      => $get($post, 'pph_amount'),
            'flag_denda'      => $get($post, 'flag_denda'),
            'status_edited'   => 'REQUEST',
            'notes_edit'      => $get($post, 'notes_edit'),
            'action'          => 'EDIT',
        ];

        $saved = $this->db->table('td_invoice_req_edit')->insert($insert);

        return $this->response->setJSON([
            'status' => (bool) $saved,
            'msg'    => $saved ? 'LM Invoice Request Edit!' : 'LM Invoice Request Edit gagal!',
        ]);
    }

    public function updateData(){
        $fp = $this->request->getPost();
        $periode = !empty($this->request->getPost('periode')) ? date('Y-m-d',strtotime('01-'.$this->request->getPost('periode'))) : NULL;
        $jtempo = !empty($this->request->getPost('due_date')) ? date('Y-m-d',strtotime($this->request->getPost('due_date'))) : NULL;
        
        $cek_periode_closing = cek_close_periode($periode);
        $cek_periode_closing2 = cek_close_periode($jtempo);
        // $flg_periode_close = 0;
        // $flg_periode_close2 = 0;
        // $cek_akses_close_periode = cek_akses_close_periode();
        // if($cek_periode_closing > 0){
        //     if($cek_akses_close_periode > 0){
        //         $flg_periode_close = 0;
        //     }else{
        //         $flg_periode_close = 1;
        //     }
        // }else{
        //     $flg_periode_close = 0;
        // }
        // if($cek_periode_closing2 > 0){
        //     if($cek_akses_close_periode > 0){
        //         $flg_periode_close2 = 0;
        //     }else{
        //         $flg_periode_close2 = 1;
        //     }
        // }else{
        //     $flg_periode_close2 = 0;
        // }

        if( $cek_periode_closing == 0 &&  $cek_periode_closing2 == 0){
            $data = $this->request->getPost('dcharge');
        
            $fp['updated_user'] = session()->get('id_user');
            $fp['updated_date'] = date('Y-m-d H:i:s');
            // echo json_encode($fp);die;
            // $s = api_json('POST','lm_invoice/update_data', $fp);
            $s = $this->model->updateInvoice($fp);
        }else{
            $s = new \stdClass();
            $s->status = false;
            $s->msg = 'Periode sudah closing';
        }
        
        // dd($s);
        return $this->response->setJSON($s);
    }
    public function hitung_fee_indah(){
        $fp = array('id_scharge' => $this->request->getPost('id_scharge'), 'id_unit' => $this->request->getPost('id_unit'));
        // $response = api('POST','undangan/cariScharge_indah',$fp);
        $id_service = $this->request->getPost('id_scharge');
        $id_unit = $this->request->getPost('id_unit');
        $ret = new \stdClass();
        $scharge_exploder = explode('#', $id_service);
        if($scharge_exploder[1] == 'SERVICE'){
            $table = 'm_service_charge';
            $select = '*';
        } else {
            $table = 'm_utilities';
            $select = '*, 0 as nominal';
        }
        $qq = "select $select from $table where id=".$scharge_exploder[0];
        $xx=$this->db->query($qq);
        $unit = $this->db->query("select luas from m_unit where id=$id_unit")->getRow();
        if($xx){
            // $this->response(['status' => true, 'msg' => 'Data Found' ,'data' => $xx->getRow(),'unit' => $unit],  RestController::HTTP_OK);
            $ret->status = true;
            $ret->msg = 'Data Found';
            $ret->data = $xx->getRow();
            $ret->unit = $unit;
        }else{
            // $this->response(['status' => false, 'msg' => 'Data Found', 'data' => null,'unit' => null],  RestController::HTTP_NOT_FOUND);
            $ret->status = false;
            $ret->msg = 'Data Not Found';
            $ret->data = null;
            $ret->unit = null;
        }
        return $this->response->setJSON($ret);
    } 
    public function load_biaya(){
        $id_unit = $this->request->getPost('id_unit');
        $q1 = $this->db->query("select aa.id as id_agreement, a.id as id_handover, mt.nama as nama_tenant, a.id_owner as id_tenant from th_handover_agreement a join ( select id_unit, max(coalesce(handover_date,'1970-01-01')) as handover_date  from th_handover_agreement where id_unit = $id_unit group by id_unit) b on a.id_unit = b.id_unit and coalesce(a.handover_date,'1970-01-01') = b.handover_date left join t_agreement aa on a.id_agreement = aa.id left join m_tenant mt on coalesce(a.id_owner,aa.id_owner) = mt.id")->getRow();
        if($q1){
        $id_handover = $q1->id_handover;
        $q2="select 1 as segmen, a.id_servicecharge  as id_ent, msc.nama  from td_handover_charge a join th_handover_agreement th on a.id_header=th.id left join m_service_charge msc on a.id_servicecharge=msc.id where th.id=$id_handover ";
        $q2 .= "union all select 2 as segmen, a.id_utilities as id_ent, mut.nama from td_handover_utilities a join th_handover_agreement th on a.id_header=th.id left join m_utilities mut on a.id_utilities=mut.id where th.id=$id_handover";
        $x2 = $this->db->query($q2);
        if($x2){
            $ret = ['status' => true,'msg' => 'Data Found','data' => $x2->getResult(),'agreement' => $q1];
        }else{
            $ret = ['status' => false,'msg' => 'Data NOT Found','data' => NULL]; 
        }
        }else{
            $ret = ['status' => false,'msg' => 'Data NOT Found','data' => NULL]; 
        }
        return $this->response->setJSON($ret);
    }
    public function deleteDok(){
        $ret = new \stdClass();
        $id = (int) $this->request->getPost('id');
        $idUser = (int) session()->get('id_user');
        $time = date('Y-m-d H:i:s');

        $this->db->transStart();
        $this->db->query(
            "update td_invoice set flag_id = false, deleted_user = ?, deleted_date = to_timestamp(?,'YYYY-MM-DD HH24:MI:SS') where id = ?",
            [$idUser, $time, $id]
        );
        $this->db->query("update th_jurnal set flag_id = false where id_invoice = ?", [$id]);
        $this->db->transComplete();

        $ret->status = $this->db->transStatus();
        $ret->msg = $ret->status ? 'Hapus data Berhasil' : 'Hapus data Gagal';
        return $this->response->setJSON($ret);
    }
    public function cek_periode_close(){
		$ret = array();
		$id = $this->request->getPost('id');
		$dt = $this->db->query("select * from th_invoice where flag_id = true and id = $id");
        $dtr = $dt->getRow();
        if ($dtr) {
			
			$periode = !empty($dtr->periode) ? date('Y-m-d',strtotime($dtr->periode)) : NULL;
			$cek_periode_closing = cek_close_periode($periode);
            // $cek_akses_close_periode = cek_akses_close_periode();
			// $flg_periode_close = 0;
			// if($cek_periode_closing > 0){
			// 	if($cek_akses_close_periode > 0){
			// 		$flg_periode_close = 0;
			// 	}else{
			// 		$flg_periode_close = 1;
			// 	}
			// }else{
			// 	$flg_periode_close = 0;
			// }
			if($cek_periode_closing == 0){
				$ret['status'] = true;
				$ret['msg'] = 'Success';
			}else{
				$ret['status'] = false;
				$ret['msg'] = MSG_CLOSING_PERIODE;
			}
		}else{
			$ret['status'] = false;
			$ret['msg'] = 'Data Not Found';
		}
		return $this->response->setJSON($ret);
	}
}
