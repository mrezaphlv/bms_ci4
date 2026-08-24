<?php
namespace App\Controllers;

use Config\Database;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;


class Checklist_tenant extends MyController
{
    private string $vpage = 'checklist_tenant';
    protected BaseConnection $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect();
    }
    public function index()
    {
        if ($this->akses->can_view == 1) {
            return $this->template('pages/' . $this->vpage . '/vchecklist_tenant', ['akses' => $this->akses]);
        }
    }
    public function grid()
    {
        $post   = $this->request->getPost();
        $start  = max(0, (int) ($post['start'] ?? 0));
        $length = max(1, (int) ($post['length'] ?? 10));
        $search = strtolower((string) ($post['search']['value'] ?? ''));
        $search = $this->db->escapeString($search);
        $tb_checkbox = $post['tb_checkbox'] ?? [];

        $where = "";

        if (!empty($search)) {
            $where .= " AND (
                lower(a3.no_agreement) LIKE '%$search%' 
                OR lower(a.no_undangan) LIKE '%$search%' 
                OR lower(b.nama) LIKE '%$search%' 
                OR lower(mu.kode_unit) LIKE '%$search%'
            )";
        }

        if (!empty($tb_checkbox)) {
            $where_checkbox = [];
            foreach ($tb_checkbox as $val) {
                if ($val == 'NEW') {
                    $where_checkbox[] = "a.status = 'NEW'";
                }
                if ($val == 'DONE') {
                    $where_checkbox[] = "a.status = 'APPROVED'";
                }
            }
            if (!empty($where_checkbox)) {
                $where .= " AND (" . implode(' OR ', $where_checkbox) . ")";
            }
        } else {
            $where .= " AND a.status IN ('NEW','APPROVED')";
        }

        $orderIdx = (int) ($post['order'][0]['column'] ?? 0);
        $requestedOrder = (string) ($post['columns'][$orderIdx]['data'] ?? 'id');
        $allowedOrder = ['id', 'no_agreement', 'no_undangan', 'status', 'handover_date', 'order_date', 'nama_owner', 'kode_unit', 'id_ctenant', 'tipe_checklist'];
        $order_col = in_array($requestedOrder, $allowedOrder, true) ? $requestedOrder : 'id';
        $order_dir = strtolower((string) ($post['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $q_count = "
            SELECT COUNT(*) AS ctr
            FROM t_agreement a
            JOIN t_checklist a1 
                ON a.id = a1.id_agreement AND a1.tipe = 'ENGINEERING'
            LEFT JOIN t_checklist a2 
                ON a.id = a2.id_agreement AND a2.tipe = 'TENANT'
            LEFT JOIN th_handover_agreement a3 
                ON a.id = a3.id_agreement
            LEFT JOIN m_tenant b 
                ON a.id_owner = b.id
            LEFT JOIN m_unit mu 
                ON a.id_unit = mu.id
            LEFT JOIN (
                SELECT id_header 
                FROM td_handover_dokumen 
                GROUP BY id_header
            ) tdoc ON a3.id = tdoc.id_header
            WHERE a.flag_id = true 
            AND a.waktu_hadir IS NOT NULL
            $where
        ";

        $recordsTotal = $this->db->query($q_count)->getRow()->ctr;

        $q_data = "
            SELECT 
                a.id,
                a3.no_agreement,
                a.no_undangan,
                a.status,
                a3.handover_date,
                a.order_date,
                b.nama AS nama_owner,
                mu.kode_unit,
                a2.id AS id_ctenant,
                a2.tipe_checklist,
                a.id AS p_id,
                tdoc.id_header AS check_doc
            FROM t_agreement a
            JOIN t_checklist a1 
                ON a.id = a1.id_agreement AND a1.tipe = 'ENGINEERING'
            LEFT JOIN t_checklist a2 
                ON a.id = a2.id_agreement AND a2.tipe = 'TENANT'
            LEFT JOIN th_handover_agreement a3 
                ON a.id = a3.id_agreement
            LEFT JOIN m_tenant b 
                ON a.id_owner = b.id
            LEFT JOIN m_unit mu 
                ON a.id_unit = mu.id
            LEFT JOIN (
                SELECT id_header 
                FROM td_handover_dokumen 
                GROUP BY id_header
            ) tdoc ON a3.id = tdoc.id_header
            WHERE a.flag_id = true
            AND a.waktu_hadir IS NOT NULL
            $where
            ORDER BY $order_col $order_dir
            LIMIT $length OFFSET $start
        ";

        $data = $this->db->query($q_data)->getResult();

        return $this->response->setJSON([
            'draw'            => (int) ($post['draw'] ?? 0),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data'            => $data
        ]);
    }
    public function convert_array()
    {
        return $this->response->setJSON($this->request->getPost());
    }
    public function saveChecklist()
    {
        if (($this->akses->can_create ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses menyimpan checklist tenant.',
            ]);
        }

        $items = $this->request->getPost('chkitem') ?? [];
        $files = $this->request->getFiles();
        $idAgreement = (int) $this->request->getPost('id_agreement');
        $payloadItems = [];

        foreach ((array) $items as $i => $item) {
            $payloadItems[$i] = $item;
            $uploadedFile = $files['chkitem'][$i]['fotoitem'] ?? null;
            $payloadItems[$i]['fotoitem'] = $this->uploadFotoItem($idAgreement, (int) $i, $uploadedFile);
        }

        $params = [
            'created_user' => (int) session()->get('id_user'),
            'created_date' => date('Y-m-d H:i:s'),
            'fitem'        => $payloadItems,
            'id_agreement' => $idAgreement,
        ];

        $ret = api_json('POST', 'checklist_tenant/saveChecklist', $params);
        return $this->response->setJSON($ret);
    }

    private function uploadFotoItem(int $idChecklist, int $idx, ?UploadedFile $file): string
    {
        if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
            return '';
        }

        if ($file->getSize() > (5 * 1024 * 1024)) {
            return '';
        }

        $dir = FCPATH . 'dokumen/checklist/tenant/' . $idChecklist;
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            return '';
        }

        $extension = $file->getClientExtension();
        $name = $idx . 'fotoitem_' . $idChecklist . $idx . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        if ($extension !== '') {
            $name .= '.' . $extension;
        }

        $file->move($dir, $name);
        return $name;
    }

    public function detailUndangan($idchecklist){
        if($this->akses->can_view == 1){
                $result = api('POST', 'checklist_tenant/getDetailUndangan', array('id' => $idchecklist));
                // print_r($result);die();
                $data['status'] = $result->status;
                if($result->status == true){
                    $data['dthead'] = $result->data->head;
                    $data['dtutil'] = $result->data->util;
                    $data['dtcharge'] = $result->data->charge;
                    $data['citem'] = $result->data->citem;
                }
                $data['id'] = $idchecklist;
               $data['akses'] = $this->akses;
               return $this->template('pages/checklist_tenant/vdetail_undangan', $data); 
            } 
            
    }
    public function add_new($id = null)
    {
        if ($this->akses->can_create == 1) {
            $result = api('POST', 'checklist_tenant/addNew', array('id' => $id));
            $data['data'] = $result->data;
            $data['item'] = $result->item;
            $data['id_agreement'] = $id;
            $data['akses'] = $this->akses;
            return $this->template('pages/' . $this->vpage . '/input', $data);
        }
    }
    public function edit($id)
    {
        if ($this->akses->can_edit == 1) {
            $result = api('POST', 'checklist_tenant/editChecklist', array('id' => $id));
            $data['data'] = $result->data;
            $data['item'] = $result->item;
            $data['id_checklist'] = $id;
            $data['akses'] = $this->akses;
            return $this->template('pages/checklist_tenant/edit', $data);
        }
    }

    public function view($id)
    {
        if ($this->akses->can_edit == 1) {
            $result = api('POST', 'checklist_tenant/editChecklist', array('id' => $id));
            $data['data'] = $result->data;
            $data['item'] = $result->item;
            $data['id_checklist'] = $id;
            $data['akses'] = $this->akses;
            return $this->template('pages/checklist_tenant/viewChecklistTenant', $data);
        }
    }

    public function updateChecklist()
    {
        if (($this->akses->can_edit ?? 0) != 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses mengubah checklist tenant.',
            ]);
        }

        $items = $this->request->getPost('chkitem') ?? [];
        $files = $this->request->getFiles();
        $idChecklist = (int) $this->request->getPost('id_checklist');
        $payloadItems = [];
        $filesToDelete = [];

        foreach ((array) $items as $i => $item) {
            $payloadItems[$i] = $item;
            $uploadedFile = $files['chkitem'][$i]['fotoitem'] ?? null;
            $newFile = $this->uploadFotoItem($idChecklist, (int) $i, $uploadedFile);
            $payloadItems[$i]['fotoitem'] = $newFile;

            if ($newFile !== '' && ! empty($item['fotonow'])) {
                $filesToDelete[] = $item['fotonow'];
            }
        }

        $params = [
            'updated_user' => (int) session()->get('id_user'),
            'updated_date' => date('Y-m-d H:i:s'),
            'fitem'        => $payloadItems,
            'id_checklist' => $idChecklist,
        ];

        $ret = api_json('POST', 'checklist_tenant/updateChecklist', $params);

        if (($ret->status ?? false) === true) {
            $dir = FCPATH . 'dokumen/checklist/tenant/' . $idChecklist;
            foreach ($filesToDelete as $oldFile) {
                $path = $dir . '/' . basename((string) $oldFile);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }

        return $this->response->setJSON($ret);
    }

    private function downloadDokumen(string $filePath)
    {
        if ($filePath === '' || ! is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('The File does not exist.');
        }

        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }

    public function print_dokumen($jd, $id_checklist)
    {
        $res = api('POST', 'checklist_tenant/pilih_dokumen', array('id_checklist' => $id_checklist, 'jenis_dokumen' => $jd));

        // dd($res);

        $dir = FCPATH . 'dokumen/print/checklist_tenant';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // dd($dir);
        switch ($jd) {
            case 'bast':
                $template_choose = api('POST', 'hand_over/pilih_bast', array('id_checklist' => $id_checklist));

              
                $template_type = $template_choose->data;

                if ($template_type == 'LUNAS') {
                    $dt = $res->data;


                    $template_file = FCPATH . 'template_dokumen/dok_bast_template.docx';
                    $filename_new = 'dok_bast-' . $id_checklist . date('YmdHis');

                    $nominal_sc = floatval($res->tarif_service->nominal_sc);
                    $nominal_sf = floatval($res->tarif_service->nominal_sf);


                    $ipl = floatval($dt->luas_unit) * $nominal_sc * 3;
                    $sf = floatval($dt->luas_unit) * $nominal_sf * 3;
                    $total = $ipl + $sf;
                    $pajak = ($ipl + $sf) * persen_ppn();
                    $harga_materai = 20000;
                    $grand_total = $ipl + $sf + $pajak + $harga_materai;

                    $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($template_file);
                    $tempWord->setValue('hari_ini', day_teks(date('N')));
                    $tempWord->setValue('tgl_txt', terbilang(date('d')));
                    $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                    $tempWord->setValue('tahun_txt', terbilang(date('Y')));
                    $tempWord->setValue('tgltoday', date('d-m-Y'));
                    $tempWord->setValue('no_agreement', $dt->no_agreement);
                    $tempWord->setValue('nama_tenant', $dt->nama_tenant);
                    $tempWord->setValue('tempat_lahir', $dt->tempat_lahir);
                    $tempWord->setValue('tgl_lahir', $dt->tgl_lahir);
                    $tempWord->setValue('nik', $dt->nik);
                    $tempWord->setValue('npwp', $dt->npwp);
                    $tempWord->setValue('alamat_tenant', $dt->alamat_tenant);
                    $tempWord->setValue('tower', $dt->nama_building);
                    $tempWord->setValue('lantai', $dt->lantai);
                    $tempWord->setValue('no_unit', $dt->no_unit);
                    $tempWord->setValue('luas_unit', $dt->luas_unit);
                    $tempWord->setValue('tgl_ppjb', $dt->tgl_ppjb);
                    $tempWord->setValue('no_ppjb', $dt->no_ppjb);
                    $tempWord->setValue('tipe_unit', '');
                    $tempWord->setValue('nominal_ipl', number_format($ipl, 0, ",", "."));
                    $tempWord->setValue('sinking_fund', number_format($sf, 0, ",", "."));
                    $tempWord->setValue('nom_total', number_format($total, 0, ",", "."));
                    $tempWord->setValue('nom_ppn', number_format($pajak, 0, ",", "."));
                    $tempWord->setValue('harga_materai', number_format($harga_materai, 0, ",", "."));
                    $tempWord->setValue('grand_total', number_format($grand_total, 0, ",", "."));
                    $tempWord->saveAs($dir . '/' . $filename_new . '.docx');

                    $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . ' . $dir . '/' . $filename_new . '.docx';
                    $command2 = 'mv ' . FCPATH . $filename_new . '.pdf ' . $dir . '/' . $filename_new . '.pdf';

                    file_put_contents($dir . '/convert_pdf.bat', $command);
                    shell_exec($command . " > debug.log 2>&1");
                    shell_exec($command2 . " > debug2.log 2>&1");
                    unlink($dir . '/' . $filename_new . '.docx');
                    return $this->downloadDokumen($dir . '/' . $filename_new . '.pdf');
                } else {
                    $dt = $res->data;
                    $template_file = FCPATH . 'template_dokumen/perjanjian_pinjam_pakai_template.docx';
                    $filename_new = 'dok_pinjam_pakai-' . $id_checklist . date('YmdHis');

                    $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($template_file);
                    $tempWord->setValue('hari_ini', day_teks(date('N')));
                    $tempWord->setValue('tgl_txt', terbilang(date('d')));
                    $tempWord->setValue('bulan_txt', bulan_teks(date('m')));
                    $tempWord->setValue('tahun_txt', terbilang(date('Y')));
                    $tempWord->setValue('tgltoday', date('d-m-Y'));
                    $tempWord->setValue('no_undangan', $dt->no_agreement);
                    $tempWord->setValue('nama_tenant', $dt->nama_tenant);
                    $tempWord->setValue('tempat_lahir', $dt->tempat_lahir);
                    $tempWord->setValue('tgl_lahir', $dt->tgl_lahir);
                    $tempWord->setValue('nik', $dt->nik);
                    $tempWord->setValue('npwp', $dt->npwp);
                    $tempWord->setValue('alamat_tenant', $dt->alamat_tenant);
                    $tempWord->setValue('tower', $dt->nama_building);
                    $tempWord->setValue('lantai', $dt->lantai);
                    $tempWord->setValue('no_unit', $dt->no_unit);
                    $tempWord->setValue('luas', $dt->luas_unit);
                    $tempWord->setValue('tipe', '');
                    $handover_date = date('d-m-Y', strtotime($dt->handover_date));
                    $durasi_pinjam_pakai = durasi_pinjam_pakai();
                    $end_date = date('Y-m-d', strtotime($handover_date . ' + ' . $durasi_pinjam_pakai . ' months'));
                    // echo $end_date;die();
                    $tempWord->setValue('start_date', $handover_date);
                    $tempWord->setValue('end_date', date('d-m-Y', strtotime($end_date)));
                    $terbilang_start_date = terbilang(date('d', strtotime($dt->handover_date))) . ' ' . bulan_teks(date('m', strtotime($dt->handover_date))) . ' ' . terbilang(date('Y', strtotime($dt->handover_date)));
                    $terbilang_end_date = terbilang(date('d', strtotime($end_date))) . ' ' . bulan_teks(date('m', strtotime($end_date))) . ' ' . terbilang(date('Y', strtotime($end_date)));
                    $tempWord->setValue('terbilang_start_date', $terbilang_start_date);
                    $tempWord->setValue('terbilang_end_date', $terbilang_end_date);
                    $tempWord->saveAs($dir . '/' . $filename_new . '.docx');

                    $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . ' . $dir . '/' . $filename_new . '.docx';
                    $command2 = 'mv ' . FCPATH . $filename_new . '.pdf ' . $dir . '/' . $filename_new . '.pdf';

                    file_put_contents($dir . '/convert_pdf.bat', $command);
                    shell_exec($command . " > debug.log 2>&1");
                    shell_exec($command2 . " > debug2.log 2>&1");

                    return $this->downloadDokumen($dir . '/' . $filename_new . '.docx');
                }
            break;
            case 'tanda_terima':
                                // print_r($jns_template);die();
                $t = $res->data;
                $file_template = FCPATH.'template_dokumen/tanda_terima_template.docx';
                // dd($file_template);
                $namafilenew = 'dok_tanda_terima-'.$id_checklist.date('YmdHis');
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
                file_put_contents($dir . '/convert_pdf.bat', $command);
                shell_exec($command);
                shell_exec($command." > debug.log 2>&1");
                shell_exec($command2);
                shell_exec($command2." > debug2.log 2>&1");
                
                unlink($dir.'/'.$namafilenew.'.docx');
                return $this->downloadDokumen($dir.'/'.$namafilenew.'.pdf');
            break;
            case 'tata_tertib':
            // print_r($result);
            $dir = FCPATH.'dokumen/print/handover';
            if(!file_exists($dir)){
            mkdir($dir, 0777, true);
            }
            $t = $res->data;
            // print_r($t);die();
            $file_template = FCPATH.'template_dokumen/surat_penerimaan_tatib_template.docx';
            $namafilenew = 'dok_penerimaan_tatib-'.$id_checklist.date('YmdHis');
            
            $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
            $tempWord->setValue('nama_tenant', $t->nama_tenant);
            $tempWord->setValue('nik_tenant', $t->nik);
            $tempWord->setValue('tower', str_replace("Tower ","",$t->nama_building));
            $tempWord->setValue('lantai', $dt->lantai);
            $tempWord->setValue('no_unit', $dt->no_unit);
            $tempWord->setValue('tgl_bast', date('d-m-Y',strtotime($t->handover_date)));
            $tempWord->setValue('tgl_ttd', tglteks(date('Y-m-d')));
            $tempWord->saveAs($dir.'/'.$namafilenew.'.docx');
            
            $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
            $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
            // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
             file_put_contents($dir . '/convert_pdf.bat', $command);
            shell_exec($command2);
            shell_exec($command2." > debug2.log 2>&1");
            shell_exec($command." > debug.log 2>&1");
            shell_exec($command2." > debug2.log 2>&1");
            unlink($dir.'/'.$namafilenew.'.docx');
            return $this->downloadDokumen($dir.'/'.$namafilenew.'.pdf');  
            
            break;
            
            case 'checklist_tenant':
                $h = $res->dthead;
                $item = $res->ctenant;
                // dd($item);
                // dd($h);
                $dir = FCPATH.'dokumen/print/cheklist_tenant';
                $file_template = FCPATH.'template_dokumen/Surat_Checklist_Tenant_template.docx';
                $namafilenew = 'Dok_Checklist_Tenant'.str_replace("/", "", $res->dthead->kode_unit);
                // $dir = FPATCH.'template_dokumen/Surat_Checklist_Tenant_template.docx';

                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                $tempWord = new \PhpOffice\PhpWord\TemplateProcessor($file_template);
                $tempWord->setValue('tgltoday', date('d-m-Y'));
                $tempWord->setValue('lantai', $h->lantai);
                $tempWord->setValue('no_urut', $h->no_urut);
                $tempWord->setValue('nama', $h->nama);

                
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $table_section = $phpWord->addSection();
                $tableStyle = array(
                    'borderColor' => '006699',
                    'borderSize'  => 6,
                    'cellMargin'  => 50,
                    'width' => 100, 
                    'marginLeft' => 1000, 
                    'marginRight' => 1000, 
                );
                $firstRowStyle = ['bgColor' => '66BBFF'];
                $phpWord->addTableStyle('myTable', $tableStyle, $firstRowStyle);
                // $table_section->addText('Basic table', "rofl");
                $table = $table_section->addTable('myTable');

               // HEADER Table
               $table->addRow();
                $table->addCell(4000, ['bgColor' => 'D3D3D3'])->addText("ITEM", ['bold' => true]);
                $table->addCell(2000, ['bgColor' => 'D3D3D3'])->addText("KONDISI", ['bold' => true]);
                $table->addCell(4000, ['bgColor' => 'D3D3D3'])->addText("KETERANGAN", ['bold' => true]);

                $no = 1;
                foreach ($item as $val) {
                    $table->addRow();
                    if ($val->id_item > 0) {
                        $table->addCell(4000)->addText($val->nama_item);
                        
                    } else {
                        $table->addCell(6000, ['bgColor' => 'E6E6E6', 'gridSpan' => 1])->addText($val->nama_item, ['bold' => true]);
                    }
                    $table->addCell(2000)->addText($val->kondisi);
                    $table->addCell(4000)->addText($val->keterangan);
                    $no++;
                }

                $objWriter = new \PhpOffice\PhpWord\Writer\Word2007($phpWord);
		        $tableStr = $objWriter->getWriterPart('Document')->getTableAsText($table);

		        $tempWord->setComplexBlock('item', $table);
                $tempWord->saveAs($dir . '/' . $namafilenew . '.docx');
                // $tempWord->setValue('');
                $command = '/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$dir.'/'.$namafilenew.'.docx';
                 $command2 = 'mv '.FCPATH.$namafilenew.'.pdf '.$dir.'/'.$namafilenew.'.pdf';
                // $output = system('/usr/lib64/libreoffice/program/soffice --headless --convert-to pdf --outdir . '.$namafilenew.'.docx');
                // file_put_contents($dir . '/convert_pdf.bat', $command);
                // shell_exec($command);
                // shell_exec($command." > debug.log 2>&1");
                // shell_exec($command2);
                // shell_exec($command2." > debug2.log 2>&1");    

                // unlink($dir.'/'.$namafilenew.'.docx');
                
                return $this->downloadDokumen($dir.'/'.$namafilenew.'.docx');
            break;
            
        }
    }
}
