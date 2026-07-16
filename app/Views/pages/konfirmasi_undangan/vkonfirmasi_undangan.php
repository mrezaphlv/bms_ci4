<style>
    .undangan-wrap {
        padding: 18px 22px;
    }
    .undangan-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 10px;
    }
    .undangan-subtitle {
        color: #6b7280;
        margin-bottom: 16px;
    }
    .toolbar-row {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        padding: 8px 0 14px;
    }
    .status-filter label {
        margin-right: 14px;
        font-weight: 600;
        color: #374151;
    }
    .summary-box {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        background: #f9fafb;
        margin-bottom: 14px;
    }
    .summary-box strong {
        color: #111827;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 180px 1fr;
        row-gap: 8px;
        column-gap: 10px;
        padding: 10px 0 2px;
    }
    .drop-zone {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 22px 18px;
        text-align: center;
        background: #f8fafc;
        color: #64748b;
    }
    .drop-zone .file-name {
        margin-top: 10px;
        color: #111827;
        font-size: 12px;
    }
    .action-link {
        margin-right: 8px;
    }
</style>

<div class="undangan-wrap">
    <div class="undangan-title">Konfirmasi Undangan</div>
    <div class="undangan-subtitle">Daftar, pencarian, detail, konfirmasi, reject, dan upload PPJB.</div>

    <div class="summary-box">
        <strong>Hak akses:</strong>
        View `<?= (int) ($akses->can_view ?? 0) ?>`,
        Edit `<?= (int) ($akses->can_edit ?? 0) ?>`,
        Approve `<?= (int) ($akses->can_approve ?? 0) ?>`
    </div>

    <div id="toolbarUndangan">
        <div class="toolbar-row">
            <div class="status-filter">
                <label><input type="checkbox" class="status-check" value="NEW" checked> NEW</label>
                <label><input type="checkbox" class="status-check" value="APPROVED" checked> APPROVED</label>
                <label><input type="checkbox" class="status-check" value="REJECTED" checked> REJECTED</label>
            </div>
            <input id="searchUndangan" class="easyui-searchbox" style="width:280px"
                data-options="prompt:'Cari no undangan / owner / unit / sales',searcher:doSearchUndangan">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" onclick="reloadUndanganGrid()">Reload</a>
        </div>
    </div>

    <table id="dgUndangan"></table>
</div>

<div id="dlgApprove" class="easyui-dialog" title="Approve Undangan" style="width:620px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgApproveButtons'">
    <form id="formApprove" enctype="multipart/form-data">
        <input type="hidden" name="id_undangan" id="approve_id_undangan">
        <div class="detail-grid">
            <div>PIN</div>
            <div><input class="easyui-passwordbox" name="pin" id="approve_pin" style="width:100%" required></div>

            <div>Tanggal Lunas</div>
            <div><input class="easyui-datebox" name="tgl_lunas" id="approve_tgl_lunas" style="width:100%" data-options="formatter:easyDateFormatter,parser:easyDateParser"></div>

            <div>No. PPJB</div>
            <div><input class="easyui-textbox" name="no_ppjb" id="approve_no_ppjb" style="width:100%"></div>

            <div>Tanggal PPJB</div>
            <div><input class="easyui-datebox" name="tgl_ppjb" id="approve_tgl_ppjb" style="width:100%" data-options="formatter:easyDateFormatter,parser:easyDateParser"></div>

            <div>Upload PPJB</div>
            <div>
                <div class="drop-zone">
                    <input type="file" name="ppjb_file" id="approve_ppjb_file" accept=".pdf">
                    <div style="margin-top:8px;">
                        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#approve_ppjb_file').trigger('click')">Pilih File PDF</a>
                    </div>
                    <div class="file-name" id="approve_file_name">Belum ada file dipilih.</div>
                </div>
            </div>
        </div>
    </form>
</div>
<div id="dlgApproveButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitApproveUndangan()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgApprove').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="dlgReject" class="easyui-dialog" title="Reject Undangan" style="width:560px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgRejectButtons'">
    <form id="formReject">
        <input type="hidden" name="id_reject" id="reject_id_undangan">
        <div class="detail-grid">
            <div>PIN</div>
            <div><input class="easyui-passwordbox" name="pin" id="reject_pin" style="width:100%" required></div>

            <div>Keterangan</div>
            <div><input class="easyui-textbox" name="keterangan" id="reject_keterangan" style="width:100%;height:80px" data-options="multiline:true" required></div>
        </div>
    </form>
</div>
<div id="dlgRejectButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitRejectUndangan()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgReject').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="dlgPpjb" class="easyui-dialog" title="Upload PPJB" style="width:620px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgPpjbButtons'">
    <form id="formPpjb" enctype="multipart/form-data">
        <input type="hidden" name="id_undangan" id="ppjb_id_undangan">
        <div class="detail-grid">
            <div>No. PPJB</div>
            <div><input class="easyui-textbox" name="no_ppjb" id="ppjb_no_ppjb" style="width:100%"></div>

            <div>Tanggal PPJB</div>
            <div><input class="easyui-datebox" name="tgl_ppjb" id="ppjb_tgl_ppjb" style="width:100%" data-options="formatter:easyDateFormatter,parser:easyDateParser"></div>

            <div>Upload PPJB</div>
            <div>
                <div class="drop-zone">
                    <input type="file" name="ppjb_file" id="ppjb_file" accept=".pdf">
                    <div style="margin-top:8px;">
                        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#ppjb_file').trigger('click')">Pilih File PDF</a>
                    </div>
                    <div class="file-name" id="ppjb_file_name">Belum ada file dipilih.</div>
                </div>
            </div>
        </div>
    </form>
</div>
<div id="dlgPpjbButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitPpjb()" style="width:90px">Simpan</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgPpjb').dialog('close')" style="width:90px">Batal</a>
</div>

<script>
    const aksesUndangan = {
        canEdit: <?= (int) ($akses->can_edit ?? 0) ?>,
        canApprove: <?= (int) ($akses->can_approve ?? 0) ?>,
    };

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

    function selectedStatuses() {
        const values = [];
        $('.status-check:checked').each(function () {
            values.push($(this).val());
        });
        return values;
    }

    function reloadUndanganGrid() {
        $('#dgUndangan').datagrid('load', {
            tb_checkbox: selectedStatuses(),
            search_value: $('#searchUndangan').searchbox('getValue')
        });
    }

    function doSearchUndangan() {
        reloadUndanganGrid();
    }

    function formatStatus(value) {
        let color = '#111827';
        if (value === 'APPROVED') color = '#15803d';
        if (value === 'REJECTED') color = '#b91c1c';
        return '<strong style="color:' + color + ';">' + (value || '') + '</strong>';
    }

    function actionFormatter(value, row) {
        const links = [];
        links.push('<a class="action-link" href="javascript:void(0)" onclick="openDetail(' + row.id + ')">Detail</a>');

        if (row.status === 'NEW' && aksesUndangan.canApprove === 1) {
            links.push('<a class="action-link" href="javascript:void(0)" onclick="openApprove(' + row.id + ')">Approve</a>');
            links.push('<a class="action-link" href="javascript:void(0)" onclick="openReject(' + row.id + ')">Reject</a>');
        }

        if (row.status === 'APPROVED' && row.file_ppjb) {
            links.push('<a class="action-link" href="javascript:void(0)" onclick="viewPpjb(' + row.id + ')">View PPJB</a>');
            links.push('<a class="action-link" href="javascript:void(0)" onclick="downloadPpjb(' + row.id + ')">Download</a>');
        }

        if (row.status === 'APPROVED' && !row.file_ppjb && (aksesUndangan.canEdit === 1 || aksesUndangan.canApprove === 1)) {
            links.push('<a class="action-link" href="javascript:void(0)" onclick="openPpjb(' + row.id + ')">Upload PPJB</a>');
        }

        return links.join('');
    }

    function openDetail(id) {
        window.location.href = '<?= site_url('undangan/detail') ?>/' + id;
    }

    function openApprove(id) {
        $('#formApprove')[0].reset();
        $('#approve_id_undangan').val(id);
        $('#approve_file_name').text('Belum ada file dipilih.');
        $('#dlgApprove').dialog('open');
    }

    function openReject(id) {
        $('#formReject')[0].reset();
        $('#reject_id_undangan').val(id);
        $('#dlgReject').dialog('open');
    }

    function openPpjb(id) {
        $('#formPpjb')[0].reset();
        $('#ppjb_id_undangan').val(id);
        $('#ppjb_file_name').text('Belum ada file dipilih.');

        $.post('<?= site_url('undangan/get-ppjb') ?>', {id_undangan: id}, function (res) {
            if (res.status) {
                $('#ppjb_no_ppjb').textbox('setValue', res.data.no_ppjb || '');
                $('#ppjb_tgl_ppjb').datebox('setValue', res.tgl_ppjb || '');
            }
            $('#dlgPpjb').dialog('open');
        }, 'json');
    }

    function viewPpjb(id) {
        window.open('<?= site_url('undangan/view-file-ppjb') ?>/' + id, '_blank');
    }

    function downloadPpjb(id) {
        window.open('<?= site_url('undangan/download-file-ppjb') ?>/' + id, '_blank');
    }

    function submitApproveUndangan() {
        const formData = new FormData(document.getElementById('formApprove'));
        $.ajax({
            url: '<?= site_url('undangan/approve') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    $('#dlgApprove').dialog('close');
                    $.messager.alert('Sukses', res.msg, 'info');
                    reloadUndanganGrid();
                    return;
                }
                $.messager.alert('Gagal', res.msg || 'Proses approve gagal.', 'error');
            },
            error: function (xhr) {
                $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat approve.', 'error');
            }
        });
    }

    function submitRejectUndangan() {
        $.post('<?= site_url('undangan/reject') ?>', $('#formReject').serialize(), function (res) {
            if (res.status) {
                $('#dlgReject').dialog('close');
                $.messager.alert('Sukses', res.msg, 'info');
                reloadUndanganGrid();
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Proses reject gagal.', 'error');
        }, 'json').fail(function (xhr) {
            $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat reject.', 'error');
        });
    }

    function submitPpjb() {
        const formData = new FormData(document.getElementById('formPpjb'));
        $.ajax({
            url: '<?= site_url('undangan/submit-ppjb') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    $('#dlgPpjb').dialog('close');
                    $.messager.alert('Sukses', res.msg, 'info');
                    reloadUndanganGrid();
                    return;
                }
                $.messager.alert('Gagal', res.msg || 'Simpan PPJB gagal.', 'error');
            },
            error: function (xhr) {
                $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat simpan PPJB.', 'error');
            }
        });
    }

    $(function () {
        $('#dgUndangan').datagrid({
            fit: true,
            height: 520,
            method: 'post',
            url: '<?= site_url('undangan/grid') ?>',
            toolbar: '#toolbarUndangan',
            singleSelect: true,
            rownumbers: true,
            pagination: true,
            pageSize: 10,
            pageList: [10, 25, 50, 100],
            fitColumns: true,
            striped: true,
            remoteSort: true,
            queryParams: {
                tb_checkbox: selectedStatuses(),
                search_value: ''
            },
            onBeforeLoad: function (param) {
                param.tb_checkbox = selectedStatuses();
                param.search_value = $('#searchUndangan').searchbox('getValue');
            },
            columns: [[
                {field: 'id', title: 'ID', width: 60, sortable: true, hidden: true},
                {field: 'no_undangan', title: 'No Undangan', width: 180, sortable: true},
                {field: 'tgl_undangan', title: 'Tanggal Undangan', width: 110, sortable: true},
                {field: 'nama_owner', title: 'Owner', width: 180, sortable: true},
                {field: 'tipe_tenant', title: 'Tipe', width: 100, sortable: true},
                {field: 'kode_unit', title: 'Unit', width: 90, sortable: true},
                {field: 'nama_building', title: 'Building', width: 140, sortable: true},
                {field: 'nama_sales', title: 'Sales', width: 160, sortable: true},
                {field: 'status', title: 'Status', width: 100, sortable: true, formatter: formatStatus},
                {field: 'aksi', title: 'Aksi', width: 240, formatter: actionFormatter}
            ]]
        });

        $('.status-check').on('change', function () {
            reloadUndanganGrid();
        });

        $('#approve_ppjb_file').on('change', function () {
            const name = this.files.length ? this.files[0].name : 'Belum ada file dipilih.';
            $('#approve_file_name').text(name);
        });

        $('#ppjb_file').on('change', function () {
            const name = this.files.length ? this.files[0].name : 'Belum ada file dipilih.';
            $('#ppjb_file_name').text(name);
        });
    });
</script>
