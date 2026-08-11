<style>
    .checklist-wrap {
        padding: 18px 22px 28px;
    }
    .checklist-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }
    .checklist-subtitle {
        color: #6b7280;
        margin-bottom: 18px;
    }
    .status-filter {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 10px;
    }
    .action-link {
        color: #1d4ed8;
        cursor: pointer;
        text-decoration: none;
        margin-right: 8px;
    }
    .action-link:last-child {
        margin-right: 0;
    }
    .toolbar-box {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 12px;
    }
</style>

<div class="checklist-wrap">
    <div class="checklist-title">Checklist Engineer</div>
    <div class="checklist-subtitle">Daftar checklist engineering, detail undangan, input checklist, approve, dan reject.</div>

    <div id="toolbarChecklist">
        <div class="toolbar-box">
            <div class="status-filter">
                <label><input type="checkbox" class="status-check" value="NEW" checked> NEW</label>
                <label><input type="checkbox" class="status-check" value="APPROVED" checked> APPROVED</label>
                <label><input type="checkbox" class="status-check" value="REJECTED" checked> REJECTED</label>
                <label><input type="checkbox" class="status-check" value="EDITED" checked> EDITED</label>
            </div>
            <div>
                <input id="searchChecklist" class="easyui-searchbox" style="width:300px"
                    data-options="prompt:'Cari no undangan / owner / tipe / unit',searcher:reloadChecklistGrid">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" onclick="reloadChecklistGrid()">Reload</a>
            </div>
        </div>
    </div>

    <table id="dgChecklistEngineer"></table>
</div>

<div id="dlgChecklistApprove" class="easyui-dialog" title="Approve Checklist" style="width:480px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgChecklistApproveButtons'">
    <form id="formChecklistApprove">
        <input type="hidden" name="id" id="approve_checklist_id">
        <div style="margin-bottom:12px">PIN</div>
        <input class="easyui-passwordbox" name="pin" id="approve_checklist_pin" style="width:100%" required>
    </form>
</div>
<div id="dlgChecklistApproveButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitChecklistApprove()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgChecklistApprove').dialog('close')" style="width:90px">Batal</a>
</div>

<div id="dlgChecklistReject" class="easyui-dialog" title="Reject Checklist" style="width:520px;padding:18px"
    data-options="closed:true,modal:true,buttons:'#dlgChecklistRejectButtons'">
    <form id="formChecklistReject">
        <input type="hidden" name="id_reject" id="reject_checklist_id">
        <div style="margin-bottom:12px">PIN</div>
        <input class="easyui-passwordbox" name="pin" id="reject_checklist_pin" style="width:100%" required>
        <div style="margin:12px 0">Keterangan Reject</div>
        <input class="easyui-textbox" name="keterangan" id="reject_checklist_notes" style="width:100%;height:90px" data-options="multiline:true" required>
    </form>
</div>
<div id="dlgChecklistRejectButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="submitChecklistReject()" style="width:90px">Submit</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgChecklistReject').dialog('close')" style="width:90px">Batal</a>
</div>

<script>
    const checklistAkses = {
        canView: <?= (int) ($akses->can_view ?? 0) ?>,
        canCreate: <?= (int) ($akses->can_create ?? 0) ?>,
        canEdit: <?= (int) ($akses->can_edit ?? 0) ?>,
        canApprove: <?= (int) ($akses->can_approve ?? 0) ?>,
    };

    function currentChecklistStatuses() {
        return $('.status-check:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function checklistRefLabel(value) {
        const text = String(value || '').toUpperCase();
        if (!text || text === 'SERAH TERIMA') {
            return 'HAND OVER';
        }
        return value;
    }

    function formatChecklistStatusCell(value, row) {
        if (!row.id_checklist && value === 'NEW') {
            return '<a class="action-link" href="javascript:void(0)" onclick="openChecklistInput(' + row.id + ')">New</a>';
        }

        if (value === 'REJECTED') {
            return '<a class="action-link" href="javascript:void(0)" onclick="openChecklistEdit(' + row.id + ')">Edit</a>';
        }

        return '<a class="action-link" href="javascript:void(0)" onclick="openChecklistView(' + row.id + ')">Done</a>';
    }

    function formatStatus(value, row) {
        if (!row.id_checklist && value === 'NEW') {
            return '-';
        }

        let color = '#111827';
        if (value === 'APPROVED') color = '#15803d';
        if (value === 'REJECTED') color = '#b91c1c';
        if (value === 'EDITED') color = '#d97706';

        return '<span style="color:' + color + ';font-weight:700;">' + (value || '-') + '</span>';
    }

    function formatChecklistActions(value, row) {
        let links = '<a class="action-link" href="javascript:void(0)" onclick="openChecklistDetail(' + row.id + ')">Detail</a>';

        if (checklistAkses.canApprove === 1 && row.id_checklist && (row.status === 'NEW' || row.status === 'EDITED')) {
            links += '<a class="action-link" href="javascript:void(0)" onclick="openChecklistApprove(' + row.id + ')">Approve</a>';
            links += '<a class="action-link" href="javascript:void(0)" onclick="openChecklistReject(' + row.id + ')">Reject</a>';
        }

        return links;
    }

    function reloadChecklistGrid() {
        $('#dgChecklistEngineer').datagrid('load', {
            search_value: $('#searchChecklist').searchbox('getValue'),
            tb_checkbox: currentChecklistStatuses(),
        });
    }

    function openChecklistInput(id) {
        window.location.href = '<?= site_url('checklist_engineer/input') ?>/' + id;
    }

    function openChecklistView(id) {
        window.location.href = '<?= site_url('checklist_engineer/view') ?>/' + id;
    }

    function openChecklistEdit(id) {
        window.location.href = '<?= site_url('checklist_engineer/edit') ?>/' + id;
    }

    function openChecklistDetail(id) {
        window.location.href = '<?= site_url('checklist_engineer/detailUndangan') ?>/' + id;
    }

    function openChecklistApprove(id) {
        $('#formChecklistApprove')[0].reset();
        $('#approve_checklist_id').val(id);
        $('#dlgChecklistApprove').dialog('open');
    }

    function openChecklistReject(id) {
        $('#formChecklistReject')[0].reset();
        $('#reject_checklist_id').val(id);
        $('#dlgChecklistReject').dialog('open');
    }

    function submitChecklistApprove() {
        $.post('<?= site_url('checklist_engineer/approve') ?>', $('#formChecklistApprove').serialize(), function (res) {
            if (res.status) {
                $('#dlgChecklistApprove').dialog('close');
                $.messager.alert('Sukses', res.msg || 'Approve berhasil.', 'info');
                reloadChecklistGrid();
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Approve gagal.', 'error');
        }, 'json').fail(function (xhr) {
            $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat approve.', 'error');
        });
    }

    function submitChecklistReject() {
        $.post('<?= site_url('checklist_engineer/reject') ?>', $('#formChecklistReject').serialize(), function (res) {
            if (res.status) {
                $('#dlgChecklistReject').dialog('close');
                $.messager.alert('Sukses', res.msg || 'Reject berhasil.', 'info');
                reloadChecklistGrid();
                return;
            }
            $.messager.alert('Gagal', res.msg || 'Reject gagal.', 'error');
        }, 'json').fail(function (xhr) {
            $.messager.alert('Gagal', xhr.responseJSON?.msg || 'Terjadi kesalahan saat reject.', 'error');
        });
    }

    $(function () {
        $('#dgChecklistEngineer').datagrid({
            fit: true,
            singleSelect: true,
            fitColumns: true,
            pagination: true,
            rownumbers: true,
            method: 'post',
            url: '<?= site_url('checklist_engineer/grid') ?>',
            toolbar: '#toolbarChecklist',
            pageSize: 10,
            pageList: [10, 50],
            queryParams: {
                tb_checkbox: currentChecklistStatuses(),
            },
            columns: [[
                {field: 'no_undangan', title: 'No Undangan', width: 150, sortable: true},
                {field: 'tgl_undangan', title: 'Tgl Undangan', width: 110, sortable: true},
                {field: 'nama_owner', title: 'Owner', width: 200, sortable: true},
                {field: 'tipe_tenant', title: 'Tipe', width: 130, sortable: true},
                {field: 'kode_unit', title: 'Unit', width: 120, sortable: true},
                {field: 'tipe_checklist', title: 'Reff', width: 130, sortable: true, formatter: checklistRefLabel},
                {field: 'status', title: 'Checklist', width: 110, formatter: formatChecklistStatusCell},
                {field: 'status_label', title: 'Status', width: 110, formatter: function (v, row) { return formatStatus(row.status, row); }},
                {field: 'id', title: 'Aksi', width: 180, formatter: formatChecklistActions}
            ]]
        });

        $('.status-check').on('change', reloadChecklistGrid);
    });
</script>
