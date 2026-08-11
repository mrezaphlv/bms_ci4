<style>
    .baris_head {
        margin-bottom: 0.5rem;
    }

    .label_head {
        font-weight: 900;
    }

    tr > th,
    tr > td {
        text-align: center;
    }

    tr.tr_head {
        cursor: pointer;
        background-color: rgba(73, 83, 113, 1);
        color: white;
    }

    #gambarItemView {
        max-height: 300px;
        max-width: 300px;
    }
</style>

<script src="<?php echo base_url('assets/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="judul_atas">
        <div>Transaksi / Kirim Undangan / Detail</div>
    </div>
    <button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;">
        <i class="bi bi-arrow-left-circle"></i>
    </button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display:inline-block;margin-bottom:20px;">
        <?php echo 'No Undangan: ' . $dthead->no_undangan; ?>
    </div>
    <br>

    <div style="text-align:right; margin-bottom:10px;">
        <?php if (empty($dthead->id_agreement_email)): ?>
            <button onclick="kirimEmail(<?php echo $id; ?>)" class="btn btn_warna1"><i class="bi bi-send"></i> Send Email</button>
        <?php endif; ?>
        <button onclick="printFileEmail(<?php echo $id; ?>)" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button>
        <?php if (!empty($dthead->file_ppjb)): ?>
            <a href="<?php echo site_url('kirim_undangan/download-file-ppjb/' . $id); ?>" class="btn btn_warna4"><i class="bi bi-file-earmark-arrow-down"></i> Download File PPJB</a>
        <?php endif; ?>
    </div>

    <div style="padding:0 20px 0;" class="row">
        <div class="col-md-6">
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">No Undangan:</div></div><div class="col-md-6 text-end"><?php echo $dthead->no_undangan; ?></div></div>
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Tgl Undangan:</div></div><div class="col-md-6 text-end"><?php echo $dthead->tgl_undangan; ?></div></div>
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Email:</div></div><div class="col-md-6 text-end"><?php echo $dthead->email_owner; ?></div></div>
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Tipe:</div></div><div class="col-md-6 text-end"><?php echo $dthead->tipe_tenant; ?></div></div>
        </div>
        <div class="col-md-6">
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Owner:</div></div><div class="col-md-6 text-end"><?php echo $dthead->nama_owner; ?></div></div>
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Sales:</div></div><div class="col-md-6 text-end"><?php echo $dthead->nama_sales; ?></div></div>
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Unit:</div></div><div class="col-md-6 text-end"><?php echo $dthead->kode_unit; ?></div></div>
            <div class="row baris_head"><div class="col-md-6"><div class="label_head">Building:</div></div><div class="col-md-6 text-end"><?php echo $dthead->nama_building; ?></div></div>
            <div class="row baris_head">
                <div class="col-md-6"><div class="label_head">Confirmation:</div></div>
                <div class="col-md-6 text-end">
                    <?php echo !empty($dthead->waktu_hadir) ? date('d-m-Y H:i', strtotime($dthead->waktu_hadir)) : ''; ?>
                    -
                    <?php
                    if ($dthead->diwakilkan == '1') {
                        echo 'Diwakilkan';
                    } elseif ($dthead->diwakilkan == '0') {
                        echo 'Tidak Diwakilkan';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($dthead->diwakilkan == '1'): ?>
        <div style="padding:0 20px 0;" class="row">
            <div class="col-md-6">
                <div class="row baris_head"><div class="col-md-6"><div class="label_head">Nama Wakil:</div></div><div class="col-md-6 text-end"><?php echo $dthead->nama_wakil; ?></div></div>
                <div class="row baris_head"><div class="col-md-6"><div class="label_head">NIK Wakil:</div></div><div class="col-md-6 text-end"><?php echo $dthead->nik_wakil; ?></div></div>
                <div class="row baris_head">
                    <div class="col-md-6"><div class="label_head">Tempat / tgl lahir Wakil:</div></div>
                    <div class="col-md-6 text-end">
                        <?php $tgl_lahir_wakil = !empty($dthead->tgl_lahir_wakil) ? date('d-m-Y', strtotime($dthead->tgl_lahir_wakil)) : null; ?>
                        <?php echo $dthead->tempat_lahir_wakil . ', ' . $tgl_lahir_wakil; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row baris_head"><div class="col-md-6"><div class="label_head">Alamat Wakil:</div></div><div class="col-md-6 text-end"><?php echo $dthead->alamat_wakil; ?></div></div>
                <div class="row baris_head"><div class="col-md-6"><div class="label_head">NPWP Wakil:</div></div><div class="col-md-6 text-end"><?php echo $dthead->npwp_wakil; ?></div></div>
                <div class="row baris_head"><div class="col-md-6"><div class="label_head">Pekerjaan Wakil:</div></div><div class="col-md-6 text-end"><?php echo $dthead->pekerjaan_wakil; ?></div></div>
            </div>
        </div>
    <?php endif; ?>

    <br>
    <div class="judul_h2">History Email</div>
    <div class="row">
        <table class="table thead_warna1" style="width:100%">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demail as $v): ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($v->created_date)); ?></td>
                        <td><?php echo date('H:i', strtotime($v->created_date)); ?></td>
                        <td><?php echo ($v->status_result == true) ? '<span style="color:green">Terkirim</span>' : '<span style="color:red">Failed</span>'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (count($citem) > 0): ?>
        <div style="margin-bottom:15px;margin-top:20px;" class="judul_h2">Checklist Engineering</div>
        <div class="row">
            <table class="table thead_warna1" style="width:100%">
                <thead>
                    <tr>
                        <th width="25%">Item</th>
                        <th width="10%">Jumlah</th>
                        <th>Kondisi</th>
                        <th>Keterangan</th>
                        <th width="20%">Foto Item</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citem as $aa): ?>
                        <?php if ($aa->segmen == 1): ?>
                            <tr class="tr_head"><td style="text-align:left;" colspan="5"><span>-</span> <?php echo $aa->nama_kategori; ?></td></tr>
                        <?php else: ?>
                            <tr>
                                <td><?php echo $aa->nama_item; ?></td>
                                <td><?php echo $aa->qty; ?></td>
                                <td><?php echo $aa->kondisi; ?></td>
                                <td><?php echo $aa->keterangan; ?></td>
                                <td>
                                    <?php if (!empty($aa->foto)): ?>
                                        <button onclick="viewPhotoEng('<?php echo $aa->foto; ?>')" type="button" class="btn btn_warna4 btn_bentuk1">View</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <br>
    <div class="judul_h2">Utilities</div>
    <div class="row">
        <table class="table thead_warna1" style="width:100%">
            <thead>
                <tr>
                    <th width="30%">Utilities</th>
                    <th width="30%">Meter Range</th>
                    <th width="30%">MeterID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dtutil as $ha): ?>
                    <tr>
                        <td><?php echo $ha->nama_utilities; ?></td>
                        <td><?php echo $ha->nama_rangetype; ?></td>
                        <td><?php echo $ha->kode_meter; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="judul_h2">Charge</div>
    <div class="row">
        <table class="table thead_warna1" style="width:100%">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Tax</th>
                    <th>Periode</th>
                    <th>Fee</th>
                    <th>Invoice Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dtcharge as $ha): ?>
                    <tr>
                        <td><?php echo $ha->nama_service; ?></td>
                        <td><?php echo $ha->nama_pajak; ?></td>
                        <td><?php echo $ha->periode; ?></td>
                        <td><?php echo number_format($ha->fee, 0, '.', '.'); ?></td>
                        <td><?php echo number_format($ha->amount, 0, '.', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th><h6 style="font-weight:bold; padding-top:6px;">TOTAL</h6></th>
                    <th colspan="2"></th>
                    <th><?php $totalFee = 0; foreach ($dtcharge as $ha) { $totalFee += $ha->fee; } echo number_format($totalFee, 0, '.', '.'); ?></th>
                    <th><?php $totalAmount = 0; foreach ($dtcharge as $ha) { $totalAmount += $ha->amount; } echo number_format($totalAmount, 0, '.', '.'); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="modal fade" id="dlgViewFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img id="gambarItemView">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const photoModal = new bootstrap.Modal(document.getElementById('dlgViewFoto'));

    $('.tr_head').click(function () {
        $(this).find('span').text(function (_, value) { return value === '-' ? '+' : '-'; });
        $(this).nextUntil('tr.tr_head').slideToggle(100);
    });

    $(document).ready(function () {
        $('#btnCancelAdd').click(function () {
            window.location = '<?php echo site_url('kirim_undangan'); ?>';
        });

        $('#dlgViewFoto').on('hidden.bs.modal', function () {
            $('#gambarItemView').attr('src', '');
        });
    });

    function viewPhotoEng(param) {
        var src = '<?php echo base_url('dokumen/checklist/engineering/' . $id_checklist); ?>/' + param;
        $('#gambarItemView').attr('src', src);
        photoModal.show();
    }

    function kirimEmail(id) {
        $.ajax({
            type: 'POST',
            data: { id: id },
            dataType: 'JSON',
            url: '<?php echo site_url('kirim_undangan/kirimEmail'); ?>',
            success: function (response) {
                if (response.status == true) {
                    Swal.fire({
                        icon: 'success',
                        title: response.msg || 'Email berhasil dikirim.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.msg || 'Gagal mengirim email.'
                    });
                }
            }
        });
    }

    function printFileEmail(id) {
        window.location = '<?php echo site_url('kirim_undangan/printFileEmail'); ?>/' + id;
    }
</script>
