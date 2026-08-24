<?php
namespace App\Controllers;

use App\Models\MUtilityRecord;
use Config\Database;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
class Utility_record extends MyController
{
    protected MUtilityRecord $model;
    protected BaseConnection $db;
    public function __construct()
    {
        parent::__construct();
        $this->model = new MUtilityRecord();
        $this->db = Database::connect();
    }
    public function index()
    {
        if ($this->akses->can_view == 1) {
            $data['utility_list'] = $this->db->query("select * from m_utilities where flag_id = true and id = 2")->getResult();
            $data['akses'] = $this->akses;
            return $this->template('pages/utility_record/vutility_record', $data);
        }
    }
    public function hom()
    {
        if ($this->akses->can_view == 1) {
            return $this->template('pages/utility_record/vutility_record2', ['akses' => $this->akses]);
        }
    }
    public function grid()
    {
        $post = $this->request->getPost();
        // $fp = array('draw' => $post['draw'] ,'start' => $post['start'], 'unit' => $post['unit'], 'periode' => $post['periode'], 'status' => $post['status'],'length' => $post['length']);
        // $result = api_json('POST', 'utility_record/grid', $fp);
        $start = $post['start'];
        $length = $post['length'];
        $where = "a.tipe = 'UTILITIES' and a.flag_id = true and a.utility_periode is not null";

        if (!empty($post['unit'])) {
            $search = strtoupper($post['unit']);
            $where .= " and (mu.kode_unit like '%$search%') ";
        }
        if (!empty($post['periode'])) {
            $tahun_periode = date('Y', strtotime('01-' . $post['periode']));
            $bulan_periode = intval(date('m', strtotime('01-' . $post['periode'])));
            $where .= " and (extract(year from a.utility_periode) = $tahun_periode and extract(month from a.utility_periode) = $bulan_periode) ";
        }
        if (!empty($post['status'])) {
            $search = strtoupper($post['status']);
            $where .= " and (a.status = '$search') ";
        }

        $q_count_all = "select count(*) as ctr from th_schedule_tagihan a left join m_unit mu on a.id_unit = mu.id  left join m_utilities mu2 on a.id_service = mu2.id and a.tipe = 'UTILITIES' left join m_meter mm on a.id_meter = mm.id left join td_invoice ti on a.id_invoice = ti.id and ti.flag_id left join th_handover_agreement tha on a.id_bast = tha.id left join m_tenant mt on a.id_tenant = mt.id left join m_tenant mt2 on tha.id_owner = mt2.id where $where ";
        $query_count_all = $this->db->query($q_count_all)->getRow()->ctr;

       $qq = "select a.id, mu.kode_unit , mu2.nama as nama_utilities, coalesce(mm.kode, a.kode_meter) as kode_meter, a.utility_periode as periode , a.start_meter , a.end_meter, a.status, ti.no_invoice, mt.nama as nama_customer , mt2.nama as nama_owner from th_schedule_tagihan a left join m_unit mu on a.id_unit = mu.id  left join m_utilities mu2 on a.id_service = mu2.id and a.tipe = 'UTILITIES' left join m_meter mm on a.id_meter = mm.id left join td_invoice ti on a.id_invoice = ti.id and ti.flag_id left join th_handover_agreement tha on a.id_bast = tha.id left join m_tenant mt on a.id_tenant = mt.id left join m_tenant mt2 on tha.id_owner = mt2.id where $where ORDER BY a.created_date desc limit $length offset $start ";

        $query = $this->db->query($qq);

        $callback = array(
            'draw' => $this->request->getPost('draw'), // Ini dari datatablenya    
            'recordsTotal' => $query_count_all,
            'recordsFiltered' => $query_count_all,
            'data' => $query->getResult(),
           
        );
        return $this->response->setJSON($callback);
    }


    public function input()
    {
        // print_r($this->akses);
        if ($this->akses->can_create == 1) {
            $data['droplist_utilities'] = api('POST', 'utility_record/dropListUtilities', NULL);
            $data['droplist_meterid'] = api('POST', 'meterid/dropListMeterID', NULL);
            $data['drop_util'] = api('POST', 'utility_record/cariUtilities', NULL);
            $data['akses'] = $this->akses;
            return $this->template('pages/utility_record/input', $data);
        }
    }

    public function saveNewUR_old()
    {
        $id_unit = $this->request->getPost('id_unit');
        $periode_bulan = date('Y-m', strtotime($this->request->getPost('periode')));
        $cek = $this->db->query("SELECT id from th_schedule_tagihan where id_unit = $id_unit and TO_CHAR(utility_periode, 'YYYY-MM') = '$periode_bulan'")->getRow();
        if($cek == null){
            $fp = $this->request->getPost();
            $fp['created_user'] = session()->get('id_user');
            $fp['created_date'] = date('Y-m-d H:i:s');
            $time = date('YmdHis');
            $unit =  $this->request->getPost('id_unit');

            $dunit = $this->db->query("select * from v_handover_agreement vha where vha.id_unit = ".$this->request->getPost('id_unit'))->getRow();

            $periode_ur = $this->request->getPost('periode');
            $start_date = date('Y-m',strtotime($periode_ur. ' - '.'1'.' month')).'-21';
            $end_date = date('Y-m',strtotime($periode_ur)).'-20';
            $periode_inv = date('Y-m',strtotime($periode_ur. ' + '.'1'.' month')).'-01';

            $data = array('created_user' => session()->get('id_user'), 
            'created_date' => date('Y-m-d H:i:s'), 
            'id_unit' => $this->request->getPost('id_unit'), 
            'id_bast' => $dunit->id,
            'id_tenant' => $dunit->id_owner,
            'id_service' => $this->request->getPost('id_utilities'),
            'tipe' =>'UTILITIES',
            'utility_periode' => $this->request->getPost('periode'), 
            'id_meter' => $this->request->getPost('id_meter'), 
            'kode_meter' => $this->request->getPost('kode_meter'), 
            'end_meter' => $this->request->getPost('end_meter'), 
            'start_meter' => $this->request->getPost('start_meter'),
            'periode_start' => $start_date,
            'periode_end' => $end_date,
            'periode' => $periode_inv);

            if(!empty($this->request->getPost('kode_meter'))){
                $data['kode_meter'] = $this->request->getPost('kode_meter');
            }

            $fileName = $this->uploadMeterPhoto((int) $unit);
            if ($fileName !== null) {
                $data['foto_meter'] = $fileName;
            }
            $s = $this->model->saveNewUR($data);
            // $s = api_json('POST', 'utility_record/saveNewUR', $fp);
            return $this->response->setJSON($s);
        }else{
           return $this->response->setJSON(['status' => false, 'msg' => 'Utility unit pada periode ini sudah diinput']);
        }
    }

    public function saveNewUR()
    {
        $id_unit = $this->request->getPost('id_unit');
        $periode_bulan = date('Y-m', strtotime($this->request->getPost('periode')));
        // $cek = $this->db->query("SELECT id from th_schedule_tagihan where id_unit = $id_unit and TO_CHAR(utility_periode, 'YYYY-MM') = '$periode_bulan'")->getRow();
        $cek = 1;
        if($cek == 1){
            $fp = $this->request->getPost();
            $fp['created_user'] = session()->get('id_user');
            $fp['created_date'] = date('Y-m-d H:i:s');
            $time = date('YmdHis');
            $unit =  $this->request->getPost('id_unit');

            //ga dipake
            //$dunit = $this->db->query("select * from v_handover_agreement vha where vha.id_unit = ".$this->request->getPost('id_unit'))->getRow();

            $periode_ur = $this->request->getPost('periode');
            $start_date = date('Y-m',strtotime($periode_ur. ' - '.'1'.' month')).'-21';
            $end_date = date('Y-m',strtotime($periode_ur)).'-20';
            $periode_inv = date('Y-m',strtotime($periode_ur. ' + '.'1'.' month')).'-01';

            $data = array('updated_user' => session()->get('id_user'), 
            'updated_date' => date('Y-m-d H:i:s'), 
            'id_meter' => $this->request->getPost('id_meter'), 
            'kode_meter' => $this->request->getPost('kode_meter'), 
            'end_meter' => $this->request->getPost('end_meter'), 
            'start_meter' => $this->request->getPost('start_meter'),
            'periode_start' => $start_date,
            'periode_end' => $end_date,
            'status' => 'DONE');

            if(!empty($this->request->getPost('kode_meter'))){
                $data['kode_meter'] = $this->request->getPost('kode_meter');
            }

            $fileName = $this->uploadMeterPhoto((int) $unit);
            if ($fileName !== null) {
                $data['foto_meter'] = $fileName;
            }
            $s = $this->model->saveNewUR($this->request->getPost('ids'),$data);
            // $s = api_json('POST', 'utility_record/saveNewUR', $fp);
            return $this->response->setJSON($s);
        }else{
           return $this->response->setJSON(['status' => false, 'msg' => 'Utility unit pada periode ini sudah diinput']);
        }
    }


    // Fungsi untuk meresize gambar
    private function resizeImage(string $filePath): void
    {
        try {
            $image = service('image');
            $image->withFile($filePath)
                ->resize(800, 600, true, 'auto')
                ->save($filePath, 85);
        } catch (\Throwable $e) {
            log_message('warning', 'Resize utility meter image gagal: {message}', ['message' => $e->getMessage()]);
        }
    }

    private function uploadMeterPhoto(int $unitId): ?string
    {
        $file = $this->request->getFile('foto_file');
        if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, ['jpg', 'jpeg'], true)) {
            return null;
        }

        $uploadPath = FCPATH . 'dokumen/utility_record';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0777, true) && ! is_dir($uploadPath)) {
            return null;
        }

        $fileName = $unitId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($uploadPath, $fileName);
        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($fullPath) && filesize($fullPath) > (500 * 1024)) {
            $this->resizeImage($fullPath);
        }

        return $fileName;
    }



    public function form($id = null)
    {
        $data = array('status' => false, 'dtunit' => NULL);
        if (empty($id)) {
            if ($this->akses->can_create == 1) {
                $data['akses'] = $this->akses;
                return $this->template('pages/trans_pembelian/input', $data);
            }
        } else {
            if ($this->akses->can_edit == 1) {
                $result = api('POST', 'trans_pembelian/getEdit', array('id' => $id));
                // print_r($result);die();
                $data['status'] = $result->status;
                if ($result->status == true) {
                    $data['dtunit'] = $result->data->unit;
                }
                $data['akses'] = $this->akses;
                return $this->template('pages/trans_pembelian/input', $data);
            }
        }
    }

    public function unitList()
    {
        // print_r($this->request->getPost());
        $post = $this->request->getPost();
        $fp = array('draw' => $post['draw'], 'start' => $post['start'], 'search' => $post['search'], 'length' => $post['length']);
        $index_col_order = $post['order'][0]['column'];
        $fp['order'] = ['dir' => $post['order'][0]['dir'], 'column' => $post['columns'][$index_col_order]['name']];
        $result = api_json('POST', 'unit/grid_dlg', $fp);
        $callback = array(
            'draw' => $this->request->getPost('draw'), // Ini dari datatablenya    
            'recordsTotal' => $result->count_all,
            'recordsFiltered' => $result->count_all,
            'data' => $result->data
        );
        return $this->response->setJSON($callback);
    }

    public function cariUtilities_old()
    {
        $post = $this->request->getPost();
        $id_utilities = $this->request->getPost('id_utilities');
        $id_unit = $this->request->getPost('id_unit');
        $periode = $this->request->getPost('periode');
        $periode_endmeter = date('Y-m-d', strtotime($periode. ' + '.'1'.' months'));
        $qq2 = "select coalesce(a.kode_meter ,mm.kode) as kode_meter, a.end_meter as last_end_meter, a.id_meter from t_utility_record a left join m_meter mm on a.id_meter = mm.id where a.id_unit = $id_unit and a.id_utilities = $id_utilities and a.flag_id = true order by a.id desc limit 1";
        $query2 = $this->db->query($qq2);
        // $result = api('POST', 'utility_record/cariUtilities', $post);
        $result = new \stdClass();
        if ($query2) {
            if ($query2->getNumRows() > 0) {
                $result->status = true;
                $result->msg = 'Data Found';
                $result->query = $query2->getRow();
            } else {
                $qq1 = "select thu.meter_start as last_end_meter, mm.kode as kode_meter, thu.id_meter from th_handover_agreement a
                left join td_handover_utilities thu on a.id=thu.id_header
                left join m_meter mm on thu.id_meter = mm.id
                where a.id_unit=$id_unit and thu.id_utilities = $id_utilities";
                $query1 = $this->db->query($qq1);
                if ($query1->getNumRows() > 0) {
                    $result->status = true;
                    $result->msg = 'Data Found';
                    $result->query = $query1->getRow();
                } else {
                    $result->status = false;
                    $result->msg = 'Data Not Found';
                    $result->query = NULL;
                    $result->lq = $this->db->getLastQuery();
                }
            }
        } else {
            // $this->response(['status' => false, 'msg' => 'Data Not Found', 'data' => null];
            $result->status = false;
            $result->msg = 'Data Not Found';
            $result->query = NULL;
            $result->lq = $this->db->getLastQuery();
        }
        return $this->response->setJSON($result);
    }

    public function cariUtilities()
    {
        $post = $this->request->getPost();
        $id_utilities = $this->request->getPost('id_utilities');
        $id_unit = $this->request->getPost('id_unit');
        $periode = $this->request->getPost('periode');
        $pperiode =date('Y-m',strtotime($this->request->getPost('periode'))).'-01';
        $periode_endmeter = date('Y-m', strtotime($periode. ' - '.'1'.' months')).'-01';

        // $qq2 = "select coalesce(a.kode_meter ,mm.kode) as kode_meter, a.end_meter as last_end_meter, a.id_meter from th_schedule_tagihan a left join m_meter mm on a.id_meter = mm.id where a.tipe = 'UTILITIES' and a.utility_periode = '$periode_endmeter' and a.id_unit = $id_unit and a.id_service = $id_utilities and a.flag_id = true order by a.id desc limit 1";
        $qq2 = "select coalesce(a.kode_meter ,mm.kode) as kode_meter, a.end_meter as last_end_meter, a.id_meter from th_schedule_tagihan a left join m_meter mm on a.id_meter = mm.id where a.tipe = 'UTILITIES' and a.utility_periode < '$pperiode' and a.id_unit = $id_unit and a.id_service = $id_utilities and a.flag_id = true order by a.utility_periode desc limit 1";

        $query2 = $this->db->query($qq2);
        
        $get_schedule = $this->db->query("select a.id, coalesce(a.kode_meter ,mm.kode) as kode_meter, a.end_meter as last_end_meter, a.id_meter from th_schedule_tagihan a left join m_meter mm on a.id_meter = mm.id where a.tipe = 'UTILITIES' and a.utility_periode = '$pperiode' and a.id_unit = $id_unit and a.id_service = $id_utilities and a.flag_id = true order by a.id desc limit 1");
        // $result = api('POST', 'utility_record/cariUtilities', $post);
        $result = new \stdClass();
        if ($query2) {
            if ($query2->getNumRows() > 0) {
                $result->status = true;
                $result->msg = 'Data Found';
                $result->query = $query2->getRow();
                $result->dt_schedule = $get_schedule->getRow();
                $result->id_schedule = $get_schedule->getRow()->id;
            } else {
                if($get_schedule->getNumRows() > 0){
                    $qq1 = "select thu.meter_start as last_end_meter, mm.kode as kode_meter, thu.id_meter from th_handover_agreement a
                left join td_handover_utilities thu on a.id=thu.id_header
                left join m_meter mm on thu.id_meter = mm.id
                where a.id_unit=$id_unit and thu.id_utilities = $id_utilities";
                $query1 = $this->db->query($qq1);
                if ($query1->getNumRows() > 0) {
                    $result->status = true;
                    $result->msg = 'Data Found';
                    $result->query = $query1->getRow();
                    $result->id_schedule = $get_schedule->getRow()->id;
                } else {
                    $result->status = false;
                    $result->msg = 'End meter tidak ada';
                    $result->query = NULL;
                    $result->lq = $this->db->getLastQuery();
                }
                }else{
                    $result->status = false;
                    $result->msg = 'Schedule penagihan unit ini tidak ada';
                    $result->query = NULL;
                }
                
            }
        } else {
            // $this->response(['status' => false, 'msg' => 'Data Not Found', 'data' => null];
            $result->status = false;
            $result->msg = 'Schedule untuk tagihan unit ini tidak ada';
            $result->query = NULL;
            $result->lq = $this->db->getLastQuery();
        }
        return $this->response->setJSON($result);
    }

    public function meterIDListUtilityRecord()
    {
        // print_r($this->request->getPost());
        $post = $this->request->getPost();
        $fp = array('draw' => $post['draw'], 'start' => $post['start'], 'search' => $post['search'], 'length' => $post['length']);
        $result = api_json('POST', 'utility_record/meterIDListUtilityRecord', $fp);
        $callback = array(
            'draw' => $this->request->getPost('draw'), // Ini dari datatablenya    
            'recordsTotal' => $result->count_all,
            'recordsFiltered' => $result->count_all,
            'data' => $result->data
        );
        return $this->response->setJSON($callback);
    }
    public function getEdit()
    {
        $id = $this->request->getPost('id');
        $fp = array('id' => $id);
        $res = api('POST', 't_utility_record/getEdit', $fp);
        return $this->response->setJSON($res);
    }
    //  function edit($id){
    //     if($this->akses->can_edit == 1){
    //     $fp = array('id' => $id);
    //     $res = api('POST','utility_record/getEdit',$fp);
    //     // print_r($res);die();
    //     $data = array('idh' => $id, 'dth' => $res->data);
    //     // 'detail' => $res->detail
    //     //       print_r($detail);die();
    //     $this->template('pages/utility_record/edit',$data);    
    //     }
    // }
    public function viewDetail($id)
    {
        $db2 = Database::connect('websec');
        $q = "select mu.kode_unit, mu2.nama as nama_util, coalesce(tur.kode_meter, mm.kode) as kodemeter ,tur.* from t_utility_record tur left join m_unit mu on tur.id_unit = mu.id left join m_meter mm on tur.id_meter=mm.id left join m_utilities mu2 on tur.id_utilities = mu2.id where tur.id=$id";

        $q = "select a.id, mu.kode_unit , mu2.nama as nama_util, coalesce(mm.kode, a.kode_meter) as kodemeter, a.utility_periode as periode , a.start_meter , a.end_meter, a.status, ti.no_invoice, a.created_user, a.updated_user, a.created_date, a.updated_date, a.foto_meter from th_schedule_tagihan a left join m_unit mu on a.id_unit = mu.id  left join m_utilities mu2 on a.id_service = mu2.id and a.tipe = 'UTILITIES' left join m_meter mm on a.id_meter = mm.id left join td_invoice ti on a.id_invoice = ti.id and ti.flag_id where a.tipe = 'UTILITIES' and a.flag_id = true and a.utility_periode is not null and a.id = $id";
        $x = $this->db->query($q);
        $id_created = $x->getRow()->created_user;
        $id_updated = $x->getRow()->updated_user;
        if (!empty($id_created)) {
            $user_created = $db2->query("select * from t_user where id = " . $id_created);
            if($user_created->getNumRows() > 0){
                $user_created = $user_created->getRow()->username;
            }else{
                $user_created = NULL;
            }
        } else {
            $user_created = NULL;
        }
        if (!empty($id_updated)) {
            $user_updated = $db2->query("select * from t_user where id = " . $id_updated);
            if($user_updated->getNumRows() > 0){
                $user_updated = $user_updated->getRow();
            }else{
                $user_updated = NULL;
            }
        } else {
            $user_updated = NULL;
        }

        $ret = new \stdClass();
        $ret->data = $x->getRow();
        $ret->user_create = $user_created;
        return $this->response->setJSON($ret);
    }
    public function edit($id)
    {
        if ($this->akses->can_edit == 1) {
            $res = $this->model->getEdit($id);
            $data = array('idh' => $id, 'dth' => $res->data);
            $data['droplist_building'] = api('POST','building/dropList',NULL);
            $data['droplist_utilities'] = api('POST','utility_record/dropListUtilities',NULL);
            $data['droplist_meterid'] = api('POST','meterid/dropListMeterID',NULL);
            $this->template('pages/utility_record/edit', $data);
        }
    }
    public function edit2($id)
    {
        if ($this->akses->can_edit == 1) {
            $fp = array('id' => $id);
            $res = api('POST', 'utility_record/getEdit', $fp);
            // print_r($res);die();
            $data = array('idh' => $id, 'dth' => $res->data);
            $data['akses'] = $this->akses;
            return $this->template('pages/utility_record/edit2', $data);
        }
    }
    // function updateData(){
    //     $id = $this->request->getPost('id');
    //     $fp = array( 'updated_date' => date('Y-m-d H:i:s'),'updated_user' => session()->get('id_user'),'nama' => $this->request->getPost('nama'),'id' => $id);
    //     $res = api('POST','tipe_tenant/updateData',$fp);
    //     return $this->response->setJSON($res);
    // }

    public function updateData()
    {
        $id = $this->request->getPost('id');
        $fp = $this->request->getPost();
        $time = date('YmdHis');
        $unit =  $this->request->getPost('id_unit');

        $data = array('updated_user' => session()->get('id_user'), 'updated_date' => date('Y-m-d H:i:s'), 'id_unit' => $this->request->getPost('id_unit'), 'id_service' => $this->request->getPost('id_utilities'), 'periode' => $this->request->getPost('periode'), 'end_meter' => $this->request->getPost('end_meter'), 'id_meter' => $this->request->getPost('id_meter'), 'kode_meter' => $this->request->getPost('kode_meter'),'start_meter' => $this->request->getPost('start_meter'),'status' => 'EDITED');
        // print_r($data);die;
        $upload_path = FCPATH . 'dokumen/utility_record/';
        $newPhoto = $this->uploadMeterPhoto((int) $unit);
        $data['foto_meter'] = $newPhoto ?? (string) $this->request->getPost('foto_file_old');
        $query = $this->db->query("SELECT * from th_schedule_tagihan where id=$id and flag_id = true");
        $foto_old = $query->getRow()->foto_meter;
        if ($newPhoto !== null && ! empty($foto_old) && is_file($upload_path . $foto_old)) {
            @unlink($upload_path . $foto_old);
        } elseif ($newPhoto === null && empty($data['foto_meter'])) {
            $data['foto_meter'] = $foto_old;
        }

        $res = $this->model->updateData($id,$data);
        return $this->response->setJSON($res);
    }

    public function updateData2()
    {
        $id = $this->request->getPost('id');
        $fp = $this->request->getPost();
        $time = date('YmdHis');
        $unit =  $this->request->getPost('id_unit');
        // print_r($fp);die;
        // $upload_path = FCPATH . 'dokumen/utility_record/';
        // $config['upload_path'] = $upload_path;
        // $config['allowed_types'] = 'jpg|jpeg'; 
        // $config['file_name'] = $unit . '-' . $time;
        // $config['max_width'] = 0; 
        // $config['max_height'] = 0; 


        // // Cek apakah ada file yang diupload
        // if ($this->upload->do_upload('foto_file')) {
        //     $file_data = $this->upload->data();
        //     $file_name = $file_data['file_name'];

        //     // Cek apakah perlu melakukan resize
        //     if ($file_data['file_size'] > 500) {
        //         $this->resizeImage($file_data['full_path']);
        //     }
        //     $fp['file_name'] = $file_name;
        // } else {
        //     $fp['file_name'] = $this->request->getPost('foto_file_old');
        // }
        // $query = $this->db->query("SELECT * from t_utility_record where id=$id and flag_id = true");
        // $foto_old = $query->getRow()->foto_meter;
        // // Cek apakah file_name diisi, jika ya, hapus foto lama
        // if (!empty($fp['file_name'])) {
        //     // Hapus foto lama
        //     if (!empty($foto_old)) {
        //         unlink($upload_path . $foto_old);
        //     }
        // } else {
        //     $fp['file_name'] = $foto_old;
        // }

        // $res = api_json('POST', 'utility_record/updateData', $fp);
        $data = array('id_unit' => $this->request->getPost('id_unit'), 'end_meter' => $this->request->getPost('end_meter'), 'start_meter' => $this->request->getPost('start_meter'));
        if (!empty($this->request->getPost('periode'))) {
            $data['periode'] = $this->request->getPost('periode');
        }
        $x = $this->db->table('t_utility_record')->where('id', (int) $this->request->getPost('id'))->update($data);
        $res = new \stdClass();
        if ($x) {
            $res->status = true;
            $res->msg = 'Success';
        } else {
            $res->status = false;
            $res->msg = 'failed';
        }
        return $this->response->setJSON($res);
    }

    public function hapusData()
    {
        $id = $this->request->getPost('id');
        $fp = array('id' => $id, 'updated_user' => session()->get('id_user'), 'updated_date' => date('Y-m-d H:i:s'), 'flag_id' => false);
        $res = api('POST', 'utility_record/hapusData', $fp);
        return $this->response->setJSON($res);
    }

    public function approveUtility()
    {
        $ret = array('status' => false, 'msg' => '');
        if (md5($this->request->getPost('pin')) == session()->get('pin')) {
            $fp = array('status' => 'DONE' ,'approved_date' => date('Y-m-d H:i:s'), 'approved_user' => session()->get('id_user'));
            $res = $this->model->updateData($this->request->getPost('id'), $fp);
            if($res == true){
                $ret['status'] = true;
                $ret['msg'] = 'Approved berhasil';
            }else{
                $ret['status'] = false;
                $ret['msg'] = 'Approved Gagal';
            }
        } else {
            $ret['msg'] = 'Pin Anda Salah';
        }

        return $this->response->setJSON($ret);
    }
    public function cekPin()
    {
        if (md5($this->request->getPost('pin')) == session()->get('pin')) {
            $ret = array('status' => true, 'msg' => 'Pin Benar');
        } else {
            $ret = array('status' => false, 'msg' => 'Pin Anda Salah');
        }
        return $this->response->setJSON($ret);
    }
    public function rejectUtilityRecord()
    {

        $ret = new \stdClass();
        $ret->status = false;
        $ret->msg = '';
        if (md5($this->request->getPost('pin')) == session()->get('pin')) {

            $data = array('status' => 'REJECTED','ket_reject' => $this->request->getPost('keterangan'),'updated_date' => date('Y-m-d H:i:s'),'updated_user' => session()->get('id_user'));

            $query = $this->db->table('th_schedule_tagihan')->where('id', (int) $this->request->getPost('id_reject'))->update($data);
            if($query){
                $ret->status = true;
                $ret->msg = 'reject success';
            }else{
                $ret->status = false;
                $ret->msg = 'reject failed';
            }
        } else {
            $ret->msg = 'Pin Anda Salah';
        }
        return $this->response->setJSON($ret);
    }
    public function softDelete()
    {
        if ($this->akses->can_delete == 1) {
            $id = $this->request->getPost('id');
            $fp = array(
                'id' => $id,
                'updated_user' => session()->get('id_user'),
                'updated_date' => date('Y-m-d H:i:s')
            );
            $response = api('POST', 'utility_record/softDelete', $fp);
            return $this->response->setJSON($response);
        } else {
            $res = new \stdClass();
            $res->status = false;
            $res->msg = $this->msg_notauth;
            return $this->response->setJSON($res);
        }
    }


    public function print_dokumen($save = '')
    {

        // dd($this->request->getPost('cetak'));
        // $result = "SELECT a.*, b.kode_unit, c.nama as nama_uti lities, d.kode as id_meter from t_utility_record a left join m_unit b on a.id_unit=b.id left join m_utilities c on a.id_utilities=c.id left join m_meter d on a.id_meter=d.id where a.flag_id=true order by b.kode_unit asc";
        $result = "select a.id_unit, b.kode_unit from t_utility_record a left join m_unit b on a.id_unit = b.id left join m_utilities c on a.id_utilities = c.id left join m_meter d on  a.id_meter = d.id where a.flag_id = true group by a.id_unit, b.kode_unit order by b.kode_unit asc";
        $dtresult = $this->db->query($result);
        $dt['data'] = $dtresult->getResult();

        $body = view('pages/utility_record/template_utility_record', $dt);
        if ($save === 'print') {
            $datetime = date('YmdHis');
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="Utility Record Template ' . $datetime . '.xls"')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0')
                ->setBody($body);
        }

        return $this->response->setBody($body);
    }

    public function print_template()
    {

        $xper = explode('-', $this->request->getPost('pperiode_template'));
        $tgl_periode = $xper[1] . '-' . $xper[0] . '-01';
        //    date('Y-m', strtotime($today. ' + '.'6'.' days')).'-'.str_pad($tgl_jtempo,2,"0",STR_PAD_LEFT);
        //ngambil start meter dari end meter sebelumnya
        $bulan_periode = date('m', strtotime($tgl_periode . ' - 1 month'));
        $tahun_periode = date('Y', strtotime($tgl_periode . ' - 1 month'));
        
        $cut_off_date_now = $xper[1] . '-' . $xper[0] . '-20';
        $bulan_now = date('m', strtotime($tgl_periode));
        $tahun_now = date('Y', strtotime($tgl_periode));
        $id_utilities = $this->request->getPost('print_template_id_utilities');

        $result = "select a.id as id_data, a.id_unit, mu.kode_unit , coalesce(mm.id,0) as id_meter, coalesce(mm.kode, a.kode_meter, tst.kode_meter) as kode_meter, coalesce(a.start_meter,tst.end_meter,thu.meter_start) as start_meter , a.end_meter from th_schedule_tagihan a join m_unit mu on a.id_unit = mu.id and mu.is_excaption = false
        left join m_tenant mt on a.id_tenant = mt.id 
        left join th_handover_agreement tha on a.id_bast = tha.id
        left join td_handover_utilities thu on tha.id = thu.id_header and thu.flag_id = true
        left join ( SELECT DISTINCT ON (id_unit, id_service, id_tenant) * FROM th_schedule_tagihan WHERE flag_id = TRUE
        AND tipe = 'UTILITIES' AND EXTRACT(YEAR FROM utility_periode) = $tahun_periode AND EXTRACT(MONTH FROM utility_periode) = ".intval($bulan_periode)." ORDER BY id_unit, id_service, id_tenant, id DESC) tst on a.id_unit = tst.id_unit and tst.flag_id = true and a.id_service = tst.id_service and tst.tipe = 'UTILITIES'
        left join m_meter mm on coalesce(a.id_meter,tst.id_meter) = mm.id
        where a.flag_id = true and a.id_service = $id_utilities and a.tipe = 'UTILITIES' and extract(year from a.utility_periode) = $tahun_now 
        and extract(month from a.utility_periode) = ".intval($bulan_now);
 
        $dtresult = $this->db->query($result);
        $dt['data'] = $dtresult->getResult();

        $body = view('pages/utility_record/template_utility_record', $dt);
        if ($this->request->getPost('cetak') === 'print') {
            $datetime = date('YmdHis');
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="Utility Record Template ' . $datetime . '.xls"')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0')
                ->setBody($body);
        }

        return $this->response->setBody($body);
    }


    public function print_data()
    {
        $where = '';
        // $data_meterrange = get_meterrange_inarray();
        // hitung_meterrange($data_meterrange,1, 5); 
        $periode = date('Y-m-d', strtotime('01-' . $this->request->getPost('speriode')));
        if (!empty($this->request->getPost('speriode'))) {
            $bulan_periode = intval(date('m', strtotime($periode)));
            $tahun_periode = intval(date('Y', strtotime($periode)));
            $where .= " and extract(year from a.utility_periode) = $tahun_periode and extract(month from a.utility_periode) = $bulan_periode ";
        }

        $q = "select a.utility_periode , a.periode, mu.kode_unit , mt.nama as nama_tenant, mu2.nama as nama_tagihan,
        a.periode_start , a.periode_end, a.start_meter , a.end_meter , mu2.nominal as tarif, mu2.abodemen, a.end_meter - a.start_meter as total_pemakaian, round((a.end_meter - a.start_meter) * mu2.nominal) as amount, round((a.end_meter - a.start_meter) * mu2.nominal) + mu2.abodemen as total_amount,
        mt2.nama as nama_owner, a.deskripsi as keterangan
        from th_schedule_tagihan a 
        left join m_tenant mt on a.id_tenant = mt.id
        left join m_unit mu on a.id_unit = mu.id
        left join m_utilities mu2 on a.id_service = mu2.id and a.tipe = 'UTILITIES'
        left join m_meter mm on a.id_meter = mm.id 
        left join th_handover_agreement tha on a.id_bast = tha.id
        left join m_tenant mt2 on tha.id_owner = mt2.id
        where a.flag_id = true $where ";

        $x = $this->db->query($q);
        $dt['data'] = $x->getResult();
        // dd($dt['data']);
        $period = date('F Y', strtotime($periode));
        $body = view('pages/utility_record/util_toinv_list', $dt);
        if ($this->request->getPost('cetak') === 'print') {
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="Utility Record - ' . $period . '.xls"')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0')
                ->setBody($body);
        }

        return $this->response->setBody($body);
    }


    public function imp_temp_old()
    {
        $file = $this->request->getFile('data');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            session()->setFlashdata('gagal', 'Gagal Upload Data');
            return redirect()->to(site_url('utility_record'));
        }

        $extension = strtolower($file->getClientExtension());
        if ($extension !== 'csv') {
            session()->setFlashdata('error', 'Bukan File csv');
            return redirect()->to(site_url('utility_record'));
        }

        $startDate   = (string) $this->request->getPost('start_date');
        $endDate     = (string) $this->request->getPost('end_date');
        $utility     = (int) $this->request->getPost('utility');
        $periode     = date('Y-m-d', strtotime('01-' . (string) $this->request->getPost('uperiode')));
        $createdUser = (int) session()->get('id_user');
        $createdDate = date('Y-m-d H:i:s');

        $folder = FCPATH . 'dokumen/upload_utility_record';
        if (! is_dir($folder) && ! mkdir($folder, 0777, true) && ! is_dir($folder)) {
            session()->setFlashdata('gagal', 'Folder upload tidak dapat dibuat');
            return redirect()->to(site_url('utility_record'));
        }

        $fileName = $file->getRandomName();
        $file->move($folder, $fileName);
        $filePath = $folder . DIRECTORY_SEPARATOR . $fileName;

        $this->db->table('log_upload_utility_record')->insert([
            'created_user' => $createdUser,
            'created_date' => $createdDate,
            'namafile'     => $fileName,
            'id_utilities' => $utility,
        ]);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            session()->setFlashdata('gagal', 'File CSV tidak dapat dibaca');
            return redirect()->to(site_url('utility_record'));
        }

        $rows = [];
        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }
            $endMeter   = (float) str_replace(',', '.', trim((string) ($row[6] ?? 0)));
            $startMeter = (float) str_replace(',', '.', trim((string) ($row[5] ?? 0)));
            if ($endMeter < $startMeter) {
                session()->setFlashdata('error', 'Error: End Meter pada unit ' . ($row[2] ?? '') . ' Tidak Valid !');
                return redirect()->to(site_url('utility_record'));
            }
        }

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }

            $idSchedule = (int) ($row[0] ?? 0);
            $idUnit     = (int) ($row[1] ?? 0);
            $idMeter    = ! empty($row[3]) ? (int) $row[3] : 0;
            $kodeMeter  = trim((string) ($row[4] ?? ''));
            $startMeter = (float) str_replace(',', '.', trim((string) ($row[5] ?? 0)));
            $endMeter   = (float) str_replace(',', '.', trim((string) ($row[6] ?? 0)));
            $keterangan = trim((string) ($row[8] ?? ''));

            if ($idSchedule === 0) {
                $this->db->table('t_utility_record')->insert([
                    'id_unit'      => $idUnit,
                    'id_utilities' => $utility,
                    'created_date' => $createdDate,
                    'created_user' => $createdUser,
                    'end_meter'    => $endMeter,
                    'periode'      => $periode,
                    'keterangan'   => $keterangan,
                    'start_meter'  => $startMeter,
                    'start_date'   => $startDate,
                    'end_date'     => $endDate,
                    'kode_meter'   => $kodeMeter,
                    'id_meter'     => $idMeter,
                ]);
                continue;
            }

            $this->model->update_data($idSchedule, [
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_user' => $createdUser,
                'end_meter'    => $endMeter,
                'start_meter'  => $startMeter,
                'id_meter'     => $idMeter,
                'deskripsi'    => $keterangan,
                'periode_start'=> $startDate,
                'periode_end'  => $endDate,
                'kode_meter'   => $kodeMeter,
            ]);
        }

        session()->setFlashdata('success', 'Berhasil Upload Data');
        return redirect()->to(site_url('utility_record'));
    }

    public function imp_temp()
    {
        $file = $this->request->getFile('data');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            session()->setFlashdata('gagal', 'Gagal Upload Data');
            return redirect()->to(site_url('utility_record'));
        }

        if (strtolower($file->getClientExtension()) !== 'csv') {
            session()->setFlashdata('error', 'Bukan File csv');
            return redirect()->to(site_url('utility_record'));
        }

        $startDate   = (string) $this->request->getPost('start_date');
        $endDate     = (string) $this->request->getPost('end_date');
        $utility     = (int) $this->request->getPost('utility');
        $createdUser = (int) session()->get('id_user');
        $createdDate = date('Y-m-d H:i:s');

        $folder = FCPATH . 'dokumen/upload_utility_record';
        if (! is_dir($folder) && ! mkdir($folder, 0777, true) && ! is_dir($folder)) {
            session()->setFlashdata('gagal', 'Folder upload tidak dapat dibuat');
            return redirect()->to(site_url('utility_record'));
        }

        $fileName = $file->getRandomName();
        $file->move($folder, $fileName);
        $filePath = $folder . DIRECTORY_SEPARATOR . $fileName;

        $this->db->table('log_upload_utility_record')->insert([
            'created_user' => $createdUser,
            'created_date' => $createdDate,
            'namafile'     => $fileName,
            'id_utilities' => $utility,
        ]);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            session()->setFlashdata('gagal', 'File CSV tidak dapat dibaca');
            return redirect()->to(site_url('utility_record'));
        }

        $rows = [];
        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }
            $endMeter   = (float) str_replace(',', '.', trim((string) ($row[6] ?? 0)));
            $startMeter = (float) str_replace(',', '.', trim((string) ($row[5] ?? 0)));
            if ($endMeter < $startMeter) {
                session()->setFlashdata('error', 'Error: End Meter pada unit ' . ($row[2] ?? '') . ' Tidak Valid !');
                return redirect()->to(site_url('utility_record'));
            }
        }

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }
            $this->model->update_data((int) ($row[0] ?? 0), [
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_user' => $createdUser,
                'end_meter'    => (float) str_replace(',', '.', trim((string) ($row[6] ?? 0))),
                'start_meter'  => (float) str_replace(',', '.', trim((string) ($row[5] ?? 0))),
                'id_meter'     => (int) ($row[3] ?? 0),
                'deskripsi'    => trim((string) ($row[8] ?? '')),
                'periode_start'=> $startDate,
                'periode_end'  => $endDate,
                'kode_meter'   => trim((string) ($row[4] ?? '')),
            ]);
        }

        session()->setFlashdata('success', 'Berhasil Upload Data');
        return redirect()->to(site_url('utility_record'));
    }

    public function imp_temp_xlsx()
    {
        $file = $this->request->getFile('data');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            session()->setFlashdata('gagal', 'Gagal Upload Data');
            return redirect()->to(site_url('utility_record'));
        }

        if (strtolower($file->getClientExtension()) !== 'xlsx') {
            session()->setFlashdata('error', 'Bukan File xlsx');
            return redirect()->to(site_url('utility_record'));
        }

        $startDate   = (string) $this->request->getPost('start_date');
        $endDate     = (string) $this->request->getPost('end_date');
        $utility     = (int) $this->request->getPost('utility');
        $createdUser = (int) session()->get('id_user');
        $createdDate = date('Y-m-d H:i:s');

        $uploadPath = FCPATH . 'dokumen/upload_utility_record';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0777, true) && ! is_dir($uploadPath)) {
            session()->setFlashdata('gagal', 'Folder upload tidak dapat dibuat');
            return redirect()->to(site_url('utility_record'));
        }

        $fileName = $file->getRandomName();
        $file->move($uploadPath, $fileName);
        $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        libxml_use_internal_errors(true);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        libxml_clear_errors();

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $this->db->table('log_upload_utility_record')->insert([
            'created_user' => $createdUser,
            'created_date' => $createdDate,
            'namafile'     => $fileName,
            'id_utilities' => $utility,
        ]);

        for ($row = 2; $row <= $highestRow; $row++) {
            $endMeter   = (float) str_replace(',', '.', trim((string) $sheet->getCell('G' . $row)->getValue()));
            $startMeter = (float) str_replace(',', '.', trim((string) $sheet->getCell('F' . $row)->getValue()));
            if ($endMeter < $startMeter) {
                session()->setFlashdata('error', 'Error: End Meter pada unit ' . $sheet->getCell('C' . $row)->getValue() . ' Tidak Valid !');
                return redirect()->to(site_url('utility_record'));
            }
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $this->model->update_data((int) $sheet->getCell('A' . $row)->getValue(), [
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_user' => $createdUser,
                'end_meter'    => (float) str_replace(',', '.', (string) $sheet->getCell('G' . $row)->getValue()),
                'start_meter'  => (float) str_replace(',', '.', (string) $sheet->getCell('F' . $row)->getValue()),
                'id_meter'     => (int) $sheet->getCell('D' . $row)->getValue(),
                'deskripsi'    => (string) $sheet->getCell('I' . $row)->getValue(),
                'periode_start'=> $startDate,
                'periode_end'  => $endDate,
                'kode_meter'   => (string) $sheet->getCell('E' . $row)->getValue(),
                'status'       => 'DONE',
            ]);
        }

        session()->setFlashdata('success', 'Berhasil Upload Data');
        return redirect()->to(site_url('utility_record'));
    }

    public function soft_delete(){
        $id = $this->request->getPost('id');
        $upd_data = array('flag_id' => false,'deleted_user' => session()->get('id_user'),'deleted_date' => date('Y-m-d H:i:s'));
       $save = $this->model->update_data($id, $upd_data);
       if($save){
        $return = array('status' => true,'msg' => 'Hapus data berhasil');
       }else{
        $return = array('status' => false,'msg' => 'Hapus data gagal');
       }
       
       return $this->response->setJSON($return);
    }
    public function rejected_data(){
        $id = $this->request->getPost('id');
        $upd_data = array('status' => 'REJECTED','deleted_user' => session()->get('id_user'),'deleted_date' => date('Y-m-d H:i:s'));
       $save = $this->model->update_data($id, $upd_data);
       if($save){
        $return = array('status' => true,'msg' => 'Hapus data berhasil');
       }else{
        $return = array('status' => false,'msg' => 'Hapus data gagal');
       }
       
       return $this->response->setJSON($return);
    }
}


/*

CREATE TABLE public.log_upload_utility_record (
	id bigserial NOT NULL,
	namafile text NULL,
	created_user int4 NULL,
	created_date timestamp NULL,
	id_utilities int4 NULL,
	CONSTRAINT log_upload_utility_record_pkey PRIMARY KEY (id)
);


*/
