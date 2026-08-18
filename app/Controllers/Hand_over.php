<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Database;
use stdClass;

class Hand_over extends BaseController
{
    protected $db;
    protected $session;
    protected $mhand_over;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->db = Database::connect();
        $this->session = session();
        $this->mhand_over = model('Mhand_over');

        // write_file() dipakai pada proses konversi dokumen.
        helper('filesystem');
    }
    public function index()
	{
  
	   // print_r($this->akses);die();
	    if($this->akses->can_view == 1){
            $data = array();
            // $data['btn1'] = $this->cek_akses(array('controller' => 'insentif_ppns'));
            // dd($data['btn1']);
	       return $this->template('pages/hand_over/vhand_over',$data); 
	    }
	}
	
    public function grid()
	{
		// $inp = json_decode(file_get_contents('php://input'));
        $post = $this->request->getPost();
        $inp = array('draw' => $post['draw'] ,'start' => $post['start'], 'search' => $post['search'],'length' => $post['length'], 'tb_checkbox' => $this->request->getPost('tb_checkbox'));
        $index_col_order = $post['order'][0]['column'];
        $inp['order'] = ['dir' => $post['order'][0]['dir'], 'column' => $post['columns'][$index_col_order]['data']];
        
		$start = $inp['start'];
		$length = $inp['length'];
		$where = '';
        $whereFilter = '';
        if(!empty($post['status'])){
            $search = strtoupper($post['status']);
            if($search == 'NEW'){
                $whereFilter .= " WHERE (aa.id_agreement is null) ";
            } else if($search == 'SETUP_UTILITIES'){
                $whereFilter .= " WHERE (aa.id_agreement is not null and aa.header_utilities is null) ";
            } else if($search == 'SETUP_CHARGE'){
                $whereFilter .= " WHERE (aa.header_utilities is not null and aa.header_charge is null) ";
            } else if($search == 'DONE'){
                $whereFilter .= " WHERE (aa.id_agreement is not null AND aa.header_utilities is not null AND aa.header_utilities is not null)";
            } else if($search == 'DIALIHKAN'){
                $whereFilter .= " WHERE (aa.id_agreement is not null AND aa.header_utilities is not null AND aa.header_utilities is not null and aa.id_bast_new is not null) ";
            }
        }


		if (!empty($inp['search']['value'])) {
			$search = $inp['search']['value'];
			$lowsearch = strtolower($search);
			$upsearch = strtoupper($search);
			// $where .= " and (a.no_undangan like '%$lowsearch%' OR aa.no_agreement like '%$lowsearch%') ";
			$where .= " and (lower(a.no_undangan) like '%$lowsearch%' OR lower(b.nama) like '%$lowsearch%' OR lower(aa.no_pinjam_pakai) like '%$lowsearch%' OR lower(mu.kode_unit) like '%$lowsearch%' OR lower(aa.no_agreement) like '%$lowsearch%' OR upper(aa.status_bayar::text) like '%$upsearch%' OR TO_CHAR(aa.handover_date, 'DD-MM-YYYY') like '%$search%' ) ";
		}

		$order = $inp['order'];
		$order_column = $order['column'];
		$order_dir = $order['dir'];

		$q1 = "SELECT md5(a.id::character varying) as mid, a.id, aa.id as id_handover_check, thu.id_header as dutil_check, thc.id_header as dcharge_check,aa.no_agreement, aa.id_agreement, tca.id as id_closed_agreement, thu.id_header as header_utilities, thc.id_header as header_charge, a.no_undangan, coalesce(to_char(aa.handover_date,'dd-mm-yyyy'),'-') as handover_date, a.status, mu.kode_unit, b.nama as nama_owner, a.file_ppjb, aa.status_bayar, aa.id_parent,	ab.id as id_checklist, ab.status as status_checklist, a.status  as status_agreement, case when a.diwakilkan = true then 'Diwakilkan' else 'Tidak Diwakilkan' end as diwakilkan, mtt.nama as tipe_tenant, aa.no_pinjam_pakai, tha2.id as id_insentive, lbn.id_bast_new , a.waktu_hadir from t_agreement a left join t_checklist ab on a.id=ab.id_agreement and ab.tipe = 'ENGINEERING' and ab.flag_id = true left join th_handover_agreement aa on a.id=aa.id_agreement and aa.flag_id = true and aa.id_owner > 0 left join t_closed_agreement tca on tca.id_handover_agreement = aa.id and tca.flag_id = true and tca.status = 'DONE' left join m_tenant b on a.id_owner=b.id left join m_sales c on a.id_sales=c.id left join m_unit mu on a.id_unit=mu.id left join m_building mb on mu.id_building=mb.id  left join (select id_header from td_handover_utilities where flag_id=true group by id_header) thu on aa.id=thu.id_header left join (select id_header from td_handover_charge where flag_id=true group by id_header) thc on aa.id=thc.id_header left join m_tipe_tenant mtt on b.id_tipe = mtt.id left join th_handover_agreement tha2 on aa.id=tha2.id_parent and tha2.flag_id = true left join log_bast_nonaktif lbn on aa.id = lbn.id_bast  where a.flag_id=true and a.status='APPROVED' $where ";
		$query_count_all = $this->db->query("select count(*) as ctr from ($q1) as aa $whereFilter")->getRow()->ctr;
		$qq = "select * from ($q1) as aa $whereFilter order by $order_column $order_dir limit $length offset $start";
		$query = $this->db->query($qq);
		
        $callback = array(
            'draw' => $this->request->getPost('draw'), // Ini dari datatablenya    
            'recordsTotal' => $query_count_all,
            'recordsFiltered' => $query_count_all,
            'data' => $query->getResult()
        );
        return $this->response->setJSON($callback);
	}
    public function grid_dokumen(){
        // print_r($this->request->getPost());die();
        $post = $this->request->getPost();
        $fp = array('draw' => $post['draw'] ,'start' => $post['start'], 'search' => $post['search'],'length' => $post['length'], 'id_header' => $this->request->getPost('id_header'));
        $result = api_json('POST', 'hand_over/grid_dokumen', $fp);
        $callback = array(
            'draw' => $this->request->getPost('draw'), // Ini dari datatablenya    
            'recordsTotal' => $result->count_all,
            'recordsFiltered' => $result->count_all,
            'data' => $result->data
        );
        return $this->response->setJSON($callback);
    }

    public function form($id_undangan){
       if($this->akses->can_create == 1){
        $cek_unit_serahterima = $this->db->query("select mu.kode_unit from t_agreement a left join m_unit mu on a.id_unit = mu.id where a.id = $id_undangan and a.id_unit in (select id_unit from v_handover_agreement where id_owner > 0)");
        $lanjut = 1;
        if($cek_unit_serahterima->getNumRows() == 0){
            $result = api('POST', 'hand_over/getAddAgreement', array('id' => $id_undangan));
            $data['id_agreement'] = $id_undangan;
            $data['dthead'] = $result->data;
            $data['dtcharge'] = $result->charge;
            $data['dtutil'] = $result->util;
         //   print_r($result->data);die();
            return $this->template('pages/hand_over/input/step_1',$data); 
        }else{
            $this->session->setFlashdata( 'error','Unit '.$cek_unit_serahterima->getRow()->kode_unit.' sudah serah terima . Jika ada pengalihan hak mohon input di menu pengalihan hak');
            return redirect()->to(base_url('hand_over'));
        }
          
       }else{
        $this->session->setFlashdata('error',$this->not_auth_return()->msg);
        return redirect()->to(base_url('hand_over'));
       } 
    }
    public function form_2($id_undangan, $id_handover){
       if($this->akses->can_edit == 1){
           $result = api('POST', 'hand_over/getAddAgreement', array('id' => $id_undangan));
           $data['id_agreement'] = $id_undangan;
           $data['dthead'] = $result->data;
           $data['dtcharge'] = $result->charge;
           $data['dtutil'] = $result->util;
        //   print_r($data['dtutil']);die();
           return $this->template('pages/hand_over/input/step_2',$data); 
       }else{

        $this->session->setFlashdata($this->not_auth_return()->msg, 'success');
        return redirect()->to(base_url('hand_over'));
       } 
    }
    public function saveNewTransaksi(){
        $fp = array('id_agreement' => $this->request->getPost('id_agreement'),'no_undangan' => $this->request->getPost('no_undangan'),'order_date' => $this->request->getPost('order_date'),'accept_date' => $this->request->getPost('accept_date'), 'id_owner' => $this->request->getPost('id_owner'),'id_unit' => $this->request->getPost('id_unit'), 'id_sales' => $this->request->getPost('id_sales'), 'charge' => $this->request->getPost('charge'),'utilities' => $this->request->getPost('utilities'),'created_date' => date('Y-m-d H:i:s'),'created_user' => $this->session->get('id_user'),'tgl_undangan' => $this->request->getPost('tgl_undangan'),'jam_undangan' => $this->request->getPost('jam_undangan'),'fito_date' => $this->request->getPost('fito_date'),'status_bayar' => $this->request->getPost('status_bayar'),'handover_date' => $this->request->getPost('handover_date'));
        $response = api_json('POST','hand_over/saveNewTransaksi', $fp);
        return $this->response->setJSON($response);
    }
    public function saveNewStep1(){
        $fp = array('id_agreement' => $this->request->getPost('id_agreement'),'no_undangan' => $this->request->getPost('no_undangan'),'order_date' => $this->request->getPost('order_date'),'accept_date' => $this->request->getPost('accept_date'), 'id_owner' => $this->request->getPost('id_owner'),'id_unit' => $this->request->getPost('id_unit'), 'id_sales' => $this->request->getPost('id_sales'), 'created_date' => date('Y-m-d H:i:s'),'created_user' => $this->session->get('id_user'),'tgl_undangan' => $this->request->getPost('tgl_undangan'),'jam_undangan' => $this->request->getPost('jam_undangan'),'status_bayar' => $this->request->getPost('status_bayar'));
        $fp['handover_date'] = !empty($this->request->getPost('handover_date')) ? date('Y-m-d',strtotime($this->request->getPost('handover_date'))) : null;
        $fp['fito_date'] = !empty($this->request->getPost('fito_date')) ? date('Y-m-d',strtotime($this->request->getPost('fito_date'))) : null;

        $response = api_json('POST','hand_over/saveNewStep1', $fp);
        return $this->response->setJSON($response);
    }

    public function detail($id){
        if($this->akses->can_view == 1){
                $result = $this->mhand_over->getDetail($id);
                // dd($result);
                $data['status'] = $result->status;
                // dd($result->data->head);
                if($result->status == true){
                    $data['dthead'] = $result->data->head;
                    $data['dtutil'] = $result->data->util;
                    $data['dtcharge'] = $result->data->charge;
                    $data['citem'] = $result->data->citem;
                    $data['ctenant'] = $result->data->ctenant;
                    $data['demail'] = $result->demail;
                }
                $data['id'] = $id;
                $data['id_checklist'] = $result->data->head->id_checklist;
               return $this->template('pages/hand_over/vdetail',$data); 
            } 
    }
    public function insentive_ppn(){
        $fp = [
            'id_agreement' =>  $this->request->getPost('id_agreement'),
            'no_ppjb' =>  $this->request->getPost('no_ppjb'),
            'tgl_ppjb' =>  $this->request->getPost('tgl_ppjb'),
            'kode_kir' =>  $this->request->getPost('kode_kir'),
            'handover_date' =>  $this->request->getPost('handover_date'),
            'created_user' => $this->session->get('id_user')
        ];
        // echo json_encode($fp);die;
        $res = api_json('POST','hand_over/submitInsentive', $fp);
        return $this->response->setJSON($res);
    }
    public function saveDokumen()
    {
        $id_handover = $this->request->getPost('id_handover');
        $dir = FCPATH . 'dokumen/upload/handover/' . $id_handover;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $this->request->getFile('dok_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSizeByUnit('kb') > 5120) {
                return $this->response->setJSON([
                    'status' => false,
                    'msg'    => 'Upload Gagal: ukuran file maksimal 5 MB',
                ]);
            }

            $filenama = $file->getRandomName();
            $file->move($dir, $filenama);

            $fp = [
                'created_user' => $this->session->get('id_user'),
                'created_date' => date('Y-m-d H:i:s'),
                'id_handover'  => $id_handover,
                'namafile'     => $filenama,
                'keterangan'   => $this->request->getPost('ket_dok'),
            ];

            $result = api('POST', 'hand_over/saveDokumen', $fp);
        } else {
            $result = new stdClass();
            $result->status = false;
            $result->msg = $file ? $file->getErrorString() : 'Upload Gagal';
        }

        return $this->response->setJSON($result);
    }

    public function data_print($id, $jns)
	{
		switch ($jns) {
			case 'bast':
				$s = $this->mhand_over->print_bast($id);
				break;
			case 'bast_dikuasakan':
				$s = $this->mhand_over->print_bast($id);
				break;
			case 'bast_insentive':
				$s = $this->mhand_over->print_bast_insentive($id);
				break;
			case 'bast_insentive_kuasa':
				$s = $this->mhand_over->print_bast_insentive($id);
				break;
			case 'bast_diwakilkan':
				$s = $this->mhand_over->print_bast_diwakilkan($id);
            
				break;
			case 'bast_diwakilkan_dikuasakan':
				$s = $this->mhand_over->print_bast_diwakilkan($id);
				break;
			case 'bast_insentive_diwakilkan':
				$s = $this->mhand_over->print_bast_insentive_diwakilkan($id);
				break;
			case 'bast_insentive_diwakilkan_kuasa':
				$s = $this->mhand_over->print_bast_insentive_diwakilkan($id);
				break;
			case 'pinjam_pakai':
				$s = $this->mhand_over->print_bast($id);
				break;
			case 'pinjam_pakai_kuasa':
				$s = $this->mhand_over->print_bast($id);
				break;
			case 'pinjam_pakai_diwakilkan':
				$s = $this->mhand_over->print_bast_diwakilkan($id);
				break;
			case 'pinjam_pakai_diwakilkan_kuasa':
				$s = $this->mhand_over->print_bast_diwakilkan($id);
				break;
			case 'surat_kuasa':
				// code to be executed if n=label2;
				break;
			case 'serah_terima_util':
				$s = $this->mhand_over->print_bast_util($id);
				break;
			case 'tanda_terima':
				$s = $this->mhand_over->print_bast($id);
				break;
			case 'tata_tertib':
				$s = $this->mhand_over->print_tatib($id);
				break;
		}
		// $this->response($s['resp'], $s['code']);
        return (object)$s['resp'];
	}

    public function print_dokumen($jns, $id_agreement){
      $nama_ttd_kuasa1 = 'Novi Cefiana';
       $nama_ttd_kuasa2 = 'Yurika Leonita';
        // print_r($jns);die;
        $nama_pt = con_nama_pt();
        $nama_apart = con_nama_apart();
        $nama_ttd_pt = con_nama_ttd_pt();
        $jabatan_ttd_pt = con_jabatan_ttd_pt();
        $con_rekening_ipl = con_rekening_ipl();
        // $result = api('POST', 'hand_over/printDokumen', array('id_agreement' => $id_agreement,'jns_dok' => $jns));
        $xh = $this->db->query("select * from th_handover_agreement a where a.id_agreement = $id_agreement")->getRow();
                $id_bast = $xh->id;
                $invcek = $this->db->query("select count(*) as ctr from td_invoice ti where ti.flag_id = true and ti.id_bast = $id_bast and ti.id_service in (29,28,7)");
                // print_r($cek_inv->getRow());die();
                if($invcek->getRow()->ctr == 0){
                    $no_va = '';
                    $q1 = "select mu.kode_unit, ctr.id as ct_handover from m_unit mu left join (select count(a.id) as id, a.id_unit from th_handover_agreement a group by a.id_unit) ctr on mu.id=ctr.id_unit where mu.id=".$xh->id_unit;
                    $dt_va = $this->db->query($q1)->getRow();
                    $no_va = gen_va_bykodeunit($dt_va->kode_unit, $dt_va->ct_handover, 'PEMILIK');
                }else{
                    $cek_inv = $this->db->query("select ti.virtual_account, sum(ti.total) as total from td_invoice ti where ti.flag_id = true and ti.id_bast = $id_bast and ti.id_service in (29,28,7) group by ti.virtual_account");
                     $no_va = $cek_inv->getRow()->virtual_account;
                }
       $result = $this->data_print($id_agreement,$jns);
        $dir = FCPATH.'dokumen/print/handover';
            if(!file_exists($dir)){
            mkdir($dir, 0777, true);
            }
        switch ($jns) {
            case 'pinjam_pakai':
            $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
            $jns_template = $pilih_template->data;
            
            $t = $result->data;
            // print_r($t);die();
            $tower = str_replace('Tower','',$t->nama_building);

            $handover_date = date('d-m-Y', strtotime($t->handover_date));
            $terbilang_handover_date = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));

            $tgl_bayar = date('d-m-Y', strtotime($t->tgl_bayar));
            $terbilang_tgl_bayar = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
            $file_template = FCPATH.'template_dokumen/perjanjian_pinjam_pakai_template.docx';
            $namafilenew = 'dok_pinjam_pakai-'.$id_agreement.date('YmdHis');
            $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
            $tempWord->setValue('terbilang_handover_date', $terbilang_handover_date);
            $tempWord->setValue('handover_date', $handover_date);
            $tempWord->setValue('hari_ini', day_teks(date('N')));
            $tempWord->setValue('tgl_txt', terbilang(date('d')));
            $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
            $tempWord->setValue('tahun_txt', terbilang(date('Y')));
            $tempWord->setValue('tgltoday', date('d-m-Y'));
            $tempWord->setValue('no_undangan', $t->no_pinjam_pakai);
            $tempWord->setValue('nama_tenant', $t->nama_tenant);
            $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
            $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
            $tempWord->setValue('nik', $t->nik);
            $tempWord->setValue('npwp', $t->npwp);
            $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
            $tempWord->setValue('tower', $tower);
            $tempWord->setValue('lantai', $t->lantai);
            $tempWord->setValue('no_unit', $t->no_unit);
            $tempWord->setValue('luas', $t->luas_unit);
            $tempWord->setValue('tgl_ppjb', date("d-M-y", strtotime($t->tgl_ppjb)));
            $tempWord->setValue('no_ppjb', $t->no_ppjb);
            $tempWord->setValue('tipe', $t->tipe_unit);
            $tempWord->setValue('nama_pt', $nama_pt);
            $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
            $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
            $tempWord->setValue('nama_apart', $nama_apart);
            $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
            $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
            $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
            $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
            $handover_date = date('d-m-Y',strtotime($t->handover_date));
            $durasi_pinjam_pakai = durasi_pinjam_pakai();
            $end_date = date('Y-m-d',strtotime($handover_date.' + '.$durasi_pinjam_pakai.' months'));
            // echo $end_date;die();
            $tempWord->setValue('start_date', $handover_date);
            $tempWord->setValue('end_date', date('d-m-Y',strtotime($t->tgl_bayar)));
            $terbilang_start_date = terbilang(date('d',strtotime($t->handover_date))).' '.bulan_teks(date('m',strtotime($t->handover_date))).' '.terbilang(date('Y',strtotime($t->handover_date)));
            if(isset($t->tgl_bayar)) {
                $terbilang_end_date = terbilang(date('d',strtotime($t->tgl_bayar))).' '.bulan_teks(date('m',strtotime($t->tgl_bayar))).' '.terbilang(date('Y',strtotime($t->tgl_bayar)));
            } else {
                $terbilang_end_date = "-";
            }
            $tempWord->setValue('terbilang_start_date', ucwords($terbilang_start_date));
            $tempWord->setValue('terbilang_end_date', ucwords($terbilang_end_date));
            $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
            
            $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
            $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
            // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
            // write_file($dir.'/convert_pdf.bat', $command);
            // shell_exec($command);
            // shell_exec($command." > debug.log 2>&1");
            // shell_exec($command2);
            // shell_exec($command2." > debug2.log 2>&1");
            
            // unlink($dir.'/'.$namafilenew.'.docx');
            return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            
            break;
            case 'pinjam_pakai_kuasa':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                
                $t = $result->data;
                // print_r($t);die();
                $tower = str_replace('Tower','',$t->nama_building);
    
                $handover_date = date('d-m-Y', strtotime($t->handover_date));
                $terbilang_handover_date = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
    
                $tgl_bayar = date('d-m-Y', strtotime($t->tgl_bayar));
                $terbilang_tgl_bayar = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                $file_template = FCPATH.'template_dokumen/perjanjian_pinjam_pakai_template_kuasa.docx';
                $namafilenew = 'dok_pinjam_pakai_kuasa_direksi-'.$id_agreement.date('YmdHis');
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('nama_ttd1', 'Novi Cefiana');
                $tempWord->setValue('nama_ttd2', 'Yurika Leonita');
                $tempWord->setValue('terbilang_handover_date', $terbilang_handover_date);
                $tempWord->setValue('handover_date', $handover_date);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', terbilang(date('d')));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', terbilang(date('Y')));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_undangan', $t->no_pinjam_pakai);
                $tempWord->setValue('nama_tenant', $t->nama_tenant);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $t->no_unit);
                $tempWord->setValue('luas', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', date("d-M-y", strtotime($t->tgl_ppjb)));
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe', $t->tipe_unit);
                $handover_date = date('d-m-Y',strtotime($t->handover_date));
                $durasi_pinjam_pakai = durasi_pinjam_pakai();
                $end_date = date('Y-m-d',strtotime($handover_date.' + '.$durasi_pinjam_pakai.' months'));
                // echo $end_date;die();
                $tempWord->setValue('start_date', $handover_date);
                $tempWord->setValue('end_date', date('d-m-Y',strtotime($t->tgl_bayar)));
                $terbilang_start_date = terbilang(date('d',strtotime($t->handover_date))).' '.bulan_teks(date('m',strtotime($t->handover_date))).' '.terbilang(date('Y',strtotime($t->handover_date)));
                if(isset($t->tgl_bayar)) {
                    $terbilang_end_date = terbilang(date('d',strtotime($t->tgl_bayar))).' '.bulan_teks(date('m',strtotime($t->tgl_bayar))).' '.terbilang(date('Y',strtotime($t->tgl_bayar)));
                } else {
                    $terbilang_end_date = "-";
                }
                $tempWord->setValue('terbilang_start_date', ucwords($terbilang_start_date));
                $tempWord->setValue('terbilang_end_date', ucwords($terbilang_end_date));
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");
                
                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
                
                break;
            case 'pinjam_pakai_diwakilkan':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                
				
                $t = $result->data;
                // print_r($t);die;
                $tower = str_replace('Tower','',$t->nama_building);
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                $nama_tenant = ucwords(strtolower($t->nama_tenant));
                $no_unit = str_pad($t->no_unit, 2, '0', STR_PAD_LEFT);
                $tgl_bayar = date('d-m-Y', strtotime($t->tgl_bayar));
                $terbilang_tgl_bayar = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));

                $file_template = FCPATH.'template_dokumen/perjanjian_pinjam_pakai_diwakilkan_template.docx';
                $namafilenew = 'dok_pinjam_pakai_diwakilkan-'.$id_agreement.date('YmdHis');
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('terbilang_tgl_bayar', $terbilang_tgl_bayar);
                $tempWord->setValue('tgl_bayar', $tgl_bayar);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_undangan', $t->no_agreement);
                $tempWord->setValue('no_pinjam_pakai', $t->no_pinjam_pakai);
                $tempWord->setValue('nama_tenant', $nama_tenant);
                $tempWord->setValue('nama_wakil', $t->nama_wakil);
                $tempWord->setValue('tempat_lahir_wakil', $t->tempat_lahir_wakil);
                $tempWord->setValue('tgl_lahir_wakil', $t->tgl_lahir_wakil);
                $tempWord->setValue('nik_wakil', $t->nik_wakil);
                $tempWord->setValue('alamat_wakil', $t->alamat_wakil);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $no_unit);
                $tempWord->setValue('luas', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', date("d-M-y", strtotime($t->tgl_ppjb)));
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe', $t->tipe_unit);
                $handover_date = date('d-m-Y',strtotime($t->handover_date));
                $durasi_pinjam_pakai = durasi_pinjam_pakai();
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                $end_date = date('Y-m-d',strtotime($handover_date.' + '.$durasi_pinjam_pakai.' months'));
                // echo $end_date;die();
                $tempWord->setValue('start_date', $handover_date);
                $tempWord->setValue('end_date', date('d-m-Y',strtotime($t->tgl_bayar)));
                $terbilang_start_date = terbilang(date('d',strtotime($t->handover_date))).' '.bulan_teks(date('m',strtotime($t->handover_date))).' '.terbilang(date('Y',strtotime($t->handover_date)));
                if(isset($t->tgl_bayar)) {
                    $terbilang_end_date = terbilang(date('d',strtotime($t->tgl_bayar))).' '.bulan_teks(date('m',strtotime($t->tgl_bayar))).' '.terbilang(date('Y',strtotime($t->tgl_bayar)));
                } else {
                    $terbilang_end_date = "-";
                }
                $tempWord->setValue('terbilang_start_date', ucwords($terbilang_start_date));
                $tempWord->setValue('terbilang_end_date', ucwords($terbilang_end_date));
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');

                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            break;
            case 'pinjam_pakai_diwakilkan_kuasa':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                
				
                $t = $result->data;
                $tower = str_replace('Tower','',$t->nama_building);
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                $nama_tenant = ucwords(strtolower($t->nama_tenant));
                $no_unit = str_pad($t->no_unit, 2, '0', STR_PAD_LEFT);
                $tgl_bayar = date('d-m-Y', strtotime($t->tgl_bayar));
                $terbilang_tgl_bayar = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));

                $file_template = FCPATH.'template_dokumen/perjanjian_pinjam_pakai_diwakilkan_template_kuasa.docx';
                $namafilenew = 'dok_pinjam_pakai_diwakilkan_kuasa_direksi-'.$id_agreement.date('YmdHis');
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                 $tempWord->setValue('nama_ttd1', 'Novi Cefiana');
                $tempWord->setValue('nama_ttd2', 'Yurika Leonita');
                $tempWord->setValue('terbilang_tgl_bayar', $terbilang_tgl_bayar);
                $tempWord->setValue('tgl_bayar', $tgl_bayar);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_undangan', $t->no_agreement);
                $tempWord->setValue('no_pinjam_pakai', $t->no_pinjam_pakai);
                $tempWord->setValue('nama_tenant', $nama_tenant);
                $tempWord->setValue('nama_wakil', $t->nama_wakil);
                $tempWord->setValue('tempat_lahir_wakil', $t->tempat_lahir_wakil);
                $tempWord->setValue('tgl_lahir_wakil', $t->tgl_lahir_wakil);
                $tempWord->setValue('nik_wakil', $t->nik_wakil);
                $tempWord->setValue('alamat_wakil', $t->alamat_wakil);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $no_unit);
                $tempWord->setValue('luas', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', date("d-M-y", strtotime($t->tgl_ppjb)));
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe', $t->tipe_unit);
                $handover_date = date('d-m-Y',strtotime($t->handover_date));
                $durasi_pinjam_pakai = durasi_pinjam_pakai();
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                $end_date = date('Y-m-d',strtotime($handover_date.' + '.$durasi_pinjam_pakai.' months'));
                // echo $end_date;die();
                $tempWord->setValue('start_date', $handover_date);
                $tempWord->setValue('end_date', date('d-m-Y',strtotime($t->tgl_bayar)));
                $terbilang_start_date = terbilang(date('d',strtotime($t->handover_date))).' '.bulan_teks(date('m',strtotime($t->handover_date))).' '.terbilang(date('Y',strtotime($t->handover_date)));
                if(isset($t->tgl_bayar)) {
                    $terbilang_end_date = terbilang(date('d',strtotime($t->tgl_bayar))).' '.bulan_teks(date('m',strtotime($t->tgl_bayar))).' '.terbilang(date('Y',strtotime($t->tgl_bayar)));
                } else {
                    $terbilang_end_date = "-";
                }
                $tempWord->setValue('terbilang_start_date', ucwords($terbilang_start_date));
                $tempWord->setValue('terbilang_end_date', ucwords($terbilang_end_date));
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');

                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            break;
            case 'bast':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                
                // print_r($no_va);die;    
                $t = $result->data;

                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_template.docx';
                $namafilenew = 'dok_bast-'.$id_agreement.date('YmdHis');
                $tower = str_replace('Tower','',$t->nama_building);
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);
    
                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $grand_total = $ipl+$sf+$pajak+$harga_materai;
                // $tempat_tgl_lahir = empty($t->tempat_lahir) ? '' : $t->tempat_lahir .'/'. $t->tgl_lahir;
                $tgl_lahir = !empty($t->tgl_lahir) ? date('d-m-Y', strtotime($t->tgl_lahir)) : NULL;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                $nama_tenant = ucwords(strtolower($t->nama_tenant));
                $no_unit = str_pad($t->no_unit, 2, '0', STR_PAD_LEFT);

                if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                } else {
                    $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                }
                
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('nama_tenant', $nama_tenant);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $tgl_lahir);
                if(!empty($t->tempat_lahir) && !empty($tgl_lahir)){
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }else{
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }
                // $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                // $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $no_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                if(empty($no_va)){
                    $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_pt));
                }else{
                $tempWord->setValue('header_pengantar', 'Virtual Account');
                $tempWord->setValue('norekening', $no_va);
                $tempWord->setValue('label_nova', 'Virtual Account');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_apart));
                }
                
                
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');

            break;
            case 'bast_dikuasakan':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                // print_r($result);die();
                $t = $result->data;
                // print_r($t);die();
                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_template_dikuasakan.docx';
                $namafilenew = 'dok_bast_kuasa_direksi-'.$id_agreement.date('YmdHis');
                $tower = str_replace('Tower','',$t->nama_building);
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);
    
                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $grand_total = $ipl+$sf+$pajak+$harga_materai;
                // $tempat_tgl_lahir = empty($t->tempat_lahir) ? '' : $t->tempat_lahir .'/'. $t->tgl_lahir;
                $tgl_lahir = !empty($t->tgl_lahir) ? date('d-m-Y', strtotime($t->tgl_lahir)) : NULL;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                $nama_tenant = ucwords(strtolower($t->nama_tenant));
                $no_unit = str_pad($t->no_unit, 2, '0', STR_PAD_LEFT);

                if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                } else {
                    $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                }
                
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('nama_ttd1', $nama_ttd_kuasa1);
                $tempWord->setValue('nama_ttd2', $nama_ttd_kuasa2);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('nama_tenant', $nama_tenant);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $tgl_lahir);
                if(!empty($t->tempat_lahir) && !empty($tgl_lahir)){
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }else{
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }
                // $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                // $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $no_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));

                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                if(empty($no_va)){
                $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper(str_replace('.','',$nama_pt)));
                }else{
                $tempWord->setValue('header_pengantar', 'Virtual Account');
                $tempWord->setValue('norekening', $no_va);
                $tempWord->setValue('label_nova', 'Virtual Account');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_apart));
                }
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');

            break;
            case 'bast_insentive':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                // print_r($jns_template);die();
                $t = $result->data;
                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_insentive_ppn_new.docx';
                $namafilenew = 'dok_bast_insentive-'.$id_agreement.date('YmdHis');
                $tower = str_replace('Tower','',$t->nama_building);
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);
                $tgl_lunas = date("d-M-y", strtotime($t->tgl_ppjb_insentive));
                
                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $grand_total = $ipl+$sf+$pajak+$harga_materai;
                // $tempat_tgl_lahir = empty($t->tempat_lahir) ? '' : $t->tempat_lahir .'/'. $t->tgl_lahir;
                $tgl_lahir = !empty($t->tgl_lahir) ? date('d-m-Y', strtotime($t->tgl_lahir)) : NULL;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                $nama_tenant = ucwords(strtolower($t->nama_tenant));
                $no_unit = str_pad($t->no_unit, 2, '0', STR_PAD_LEFT);

                // if($t->status_bayar == 'LUNAS'){
                //     $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                //     $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                // } else {
                //     $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                //     $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                // }
                $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('nama_tenant', $nama_tenant);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $tgl_lahir);
                if(!empty($t->tempat_lahir) && !empty($tgl_lahir)){
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }else{
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }
                // $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                // $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('no_ppjb_insentive', $t->no_ppjb_insentive);
                $tempWord->setValue('tgl_ppjb_insentive', $tgl_lunas);
                $tempWord->setValue('kode_kir', $t->kode_kir);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $no_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));

                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);

                if(empty($no_va)){
                    $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper(str_replace('.','',$nama_pt)));
                }else{
                $tempWord->setValue('header_pengantar', 'Virtual Account');
                $tempWord->setValue('norekening', $no_va);
                $tempWord->setValue('label_nova', 'Virtual Account');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_apart));
                }
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');

            break;
            case 'bast_insentive_kuasa':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                // print_r($jns_template);die();
                $t = $result->data;
                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_insentive_ppn_new_dikuasakan.docx';
                $namafilenew = 'dok_bast_insentive_kuasa_direksi-'.$id_agreement.date('YmdHis');
                $tower = str_replace('Tower','',$t->nama_building);
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);
                $tgl_lunas = date("d-M-y", strtotime($t->tgl_ppjb_insentive));
                
                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $grand_total = $ipl+$sf+$pajak+$harga_materai;
                // $tempat_tgl_lahir = empty($t->tempat_lahir) ? '' : $t->tempat_lahir .'/'. $t->tgl_lahir;
                $tgl_lahir = !empty($t->tgl_lahir) ? date('d-m-Y', strtotime($t->tgl_lahir)) : NULL;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                $nama_tenant = ucwords(strtolower($t->nama_tenant));
                $no_unit = str_pad($t->no_unit, 2, '0', STR_PAD_LEFT);

                if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                } else {
                    $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                }
                
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('nama_ttd1', $nama_ttd_kuasa1);
                $tempWord->setValue('nama_ttd2', $nama_ttd_kuasa2);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('nama_tenant', $nama_tenant);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $tgl_lahir);
                if(!empty($t->tempat_lahir) && !empty($tgl_lahir)){
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }else{
                    $tempWord->setValue('ttl', $t->tempat_lahir.' '.$tgl_lahir);
                }
                // $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                // $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('no_ppjb_insentive', $t->no_ppjb_insentive);
                $tempWord->setValue('tgl_ppjb_insentive', $tgl_lunas);
                $tempWord->setValue('kode_kir', $t->kode_kir);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $no_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));
                //config
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);

                if(empty($no_va)){
                $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper(str_replace('.','',$nama_pt)));
                }else{
                $tempWord->setValue('header_pengantar', 'Virtual Account');
                $tempWord->setValue('norekening', $no_va);
                $tempWord->setValue('label_nova', 'Virtual Account');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_apart));
                }
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');

            break;
            case 'bast_diwakilkan':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
               
                $t = $result->data;

                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_diwakilkan_template.docx';
                $namafilenew = 'dok_bast_diwakilkan-'.$id_agreement.date('YmdHis');
                // print_r($t->luas_unit);die();
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);

                $handover_date = date('d-m-Y', strtotime($t->handover_date));
                $day = terbilang(date('d', strtotime($t->handover_date)));
                $month = bulan_teks(date('m', strtotime($t->handover_date)));
                $year = terbilang(date('Y', strtotime($t->handover_date)));
                $handover_date_terbilang = ucwords($day) . " " . ucwords($month) . " " . ucwords($year);
                $tgl_lahir_wakil = date('d-m-Y', strtotime($t->tgl_lahir_wakil));

                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $tower = str_replace('Tower','',$t->nama_building);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                } else {
                    $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                }

                $grand_total = $ipl+$sf+$pajak+$harga_materai;

                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('terbilang_handover_date', $handover_date_terbilang);
                $tempWord->setValue('handover_date', $handover_date);
                $tempWord->setValue('nama_tenant', $t->nama_tenant);
                $tempWord->setValue('nama_wakil', $t->nama_wakil);
                $tempWord->setValue('tempat_lahir_wakil', $t->tempat_lahir_wakil);
                $tempWord->setValue('tgl_lahir_wakil', $tgl_lahir_wakil);
                $tempWord->setValue('nik_wakil', $t->nik_wakil);
                $tempWord->setValue('alamat_wakil', $t->alamat_wakil);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $t->no_unit);
                $tempWord->setValue('luas_unit', $t->luas_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                //config
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
                if(!empty($t->pekerjaan_wakil)){
                    $tempWord->setValue('pekerjaan_wakil', $t->pekerjaan_wakil);
                }else{
                    $tempWord->setValue('pekerjaan_wakil', '-');
                }
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));
                if(empty($no_va)){
                    $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper(str_replace('.','',$nama_pt)));
                }else{
                $tempWord->setValue('header_pengantar', 'Virtual Account');
                $tempWord->setValue('norekening', $no_va);
                $tempWord->setValue('label_nova', 'Virtual Account');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_apart));
                }
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');

                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            break;
            case 'bast_diwakilkan_dikuasakan':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
               
                $t = $result->data;

                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_diwakilkan_template_dikuasakan.docx';
                $namafilenew = 'dok_bast_diwakilkan_kuasa_direksi-'.$id_agreement.date('YmdHis');
                // print_r($t->luas_unit);die();
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);

                $handover_date = date('d-m-Y', strtotime($t->handover_date));
                $day = terbilang(date('d', strtotime($t->handover_date)));
                $month = bulan_teks(date('m', strtotime($t->handover_date)));
                $year = terbilang(date('Y', strtotime($t->handover_date)));
                $handover_date_terbilang = ucwords($day) . " " . ucwords($month) . " " . ucwords($year);
                $tgl_lahir_wakil = date('d-m-Y', strtotime($t->tgl_lahir_wakil));

                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $tower = str_replace('Tower','',$t->nama_building);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                } else {
                    $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                }

                $grand_total = $ipl+$sf+$pajak+$harga_materai;

                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                 $tempWord->setValue('nama_ttd1', $nama_ttd_kuasa1);
                $tempWord->setValue('nama_ttd2', $nama_ttd_kuasa2);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('terbilang_handover_date', $handover_date_terbilang);
                $tempWord->setValue('handover_date', $handover_date);
                $tempWord->setValue('nama_tenant', $t->nama_tenant);
                $tempWord->setValue('nama_wakil', $t->nama_wakil);
                $tempWord->setValue('tempat_lahir_wakil', $t->tempat_lahir_wakil);
                $tempWord->setValue('tgl_lahir_wakil', $tgl_lahir_wakil);
                $tempWord->setValue('nik_wakil', $t->nik_wakil);
                $tempWord->setValue('alamat_wakil', $t->alamat_wakil);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $t->no_unit);
                $tempWord->setValue('luas_unit', $t->luas_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));
                //config
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));

                if(empty($no_va)){
                    $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper(str_replace('.','',$nama_pt)));
                }else{
                $tempWord->setValue('header_pengantar', 'Virtual Account');
                $tempWord->setValue('norekening', $no_va);
                $tempWord->setValue('label_nova', 'Virtual Account');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_apart));
                }
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');

                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            break;
            case 'bast_insentive_diwakilkan':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
               
                $t = $result->data;

                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_insentive_ppn_diwakilkan.docx';
                $namafilenew = 'dok_bast_diwakilkan_insentive-'.$id_agreement.date('YmdHis');
                // print_r($t->luas_unit);die();
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);

                $handover_date = date('d-m-Y', strtotime($t->handover_date));
                $day = terbilang(date('d', strtotime($t->handover_date)));
                $month = bulan_teks(date('m', strtotime($t->handover_date)));
                $year = terbilang(date('Y', strtotime($t->handover_date)));
                $handover_date_terbilang = ucwords($day) . " " . ucwords($month) . " " . ucwords($year);
                $tgl_lahir_wakil = date('d-m-Y', strtotime($t->tgl_lahir_wakil));
                $tgl_lunas = date("d-M-y", strtotime($t->tgl_ppjb_insentive));

                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $tower = str_replace('Tower','',$t->nama_building);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                // if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                // } 
                // else {
                //     $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                //     $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                // }

                $grand_total = $ipl + $sf + $pajak + $harga_materai;
                if ($t->npwp_wakil || $t->pekerjaan_wakil) {
                    $npwp_wakil = $t->npwp_wakil;
                    $pekerjaan_wakil = $t->pekerjaan_wakil;
                } else {
                    $npwp_wakil = "*Isi npwp kuasa*";
                    $pekerjaan_wakil = "*isi pekerjaan kuasa*";
                }

                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('no_ppjb_insentive', $t->no_ppjb_insentive);
                $tempWord->setValue('tgl_ppjb_insentive', $tgl_lunas);
                $tempWord->setValue('kode_kir', $t->kode_kir);
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('terbilang_handover_date', $handover_date_terbilang);
                $tempWord->setValue('handover_date', $handover_date);
                $tempWord->setValue('nama_tenant', $t->nama_tenant);
                $tempWord->setValue('nama_wakil', $t->nama_wakil);
                $tempWord->setValue('tempat_lahir_wakil', $t->tempat_lahir_wakil);
                $tempWord->setValue('tgl_lahir_wakil', $tgl_lahir_wakil);
                $tempWord->setValue('nik_wakil', $t->nik_wakil);
                $tempWord->setValue('alamat_wakil', $t->alamat_wakil);
                $tempWord->setValue('npwp_wakil', $npwp_wakil);
                $tempWord->setValue('pekerjaan_wakil', $pekerjaan_wakil);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $t->no_unit);
                $tempWord->setValue('luas_unit', $t->luas_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));
                 //config
                 $tempWord->setValue('nama_pt', $nama_pt);
                 $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                 $tempWord->setValue('nama_apart', $nama_apart);
                 $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                 $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                 $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                 $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                 $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));

                 $tempWord->setValue('header_pengantar', 'transfer Ke');
                 $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                 $tempWord->setValue('label_nova', 'No. Rekening');
                 $tempWord->setValue('atas_nama_val', strtoupper($nama_pt));
 
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');

                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            break;
            case 'bast_insentive_diwakilkan_kuasa':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
               
                $t = $result->data;

                $s = $result->tarif_service;
                $file_template = FCPATH.'template_dokumen/dok_bast_insentive_ppn_diwakilkan_dikuasakan.docx';
                $namafilenew = 'dok_bast_diwakilkan_insentive_kuasa_direksi-'.$id_agreement.date('YmdHis');
                // print_r($t->luas_unit);die();
                $nominal_sc = floatval($s->nominal_sc);
                $nominal_sf = floatval($s->nominal_sf);

                $handover_date = date('d-m-Y', strtotime($t->handover_date));
                $day = terbilang(date('d', strtotime($t->handover_date)));
                $month = bulan_teks(date('m', strtotime($t->handover_date)));
                $year = terbilang(date('Y', strtotime($t->handover_date)));
                $handover_date_terbilang = ucwords($day) . " " . ucwords($month) . " " . ucwords($year);
                $tgl_lahir_wakil = date('d-m-Y', strtotime($t->tgl_lahir_wakil));
                $tgl_lunas = date("d-M-y", strtotime($t->tgl_ppjb_insentive));

                $luas_unit = str_replace(',', '.', $t->luas_unit);
                $tower = str_replace('Tower','',$t->nama_building);
                $ipl = floatval($luas_unit)*$nominal_sc*3;
                $sf = floatval($luas_unit)*$nominal_sf*3;
                $total = $ipl+$sf;
                $pajak = ($ipl+$sf)*persen_ppn();
                $harga_materai = 20000;
                $tgl_ppjb = date("d-M-y", strtotime($t->tgl_ppjb));
                // if($t->status_bayar == 'LUNAS'){
                    $tgl_surat= date('d-m-Y', strtotime($t->handover_date));
                    $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->handover_date)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->handover_date)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->handover_date))));
                // } 
                // else {
                //     $tgl_surat= date('d-m-Y', strtotime($t->tgl_bayar));
                //     $terbilang_tgl_surat = ucwords(terbilang(date('d', strtotime($t->tgl_bayar)))) . ' Bulan ' . ucwords(bulan_teks(date('m', strtotime($t->tgl_bayar)))) . ' Tahun ' . ucwords(terbilang(date('Y', strtotime($t->tgl_bayar))));
                // }

                $grand_total = $ipl + $sf + $pajak + $harga_materai;
                if ($t->npwp_wakil || $t->pekerjaan_wakil) {
                    $npwp_wakil = $t->npwp_wakil;
                    $pekerjaan_wakil = $t->pekerjaan_wakil;
                } else {
                    $npwp_wakil = "*Isi npwp kuasa*";
                    $pekerjaan_wakil = "*isi pekerjaan kuasa*";
                }

                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                 $tempWord->setValue('nama_ttd1', $nama_ttd_kuasa1);
                $tempWord->setValue('nama_ttd2', $nama_ttd_kuasa2);
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('tgl_txt', ucwords(terbilang(date('d'))));
                $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                $tempWord->setValue('tahun_txt', ucwords(terbilang(date('Y'))));
                $tempWord->setValue('terbilang_tgl_surat', $terbilang_tgl_surat);
                $tempWord->setValue('tgl_surat', $tgl_surat);
                $tempWord->setValue('no_ppjb_insentive', $t->no_ppjb_insentive);
                $tempWord->setValue('tgl_ppjb_insentive', $tgl_lunas);
                $tempWord->setValue('kode_kir', $t->kode_kir);
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('no_agreement', $t->no_agreement);
                $tempWord->setValue('terbilang_handover_date', $handover_date_terbilang);
                $tempWord->setValue('handover_date', $handover_date);
                $tempWord->setValue('nama_tenant', $t->nama_tenant);
                $tempWord->setValue('nama_wakil', $t->nama_wakil);
                $tempWord->setValue('tempat_lahir_wakil', $t->tempat_lahir_wakil);
                $tempWord->setValue('tgl_lahir_wakil', $tgl_lahir_wakil);
                $tempWord->setValue('nik_wakil', $t->nik_wakil);
                $tempWord->setValue('alamat_wakil', $t->alamat_wakil);
                $tempWord->setValue('npwp_wakil', $npwp_wakil);
                $tempWord->setValue('pekerjaan_wakil', $pekerjaan_wakil);
                $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                $tempWord->setValue('nik', $t->nik);
                $tempWord->setValue('npwp', $t->npwp);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->setValue('tower', $tower);
                $tempWord->setValue('lantai', $t->lantai);
                $tempWord->setValue('no_unit', $t->no_unit);
                $tempWord->setValue('luas_unit', $t->luas_unit);
                $tempWord->setValue('lu', $t->luas_unit);
                $tempWord->setValue('tgl_ppjb', $tgl_ppjb);
                $tempWord->setValue('no_ppjb', $t->no_ppjb);
                $tempWord->setValue('tipe_unit', $t->tipe_unit);
                $tempWord->setValue('nominal_ipl', number_format($ipl,0,",","."));
                $tempWord->setValue('sinking_fund', number_format($sf,0,",","."));
                $tempWord->setValue('nom_total', number_format($total,0,",","."));
                $tempWord->setValue('nom_ppn', number_format($pajak,0,",","."));
                $tempWord->setValue('hrg_mtr', number_format($harga_materai,0,",","."));
                $tempWord->setValue('grand_total', number_format($grand_total,0,",","."));

                //config
                $tempWord->setValue('nama_pt', $nama_pt);
                $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
                $tempWord->setValue('nama_apart', $nama_apart);
                $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
                $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
                $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
                $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
                $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));

                $tempWord->setValue('header_pengantar', 'transfer Ke');
                $tempWord->setValue('norekening', $con_rekening_ipl->norekening);
                $tempWord->setValue('label_nova', 'No. Rekening');
                $tempWord->setValue('atas_nama_val', strtoupper($nama_pt));

                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');

                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // write_file($dir.'/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");

                // unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.docx');
            break;
            case 'surat_kuasa':
                $file_path = FCPATH . 'template_dokumen/Dok_Surat_kuasa.pdf';
                if (is_file($file_path)) {
                    return $this->response->download($file_path, null);
                }

                return $this->response
                    ->setStatusCode(404)
                    ->setBody('The File does not exist.');

            case 'izin_huni':
            // code to be executed if n=label3;
            break;
            case 'serah_terima_util':
            $dir = FCPATH.'dokumen/print/handover';
            $file_template = FCPATH.'template_dokumen/bast_utilitas_template.docx';
            $namafilenew = 'bast_utilitas-'.$id_agreement.date('YmdHis');
            $t = $result->data;
            // dd($result->mwater->meter_start);
            $dunit = str_replace("Tower ","",$t->nama_building).'/'.$t->lantai.'/'.$t->no_unit;
            $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
            $tempWord->setValue('tgltoday', tglteks(date('Y-m-d')));
            $tempWord->setValue('hari_ini', day_teks(date('N')));
            $tempWord->setValue('nama_tenant', $t->nama_tenant);
            $tempWord->setValue('no_meter_air', $result->mwater->kode_meter);
            $tempWord->setValue('meter_start_air', $result->mwater->meter_start);
            $tempWord->setValue('no_meter_listrik', $result->mlistrik);
            $tempWord->setValue('dunit', $dunit);
            $tempWord->setValue('telepon', $t->wa_tenant);
            $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
            $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
            
             $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
            $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
            // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
            write_file($dir.'/convert_pdf.bat', $command);
            shell_exec($command);
            shell_exec($command." > debug.log 2>&1");
            shell_exec($command2);
            shell_exec($command2." > debug2.log 2>&1");    

            unlink($dir.'/'.$namafilenew.'.docx');
            
            return $this->download_dokumen($dir.'/'.$namafilenew.'.pdf');
            break;
            case 'serah_terima_util_diwakilkan':
                $dir = FCPATH.'dokumen/print/handover';
                $file_template = FCPATH.'template_dokumen/bast_utilitas_template.docx';
                $namafilenew = 'bast_utilitas-'.$id_agreement.date('YmdHis');
                $t = $result->data;
                // dd($result->mwater->meter_start);
                $dunit = str_replace("Tower ","",$t->nama_building).'/'.$t->lantai.'/'.$t->no_unit;
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('tgltoday', tglteks(date('Y-m-d')));
                $tempWord->setValue('hari_ini', day_teks(date('N')));
                $tempWord->setValue('nama_tenant', $t->nama_tenant);
                $tempWord->setValue('no_meter_air', $result->mwater->kode_meter);
                $tempWord->setValue('meter_start_air', $result->mwater->meter_start);
                $tempWord->setValue('no_meter_listrik', $result->mlistrik);
                $tempWord->setValue('dunit', $dunit);
                $tempWord->setValue('telepon', $t->wa_tenant);
                $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                write_file($dir.'/convert_pdf.bat', $command);
                shell_exec($command);
                shell_exec($command." > debug.log 2>&1");
                shell_exec($command2);
                shell_exec($command2." > debug2.log 2>&1");    
    
                unlink($dir.'/'.$namafilenew.'.docx');
                
                return $this->download_dokumen($dir.'/'.$namafilenew.'.pdf');
                break;
            case 'tanda_terima':
                $pilih_template = api('POST', 'hand_over/pilih_bast', array('id_agreement' => $id_agreement));
                $jns_template = $pilih_template->data;
                // print_r($jns_template);die();
                $t = $result->data;
                $file_template = FCPATH.'template_dokumen/tanda_terima_template.docx';
                // dd($file_template);
                $namafilenew = 'dok_tanda_terima-'.$id_agreement.date('YmdHis');
                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                // $tempWord->setValue('hari_ini', day_teks(date('N')));
                // $tempWord->setValue('tgl_txt', terbilang(date('d')));
                // $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                // $tempWord->setValue('tahun_txt', terbilang(date('Y')));
                // $tempWord->setValue('tgltoday', date('d-m-Y'));
                // $tempWord->setValue('kode_unit', $t->kode_unit);
                // $tempWord->setValue('no_undangan', $t->no_agreement);
                // $tempWord->setValue('nama_tenant', $t->nama_tenant);
                // $tempWord->setValue('tempat_lahir', $t->tempat_lahir);
                // $tempWord->setValue('tgl_lahir', $t->tgl_lahir);
                // $tempWord->setValue('nik', $t->nik);
                // $tempWord->setValue('npwp', $t->npwp);
                // $tempWord->setValue('alamat_tenant', $t->alamat_tenant);
                // $tempWord->setValue('no_hp', $t->no_hp);
                $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
                
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                write_file($dir.'/convert_pdf.bat', $command);
                shell_exec($command);
                shell_exec($command." > debug.log 2>&1");
                shell_exec($command2);
                shell_exec($command2." > debug2.log 2>&1");
                
                unlink($dir.'/'.$namafilenew.'.docx');
                return $this->download_dokumen($dir.'/'.$namafilenew.'.pdf');
            break;
            case 'tata_tertib':
            // print_r($result);
            $dir = FCPATH.'dokumen/print/handover';
            if(!file_exists($dir)){
            mkdir($dir, 0777, true);
            }
            $t = $result->data;
            // print_r($t);die();
            $file_template = FCPATH.'template_dokumen/surat_penerimaan_tatib_template.docx';
            $namafilenew = 'dok_penerimaan_tatib-'.$id_agreement.date('YmdHis');
            
            $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
            $tempWord->setValue('nama_tenant', $t->nama_tenant);
            $tempWord->setValue('nik_tenant', $t->nik);
            $tempWord->setValue('tower', str_replace("Tower ","",$t->nama_building));
            $tempWord->setValue('lantai', $t->lantai);
            $tempWord->setValue('no_unit', $t->no_unit);
            $tempWord->setValue('tgl_bast', date('d-m-Y',strtotime($t->handover_date)));
            $tempWord->setValue('tgl_ttd', tglteks(date('Y-m-d')));

            $tempWord->setValue('nama_pt', $nama_pt);
            $tempWord->setValue('nama_pt_upper', strtoupper($nama_pt));
            $tempWord->setValue('nama_apart', $nama_apart);
            $tempWord->setValue('nama_apart_upper', strtoupper($nama_apart));
            $tempWord->setValue('nama_ttd_pt', $nama_ttd_pt);
            $tempWord->setValue('jabatan_ttd_pt', $jabatan_ttd_pt);
            $tempWord->setValue('inisial_bank_transfer', $con_rekening_ipl->inisial_bank);
            $tempWord->setValue('nama_pt_ttd', strtoupper(str_replace('.','',$nama_pt)));
            $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
            
            $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
            $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
            // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
            write_file($dir.'/convert_pdf.bat', $command);
            shell_exec($command);
            shell_exec($command." > debug.log 2>&1");
            shell_exec($command2);
            shell_exec($command2." > debug2.log 2>&1");
            // shell_exec($command." > debug.log 2>&1");
            // shell_exec($command2." > debug2.log 2>&1");
            unlink($dir.'/'.$namafilenew.'.docx');
            return $this->download_dokumen($dir.'/'.$namafilenew.'.pdf');  
            
            break;
            
        }
        
    }
    public function download_dokumen($file_path)
    {
        if (!empty($file_path) && is_file($file_path)) {
            $filename = basename($file_path);
            $data = file_get_contents($file_path);
            @unlink($file_path);

            return $this->response->download($filename, $data);
        }

        return $this->response
            ->setStatusCode(404)
            ->setBody('The File does not exist.');
    }

    public function download_file($id)
    {
        $res = api('POST', 'hand_over/download_file_upload', ['id' => $id]);
        $file_path = FCPATH . 'dokumen/upload/handover/' . $res->data->id_header . '/' . $res->data->namafile;

        if (!empty($file_path) && is_file($file_path)) {
            return $this->response->download($file_path, null);
        }

        return $this->response
            ->setStatusCode(404)
            ->setBody('The File does not exist.');
    }

    public function saveNewStep2(){
        $fp = $this->request->getPost();
        $fp['created_user'] = $this->session->get('id_user');
        $s = api_json('POST','hand_over/saveNewStep2',$fp);
        return $this->response->setJSON($s);
    }
    public function form_3($id_undangan, $id_handover){
       if($this->akses->can_approve == 1){
        $cek = $this->db->query("select count(*) as ctr from t_agreement ta left join th_handover_agreement tha on ta.id=tha.id_agreement  where ta.id=$id_undangan and tha.id=$id_handover")->getRow()->ctr;
        if($cek > 0){
          $result = api('POST', 'hand_over/getAddAgreement', array('id' => $id_undangan));
           $data['id_agreement'] = $id_undangan;
           $data['id_handover'] = $id_handover;
           $data['dthead'] = $result->data;
           $data['dtcharge'] = $result->charge;
           $data['dtutil'] = $result->util;
           $data['droplist_pajak'] = $this->db->query("select * from m_pajak where flag_id = true and lower(nama_pajak) like '%ppn%'")->getResult();
          
           return $this->template('pages/hand_over/input/step_3',$data);   
       } else{
        $this->session->setFlashdata($this->not_auth_return()->msg, 'success');
        return redirect()->to(base_url('hand_over'));
       }
           
       }else{

        $this->session->setFlashdata($this->not_auth_return()->msg, 'success');
        return redirect()->to(base_url('hand_over'));
       } 
    }
    public function saveNewStep3()
	{
        $ret = new stdClass();
		$p = $this->request->getPost();
		if (count($p['charge']) > 0) {
			for ($i = 1; $i < count($p['charge']); $i++) {
				$v = $p['charge'][$i];
				$insert = array('id_header' => $p['id_handover'], 'id_servicecharge' => $v['s_scharge'], 'id_pajak' => $v['s_pajak'], 'periode' => $v['s_periode'], 'fee' => ribuan_to_decimal($v['fee']), 'amount' => ribuan_to_decimal($v['amount']), 'nilai_pajak' => cariTarifPajak($v['s_pajak']), 'created_date' => date('Y-m-d H:i:s'), 'created_user' => $this->session->get('id_user'));
                $insert['start_date'] = !empty($v['start_date']) ? date('Y-m-d', strtotime($v['start_date'])) : null;
				$x = $this->db->table('td_handover_charge')->insert($insert);
			}
			$s = $this->mhand_over->gen_schedule_tagihan($p['id_handover'], $this->session->get('id_user'));
			// $this->response(['status' => true, 'msg' => 'Success', 'data' => ['id_handover' => $p->id_handover, 'id_undangan' => $p->id_undangan]],  RestController::HTTP_OK);
            $ret = $s;
		} else {
			// $this->response(['status' => false, 'msg' => 'Failed'],  RestController::HTTP_NOT_FOUND);
            $ret->status = false;
            $ret->msg = 'Simpan Data Gagal';
		}
        return $this->response->setJSON($ret);
	}
    public function gen_schedule_tagihan(){
        $fp = array('id_handover' => $this->request->getPost('id_handover'),'created_user' => $this->session->get('id_user'));
        $s = api('POST','hand_over/gen_schedule_tagihan', $fp);
        return $this->response->setJSON($s);
    }
    public function pengalihan_hak($id){
        $data['data'] = $this->mhand_over->get_for_pengalihan_hak($id);
        return $this->template('pages/hand_over/input/pengalihan_hak',$data);
    }
    public function input_lunas(){
        
        $id_undangan = $this->request->getPost('idp_undangan');
        $id_bast = $this->request->getPost('idp_bast');
        $ret = new stdClass();
        if(!empty($this->request->getPost('tgl_lunas')) && !empty($this->request->getPost('tgl_bast'))){
            $tgl_lunas = date('Y-m-d', strtotime($this->request->getPost('tgl_lunas')));
            $tgl_bast = date('Y-m-d', strtotime($this->request->getPost('tgl_bast')));
            if(!empty($this->request->getPost('idp_undangan')) && !empty($this->request->getPost('idp_bast'))){
                $nomer_bast = $this->db->query("select fn_gen_no_bast(date('$tgl_bast'), id_unit) as nomer from th_handover_agreement where flag_id = true and id_agreement = $id_undangan and id = $id_bast");
                if($nomer_bast){
                    
                    $upd = array('no_agreement' => $nomer_bast->getRow()->nomer, 
                    'tgl_lunas' => $tgl_bast,
                    'updated_user' => $this->session->get('id_user'),
                    'updated_date' => date('Y-m-d H:i:s'),
                    'status_bayar' => 'LUNAS',
                    );
                    $exec = $this->db->table('th_handover_agreement')
                        ->where(['id_agreement' => $id_undangan, 'id' => $id_bast])
                        ->update($upd);
                    $updu = array('tgl_bayar' => $tgl_lunas,'updated_user' => $this->session->get('id_user'),'updated_date' => date('Y-m-d H:i:s'));
                    // $qx = "INSERT INTO xlog.th_handover_agreement_log_edit (id_handover, id_agreement, no_agreement, flag_id, created_date, created_user, updated_date, updated_user, approved_date, approved_user, handover_date, status_bayar, fito_date, id_unit, id_owner, no_pinjam_pakai, free_ipl, id_parent, no_ppjb, tgl_lunas, kode_kir, diwakilkan, tgl_input, user_input, keterangan_edit) SELECT id, id_agreement, no_agreement, flag_id, created_date, created_user, updated_date, updated_user, approved_date, approved_user, handover_date, status_bayar, fito_date, id_unit, id_owner, no_pinjam_pakai, free_ipl, id_parent, no_ppjb, tgl_lunas, kode_kir, diwakilkan, 'Input tanggal konfirmasi bast' as keterangan_edit FROM th_handover_agreement where id = $id_bast";
                    // $x = $this->db->query($qx);
                    $this->edit_undangan($id_undangan, $updu, 't_agreement');
                    if($exec){
                    $ret->status = true;
                    $ret->msg = 'Input Tanggal Lunas Berhasil';
                    }
                }else{
                    $ret->status = false;
                    $ret->msg = 'Input Gagal';
                }
            }
        }else{
            $ret->status = false;
            $ret->msg = 'Input Gagal';
        }
        return $this->response->setJSON($ret);
    }
    public function edit_undangan($id, $data, $table){
        $this->db->table($table)->where('id', $id)->update($data);
    }
}
