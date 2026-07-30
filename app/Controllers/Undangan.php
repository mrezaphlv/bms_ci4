<?php

namespace App\Controllers;

use App\Models\Mundangan;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

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

        $fp['order'] = [
            'dir'    => $post['order'][0]['dir'] ?? ($post['order'] ?? 'desc'),
            'column' => $post['columns'][$post['order'][0]['column'] ?? 0]['field'] ?? ($post['sort'] ?? 'id'),
        ];

        $result = $this->mundangan->grid($fp);

        return $this->response->setJSON([
            'total' => $result['count_all'] ?? 0,
            'rows'  => $result['data'] ?? [],
        ]);
    }

    public function form()
    {
        if (($this->akses->can_create ?? 0) != 1) {
            return redirect()->to(site_url('undangan'))->with('error', 'Anda tidak memiliki akses menambah undangan.');
        }

        return $this->template('pages/konfirmasi_undangan/input', [
            'akses'  => $this->akses,
            'isEdit' => false,
        ] + $this->mundangan->getFormData());
    }

    public function edit(int $id)
    {
        if (($this->akses->can_edit ?? 0) != 1) {
            return redirect()->to(site_url('undangan'))->with('error', 'Anda tidak memiliki akses mengubah undangan.');
        }

        $agreement = $this->mundangan->getAgreement($id);
        if (! $agreement) {
            throw PageNotFoundException::forPageNotFound('Data undangan tidak ditemukan.');
        }

        return $this->template('pages/konfirmasi_undangan/input', [
            'akses'  => $this->akses,
            'isEdit' => true,
        ] + $this->mundangan->getFormData($id));
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

    public function save()
    {
        if (($this->akses->can_create ?? 0) != 1) {
            return redirect()->to(site_url('undangan'))->with('error', 'Anda tidak memiliki akses menambah undangan.');
        }

        $validated = $this->validateUndanganRequest();
        if ($validated !== true) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        try {
            [$header, $utilities, $charges, , ] = $this->buildUndanganPayload(false);
            $id = $this->mundangan->saveUndangan($header, $utilities, $charges, (int) session()->get('id_user'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('undangan/detail/' . $id))->with('success', 'Undangan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (($this->akses->can_edit ?? 0) != 1) {
            return redirect()->to(site_url('undangan'))->with('error', 'Anda tidak memiliki akses mengubah undangan.');
        }

        if (! $this->mundangan->getAgreement($id)) {
            throw PageNotFoundException::forPageNotFound('Data undangan tidak ditemukan.');
        }

        $validated = $this->validateUndanganRequest();
        if ($validated !== true) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        try {
            [$header, $utilities, $charges, $deletedUtilIds, $deletedChargeIds] = $this->buildUndanganPayload(true, $id);
            $saved = $this->mundangan->updateUndangan($id, $header, $utilities, $charges, $deletedUtilIds, $deletedChargeIds, (int) session()->get('id_user'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        if (! $saved) {
            return redirect()->back()->withInput()->with('error', 'Perubahan undangan gagal disimpan.');
        }

        return redirect()->to(site_url('undangan/detail/' . $id))->with('success', 'Undangan berhasil diperbarui.');
    }

    public function grid_sales_dlg()
    {
        return $this->easyuiGridResponse($this->mundangan->getSalesGrid($this->buildDialogGridParams()));
    }

    public function grid_owner_dlg()
    {
        return $this->easyuiGridResponse($this->mundangan->getOwnerGrid($this->buildDialogGridParams()));
    }

    public function grid_unit_dlg()
    {
        return $this->easyuiGridResponse($this->mundangan->getUnitGrid($this->buildDialogGridParams()));
    }

    protected function buildDialogGridParams(): array
    {
        $post = $this->request->getPost();
        $page  = max(1, (int) ($post['page'] ?? 1));
        $rows  = max(1, (int) ($post['rows'] ?? ($post['length'] ?? 10)));
        $start = isset($post['start']) ? (int) $post['start'] : (($page - 1) * $rows);

        $searchValue = '';
        if (isset($post['search']['value'])) {
            $searchValue = (string) $post['search']['value'];
        } elseif (isset($post['q'])) {
            $searchValue = (string) $post['q'];
        } elseif (isset($post['search_value'])) {
            $searchValue = (string) $post['search_value'];
        }

        $orderColumn = (string) ($post['sort'] ?? '');
        $orderDir    = (string) ($post['order'] ?? 'asc');

        if ($orderColumn === '' && isset($post['order'][0]['column'])) {
            $indexColOrder = $post['order'][0]['column'];
            $orderColumn   = (string) ($post['columns'][$indexColOrder]['name'] ?? $post['columns'][$indexColOrder]['data'] ?? 'id');
            $orderDir      = (string) ($post['order'][0]['dir'] ?? 'asc');
        }

        return [
            'page'   => $page,
            'draw'   => $post['draw'] ?? 0,
            'start'  => $start,
            'search' => ['value' => trim($searchValue)],
            'length' => $rows,
            'order'  => [
                'dir'    => strtolower($orderDir) === 'desc' ? 'desc' : 'asc',
                'column' => $orderColumn !== '' ? $orderColumn : 'id',
            ],
        ];
    }

    protected function lookupGridResponse(array $result)
    {
        $draw = $this->request->getPost('draw') ?? 0;

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['count_all'] ?? 0,
            'recordsFiltered' => $result['count_all'] ?? 0,
            'data'            => $result['data'] ?? [],
        ]);
    }

    protected function easyuiGridResponse(array $result)
    {
        return $this->response->setJSON([
            'total' => $result['count_all'] ?? 0,
            'rows'  => $result['data'] ?? [],
        ]);
    }

    public function cariMeterrange()
    {
        $idUtil = (int) $this->request->getPost('id_util');

        return $this->response->setJSON($this->mundangan->getMeterRangeByUtility($idUtil));
    }

    public function hitung_fee()
    {
        $idScharge = (int) $this->request->getPost('id_scharge');
        $idUnit    = (int) $this->request->getPost('id_unit');
        $data      = $this->mundangan->getChargeCalculation($idScharge, $idUnit);

        return $this->response->setJSON([
            'status' => (bool) $data,
            'data'   => $data,
            'unit'   => $data->unit ?? null,
        ]);
    }

    public function cariTarifPajak()
    {
        $id        = (int) $this->request->getPost('id');
        $hargaJual = (float) $this->request->getPost('harga_jual');
        $data      = $this->mundangan->getPajakTarif($id, $hargaJual);

        return $this->response->setJSON([
            'status' => (bool) $data,
            'msg'    => $data ? 'Data Found' : 'Data Not Found',
            'data'   => $data,
        ]);
    }

    public function previewNomor()
    {
        $tglUndangan = $this->parseDate((string) $this->request->getPost('tgl_undangan'));
        $idUnit      = (int) $this->request->getPost('id_unit');

        if ($tglUndangan === null || $idUnit <= 0) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Tanggal undangan dan unit harus dipilih lebih dulu.',
            ]);
        }

        $nomor = $this->mundangan->generateNoUndangan($tglUndangan, $idUnit);

        return $this->response->setJSON([
            'status'      => ! empty($nomor),
            'no_undangan' => $nomor,
        ]);
    }

    protected function validateUndanganRequest()
    {
        $rules = [
            'tgl_undangan' => 'required',
            'jam_undangan' => 'required',
            'id_owner'     => 'required|integer',
            'id_unit'      => 'required|integer',
            'id_sales'     => 'required|integer',
        ];

        return $this->validate($rules);
    }

    protected function buildUndanganPayload(bool $isEdit, ?int $id = null): array
    {
        $idOwner     = (int) $this->request->getPost('id_owner');
        $idUnit      = (int) $this->request->getPost('id_unit');
        $idSales     = (int) $this->request->getPost('id_sales');
        $tglUndangan = $this->parseDate((string) $this->request->getPost('tgl_undangan'));
        $jamUndangan = trim((string) $this->request->getPost('jam_undangan'));

        if ($tglUndangan === null) {
            throw new \RuntimeException('Format tanggal undangan tidak valid.');
        }

        if (! preg_match('/^\d{2}:\d{2}$/', $jamUndangan)) {
            throw new \RuntimeException('Format jam undangan tidak valid.');
        }

        $utilities = $this->request->getPost('util') ?? [];
        $charges   = $this->request->getPost('charge') ?? [];

        if (! is_array($utilities) || count($utilities) === 0) {
            throw new \RuntimeException('Minimal satu utilities harus diisi.');
        }

        if (! is_array($charges) || count($charges) === 0) {
            throw new \RuntimeException('Minimal satu charge harus diisi.');
        }

        $timestamp = $tglUndangan . ' ' . $jamUndangan . ':00';
        $nomor     = trim((string) $this->request->getPost('no_undangan'));

        if ($isEdit) {
            $existing = $this->mundangan->getAgreement((int) $id);
            if (! $existing) {
                throw new \RuntimeException('Data undangan tidak ditemukan.');
            }
            $nomor = $nomor !== '' ? $nomor : (string) $existing->no_undangan;
        }

        if ($nomor === '') {
            $nomor = (string) $this->mundangan->generateNoUndangan($tglUndangan, $idUnit);
        }

        if ($nomor === '') {
            throw new \RuntimeException('Nomor undangan gagal dibuat.');
        }

        $header = [
            'no_undangan' => $nomor,
            'id_owner'    => $idOwner,
            'id_unit'     => $idUnit,
            'id_sales'    => $idSales,
            'tgl_undangan'=> $timestamp,
            'status'      => $isEdit ? ((string) ($existing->status ?? 'NEW')) : 'NEW',
        ];

        if ($isEdit) {
            $header['updated_date'] = date('Y-m-d H:i:s');
            $header['updated_user'] = session()->get('id_user');
        } else {
            $header['created_date'] = date('Y-m-d H:i:s');
            $header['created_user'] = session()->get('id_user');
            $header['flag_id']      = true;
        }

        $deletedUtilIds   = array_filter(array_map('intval', $this->request->getPost('dutil_deleted') ?? []));
        $deletedChargeIds = array_filter(array_map('intval', $this->request->getPost('dcharge_deleted') ?? []));

        return [$header, $utilities, $charges, $deletedUtilIds, $deletedChargeIds];
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
            'tgl_ppjb'     => $this->parseDate($this->request->getPost('tgl_ppjb')),
            'no_ppjb'      => trim((string) $this->request->getPost('no_ppjb')) ?: null,
            'updated_date' => date('Y-m-d H:i:s'),
            'updated_user' => session()->get('id_user'),
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
