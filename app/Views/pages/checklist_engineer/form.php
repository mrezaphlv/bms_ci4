<?php
    $pageTitle = $mode === 'edit' ? 'Edit Checklist Engineering' : 'Input Checklist Engineering';
?>
<style>
    .checklist-form-wrap {
        padding: 18px 22px 28px;
    }
    .checklist-form-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
    }
    .checklist-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 20px;
    }
    .checklist-item-table {
        width: 100%;
        border-collapse: collapse;
    }
    .checklist-item-table th,
    .checklist-item-table td {
        border: 1px solid #e5e7eb;
        padding: 10px;
        vertical-align: middle;
    }
    .checklist-item-table th {
        background: #f3f4f6;
        text-align: center;
    }
    .category-row {
        background: #334155;
        color: #fff;
        cursor: pointer;
        font-weight: 700;
    }
    .section-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .toggle-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .thumb-link {
        color: #1d4ed8;
        cursor: pointer;
        text-decoration: none;
    }
</style>

<div class="checklist-form-wrap">
    <a href="<?= esc($backUrl) ?>" class="easyui-linkbutton" iconCls="icon-back">Kembali</a>
    <div class="checklist-form-title"><?= esc($pageTitle) ?> - <?= esc($header->kode_unit ?? '-') ?></div>

    <div class="section-actions">
        <div></div>
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-add" onclick="openAddItemDialog()">Add Item</a>
    </div>

    <div class="checklist-card">
        <form id="formChecklist" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id_checklist" value="<?= (int) $id_checklist ?>">
            <table class="checklist-item-table">
                <thead>
                    <tr>
                        <th style="width:26%">Item</th>
                        <th style="width:10%">Jumlah</th>
                        <th style="width:16%">Kondisi</th>
                        <th>Keterangan</th>
                        <th style="width:18%">Foto Item</th>
                        <th style="width:8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <?php if ((int) $item->segmen === 1): ?>
                            <tr class="category-row">
                                <td colspan="6"><span>-</span> <?= esc($item->nama_kategori) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr class="item-row">
                                <td>
                                    <?= esc($item->nama_item) ?>
                                    <input type="hidden" name="chkitem[<?= $index ?>][id_mitem]" value="<?= esc((string) $item->id_item) ?>">
                                    <input type="hidden" name="chkitem[<?= $index ?>][nama_item]" value="<?= esc($item->nama_item) ?>">
                                </td>
                                <td>
                                    <input class="easyui-numberbox" name="chkitem[<?= $index ?>][qty]" value="<?= esc((string) ($item->qty ?? 1)) ?>" style="width:100%" data-options="min:0,precision:0">
                                </td>
                                <td>
                                    <?php $kondisiValue = strtoupper((string) ($item->kondisi ?? 'GOOD')); ?>
                                    <select class="easyui-combobox" name="chkitem[<?= $index ?>][s_kondisi]" style="width:100%" data-options="editable:false,panelHeight:'auto'">
                                        <option value="GOOD" <?= $kondisiValue === 'GOOD' ? 'selected' : '' ?>>Good</option>
                                        <option value="NOT GOOD" <?= $kondisiValue === 'NOT GOOD' ? 'selected' : '' ?>>Not Good</option>
                                    </select>
                                </td>
                                <td>
                                    <input class="easyui-textbox" name="chkitem[<?= $index ?>][keterangan]" value="<?= esc((string) ($item->keterangan ?? '')) ?>" style="width:100%">
                                </td>
                                <td>
                                    <input type="hidden" name="chkitem[<?= $index ?>][fotonow]" value="<?= esc((string) ($item->foto ?? '')) ?>">
                                    <input type="file" name="chkitem[<?= $index ?>][fotoitem]" accept="image/*" style="width:100%">
                                    <?php if (!empty($item->foto)): ?>
                                        <div style="margin-top:6px">
                                            <a class="thumb-link" href="javascript:void(0)" onclick="viewPhoto('<?= esc($item->foto, 'js') ?>')">Lihat foto saat ini</a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="removeChecklistRow(this)">Hapus</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:18px;text-align:right">
                <a href="<?= esc($backUrl) ?>" class="easyui-linkbutton">Cancel</a>
                <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitChecklistForm()">Save</a>
            </div>
        </form>
    </div>
</div>

<div id="dlgAddChecklistItem" class="easyui-dialog" title="Add Item" style="width:520px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgAddChecklistItemButtons'">
    <form id="formAddChecklistItem">
        <?= csrf_field() ?>
        <input type="hidden" name="id_checklist" value="<?= (int) $id_checklist ?>">
        <div style="margin-bottom:12px">Nama</div>
        <input class="easyui-textbox" name="nama" id="add_item_name" style="width:100%" required>

        <div style="margin:12px 0">Kategori</div>
        <select class="easyui-combobox" name="kategori_item_id" id="add_item_category" style="width:100%" data-options="editable:false,panelHeight:'auto'" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($kategoriItemOptions as $kategori): ?>
                <option value="<?= (int) $kategori->id ?>"><?= esc($kategori->nama) ?></option>
            <?php endforeach; ?>
        </select>

        <div style="margin:12px 0">No Urut</div>
        <input class="easyui-numberbox" name="no_urut" id="add_item_order" style="width:100%" data-options="min:1,precision:0" required>

        <div style="margin:12px 0">Nilai</div>
        <input class="easyui-numberbox" name="nilai" id="add_item_value" style="width:100%" data-options="min:0,precision:0" value="0">

        <div style="margin:12px 0">Tenant Check</div>
        <label class="toggle-chip">
            <input type="checkbox" id="add_item_tenant_check">
            <span>Aktif</span>
        </label>
        <input type="hidden" name="tenant_check" id="add_item_tenant_check_value" value="0">
    </form>
</div>
<div id="dlgAddChecklistItemButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitAddItem()" style="width:90px">Save</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgAddChecklistItem').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="dlgChecklistPhoto" class="easyui-dialog" title="Foto Item" style="width:560px;padding:18px"
    data-options="closed:true,modal:true">
    <div style="text-align:center">
        <img id="checklistPhotoPreview" src="" alt="Foto Item" style="max-width:100%;max-height:420px">
    </div>
</div>

<script>
    function bindCategoryToggle() {
        $('.category-row').off('click').on('click', function () {
            const span = $(this).find('span');
            span.text(span.text() === '-' ? '+' : '-');
            $(this).nextUntil('.category-row').toggle();
        });
    }

    function removeChecklistRow(button) {
        $(button).closest('tr').remove();
    }

    function openAddItemDialog() {
        $('#formAddChecklistItem')[0].reset();
        $('#add_item_category').combobox('setValue', '');
        $('#add_item_tenant_check').prop('checked', false);
        $('#add_item_tenant_check_value').val('0');
        $('#dlgAddChecklistItem').dialog('open');
    }

    function submitChecklistForm() {
        const formData = new FormData(document.getElementById('formChecklist'));
        $.ajax({
            url: '<?= esc($saveUrl) ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status) {
                    $.messager.alert('Sukses', res.msg || 'Data checklist berhasil disimpan.', 'info', function () {
                        window.location.href = '<?= site_url('checklist_engineer') ?>';
                    });
                    return;
                }
                $.messager.alert('Gagal', res.msg || 'Data checklist gagal disimpan.', 'error');
            },
            error: function (xhr) {
                $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat menyimpan checklist.', 'error');
            }
        });
    }

    function submitAddItem() {
        $('#add_item_tenant_check_value').val($('#add_item_tenant_check').is(':checked') ? '1' : '0');
        $.post('<?= site_url('checklist_engineer/add-item') ?>', $('#formAddChecklistItem').serialize(), function (res) {
            if (res.status) {
                $.messager.alert('Sukses', res.msg || 'Item berhasil ditambahkan.', 'info', function () {
                    window.location.reload();
                });
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Item gagal ditambahkan.', 'error');
        }, 'json').fail(function (xhr) {
            $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat menambah item.', 'error');
        });
    }

    function viewPhoto(fileName) {
        const src = '<?= base_url('dokumen/checklist/engineering/' . (int) $id_checklist) ?>/' + fileName;
        $('#checklistPhotoPreview').attr('src', src);
        $('#dlgChecklistPhoto').dialog('open');
    }

    $(function () {
        bindCategoryToggle();
        $.parser.parse('#formChecklist');
        $.parser.parse('#formAddChecklistItem');
    });
</script>
