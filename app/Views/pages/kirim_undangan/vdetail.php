<?php
    $totalFee = 0;
    $totalAmount = 0;
    foreach ($dtcharge as $chargeRow) {
        $totalFee += (float) ($chargeRow->fee ?? 0);
        $totalAmount += (float) ($chargeRow->amount ?? 0);
    }

    $statusEmail = empty($dthead->id_agreement_email) ? 'Belum dikirim' : 'Sudah dikirim';
    $statusConfirm = empty($dthead->waktu_hadir) ? 'Belum konfirmasi' : 'Sudah konfirmasi';
    $isWakil = (string) ($dthead->diwakilkan ?? '') === '1';
    $confirmationText = '-';
    if (!empty($dthead->waktu_hadir)) {
        $confirmationText = date('d-m-Y H:i', strtotime((string) $dthead->waktu_hadir)) . ' - ' . ($isWakil ? 'Diwakilkan' : 'Tidak Diwakilkan');
    }
?>

<style>
    .detail-shell {
        padding: 18px 22px 28px;
        color: #24324a;
    }

    .detail-hero {
        background: linear-gradient(135deg, #4f5d7d 0%, #66769c 100%);
        color: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        margin-bottom: 18px;
        box-shadow: 0 12px 28px rgba(79, 93, 125, 0.18);
    }

    .detail-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .detail-hero-path {
        font-size: 12px;
        opacity: 0.88;
        margin-bottom: 8px;
    }

    .detail-hero-title {
        font-size: 28px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 8px;
    }

    .detail-hero-subtitle {
        font-size: 13px;
        opacity: 0.9;
        max-width: 720px;
    }

    .detail-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .detail-badge {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    .detail-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .detail-toolbar-left,
    .detail-toolbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .detail-card {
        background: #fff;
        border: 1px solid #dbe3f0;
        border-radius: 14px;
        padding: 18px 20px;
        margin-bottom: 18px;
        box-shadow: 0 10px 24px rgba(148, 163, 184, 0.08);
    }

    .detail-card-title {
        font-size: 18px;
        font-weight: 800;
        color: #334155;
        margin-bottom: 14px;
    }

    .detail-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 14px 22px;
    }

    .detail-info-item {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 10px;
        align-items: start;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }

    .detail-info-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .detail-info-value {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.5;
        word-break: break-word;
    }

    .detail-info-value.muted {
        color: #475569;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #eef2ff;
        color: #334155;
    }

    .status-pill.success {
        background: #e8f7ee;
        color: #177245;
    }

    .status-pill.warn {
        background: #fff4db;
        color: #9a6700;
    }

    .section-stack {
        display: grid;
        gap: 18px;
    }

    .table-card {
        overflow: hidden;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .read-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 12px;
    }

    .read-table th,
    .read-table td {
        border: 1px solid #e6ebf2;
        padding: 11px 12px;
        vertical-align: middle;
        line-height: 1.45;
        text-align: center;
    }

    .read-table th {
        background: #4f5d7d;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .read-table td.text-left,
    .read-table th.text-left {
        text-align: left;
    }

    .read-table .group-row td {
        background: #eef3fb;
        color: #334155;
        font-weight: 800;
    }

    .read-table tfoot td,
    .read-table tfoot th {
        background: #f8fafc;
        font-weight: 800;
    }

    .empty-state {
        padding: 18px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        color: #64748b;
        font-size: 13px;
        text-align: center;
    }

    .wakil-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 14px 22px;
    }

    .image-frame {
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 16px;
        text-align: center;
    }

    #gambarItemView {
        max-height: 60vh;
        max-width: 100%;
        border-radius: 10px;
    }

    @media (max-width: 991px) {
        .detail-info-grid,
        .wakil-grid {
            grid-template-columns: 1fr;
        }

        .detail-info-item {
            grid-template-columns: 1fr;
        }

        .detail-hero-title {
            font-size: 24px;
        }
    }
</style>

<script src="<?php echo base_url('assets/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="detail-shell">
    <div class="detail-hero">
        <div class="detail-hero-top">
            <div>
                <div class="detail-hero-path">Transaksi / Kirim Undangan / Detail</div>
                <div class="detail-hero-title"><?= esc($dthead->no_undangan ?? '-') ?></div>
                <div class="detail-hero-subtitle">Ringkasan detail undangan, status pengiriman email, konfirmasi kehadiran, checklist engineering, utilities, dan charge.</div>
            </div>
        </div>
        <div class="detail-badges">
            <div class="detail-badge">Owner: <?= esc($dthead->nama_owner ?? '-') ?></div>
            <div class="detail-badge">Unit: <?= esc($dthead->kode_unit ?? '-') ?></div>
            <div class="detail-badge">Building: <?= esc($dthead->nama_building ?? '-') ?></div>
            <div class="detail-badge">Email: <?= esc($statusEmail) ?></div>
            <div class="detail-badge">Konfirmasi: <?= esc($statusConfirm) ?></div>
        </div>
    </div>

    <div class="detail-toolbar">
        <div class="detail-toolbar-left">
            <button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;">
                <i class="bi bi-arrow-left-circle"></i>
            </button>
        </div>
        <div class="detail-toolbar-right">
            <?php if (empty($dthead->id_agreement_email)): ?>
                <button onclick="kirimEmail(<?php echo $id; ?>)" class="btn btn_warna1 btn_bentuk1"><i class="bi bi-send"></i> Send Email</button>
            <?php endif; ?>
            <button onclick="printFileEmail(<?php echo $id; ?>)" class="btn btn_warna1 btn_bentuk1"><i class="bi bi-printer"></i> Print</button>
            <?php if (!empty($dthead->file_ppjb)): ?>
                <a href="<?php echo site_url('kirim_undangan/download-file-ppjb/' . $id); ?>" class="btn btn_warna4 btn_bentuk1"><i class="bi bi-file-earmark-arrow-down"></i> Download File PPJB</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-title">Informasi Undangan</div>
        <div class="detail-info-grid">
            <div class="detail-info-item">
                <div class="detail-info-label">No Undangan</div>
                <div class="detail-info-value"><?= esc($dthead->no_undangan ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Tanggal Undangan</div>
                <div class="detail-info-value"><?= esc($dthead->tgl_undangan ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Owner</div>
                <div class="detail-info-value"><?= esc($dthead->nama_owner ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Email</div>
                <div class="detail-info-value"><?= esc($dthead->email_owner ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Tipe Tenant</div>
                <div class="detail-info-value"><?= esc($dthead->tipe_tenant ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Sales</div>
                <div class="detail-info-value"><?= esc($dthead->nama_sales ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Unit</div>
                <div class="detail-info-value"><?= esc($dthead->kode_unit ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Building</div>
                <div class="detail-info-value"><?= esc($dthead->nama_building ?? '-') ?></div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Status Email</div>
                <div class="detail-info-value">
                    <span class="status-pill <?= empty($dthead->id_agreement_email) ? 'warn' : 'success' ?>"><?= esc($statusEmail) ?></span>
                </div>
            </div>
            <div class="detail-info-item">
                <div class="detail-info-label">Confirmation</div>
                <div class="detail-info-value muted"><?= esc($confirmationText) ?></div>
            </div>
        </div>
    </div>

    <?php if ($isWakil): ?>
        <div class="detail-card">
            <div class="detail-card-title">Data Perwakilan</div>
            <div class="wakil-grid">
                <div class="detail-info-item">
                    <div class="detail-info-label">Nama Wakil</div>
                    <div class="detail-info-value"><?= esc($dthead->nama_wakil ?? '-') ?></div>
                </div>
                <div class="detail-info-item">
                    <div class="detail-info-label">NIK Wakil</div>
                    <div class="detail-info-value"><?= esc($dthead->nik_wakil ?? '-') ?></div>
                </div>
                <div class="detail-info-item">
                    <div class="detail-info-label">Tempat / Tgl Lahir</div>
                    <div class="detail-info-value">
                        <?= esc(($dthead->tempat_lahir_wakil ?? '-') . (!empty($dthead->tgl_lahir_wakil) ? ', ' . date('d-m-Y', strtotime((string) $dthead->tgl_lahir_wakil)) : '')) ?>
                    </div>
                </div>
                <div class="detail-info-item">
                    <div class="detail-info-label">Alamat Wakil</div>
                    <div class="detail-info-value"><?= esc($dthead->alamat_wakil ?? '-') ?></div>
                </div>
                <div class="detail-info-item">
                    <div class="detail-info-label">NPWP Wakil</div>
                    <div class="detail-info-value"><?= esc($dthead->npwp_wakil ?? '-') ?></div>
                </div>
                <div class="detail-info-item">
                    <div class="detail-info-label">Pekerjaan Wakil</div>
                    <div class="detail-info-value"><?= esc($dthead->pekerjaan_wakil ?? '-') ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="section-stack">
        <div class="detail-card table-card">
            <div class="detail-card-title">History Email</div>
            <div class="table-wrap">
                <table class="read-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($demail)): ?>
                            <?php foreach ($demail as $v): ?>
                                <tr>
                                    <td><?= esc(date('d-m-Y', strtotime((string) $v->created_date))) ?></td>
                                    <td><?= esc(date('H:i', strtotime((string) $v->created_date))) ?></td>
                                    <td>
                                        <?php if ($v->status_result == true): ?>
                                            <span style="color:#177245;font-weight:700;">Terkirim</span>
                                        <?php else: ?>
                                            <span style="color:#c2410c;font-weight:700;">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="empty-state">Belum ada riwayat email.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (count($citem) > 0): ?>
            <div class="detail-card table-card">
                <div class="detail-card-title">Checklist Engineering</div>
                <div class="table-wrap">
                    <table class="read-table">
                        <thead>
                            <tr>
                                <th class="text-left" width="25%">Item</th>
                                <th width="10%">Jumlah</th>
                                <th>Kondisi</th>
                                <th class="text-left">Keterangan</th>
                                <th width="18%">Foto Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citem as $aa): ?>
                                <?php if ((int) ($aa->segmen ?? 0) === 1): ?>
                                    <tr class="group-row tr_head">
                                        <td class="text-left" colspan="5"><span>-</span> <?= esc($aa->nama_kategori ?? '-') ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td class="text-left"><?= esc($aa->nama_item ?? '-') ?></td>
                                        <td><?= esc($aa->qty ?? '-') ?></td>
                                        <td><?= esc($aa->kondisi ?? '-') ?></td>
                                        <td class="text-left"><?= esc($aa->keterangan ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($aa->foto)): ?>
                                                <button onclick="viewPhotoEng('<?php echo esc($aa->foto, 'attr'); ?>')" type="button" class="btn btn_warna4 btn_bentuk1">View</button>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="detail-card table-card">
            <div class="detail-card-title">Utilities</div>
            <div class="table-wrap">
                <table class="read-table">
                    <thead>
                        <tr>
                            <th class="text-left" width="30%">Utilities</th>
                            <th class="text-left" width="30%">Meter Range</th>
                            <th class="text-left" width="30%">MeterID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dtutil)): ?>
                            <?php foreach ($dtutil as $ha): ?>
                                <tr>
                                    <td class="text-left"><?= esc($ha->nama_utilities ?? '-') ?></td>
                                    <td class="text-left"><?= esc($ha->nama_rangetype ?? '-') ?></td>
                                    <td class="text-left"><?= esc($ha->kode_meter ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="empty-state">Belum ada data utilities.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="detail-card table-card">
            <div class="detail-card-title">Charge</div>
            <div class="table-wrap">
                <table class="read-table">
                    <thead>
                        <tr>
                            <th class="text-left">Service</th>
                            <th class="text-left">Tax</th>
                            <th>Periode</th>
                            <th>Fee</th>
                            <th>Invoice Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dtcharge)): ?>
                            <?php foreach ($dtcharge as $ha): ?>
                                <tr>
                                    <td class="text-left"><?= esc($ha->nama_service ?? '-') ?></td>
                                    <td class="text-left"><?= esc($ha->nama_pajak ?? '-') ?></td>
                                    <td><?= esc($ha->periode ?? '-') ?></td>
                                    <td><?= esc(number_format((float) ($ha->fee ?? 0), 0, '.', '.')) ?></td>
                                    <td><?= esc(number_format((float) ($ha->amount ?? 0), 0, '.', '.')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">Belum ada data charge.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-left">TOTAL</th>
                            <th colspan="2"></th>
                            <th><?= esc(number_format($totalFee, 0, '.', '.')) ?></th>
                            <th><?= esc(number_format($totalAmount, 0, '.', '.')) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dlgViewFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border:0;border-radius:16px;">
            <div class="modal-body">
                <div class="image-frame">
                    <img id="gambarItemView">
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
