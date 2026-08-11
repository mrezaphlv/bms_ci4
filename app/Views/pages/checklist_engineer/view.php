<style>
    .checklist-view-wrap {
        padding: 18px 22px 28px;
    }
    .checklist-view-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
    }
    .checklist-view-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 20px;
    }
    .checklist-view-table {
        width: 100%;
        border-collapse: collapse;
    }
    .checklist-view-table th,
    .checklist-view-table td {
        border: 1px solid #e5e7eb;
        padding: 10px;
        vertical-align: middle;
    }
    .checklist-view-table th {
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

<div class="checklist-view-wrap">
    <a href="<?= site_url('checklist_engineer') ?>" class="easyui-linkbutton" iconCls="icon-back">Kembali</a>
    <div class="checklist-view-title">Checklist Engineering - <?= esc($header->kode_unit ?? '-') ?></div>

    <?php if (in_array((string) ($header->status ?? ''), ['NEW', 'EDITED'], true)): ?>
        <div style="margin-bottom:16px">
            <a href="<?= site_url('checklist_engineer/edit/' . (int) $id_checklist) ?>" class="easyui-linkbutton" iconCls="icon-edit">Edit</a>
        </div>
    <?php else: ?>
        <div style="margin-bottom:16px">Status: <strong><?= esc((string) ($header->status ?? '-')) ?></strong></div>
    <?php endif; ?>

    <div class="checklist-view-card">
        <table class="checklist-view-table">
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
                <?php foreach ($items as $item): ?>
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
                                    <a href="javascript:void(0)" onclick="viewPhoto('<?= esc($item->foto, 'js') ?>')">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (in_array((string) ($header->status ?? ''), ['NEW', 'EDITED'], true)): ?>
            <div style="margin-top:18px;text-align:right">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="openRejectDialog()">Reject</a>
                <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="openApproveDialog()">Approve</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="dlgChecklistViewApprove" class="easyui-dialog" title="Approve Checklist" style="width:480px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgChecklistViewApproveButtons'">
    <form id="formChecklistViewApprove">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $id_checklist ?>">
        <div style="margin-bottom:12px">PIN</div>
        <input class="easyui-passwordbox" name="pin" style="width:100%" required>
    </form>
</div>
<div id="dlgChecklistViewApproveButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitApprove()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick=\"$('#dlgChecklistViewApprove').dialog('close')\" style="width:90px">Batal</a>
</div>

<div id="dlgChecklistViewReject" class="easyui-dialog" title="Reject Checklist" style="width:520px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgChecklistViewRejectButtons'">
    <form id="formChecklistViewReject">
        <?= csrf_field() ?>
        <input type="hidden" name="id_reject" value="<?= (int) $id_checklist ?>">
        <div style="margin-bottom:12px">PIN</div>
        <input class="easyui-passwordbox" name="pin" style="width:100%" required>
        <div style="margin:12px 0">Keterangan</div>
        <input class="easyui-textbox" name="keterangan" style="width:100%;height:90px" data-options="multiline:true" required>
    </form>
</div>
<div id="dlgChecklistViewRejectButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitReject()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick=\"$('#dlgChecklistViewReject').dialog('close')\" style="width:90px">Batal</a>
</div>

<div id="dlgChecklistViewPhoto" class="easyui-dialog" title="Foto Item" style="width:560px;padding:18px"
    data-options="closed:true,modal:true">
    <div style="text-align:center">
        <img id="checklistViewPhotoPreview" src="" alt="Foto Item" style="max-width:100%;max-height:420px">
    </div>
</div>

<script>
    function bindViewCategoryToggle() {
        $('.category-row').off('click').on('click', function () {
            const span = $(this).find('span');
            span.text(span.text() === '-' ? '+' : '-');
            $(this).nextUntil('.category-row').toggle();
        });
    }

    function openApproveDialog() {
        $('#formChecklistViewApprove')[0].reset();
        $('#dlgChecklistViewApprove').dialog('open');
    }

    function openRejectDialog() {
        $('#formChecklistViewReject')[0].reset();
        $('#dlgChecklistViewReject').dialog('open');
    }

    function submitApprove() {
        $.post('<?= site_url('checklist_engineer/approve') ?>', $('#formChecklistViewApprove').serialize(), function (res) {
            if (res.status) {
                $.messager.alert('Sukses', res.msg || 'Approve berhasil.', 'info', function () {
                    window.location.href = '<?= site_url('checklist_engineer') ?>';
                });
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Approve gagal.', 'error');
        }, 'json');
    }

    function submitReject() {
        $.post('<?= site_url('checklist_engineer/reject') ?>', $('#formChecklistViewReject').serialize(), function (res) {
            if (res.status) {
                $.messager.alert('Sukses', res.msg || 'Reject berhasil.', 'info', function () {
                    window.location.href = '<?= site_url('checklist_engineer') ?>';
                });
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Reject gagal.', 'error');
        }, 'json');
    }

    function viewPhoto(fileName) {
        const src = '<?= base_url('dokumen/checklist/engineering/' . (int) $id_checklist) ?>/' + fileName;
        $('#checklistViewPhotoPreview').attr('src', src);
        $('#dlgChecklistViewPhoto').dialog('open');
    }

    $(function () {
        bindViewCategoryToggle();
    });
</script>
