<?php
    $statusChecklist = (string) ($dthead->status_checklist ?? '');
    $statusColor = '#111827';
    if ($statusChecklist === 'APPROVED') {
        $statusColor = '#15803d';
    } elseif ($statusChecklist === 'REJECTED') {
        $statusColor = '#b91c1c';
    }
?>
<style>
    .checklist-detail-wrap {
        padding: 18px 22px 28px;
    }
    .detail-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 18px;
    }
    .detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 20px;
        margin-bottom: 18px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 10px 24px;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px dashed #e5e7eb;
        padding: 6px 0;
    }
    .detail-row strong {
        color: #374151;
    }
    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }
    .detail-table th,
    .detail-table td {
        border: 1px solid #e5e7eb;
        padding: 10px;
        vertical-align: middle;
    }
    .detail-table th {
        background: #f3f4f6;
        text-align: center;
    }
    .category-row {
        background: #334155;
        color: #fff;
        cursor: pointer;
        font-weight: 700;
    }
</style>

<div class="checklist-detail-wrap">
    <a href="<?= esc($backUrl) ?>" class="easyui-linkbutton" iconCls="icon-back">Kembali</a>
    <div class="detail-title">Detail Checklist Engineer - <?= esc((string) ($dthead->no_undangan ?? '-')) ?></div>

    <?php if (in_array($statusChecklist, ['NEW', 'EDITED'], true)): ?>
        <div style="margin-bottom:16px;text-align:right">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="openDetailReject()">Reject</a>
            <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="openDetailApprove()">Approve</a>
        </div>
    <?php endif; ?>

    <div class="detail-card">
        <div class="detail-grid">
            <div>
                <div class="detail-row"><strong>No Undangan</strong><span><?= esc((string) ($dthead->no_undangan ?? '-')) ?></span></div>
                <div class="detail-row"><strong>Order Date</strong><span><?= !empty($dthead->order_date) ? date('d-m-Y', strtotime((string) $dthead->order_date)) : '-' ?></span></div>
                <div class="detail-row"><strong>Accept Date</strong><span><?= !empty($dthead->accept_date) ? date('d-m-Y', strtotime((string) $dthead->accept_date)) : '-' ?></span></div>
                <div class="detail-row"><strong>Email</strong><span><?= esc((string) ($dthead->email_owner ?? '-')) ?></span></div>
                <div class="detail-row"><strong>Tipe</strong><span><?= esc((string) ($dthead->tipe_tenant ?? '-')) ?></span></div>
            </div>
            <div>
                <div class="detail-row"><strong>Owner</strong><span><?= esc((string) ($dthead->nama_owner ?? '-')) ?></span></div>
                <div class="detail-row"><strong>Sales</strong><span><?= esc((string) ($dthead->nama_sales ?? '-')) ?></span></div>
                <div class="detail-row"><strong>Unit</strong><span><?= esc((string) ($dthead->kode_unit ?? '-')) ?></span></div>
                <div class="detail-row"><strong>Building</strong><span><?= esc((string) ($dthead->nama_building ?? '-')) ?></span></div>
                <div class="detail-row"><strong>Status</strong><span style="color:<?= $statusColor ?>;font-weight:700"><?= esc($statusChecklist ?: '-') ?></span></div>
            </div>
        </div>
        <?php if ($statusChecklist === 'REJECTED' && !empty($dthead->notes)): ?>
            <div style="margin-top:12px;color:#b91c1c"><strong>Keterangan Reject:</strong> <?= esc((string) $dthead->notes) ?></div>
        <?php endif; ?>
        <?php if (in_array($statusChecklist, ['NEW', 'REJECTED'], true)): ?>
            <div style="margin-top:16px;text-align:right">
                <a href="<?= esc($editUrl) ?>" class="easyui-linkbutton" iconCls="icon-edit">Edit</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($citem)): ?>
        <div class="detail-card">
            <div style="font-weight:700;margin-bottom:12px">Checklist Engineering</div>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width:26%">Item</th>
                        <th style="width:10%">Jumlah</th>
                        <th style="width:16%">Kondisi</th>
                        <th>Keterangan</th>
                        <th style="width:16%">Foto Item</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citem as $item): ?>
                        <?php if ((int) $item->segmen === 1): ?>
                            <tr class="category-row">
                                <td colspan="5"><span>-</span> <?= esc($item->nama_kategori) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?= esc($item->nama_item) ?></td>
                                <td><?= esc((string) $item->qty) ?></td>
                                <td><?= esc((string) $item->kondisi) ?></td>
                                <td><?= esc((string) $item->keterangan) ?></td>
                                <td>
                                    <?php if (!empty($item->foto)): ?>
                                        <a href="javascript:void(0)" onclick="viewDetailPhoto('<?= esc($item->foto, 'js') ?>')">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="detail-card">
        <div style="font-weight:700;margin-bottom:12px">Utilities</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Utilities</th>
                    <th>Meter Range</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dtutil as $util): ?>
                    <tr>
                        <td><?= esc((string) ($util->nama_utilities ?? '-')) ?></td>
                        <td><?= esc((string) ($util->nama_rangetype ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="detail-card">
        <div style="font-weight:700;margin-bottom:12px">Charge</div>
        <table class="detail-table">
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
                <?php $totalFee = 0; $totalAmount = 0; ?>
                <?php foreach ($dtcharge as $charge): ?>
                    <?php $totalFee += (float) ($charge->fee ?? 0); ?>
                    <?php $totalAmount += (float) ($charge->amount ?? 0); ?>
                    <tr>
                        <td><?= esc((string) ($charge->nama_service ?? '-')) ?></td>
                        <td><?= esc((string) ($charge->nama_pajak ?? '-')) ?></td>
                        <td><?= esc((string) ($charge->periode ?? '-')) ?></td>
                        <td><?= number_format((float) ($charge->fee ?? 0), 2, ',', '.') ?></td>
                        <td><?= number_format((float) ($charge->amount ?? 0), 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">TOTAL</th>
                    <th><?= number_format($totalFee, 2, ',', '.') ?></th>
                    <th><?= number_format($totalAmount, 2, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="detailApproveDialog" class="easyui-dialog" title="Approve Checklist" style="width:480px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#detailApproveDialogButtons'">
    <form id="detailApproveForm">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <div style="margin-bottom:12px">PIN</div>
        <input class="easyui-passwordbox" name="pin" style="width:100%" required>
    </form>
</div>
<div id="detailApproveDialogButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitDetailApprove()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#detailApproveDialog').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="detailRejectDialog" class="easyui-dialog" title="Reject Checklist" style="width:520px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#detailRejectDialogButtons'">
    <form id="detailRejectForm">
        <?= csrf_field() ?>
        <input type="hidden" name="id_reject" value="<?= (int) $id ?>">
        <div style="margin-bottom:12px">PIN</div>
        <input class="easyui-passwordbox" name="pin" style="width:100%" required>
        <div style="margin:12px 0">Keterangan</div>
        <input class="easyui-textbox" name="keterangan" style="width:100%;height:90px" data-options="multiline:true" required>
    </form>
</div>
<div id="detailRejectDialogButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitDetailReject()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#detailRejectDialog').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="detailPhotoDialog" class="easyui-dialog" title="Foto Item" style="width:560px;padding:18px"
    data-options="closed:true,modal:true">
    <div style="text-align:center">
        <img id="detailPhotoPreview" src="" alt="Foto Item" style="max-width:100%;max-height:420px">
    </div>
</div>

<script>
    function bindDetailCategoryToggle() {
        $('.category-row').off('click').on('click', function () {
            const span = $(this).find('span');
            span.text(span.text() === '-' ? '+' : '-');
            $(this).nextUntil('.category-row').toggle();
        });
    }

    function openDetailApprove() {
        $('#detailApproveForm')[0].reset();
        $('#detailApproveDialog').dialog('open');
    }

    function openDetailReject() {
        $('#detailRejectForm')[0].reset();
        $('#detailRejectDialog').dialog('open');
    }

    function submitDetailApprove() {
        $.post('<?= site_url('checklist_engineer/approve') ?>', $('#detailApproveForm').serialize(), function (res) {
            if (res.status) {
                $.messager.alert('Sukses', res.msg || 'Approve berhasil.', 'info', function () {
                    window.location.href = '<?= site_url('checklist_engineer') ?>';
                });
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Approve gagal.', 'error');
        }, 'json');
    }

    function submitDetailReject() {
        $.post('<?= site_url('checklist_engineer/reject') ?>', $('#detailRejectForm').serialize(), function (res) {
            if (res.status) {
                $.messager.alert('Sukses', res.msg || 'Reject berhasil.', 'info', function () {
                    window.location.href = '<?= site_url('checklist_engineer') ?>';
                });
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Reject gagal.', 'error');
        }, 'json');
    }

    function viewDetailPhoto(fileName) {
        const src = '<?= base_url('dokumen/checklist/engineering/' . (int) $id) ?>/' + fileName;
        $('#detailPhotoPreview').attr('src', src);
        $('#detailPhotoDialog').dialog('open');
    }

    $(function () {
        bindDetailCategoryToggle();
    });
</script>
