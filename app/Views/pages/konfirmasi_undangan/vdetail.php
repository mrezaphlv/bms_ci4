<?php
    $statusColor = '#111827';
    if (($dthead->status ?? '') === 'APPROVED') {
        $statusColor = '#15803d';
    } elseif (($dthead->status ?? '') === 'REJECTED') {
        $statusColor = '#b91c1c';
    }

    $chargeFeeTotal = 0;
    $chargeAmountTotal = 0;
    foreach ($dtcharge as $charge) {
        $chargeFeeTotal += (float) ($charge->fee ?? 0);
        $chargeAmountTotal += (float) ($charge->amount ?? 0);
    }
?>

<style>
    .detail-wrap {
        padding: 18px 22px 28px;
    }
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .detail-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }
    .detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        padding: 18px;
        margin-bottom: 18px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(250px, 1fr));
        gap: 10px 24px;
    }
    .detail-item {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 8px;
    }
    .detail-label {
        font-weight: 700;
        color: #374151;
    }
    .detail-value {
        color: #111827;
    }
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin: 18px 0 10px;
    }
    .reject-note {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>

<div class="detail-wrap">
    <div class="detail-header">
        <div>
            <a href="<?= site_url('undangan') ?>" class="easyui-linkbutton" iconCls="icon-back">Kembali</a>
        </div>
        <div class="detail-title">Detail Konfirmasi Undangan</div>
        <div>
            <?php if (($dthead->status ?? '') === 'NEW' && (int) ($akses->can_approve ?? 0) === 1): ?>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="openRejectDialog()">Reject</a>
                <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="openApproveDialog()">Approve</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">No Undangan</div>
                <div class="detail-value"><?= esc($dthead->no_undangan ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tanggal Undangan</div>
                <div class="detail-value"><?= !empty($dthead->tgl_undangan) ? date('d-m-Y', strtotime((string) $dthead->tgl_undangan)) : '-' ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Owner</div>
                <div class="detail-value"><?= esc($dthead->nama_owner ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?= esc($dthead->email_owner ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tipe Tenant</div>
                <div class="detail-value"><?= esc($dthead->tipe_tenant ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Sales</div>
                <div class="detail-value"><?= esc($dthead->nama_sales ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Unit</div>
                <div class="detail-value"><?= esc($dthead->kode_unit ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Building</div>
                <div class="detail-value"><?= esc($dthead->nama_building ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value" style="font-weight:700;color:<?= $statusColor ?>;"><?= esc($dthead->status ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">No. PPJB</div>
                <div class="detail-value"><?= esc($dthead->no_ppjb ?? '-') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tanggal PPJB</div>
                <div class="detail-value"><?= !empty($dthead->tgl_ppjb) ? date('d-m-Y', strtotime((string) $dthead->tgl_ppjb)) : '-' ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">PPJB</div>
                <div class="detail-value">
                    <?php if (! empty($dthead->file_ppjb)): ?>
                        <a href="<?= site_url('undangan/view-file-ppjb/' . $id) ?>" target="_blank" class="easyui-linkbutton" iconCls="icon-search">View</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (($dthead->status ?? '') === 'REJECTED' && !empty($dthead->notes)): ?>
            <div class="reject-note">
                <strong>Keterangan Reject:</strong> <?= esc($dthead->notes) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="section-title">Utilities</div>
    <table class="easyui-datagrid" style="width:100%;" data-options="singleSelect:true,fitColumns:true">
        <thead>
            <tr>
                <th data-options="field:'nama_utilities',width:180">Utilities</th>
                <th data-options="field:'nama_rangetype',width:180">Meter Range</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dtutil as $row): ?>
                <tr>
                    <td><?= esc($row->nama_utilities ?? '-') ?></td>
                    <td><?= esc($row->nama_rangetype ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">Charge</div>
    <table class="easyui-datagrid" style="width:100%;" data-options="singleSelect:true,fitColumns:true">
        <thead>
            <tr>
                <th data-options="field:'nama_service',width:180">Service</th>
                <th data-options="field:'nama_pajak',width:150">Tax</th>
                <th data-options="field:'periode',width:100">Periode</th>
                <th data-options="field:'fee',width:120,align:'right'">Fee</th>
                <th data-options="field:'amount',width:120,align:'right'">Invoice Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dtcharge as $row): ?>
                <tr>
                    <td><?= esc($row->nama_service ?? '-') ?></td>
                    <td><?= esc($row->nama_pajak ?? '-') ?></td>
                    <td><?= esc($row->periode ?? '-') ?></td>
                    <td><?= number_format((float) ($row->fee ?? 0), 0, ',', '.') ?></td>
                    <td><?= number_format((float) ($row->amount ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;font-weight:700;">TOTAL</td>
                <td style="text-align:right;font-weight:700;"><?= number_format($chargeFeeTotal, 0, ',', '.') ?></td>
                <td style="text-align:right;font-weight:700;"><?= number_format($chargeAmountTotal, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php if (($dthead->status ?? '') === 'NEW' && (int) ($akses->can_approve ?? 0) === 1): ?>
<div id="detailDlgApprove" class="easyui-dialog" title="Approve Undangan" style="width:620px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#detailDlgApproveButtons'">
    <form id="detailFormApprove" enctype="multipart/form-data">
        <input type="hidden" name="id_undangan" value="<?= (int) $id ?>">
        <table style="width:100%;">
            <tr><td style="width:180px;padding:6px 0;">PIN</td><td><input class="easyui-passwordbox" name="pin" style="width:100%" required></td></tr>
            <tr><td style="padding:6px 0;">Tanggal Lunas</td><td><input class="easyui-datebox" name="tgl_lunas" style="width:100%" data-options="formatter:easyDateFormatter,parser:easyDateParser"></td></tr>
            <tr><td style="padding:6px 0;">No. PPJB</td><td><input class="easyui-textbox" name="no_ppjb" style="width:100%"></td></tr>
            <tr><td style="padding:6px 0;">Tanggal PPJB</td><td><input class="easyui-datebox" name="tgl_ppjb" style="width:100%" data-options="formatter:easyDateFormatter,parser:easyDateParser"></td></tr>
            <tr><td style="padding:6px 0;">Upload PPJB</td><td><input type="file" name="ppjb_file" accept=".pdf"></td></tr>
        </table>
    </form>
</div>
<div id="detailDlgApproveButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitDetailApprove()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#detailDlgApprove').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="detailDlgReject" class="easyui-dialog" title="Reject Undangan" style="width:560px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#detailDlgRejectButtons'">
    <form id="detailFormReject">
        <input type="hidden" name="id_reject" value="<?= (int) $id ?>">
        <table style="width:100%;">
            <tr><td style="width:180px;padding:6px 0;">PIN</td><td><input class="easyui-passwordbox" name="pin" style="width:100%" required></td></tr>
            <tr><td style="padding:6px 0;">Keterangan</td><td><input class="easyui-textbox" name="keterangan" style="width:100%;height:90px" data-options="multiline:true" required></td></tr>
        </table>
    </form>
</div>
<div id="detailDlgRejectButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitDetailReject()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#detailDlgReject').dialog('close')" style="width:90px">Batal</a>
</div>

<script>
    function easyDateFormatter(date) {
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return d + '-' + m + '-' + y;
    }

    function easyDateParser(s) {
        if (!s) return new Date();
        const parts = s.split('-');
        if (parts.length !== 3) return new Date();
        return new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));
    }

    function openApproveDialog() {
        $('#detailDlgApprove').dialog('open');
    }

    function openRejectDialog() {
        $('#detailDlgReject').dialog('open');
    }

    function submitDetailApprove() {
        const formData = new FormData(document.getElementById('detailFormApprove'));
        $.ajax({
            url: '<?= site_url('undangan/approve') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    $.messager.alert('Sukses', res.msg, 'info', function () {
                        window.location.reload();
                    });
                    return;
                }
                $.messager.alert('Gagal', res.msg || 'Approve gagal.', 'error');
            },
            error: function (xhr) {
                $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat approve.', 'error');
            }
        });
    }

    function submitDetailReject() {
        $.post('<?= site_url('undangan/reject') ?>', $('#detailFormReject').serialize(), function (res) {
            if (res.status) {
                $.messager.alert('Sukses', res.msg, 'info', function () {
                    window.location.reload();
                });
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Reject gagal.', 'error');
        }, 'json').fail(function (xhr) {
            $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat reject.', 'error');
        });
    }
</script>
<?php endif; ?>
