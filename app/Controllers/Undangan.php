<?php

namespace App\Controllers;

use App\Models\Mundangan;
use CodeIgniter\Exceptions\PageNotFoundException;

class Undangan extends MyController
{
    protected Mundangan $mundangan;

    public function __construct()
    {
        parent::__construct();
        $this->mundangan = new Mundangan();
    }

    public function index()
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Anda tidak memiliki akses ke modul Konfirmasi Undangan.');
        }

        return $this->template('pages/konfirmasi_undangan/vkonfirmasi_undangan', [
            'akses' => $this->akses,
        ]);
    }

    public function grid()
    {
        if (($this->akses->can_view ?? 0) != 1) {
        return $this->response->setStatusCode(403)->setJSON([
                'total'   => 0,
                'rows'    => [],
                'message' => 'Akses ditolak.',
            ]);
        }

        $post = $this->request->getPost();
        $page = max(1, (int) ($post['page'] ?? 1));
        $rows = max(1, (int) ($post['rows'] ?? ($post['length'] ?? 10)));
        $fp   = [
            'draw'        => $post['draw'] ?? 0,
            'start'       => $post['start'] ?? (($page - 1) * $rows),
            'search'      => $post['search'] ?? ['value' => $post['search_value'] ?? ''],
            'length'      => $rows,
            'tb_checkbox' => $post['tb_checkbox'] ?? [],
        ];

        $fp['order']   = [
            'dir'    => $post['order'][0]['dir'] ?? ($post['order'] ?? 'desc'),
            'column' => $post['columns'][$post['order'][0]['column'] ?? 0]['field'] ?? ($post['sort'] ?? 'id'),
        ];

        $result = $this->mundangan->grid($fp);

        return $this->response->setJSON([
            'total' => $result['count_all'] ?? 0,
            'rows'  => $result['data'] ?? [],
        ]);
    }

    public function detail(int $id)
    {
        if (($this->akses->can_view ?? 0) != 1) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $detail = $this->mundangan->getDetail($id);

        if (! $detail) {
            throw PageNotFoundException::forPageNotFound('Data undangan tidak ditemukan.');
        }

        return $this->template('pages/konfirmasi_undangan/vdetail', [
            'akses'    => $this->akses,
            'id'       => $id,
            'dthead'   => $detail->head,
            'dtutil'   => $detail->util,
            'dtcharge' => $detail->charge,
        ]);
    }

    public function cekPin()
    {
        $pin = (string) $this->request->getPost('pin');
        $ok  = md5($pin) === (string) session()->get('pin');

        return $this->response->setJSON([
            'status' => $ok,
            'msg'    => $ok ? 'Pin Benar' : 'Pin Anda Salah',
        ]);
    }

    public function approveUndangan()
    {
        if (($this->akses->can_approve ?? 0) != 1) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki hak approve.',
            ]);
        }

        $id  = (int) $this->request->getPost('id_undangan');
        $pin = (string) $this->request->getPost('pin');

        if (md5($pin) !== (string) session()->get('pin')) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Pin Anda Salah',
            ]);
        }

        $agreement = $this->mundangan->getAgreement($id);
        if (! $agreement) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Data undangan tidak ditemukan.',
            ]);
        }

        if (($agreement->status ?? '') !== 'NEW') {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Hanya undangan dengan status NEW yang bisa di-approve.',
            ]);
        }

        $fileName = $this->handlePpjbUpload($id, 'ppjb_file', $agreement->file_ppjb ?? null);
        if (is_array($fileName) && ($fileName['status'] ?? false) === false) {
            return $this->response->setJSON($fileName);
        }

        $data = [
            'status'        => 'APPROVED',
            'tgl_bayar'     => $this->parseDate($this->request->getPost('tgl_lunas')),
            'tgl_ppjb'      => $this->parseDate($this->request->getPost('tgl_ppjb')),
            'no_ppjb'       => trim((string) $this->request->getPost('no_ppjb')) ?: null,
            'updated_date'  => date('Y-m-d H:i:s'),
            'updated_user'  => session()->get('id_user'),
            'approved_date' => date('Y-m-d H:i:s'),
            'approved_user' => session()->get('id_user'),
        ];

        if (is_string($fileName)) {
            $data['file_ppjb'] = $fileName;
        }

        $saved = $this->mundangan->approveUndangan($id, $data);

        return $this->response->setJSON([
            'status' => $saved,
            'msg'    => $saved ? 'Approve undangan berhasil.' : 'Approve undangan gagal.',
        ]);
    }

    public function rejectUndangan()
    {
        if (($this->akses->can_approve ?? 0) != 1) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki hak reject.',
            ]);
        }

        $id  = (int) $this->request->getPost('id_reject');
        $pin = (string) $this->request->getPost('pin');

        if (md5($pin) !== (string) session()->get('pin')) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Pin Anda Salah',
            ]);
        }

        $notes = trim((string) $this->request->getPost('keterangan'));
        if ($notes === '') {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Keterangan reject wajib diisi.',
            ]);
        }

        $saved = $this->mundangan->rejectUndangan($id, [
            'status'       => 'REJECTED',
            'notes'        => $notes,
            'updated_date' => date('Y-m-d H:i:s'),
            'updated_user' => session()->get('id_user'),
        ]);

        return $this->response->setJSON([
            'status' => $saved,
            'msg'    => $saved ? 'Reject undangan berhasil.' : 'Reject undangan gagal.',
        ]);
    }

    public function submitPPJB()
    {
        if (($this->akses->can_edit ?? 0) != 1 && ($this->akses->can_approve ?? 0) != 1) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'msg'    => 'Anda tidak memiliki akses upload PPJB.',
            ]);
        }

        $id        = (int) $this->request->getPost('id_undangan');
        $agreement = $this->mundangan->getAgreement($id);

        if (! $agreement) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Data undangan tidak ditemukan.',
            ]);
        }

        $fileName = $this->handlePpjbUpload($id, 'ppjb_file', $agreement->file_ppjb ?? null);
        if (is_array($fileName) && ($fileName['status'] ?? false) === false) {
            return $this->response->setJSON($fileName);
        }

        $data = [
            'tgl_ppjb'      => $this->parseDate($this->request->getPost('tgl_ppjb')),
            'no_ppjb'       => trim((string) $this->request->getPost('no_ppjb')) ?: null,
            'updated_date'  => date('Y-m-d H:i:s'),
            'updated_user'  => session()->get('id_user'),
        ];

        if (is_string($fileName)) {
            $data['file_ppjb'] = $fileName;
        }

        $saved = $this->mundangan->submitPpjb($id, $data);

        return $this->response->setJSON([
            'status' => $saved,
            'msg'    => $saved ? 'Data PPJB berhasil disimpan.' : 'Data PPJB gagal disimpan.',
        ]);
    }

    public function getPpjb()
    {
        $id   = (int) $this->request->getPost('id_undangan');
        $data = $this->mundangan->getAgreement($id);

        return $this->response->setJSON([
            'status'   => (bool) $data,
            'msg'      => $data ? 'Data Found' : 'Data Not Found',
            'data'     => $data,
            'tgl_ppjb' => ($data && ! empty($data->tgl_ppjb)) ? date('d-m-Y', strtotime((string) $data->tgl_ppjb)) : '',
        ]);
    }

    public function view_file_ppjb(int $id)
    {
        $agreement = $this->mundangan->getAgreement($id);
        $path      = $this->resolvePpjbPath($agreement->file_ppjb ?? null);

        if (! $agreement || ! $path) {
            throw PageNotFoundException::forPageNotFound('File PPJB tidak ditemukan.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody((string) file_get_contents($path));
    }

    public function download_file_ppjb(int $id)
    {
        $agreement = $this->mundangan->getAgreement($id);
        $path      = $this->resolvePpjbPath($agreement->file_ppjb ?? null);

        if (! $agreement || ! $path) {
            throw PageNotFoundException::forPageNotFound('File PPJB tidak ditemukan.');
        }

        return $this->response->download($path, null);
    }

    protected function parseDate(?string $date): ?string
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        $formats = ['d-m-Y', 'Y-m-d'];
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed instanceof \DateTime) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    protected function handlePpjbUpload(int $id, string $fieldName, ?string $oldFile = null): string|array|null
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (strtolower((string) $file->getExtension()) !== 'pdf') {
            return [
                'status' => false,
                'msg'    => 'File PPJB harus berformat PDF.',
            ];
        }

        if ($file->getSizeByUnit('mb') > 5) {
            return [
                'status' => false,
                'msg'    => 'Ukuran file PPJB maksimal 5 MB.',
            ];
        }

        $dir = FCPATH . 'dokumen/ppjb/';
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $newName = 'dok_ppjb_' . $id . '_' . date('YmdHis') . '.pdf';
        $file->move($dir, $newName, true);

        if (! empty($oldFile)) {
            $oldPath = $dir . $oldFile;
            if (is_file($oldPath) && basename($oldPath) !== $newName) {
                @unlink($oldPath);
            }
        }

        return $newName;
    }

    protected function resolvePpjbPath(?string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $path = FCPATH . 'dokumen/ppjb/' . $filename;

        return is_file($path) ? $path : null;
    }
}
