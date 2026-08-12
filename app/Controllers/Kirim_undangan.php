<?php

namespace App\Controllers;

use App\Models\Mkirim_undangan;
use stdClass;

class Kirim_undangan extends MyController
{
    protected $mkirim_undangan;
    protected $db;
    protected $session;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->mkirim_undangan = new Mkirim_undangan();
    }

    public function index()
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Anda tidak memiliki akses ke modul Kirim Undangan.');
        }

        return $this->template('pages/kirim_undangan/vkirim_undangan', [
            'akses' => $this->akses,
        ]);
    }

    public function grid()
    {
        $post = $this->request->getPost();
        $page = max(1, (int) ($post['page'] ?? 1));
        $rows = max(1, (int) ($post['rows'] ?? ($post['length'] ?? 10)));
        $start = isset($post['start']) ? (int) $post['start'] : (($page - 1) * $rows);

        $searchValue = '';
        if (isset($post['search']['value'])) {
            $searchValue = (string) $post['search']['value'];
        } elseif (isset($post['search_value'])) {
            $searchValue = (string) $post['search_value'];
        } elseif (isset($post['q'])) {
            $searchValue = (string) $post['q'];
        }

        $orderColumn = (string) ($post['sort'] ?? 'id');
        $orderDir = (string) ($post['order'] ?? 'desc');

        if (isset($post['order'][0]['column'])) {
            $indexColOrder = $post['order'][0]['column'];
            $orderColumn = (string) ($post['columns'][$indexColOrder]['field'] ?? $post['columns'][$indexColOrder]['data'] ?? 'id');
            $orderDir = (string) ($post['order'][0]['dir'] ?? 'desc');
        }

        $fp = [
            'draw' => $post['draw'] ?? 0,
            'start' => $start,
            'search' => ['value' => trim($searchValue)],
            'length' => $rows,
            'tb_checkbox' => $this->request->getPost('tb_checkbox') ?? [],
            'order' => [
                'dir' => strtolower($orderDir) === 'asc' ? 'asc' : 'desc',
                'column' => $orderColumn !== '' ? $orderColumn : 'id',
            ],
        ];

        $result = $this->mkirim_undangan->grid($fp);

        return $this->response->setJSON([
            'total' => $result->count_all ?? 0,
            'rows' => $result->data ?? [],
        ]);
    }

    public function convert_array()
    {
        return $this->response->setJSON($this->request->getPost());
    }

    public function getEdit()
    {
        $id = $this->request->getPost('id');
        $fp = array('id'=> $id);
        $res = api('POST','meterid/getEdit',$fp);
        return $this->response->setJSON($res);
    }

    public function form($id = null)
    {
        $data = array('status' => false,'dthead' => NULL,'dtutil' => array(),'dtcharge' => array());
        if(empty($id)){
            if($this->akses->can_create == 1){
                return $this->template('pages/konfirmasi_undangan/input', $data + ['akses' => $this->akses]);
            }
        }else{
            if($this->akses->can_edit == 1){
                $result = api('POST', 'undangan/getEdit', array('id' => $id));
                // print_r($result);die();
                $data['status'] = $result->status;
                if($result->status == true){
                    $data['dthead'] = $result->data->head;
                    $data['dtutil'] = $result->data->util;
                    $data['dtcharge'] = $result->data->charge;
                }
                return $this->template('pages/konfirmasi_undangan/input', $data + ['akses' => $this->akses]);
            }
        }

        return redirect()->to(site_url('kirim_undangan'));
    }

    public function detail($id)
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $result = $this->mkirim_undangan->getDetail($id);
        if (($result->status ?? false) !== true || empty($result->data?->head)) {
            return redirect()->to(site_url('kirim_undangan'))->with('error', 'Data kirim undangan tidak ditemukan.');
        }

        $data['status'] = $result->status;
        $data['dthead'] = $result->data->head;
        $data['dtutil'] = $result->data->util;
        $data['dtcharge'] = $result->data->charge;
        $data['citem'] = $result->data->citem;
        $data['demail'] = $result->demail;
        $data['id'] = $id;
        $data['id_checklist'] = $result->data->head->id_checklist;
        $data['akses'] = $this->akses;

        return $this->template('pages/kirim_undangan/vdetail', $data);
    }

    public function input($id = null)
    {
        if(empty($id)){
            if($this->akses->can_create == 1){
                return $this->template('pages/konfirmasi_undangan/input/step_1', ['akses' => $this->akses]);
            }
        }

        return redirect()->to(site_url('kirim_undangan'));
    }

    public function input_2($id = null)
    {
        if(empty($id)){
            if($this->akses->can_create == 1){
                return $this->template('pages/konfirmasi_undangan/input/step_2', ['akses' => $this->akses]);
            }
        }

        return redirect()->to(site_url('kirim_undangan'));
    }

    public function input_3($id = null)
    {
        if(empty($id)){
            if($this->akses->can_create == 1){
                return $this->template('pages/konfirmasi_undangan/input/step_3', ['akses' => $this->akses]);
            }
        }

        return redirect()->to(site_url('kirim_undangan'));
    }

    public function updateData()
    {
        $id = $this->request->getPost('id');
        $fp = array( 'updated_date' => date('Y-m-d H:i:s'),'updated_user' => $this->session->get('id_user'),'kode' => $this->request->getPost('kode'),'start_meter' => $this->request->getPost('start_meter'),'utilities' => $this->request->getPost('utilities'),'id' => $id);
        $res = api('POST','meterid/updateData',$fp);
        return $this->response->setJSON($res);
    }
    public function hapusData()
    {
        $id = $this->request->getPost('id');
        $fp = array('id'=>$id,'updated_user' => $this->session->get('id_user'), 'updated_date' => date('Y-m-d H:i:s'), 'flag_id' => false);
        $res = api('POST','range_type/hapusData',$fp);
        return $this->response->setJSON($res);
    }
    public function saveNewData()
    {
        $fp = array( 'created_user' => $this->session->get('id_user'),'kode' => $this->request->getPost('kode'),'start_meter' => $this->request->getPost('start_meter'),'utilities' => $this->request->getPost('utilities'));
        $res = api('POST','meterid/saveNewData',$fp);
        return $this->response->setJSON($res);
    }
    public function softDelete()
    {
        $id = $this->request->getPost('id');
        $fp = array('id' => $id,
        'updated_user' => $this->session->get('id_user'), 'updated_date' => date('Y-m-d H:i:s'));
        $response = api('POST','meterid/softDelete',$fp);
        return $this->response->setJSON($response);
    }
    public function cariMeterrange()
    {
        $id_util = $this->request->getPost('id_util');
        $fp = array('id_util' => $id_util);
        $response = api('POST','undangan/cariMeterrange',$fp);
        return $this->response->setJSON($response);
    }
    public function hitung_fee()
    {
        $fp = array('id_scharge' => $this->request->getPost('id_scharge'), 'id_unit' => $this->request->getPost('id_unit'));
        $response = api('POST','undangan/cariScharge',$fp);
        return $this->response->setJSON($response);
    }
    public function cariTarifPajak()
    {
        $id = $this->request->getPost('id');
        $response = api('POST','undangan/cariTarifPajak',array('id' => $id));
        return $this->response->setJSON($response);
    }
    public function saveUndangan()
    {
        $fp = array('no_undangan' => $this->request->getPost('no_undangan'),'order_date' => $this->request->getPost('order_date'),'accept_date' => $this->request->getPost('accept_date'), 'id_owner' => $this->request->getPost('id_owner'),'id_unit' => $this->request->getPost('id_unit'), 'id_sales' => $this->request->getPost('id_sales'), 'charge' => $this->request->getPost('charge'),'utilities' => $this->request->getPost('utilities'),'created_date' => date('Y-m-d H:i:s'),'created_user' => $this->session->get('id_user'));
        $response = api_json('POST','undangan/saveNewData', $fp);
        return $this->response->setJSON($response);
    }
    public function updateUndangan()
    {
        $fp = array('id_undangan' => $this->request->getPost('id_undangan'),'no_undangan' => $this->request->getPost('no_undangan'),'order_date' => $this->request->getPost('order_date'),'accept_date' => $this->request->getPost('accept_date'), 'id_owner' => $this->request->getPost('id_owner'),'id_unit' => $this->request->getPost('id_unit'), 'id_sales' => $this->request->getPost('id_sales'), 'charge' => $this->request->getPost('charge'),'utilities' => $this->request->getPost('utilities'),'updated_date' => date('Y-m-d H:i:s'),'updated_user' => $this->session->get('id_user'),'dcharge_deleted' => $this->request->getPost('dcharge_deleted'), 'dutil_deleted' => $this->request->getPost('dutil_deleted'));
        $response = api_json('POST','undangan/updateData', $fp);
        return $this->response->setJSON($response);
    }
    public function cekPin()
    {
        // $fp = array('id_user' => $this->session->get('id_user'));
        if(md5($this->request->getPost('pin')) == $this->session->get('pin')){
            $ret = array('status' => true, 'msg' => 'Pin Benar');
        }else{
            $ret = array('status' => false, 'msg' => 'Pin Anda Salah');
        }
        return $this->response->setJSON($ret);
    }
    public function approveUndangan()
    {
        $ret = array('status'=> false,'msg' => '');

        if(md5($this->request->getPost('pin')) == $this->session->get('pin')){
            $fp = array(
                'id_undangan' => $this->request->getPost('id_undangan'),
                'tgl_lunas' => $this->request->getPost('tgl_lunas'),
                'status' => 'APPROVED',
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_user' => $this->session->get('id_user')
            );

            $uploadedFile = $this->request->getFile('ppjb_file');
            if ($uploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
                if (!$uploadedFile->isValid()) {
                    $ret['msg'] = $uploadedFile->getErrorString();
                    return $this->response->setJSON($ret);
                }

                if ($uploadedFile->getSize() > (5 * 1024 * 1024)) {
                    $ret['msg'] = 'Ukuran file PPJB maksimal 5 MB.';
                    return $this->response->setJSON($ret);
                }

                $nama_file = 'dok_ppjb_'.$this->request->getPost('id_undangan').'_'.date('YmdHis');
                $extension = $uploadedFile->getClientExtension();
                $fileppjb = $nama_file . ($extension !== '' ? '.' . $extension : '');

                $uploadPath = FCPATH . 'dokumen/ppjb/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                try {
                    $uploadedFile->move($uploadPath, $fileppjb, true);
                    $fp['file_ppjb'] = $fileppjb;
                } catch (\Throwable $e) {
                    $ret['msg'] = $e->getMessage();
                    return $this->response->setJSON($ret);
                }
            }

            $ret = api('POST','undangan/approveUndangan',$fp);
        }else{
            $ret['msg'] = 'Pin Anda Salah';
        }

        return $this->response->setJSON($ret);
    }
    public function rejectUndangan()
    {
        // $ret = array('status'=> false,'msg' => '');
        $ret = new stdClass();
        $ret->status = false;
        $ret->msg = '';
        if(md5($this->request->getPost('pin')) == $this->session->get('pin')){
        $fp = array('id_reject' => $this->request->getPost('id_reject'),'keterangan' => $this->request->getPost('keterangan'),'status' => 'REJECTED','updated_date' => date('Y-m-d H:i:s'),'updated_user' => $this->session->get('id_user'));
        $ret = api('POST','undangan/rejectUndangan',$fp);
        }else{
            $ret->msg = 'Pin Anda Salah';
        }
        
        return $this->response->setJSON($ret);
    }
    public function download_file_ppjb($id)
    {
        $ret = api('POST','undangan/getFileppjb',array('id' => $id));

        if (
            !isset($ret->status) ||
            $ret->status !== true ||
            empty($ret->data->file_ppjb)
        ) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('The File does not exist.');
        }

        $file_path = FCPATH . 'dokumen/ppjb/' . $ret->data->file_ppjb;

        if (!is_file($file_path)) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('The File does not exist.');
        }

        return $this->response
            ->download($file_path, null)
            ->setFileName(basename($file_path));
    }
    public function kirimEmail()
    {
        $id = $this->request->getPost('id');
        $fp = array('id' => $id);
        $dataEmail = api('POST','kirim_undangan/dataEmail',$fp);
        // print_r($dataEmail);die();
        $dt = $dataEmail->data;
        $b = 'Salam Hangat dari Pacific Garden Apartemen<br><br><br>';
        $b .= 'Dengan Hormat,<br>';
        $b .= 'Pertama-tama kami mengucapkan terimakasih telah memilih Pacific Garden sebagai tempat hunian maupun investasi Bapak/Ibu.<br>';
        $b .= 'Melalui email ini, kami kirimkan undangan serah terima unit.<br><br>
            Adapun jadwal, tempat dan persyaratan serah terima terlampir dalam Surat Undangan Serah Terima.<br><br>
            Untuk informasi lebih lanjut Bapak/Ibu dapat menghubungi kami di 021-50111033 atau via whatsapp +62 812-1998-5057.<br><br>
            Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih. <br><br><br><br>
                Best Regards,';
        
        $namafilenew = 'Surat_Undangan_';
         $t = $dataEmail->data;
    $tarif_ppn = $this->db->query("select kode, nilai from sys_parameter sp where kode = 'ppn'")->getRow()->nilai;
    $nominal_sc = floatval($dataEmail->tarif_service->nominal_sc);
    $nominal_sf = floatval($dataEmail->tarif_service->nominal_sf);

    $ipl = floatval(ribuan_to_decimal($t->luas_unit))*$nominal_sc*3;
    $sf = floatval(ribuan_to_decimal($t->luas_unit))*$nominal_sf*3;
    // print_r($nominal_sf);die();
    $pajak = (($ipl+$sf)*$tarif_ppn) / 100;
    $harga_materai = 10000;
    
        $kodeunit = str_replace("/","",$t->kode_unit);
        $namafilenew = 'Surat_Undangan_'.$kodeunit.date('YmdHis');
        // $namafilenew = 'Surat_Undangan_'.['kode_unit'];
        if($t->jenis_tenant == 'PERUSAHAAN'){
            $grand_total = $ipl+$sf+$harga_materai;
            $file_template = FCPATH.'template_dokumen/Surat_Undangan_asli_PT_template.docx';
        }else{
            $file_template = FCPATH.'template_dokumen/Surat_Undangan_asli_template.docx';
        }

        $fee = $this->db->query("select msc.nominal as fee_sf, b.nominal as fee_sc  from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id left join (select msc.nominal from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id where sp.kode ='ID_SC') b on 1=1 where sp.kode ='ID_SF'")->getRow();
        
        $harga_materai = $this->db->query("select sp.kode, sp.nilai, msc.nominal from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id where sp.kode = 'id_materai_service'")->getRow()->nominal;
        $grand_total = $ipl+$sf+$pajak+$harga_materai;
        $dir = FCPATH.'dokumen_undangan';
        if(!file_exists($dir)){
            mkdir($dir,0777, true);
        }
        $tgl_undang_kirim = date('Y-m-d', strtotime($t->tgl_undangan));
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
        $templateProcessor->setValue('tgl_today', tglteks(date('Y-m-d')));
        $templateProcessor->setValue('no_undangan', $t->no_undangan);
        $templateProcessor->setValue('nama_tenant', $t->nama_tenant);
        $templateProcessor->setValue('kode_unit', $t->kode_unit);
        $templateProcessor->setValue('alamat_tenant', $t->alamat_tenant);
        $templateProcessor->setValue('tgl_undangan', tglteks(date('Y-m-d',strtotime($tgl_undang_kirim))));
        $templateProcessor->setValue('jam_undangan', date('H:i',strtotime($t->tgl_undangan)));
        $templateProcessor->setValue('hari_undangan', day_teks(date('N',strtotime($t->tgl_undangan))));
        $templateProcessor->setValue('ipl', number_format($ipl,0,',','.'));
        $templateProcessor->setValue('dn_cdg', number_format($sf,0,',','.'));
        $templateProcessor->setValue('trf_pjk', number_format($pajak,0,',','.'));
        $templateProcessor->setValue('tarif_persen_pajak', 12);
        $templateProcessor->setValue('luas_unit', $t->luas_unit);
        $templateProcessor->setValue('manager_bm', 'Natalia');
        $templateProcessor->setValue('harga_materai_asli', number_format($harga_materai/2,0,',','.'));
        $templateProcessor->setValue('hrg_mtr', number_format($harga_materai,0,',','.'));
        $templateProcessor->setValue('fee_ipl', number_format($fee->fee_sc,0,',','.'));
        $templateProcessor->setValue('fee_sf', number_format($fee->fee_sf,0,',','.'));
        $templateProcessor->setValue('grand_total', number_format($grand_total,0,',','.'));
        $templateProcessor->setValue('grand_total_terbilang', ucwords(terbilang($grand_total)));
        $templateProcessor->saveAs($dir.'/'.$namafilenew.'.docx');
        
        $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
        $command2 = 'mv '.FCPATH.$namafilenew.'.pdf dokumen_undangan/'.$namafilenew.'.pdf';
        // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx');
        file_put_contents($dir.'/convert_pdf.bat', $command);
        shell_exec($command);
        shell_exec($command." > debug.log 2>&1");
        shell_exec($command2);
        shell_exec($command2." > debug2.log 2>&1");
        
        if(file_exists($dir.'/'.$namafilenew.'.docx')){
            unlink($dir.'/'.$namafilenew.'.docx');
        }
        
        //$send = kirimEmail(strtolower(trim($dt->email_tenant)), 'Undangan Serah Terima Unit',$b,$dir.'/'.$namafilenew.'.pdf');//ubah docx menjadi .pdf
        // $cc = kirimEmail('generaladm.bmpg@indopasifik.id','Undangan Serah Terima Unit '.$dt->email_tenant,$b,$dir.'/'.$namafilenew.'.pdf');
        // print_r($dt->email_tenant);die();
        $semail = [];
        $semail[0] = new stdClass();
        $semail[0]->email = trim($dt->email_tenant);
        $semail[0]->name = trim($dt->email_tenant);
        // $semail[0]->email = 'rezapahlevi927@gmail.com';
        //$semail[0]->name = 'rezapahlevi927@gmail.com';
        $attach = new stdClass();
        $attach->mimeType = mime_content_type($dir.'/'.$namafilenew.'.pdf');
        $attach->filename = $namafilenew.'.pdf';
        $filee = file_get_contents($dir.'/'.$namafilenew.'.pdf');
        $attach->value = base64_encode($filee);
        $attachment[0] = $attach;
        $par['body'] = $b;
		$par['subject'] =  'Undangan Serah Terima Unit';
        $send = email_api($par, $semail, $attachment);
        
        $fp1 = array('id_agreement' => $id, 'status_send' => $send,'email_tenant' => $dt->email_tenant,'file_undangan' => $namafilenew.'.pdf','created_user' => $this->session->get('id_user'), 'created_date' => date('Y-m-d H:i:s'));
        $in = api('POST','kirim_undangan/email_history_insert', $fp1);
        return $this->response->setJSON($in);
    }
    
    public function submitConfirm()
    {
        // print_r($this->request->getPost());
		
        $fp = array(
			'updated_user' => $this->session->get('id_user'), 
			'updated_date' => date('Y-m-d H:i:s'), 
			'id_agreement' => $this->request->getPost('id_conf_kehadiran'),
			'tgl_hadir' => $this->request->getPost('tgl_hadir'),
			'jam_hadir' => $this->request->getPost('jam_hadir'),
			'diwakilkan' => $this->request->getPost('diwakilkan'),
			'nama_wakil' => $this->request->getPost('nama_wakil'),
			'tempat_lahir_wakil' => $this->request->getPost('tempat_lahir'),
			'tgl_lahir_wakil' => $this->request->getPost('tgl_lahir'),
			'nik_wakil' => $this->request->getPost('nik_wakil'),
			'alamat_wakil' => $this->request->getPost('alamat_wakil'),
            'npwp_wakil' => $this->request->getPost('npwp_wakil'),
            'pekerjaan_wakil' => $this->request->getPost('pekerjaan_wakil')
		);
		
        // $res = api('POST','kirim_undangan/submitConfirm', $fp);
        $res = $this->mkirim_undangan->submitConfirm($fp);
        return $this->response->setJSON($res);
    }
    
    public function printFileEmail($id)
    {
        $fp = array('id' => $id);
        $dataEmail = api('POST','kirim_undangan/dataEmail',$fp);
        // print_r($dataEmail);die();
        $dt = $dataEmail->data;
        $b = 'Salam Hangat dari Pacific Garden Apartemen<br><br><br>';
        $b .= 'Dengan Hormat,<br>';
        $b .= 'Pertama-tama kami mengucapkan terimakasih telah memilih Pacific Garden sebagai tempat hunian maupun investasi Bapak/Ibu.<br>';
        $b .= 'Melalui email ini, kami kirimkan undangan serah terima unit.<br><br>
            Adapun jadwal, tempat dan persyaratan serah terima terlampir dalam Surat Undangan Serah Terima.<br><br>
            Untuk informasi lebih lanjut Bapak/Ibu dapat menghubungi kami di 021-50111033 atau via whatsapp +62 812-1998-5057.<br><br>
            Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih. <br><br><br><br>
                Best Regards,';
        
        $namafilenew = 'Surat_Undangan_';
         $t = $dataEmail->data;
    $tarif_ppn = $this->db->query("select kode,nilai from sys_parameter sp where kode = 'ppn'")->getRow()->nilai;
	$tarif_ppn = empty($tarif_ppn) ? 0 : $tarif_ppn; 
    $nominal_sc = floatval($dataEmail->tarif_service->nominal_sc);
    $nominal_sf = floatval($dataEmail->tarif_service->nominal_sf);

    $ipl = floatval(ribuan_to_decimal($t->luas_unit))*$nominal_sc*3;
    $sf = floatval(ribuan_to_decimal($t->luas_unit))*$nominal_sf*3;
    // print_r($nominal_sf);die();
    $pajak = (($ipl+$sf)*$tarif_ppn) / 100;
    $harga_materai = 10000;
    
        $kodeunit = str_replace("/","",$t->kode_unit);
        $namafilenew = 'Surat_Undangan_'.$kodeunit.date('YmdHis');
        // $namafilenew = 'Surat_Undangan_'.['kode_unit'];
        if($t->jenis_tenant == 'PERUSAHAAN'){
            $grand_total = $ipl+$sf+$harga_materai;
            $file_template = FCPATH.'template_dokumen/Surat_Undangan_asli_PT_template.docx';
        }else{
            $file_template = FCPATH.'template_dokumen/Surat_Undangan_asli_template.docx';
        }

        $fee = $this->db->query("select msc.nominal as fee_sf, b.nominal as fee_sc  from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id left join (select msc.nominal from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id where sp.kode ='ID_SC') b on 1=1 where sp.kode ='ID_SF'")->getRow();
        
        $harga_materai = $this->db->query("select sp.kode, sp.nilai, msc.nominal from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id where sp.kode = 'id_materai_service'")->getRow()->nominal;
        $grand_total = $ipl+$sf+$pajak+$harga_materai;
        $dir = FCPATH.'dokumen_undangan';
        if(!file_exists($dir)){
            mkdir($dir,0777, true);
        }
        $tgl_undang_kirim = date('Y-m-d', strtotime($t->tgl_undangan));
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
        $templateProcessor->setValue('tgl_today', tglteks(date('Y-m-d')));
        $templateProcessor->setValue('no_undangan', $t->no_undangan);
        $templateProcessor->setValue('nama_tenant', $t->nama_tenant);
        $templateProcessor->setValue('kode_unit', $t->kode_unit);
        $templateProcessor->setValue('alamat_tenant', $t->alamat_tenant);
        $templateProcessor->setValue('tgl_undangan', tglteks(date('Y-m-d',strtotime($tgl_undang_kirim))));
        $templateProcessor->setValue('jam_undangan', date('H:i',strtotime($t->tgl_undangan)));
        $templateProcessor->setValue('hari_undangan', day_teks(date('N',strtotime($t->tgl_undangan))));
        $templateProcessor->setValue('ipl', number_format($ipl,0,',','.')); 
        $templateProcessor->setValue('dn_cdg', number_format($sf,0,',','.')); //dana cadangan
        $templateProcessor->setValue('trf_pjk', number_format($pajak,0,',','.')); //tarif pajak
        $templateProcessor->setValue('tarif_persen_pajak', $tarif_ppn);
        $templateProcessor->setValue('luas_unit', $t->luas_unit);
        $templateProcessor->setValue('manager_bm', 'Fitri Hadis');
        $templateProcessor->setValue('harga_materai_asli', number_format($harga_materai/2,0,',','.'));
        $templateProcessor->setValue('hrg_mtr', number_format($harga_materai,0,',','.')); //harga materai
        $templateProcessor->setValue('fee_ipl', number_format($fee->fee_sc,0,',','.'));
        $templateProcessor->setValue('fee_sf', number_format($fee->fee_sf,0,',','.'));
        $templateProcessor->setValue('grand_total', number_format($grand_total,0,',','.'));
        $templateProcessor->setValue('grand_total_terbilang', terbilang($grand_total));
        $templateProcessor->saveAs($dir.'/'.$namafilenew.'.docx');
        
        $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
        $command2 = 'mv '.FCPATH.$namafilenew.'.pdf dokumen_undangan/'.$namafilenew.'.pdf';
        // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx');
        file_put_contents($dir.'/convert_pdf.bat', $command);
        shell_exec($command);
        shell_exec($command." > debug.log 2>&1");
        shell_exec($command2);
        shell_exec($command2." > debug2.log 2>&1");
        
        if(file_exists($dir.'/'.$namafilenew.'.docx')){
            unlink($dir.'/'.$namafilenew.'.docx');
        }
        // print_r($dt->email_tenant);die();
        cetak_delete_dokumen($dir.'/'.$namafilenew.'.pdf');
    }
 
    public function printFileEmail_bck($id)
    {
        $fp = array('id' => $id);
        $dataEmail = api('POST','kirim_undangan/dataEmail',$fp);
        // print_r($dataEmail);
        $dt = $dataEmail->data;
        $namafilenew = 'Surat_Undangan_';
         $t = $dataEmail->data;
    $nominal_sc = floatval($dataEmail->tarif_service->nominal_sc);
    $nominal_sf = floatval($dataEmail->tarif_service->nominal_sf);
    
    $ipl = floatval(ribuan_to_decimal($t->luas_unit))*$nominal_sc*3;
    $sf = floatval(ribuan_to_decimal($t->luas_unit))*$nominal_sf*3;
    $pajak = ($ipl+$sf)*0.1;
    $harga_materai = 20000;
    $grand_total = $ipl+$sf+$pajak+$harga_materai;
        $kodeunit = str_replace("/","",$t->kode_unit);
        $namafilenew = 'Surat_Undangan_'.$kodeunit.'-'.date('YmdHis');
        // $namafilenew = 'Surat_Undangan_'.['kode_unit'];
        if($t->jenis_tenant == 'PERUSAHAAN'){
            $grand_total = $ipl+$sf+$harga_materai;
            $file_template = FCPATH.'template_dokumen/Surat_Undangan_asli_PT_template.docx';
        }else{
            $file_template = FCPATH.'template_dokumen/Surat_Undangan_asli_template.docx';
        }

        $fee = $this->db->query("select msc.nominal as fee_sf, b.nominal as fee_sc  from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id left join (select msc.nominal from sys_parameter sp left join m_service_charge msc on sp.nilai::int=msc.id where sp.kode ='ID_SC') b on 1=1 where sp.kode ='ID_SF'")->getRow();
        $dir = FCPATH.'dokumen_undangan';
        // if(!file_exists($dir)){
        //     mkdir($dir,0755, true);;
        // }
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
        $templateProcessor->setValue('tgl_today', tglteks(date('Y-m-d')));
        $templateProcessor->setValue('no_undangan', $t->no_undangan);
        $templateProcessor->setValue('nama_tenant', $t->nama_tenant);
        $templateProcessor->setValue('kode_unit', $t->kode_unit);
        $templateProcessor->setValue('alamat_tenant', $t->alamat_tenant);
        $templateProcessor->setValue('tgl_undangan', tglteks(date('Y-m-d',strtotime($t->tgl_undangan))));
        $templateProcessor->setValue('jam_undangan', date('H:i',strtotime($t->tgl_undangan)));
        $templateProcessor->setValue('hari_undangan', day_teks(date('N',strtotime($t->tgl_undangan))));
        $templateProcessor->setValue('iuran_pengelolaan', number_format($ipl,0,',','.'));
        $templateProcessor->setValue('dana_cadangan', number_format($sf,0,',','.'));
        $templateProcessor->setValue('tarif_pajak', number_format($pajak,0,',','.'));
        $templateProcessor->setValue('harga_materai_asli', number_format($harga_materai,0,',','.'));
        $templateProcessor->setValue('harga_materai', number_format($harga_materai * 2,0,',','.'));
        $templateProcessor->setValue('grand_total', number_format($grand_total,0,',','.'));
        $templateProcessor->setValue('fee_ipl', number_format($fee->fee_sc,0,',','.'));
        $templateProcessor->setValue('fee_sf', number_format($fee->fee_sf,0,',','.'));
        $templateProcessor->setValue('grand_total_terbilang', terbilang($grand_total));
        $templateProcessor->saveAs($dir.'/'.$namafilenew.'.docx');
        
        $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
        $command2 = 'mv '.FCPATH.$namafilenew.'.pdf dokumen_undangan/'.$namafilenew.'.pdf';
        
        file_put_contents($dir.'/convert_pdf.bat', $command);
        shell_exec($command." > debug.log 2>&1");
        shell_exec($command2." > debug2.log 2>&1");
        cetak_delete_dokumen('dokumen_undangan/'.$namafilenew.'.pdf');
    }
    public function load_reconfirm()
    {
        $id = $this->request->getPost('id');
        $x = $this->db->query(
            "select id, waktu_hadir,
                    case when diwakilkan = true then 'true' else 'false' end as diwakilkan,
                    nama_wakil, tempat_lahir_wakil, nik_wakil, alamat_wakil,
                    npwp_wakil, pekerjaan_wakil, tgl_lahir_wakil
             from t_agreement
             where id = ?",
            [$id]
        );

        $ret = new stdClass();
        $data = $x->getRow();

        if($data){
            $ret->status = true;
            $ret->data = $data;
            $ret->data->tgl_hadir = date('Y-m-d',strtotime($data->waktu_hadir));
            $ret->data->jam_hadir = date('H:i',strtotime($data->waktu_hadir));
        }else{
            $ret->status = false;
            $ret->data = NULL;
        }

        return $this->response->setJSON($ret);
    }
}
