<?php
    $oldInput = session('_ci_old_input') ?? [];
    $oldPost = is_array($oldInput) ? ($oldInput['post'] ?? []) : [];

    $header = (object) [
        'id'           => $oldPost['id_undangan'] ?? ($dthead->id ?? null),
        'no_undangan'  => $oldPost['no_undangan'] ?? ($dthead->no_undangan ?? ''),
        'tgl_undangan' => $oldPost['tgl_undangan'] ?? ($dthead->tgl_undangan ?? ''),
        'jam_undangan' => $oldPost['jam_undangan'] ?? ($dthead->jam_undangan ?? ''),
        'id_owner'     => $oldPost['id_owner'] ?? ($dthead->id_owner ?? ''),
        'nama_owner'   => $oldPost['owner_show'] ?? ($dthead->nama_owner ?? ''),
        'id_unit'      => $oldPost['id_unit'] ?? ($dthead->id_unit ?? ''),
        'kode_unit'    => $oldPost['unit_show'] ?? ($dthead->kode_unit ?? ''),
        'nm_building'  => $oldPost['nm_building'] ?? ($dthead->nama_building ?? ''),
        'id_sales'     => $oldPost['id_sales'] ?? ($dthead->id_sales ?? ''),
        'nama_sales'   => $oldPost['sales_show'] ?? ($dthead->nama_sales ?? ''),
    ];

    $utilRows = $oldPost['util'] ?? $dtutil ?? [];
    $chargeRows = $oldPost['charge'] ?? $dtcharge ?? [];

    if (empty($utilRows)) {
        $utilRows = [['id_dutil' => '', 's_util' => '', 's_meterrange' => '']];
    }

    if (empty($chargeRows)) {
        $chargeRows = [['id_dcharge' => '', 's_scharge' => '', 's_pajak' => '', 's_periode' => '', 'fee' => '', 'amount' => '', 'nilai_pajak' => '']];
    }
?>

<style>
    .undangan-form-wrap {
        padding: 18px 22px 28px;
    }
    .undangan-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .undangan-form-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }
    .undangan-stepper {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }
    .step-card {
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #f9fafb;
        font-weight: 700;
        color: #6b7280;
        text-align: center;
    }
    .step-card.active {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #93c5fd;
    }
    .form-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 20px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 1fr));
        gap: 16px 20px;
    }
    .form-group label {
        display: block;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
    }
    .required {
        color: #dc2626;
    }
    .input-group-btn {
        display: flex;
        gap: 8px;
    }
    .step-pane {
        display: none;
    }
    .step-pane.active {
        display: block;
    }
    .table-form {
        width: 100%;
        border-collapse: collapse;
    }
    .table-form th,
    .table-form td {
        border: 1px solid #e5e7eb;
        padding: 10px;
        vertical-align: middle;
    }
    .table-form th {
        background: #f3f4f6;
        color: #374151;
        text-align: center;
    }
    .step-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }
    .section-actions {
        margin-top: 12px;
    }
    .hint-box {
        margin-bottom: 16px;
        border-radius: 10px;
        padding: 12px 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e3a8a;
    }
    .number-cell input {
        text-align: right;
    }
</style>

<div class="undangan-form-wrap">
    <div class="undangan-form-header">
        <a href="<?= site_url('undangan') ?>" class="easyui-linkbutton" iconCls="icon-back">Kembali</a>
        <div class="undangan-form-title"><?= $isEdit ? 'Edit Undangan' : 'Tambah Undangan' ?></div>
        <div></div>
    </div>

    <div class="undangan-stepper">
        <div class="step-card active" data-step-label="0">1. Header</div>
        <div class="step-card" data-step-label="1">2. Utilities</div>
        <div class="step-card" data-step-label="2">3. Charge</div>
    </div>

    
    <form id="formUndangan" method="post" action="<?= $isEdit ? site_url('undangan/update/' . (int) $header->id) : site_url('undangan/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id_undangan" value="<?= esc((string) $header->id) ?>">
        <input type="hidden" name="dutil_deleted_json" id="dutil_deleted_json">
        <input type="hidden" name="dcharge_deleted_json" id="dcharge_deleted_json">

        <div class="form-card step-pane active" data-step-pane="0">
            <div class="form-grid">
                <div class="form-group">
                    <label>No. Undangan</label>
                    <input type="text" class="form-control" name="no_undangan" id="no_undangan" value="<?= esc((string) $header->no_undangan) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Tanggal Undangan <span class="required">*</span></label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="tgl_undangan" id="tgl_undangan" value="<?= esc((string) $header->tgl_undangan) ?>" autocomplete="off">
                        </div>
                        <div class="col-sm-6">
                            <input type="time" class="form-control" name="jam_undangan" id="jam_undangan" value="<?= esc((string) $header->jam_undangan) ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Owner <span class="required">*</span></label>
                    <input type="hidden" name="id_owner" id="id_owner" value="<?= esc((string) $header->id_owner) ?>">
                    <div class="input-group-btn">
                        <input type="text" class="form-control" name="owner_show" id="owner_show" value="<?= esc((string) $header->nama_owner) ?>" readonly>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="openOwnerDialog()">Pilih</a>
                    </div>
                </div>
                <div class="form-group">
                    <label>Unit <span class="required">*</span></label>
                    <input type="hidden" name="id_unit" id="id_unit" value="<?= esc((string) $header->id_unit) ?>">
                    <div class="input-group-btn">
                        <input type="text" class="form-control" name="unit_show" id="unit_show" value="<?= esc((string) $header->kode_unit) ?>" readonly>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="openUnitDialog()">Pilih</a>
                    </div>
                </div>
                <div class="form-group">
                    <label>Building</label>
                    <input type="text" class="form-control" name="nm_building" id="nm_building" value="<?= esc((string) $header->nm_building) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Sales <span class="required">*</span></label>
                    <input type="hidden" name="id_sales" id="id_sales" value="<?= esc((string) $header->id_sales) ?>">
                    <div class="input-group-btn">
                        <input type="text" class="form-control" name="sales_show" id="sales_show" value="<?= esc((string) $header->nama_sales) ?>" readonly>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="openSalesDialog()">Pilih</a>
                    </div>
                </div>
            </div>

            <div class="step-actions">
                <a href="<?= site_url('undangan') ?>" class="easyui-linkbutton">Batal</a>
                <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="goToStep(1)">Next</a>
            </div>
        </div>

        <div class="form-card step-pane" data-step-pane="1">
            <table class="table-form" id="utilTable">
                <thead>
                    <tr>
                        <th style="width:44%">Utilities</th>
                        <th style="width:44%">Meter Range</th>
                        <th style="width:12%">#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilRows as $index => $row): ?>
                        <?php $rowObj = (object) $row; ?>
                        <tr class="util-row" data-row-id="<?= $index ?>">
                            <td>
                                <input type="hidden" class="util-detail-id" value="<?= esc((string) ($rowObj->id ?? $rowObj->id_dutil ?? '')) ?>">
                                <input
                                    type="text"
                                    class="util-select"
                                    value="<?= esc((string) ($rowObj->id_utilities ?? $rowObj->s_util ?? '')) ?>"
                                    data-selected-text="<?= esc((string) ($rowObj->nama_utilities ?? '')) ?>"
                                    style="width:100%">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="util-meter-range"
                                    value="<?= esc((string) ($rowObj->id_meterrange ?? $rowObj->s_meterrange ?? '')) ?>"
                                    data-selected="<?= esc((string) ($rowObj->id_meterrange ?? $rowObj->s_meterrange ?? '')) ?>"
                                    data-selected-text="<?= esc((string) ($rowObj->nama_rangetype ?? '')) ?>"
                                    <?= empty($rowObj->id_utilities ?? $rowObj->s_util ?? '') ? 'disabled' : '' ?>
                                    style="width:100%">
                            </td>
                            <td style="text-align:center">
                                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="removeUtilRow(this)">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="section-actions">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="addUtilRow()">Add New</a>
            </div>

            <div class="step-actions">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="goToStep(0)">Previous</a>
                <a href="javascript:void(0)" class="easyui-linkbutton c6" onclick="goToStep(2)">Next</a>
            </div>
        </div>

        <div class="form-card step-pane" data-step-pane="2">
            <table class="table-form" id="chargeTable">
                <thead>
                    <tr>
                        <th style="width:19%">Service</th>
                        <th style="width:19%">Tax</th>
                        <th style="width:15%">Periode</th>
                        <th style="width:19%">Fee</th>
                        <th style="width:19%">Invoice Amount</th>
                        <th style="width:9%">#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chargeRows as $index => $row): ?>
                        <?php $rowObj = (object) $row; ?>
                        <tr class="charge-row" data-row-id="<?= $index ?>">
                            <td>
                                <input type="hidden" class="charge-detail-id" value="<?= esc((string) ($rowObj->id ?? $rowObj->id_dcharge ?? '')) ?>">
                                <select class="form-control charge-service">
                                    <option value="">- Pilih -</option>
                                    <?php foreach ($droplist_charge as $item): ?>
                                        <?php $selected = (string) ($rowObj->id_servicecharge ?? $rowObj->s_scharge ?? '') === (string) $item->id ? 'selected' : ''; ?>
                                        <option value="<?= $item->id ?>" <?= $selected ?>><?= esc($item->nama) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-control charge-tax">
                                    <option value="">- Pilih -</option>
                                    <?php foreach ($droplist_pajak as $item): ?>
                                        <?php $selected = (string) ($rowObj->id_pajak ?? $rowObj->s_pajak ?? '') === (string) $item->id ? 'selected' : ''; ?>
                                        <option value="<?= $item->id ?>" data-rate="<?= esc((string) $item->nilai_pajak) ?>" <?= $selected ?>><?= esc($item->nama_pajak . ' - ' . $item->nilai_pajak) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <?php $periodeValue = (string) ($rowObj->periode ?? $rowObj->s_periode ?? ''); ?>
                                <select class="form-control charge-period">
                                    <option value="">- Pilih -</option>
                                    <option value="1" <?= $periodeValue === '1' ? 'selected' : '' ?>>1 Bulan</option>
                                    <option value="3" <?= $periodeValue === '3' ? 'selected' : '' ?>>3 Bulan</option>
                                    <option value="6" <?= $periodeValue === '6' ? 'selected' : '' ?>>6 Bulan</option>
                                    <option value="12" <?= $periodeValue === '12' ? 'selected' : '' ?>>12 Bulan</option>
                                </select>
                            </td>
                            <td class="number-cell">
                                <input type="text" class="form-control charge-fee" value="<?= esc((string) ($rowObj->fee ?? '')) ?>" readonly>
                            </td>
                            <td class="number-cell">
                                <input type="text" class="form-control charge-amount" value="<?= esc((string) ($rowObj->amount ?? '')) ?>" readonly>
                                <input type="hidden" class="charge-tax-value" value="<?= esc((string) ($rowObj->nilai_pajak ?? '')) ?>">
                            </td>
                            <td style="text-align:center">
                                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="removeChargeRow(this)">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="section-actions">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="addChargeRow()">Add New</a>
            </div>

            <div class="step-actions">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="goToStep(1)">Previous</a>
                <button type="submit" class="easyui-linkbutton c6"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan' ?></button>
            </div>
        </div>
    </form>
</div>

<div id="dlgOwnerCari" class="easyui-dialog" title="Cari Tenant" style="width:760px;height:460px;padding:12px"
    data-options="closed:true,modal:true">
    <table id="tableOwnerDlg"></table>
</div>

<div id="dlgUnitCari" class="easyui-dialog" title="Cari Unit" style="width:760px;height:460px;padding:12px"
    data-options="closed:true,modal:true">
    <table id="tableUnitDlg"></table>
</div>

<div id="dlgSalesCari" class="easyui-dialog" title="Cari Sales" style="width:760px;height:460px;padding:12px"
    data-options="closed:true,modal:true">
    <table id="tableSalesDlg"></table>
</div>

<script>
    const deletedUtilIds = [];
    const deletedChargeIds = [];
    const utilOptions = <?= json_encode(array_map(static fn($item) => ['id' => $item->id, 'nama' => $item->nama], $droplist_util), JSON_UNESCAPED_UNICODE) ?>;

    function initDatepicker() {
        $('#tgl_undangan').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        }).on('changeDate', function () {
            refreshNomorUndangan();
        });
    }

    function goToStep(step) {
        $('.step-pane').removeClass('active');
        $('.step-card').removeClass('active');
        $('[data-step-pane="' + step + '"]').addClass('active');
        $('[data-step-label="' + step + '"]').addClass('active');

        if (step === 1) {
            window.setTimeout(function () {
                initUtilitiesStep();
            }, 50);
        }
    }

    function initUtilitiesStep() {
        $('#utilTable tbody tr').each(function () {
            initUtilRow($(this));
        });
    }

    function openOwnerDialog() {
        $('#dlgOwnerCari').dialog('open');
        $('#tableOwnerDlg').datagrid('reload');
    }

    function openUnitDialog() {
        $('#dlgUnitCari').dialog('open');
        $('#tableUnitDlg').datagrid('reload');
    }

    function openSalesDialog() {
        $('#dlgSalesCari').dialog('open');
        $('#tableSalesDlg').datagrid('reload');
    }

    function setOwner(row) {
        $('#id_owner').val(row.id);
        $('#owner_show').val(row.nama);
        $('#dlgOwnerCari').dialog('close');
    }

    function setUnit(row) {
        $('#id_unit').val(row.id);
        $('#unit_show').val(row.kode_unit);
        $('#nm_building').val(row.nama_building || '');
        $('#dlgUnitCari').dialog('close');
        refreshNomorUndangan();
        recalcAllChargeRows();
    }

    function setSales(row) {
        $('#id_sales').val(row.id);
        $('#sales_show').val(row.nama);
        $('#dlgSalesCari').dialog('close');
    }

    function formatDialogAction(type, value, row) {
        return '<a href="javascript:void(0)" onclick="' + type + '(' + encodeURIComponent(JSON.stringify(row)) + ')">Pilih</a>';
    }

    function selectOwner(encoded) {
        setOwner(JSON.parse(decodeURIComponent(encoded)));
    }

    function selectUnit(encoded) {
        setUnit(JSON.parse(decodeURIComponent(encoded)));
    }

    function selectSales(encoded) {
        setSales(JSON.parse(decodeURIComponent(encoded)));
    }

    function refreshNomorUndangan() {
        const tgl = $('#tgl_undangan').val();
        const idUnit = $('#id_unit').val();
        if (!tgl || !idUnit || <?= $isEdit ? 'true' : 'false' ?>) {
            return;
        }

        $.post('<?= site_url('undangan/preview-nomor') ?>', {tgl_undangan: tgl, id_unit: idUnit}, function (res) {
            if (res.status) {
                $('#no_undangan').val(res.no_undangan || '');
            }
        }, 'json');
    }

    function addUtilRow() {
        $('#utilTable tbody').append(
            '<tr class="util-row">' +
                '<td><input type="hidden" class="util-detail-id" value=""><input type="text" class="util-select" style="width:100%"></td>' +
                '<td><input type="text" class="util-meter-range" disabled style="width:100%"></td>' +
                '<td style="text-align:center"><a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="removeUtilRow(this)">Hapus</a></td>' +
            '</tr>'
        );
        const newRow = $('#utilTable tbody tr:last');
        $.parser.parse(newRow.find('td:last'));
        if ($('[data-step-pane="1"]').hasClass('active')) {
            initUtilRow(newRow);
        }
    }

    function addChargeRow() {
        const services = <?= json_encode(array_map(static fn($item) => ['id' => $item->id, 'nama' => $item->nama], $droplist_charge), JSON_UNESCAPED_UNICODE) ?>;
        const taxes = <?= json_encode(array_map(static fn($item) => ['id' => $item->id, 'nama' => $item->nama_pajak . ' - ' . $item->nilai_pajak], $droplist_pajak), JSON_UNESCAPED_UNICODE) ?>;
        let serviceHtml = '<option value="">- Pilih -</option>';
        let taxHtml = '<option value="">- Pilih -</option>';

        services.forEach(function (item) {
            serviceHtml += '<option value="' + item.id + '">' + item.nama + '</option>';
        });
        taxes.forEach(function (item) {
            taxHtml += '<option value="' + item.id + '">' + item.nama + '</option>';
        });

        $('#chargeTable tbody').append(
            '<tr class="charge-row">' +
                '<td><input type="hidden" class="charge-detail-id" value=""><select class="form-select charge-service">' + serviceHtml + '</select></td>' +
                '<td><select class="form-select charge-tax">' + taxHtml + '</select></td>' +
                '<td><select class="form-select charge-period"><option value="">- Pilih -</option><option value="1">1 Bulan</option><option value="3">3 Bulan</option><option value="6">6 Bulan</option><option value="12">12 Bulan</option></select></td>' +
                '<td class="number-cell"><input type="text" class="form-control charge-fee" readonly></td>' +
                '<td class="number-cell"><input type="text" class="form-control charge-amount" readonly><input type="hidden" class="charge-tax-value" value=""></td>' +
                '<td style="text-align:center"><a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="removeChargeRow(this)">Hapus</a></td>' +
            '</tr>'
        );
        $.parser.parse($('#chargeTable tbody tr:last td:last'));
    }

    function removeUtilRow(button) {
        const row = $(button).closest('tr');
        const detailId = row.find('.util-detail-id').val();
        if (detailId) {
            deletedUtilIds.push(detailId);
        }
        row.remove();
    }

    function removeChargeRow(button) {
        const row = $(button).closest('tr');
        const detailId = row.find('.charge-detail-id').val();
        if (detailId) {
            deletedChargeIds.push(detailId);
        }
        row.remove();
    }

    function buildMeterRangeGrid(row) {
        const meterRangeInput = row.find('.util-meter-range');
        const selectedMeterRange = String(meterRangeInput.attr('data-selected') || meterRangeInput.val() || '');
        const shouldDisable = !row.find('.util-select').val();

        if (meterRangeInput.hasClass('combogrid-f')) {
            if (selectedMeterRange !== '') {
                meterRangeInput.combogrid('setValue', selectedMeterRange);
            }
            if (shouldDisable) {
                meterRangeInput.combogrid('disable');
            } else {
                meterRangeInput.combogrid('enable');
            }
            return;
        }

        meterRangeInput.combogrid({
            width: '100%',
            panelWidth: 420,
            method: 'post',
            url: '<?= site_url('undangan/grid-meterrange-dlg') ?>',
            queryParams: { id_util: 0 },
            idField: 'id_meterrange',
            textField: 'nama',
            fitColumns: true,
            editable: false,
            disabled: shouldDisable,
            panelHeight: 'auto',
            pagination: false,
            columns: [[
                {field: 'nama', title: 'Meter Range', width: 220}
            ]]
        });

        if (selectedMeterRange !== '') {
            meterRangeInput.combogrid('setValue', selectedMeterRange);
        }
    }

    function loadMeterRange(row) {
        const utilityId = row.find('.util-select').combobox('getValue');
        const meterRangeInput = row.find('.util-meter-range');
        const selectedMeterRange = String(meterRangeInput.attr('data-selected') || meterRangeInput.val() || '');

        meterRangeInput.combogrid('clear');
        if (!utilityId) {
            meterRangeInput.combogrid('grid').datagrid('loadData', { total: 0, rows: [] });
            meterRangeInput.combogrid('disable');
            return;
        }

        meterRangeInput.combogrid('enable');
        meterRangeInput.combogrid('grid').datagrid('load', { id_util: utilityId });

        meterRangeInput.combogrid('grid').datagrid({
            onLoadSuccess: function (data) {
                const rows = data.rows || [];
                if (selectedMeterRange !== '') {
                    meterRangeInput.combogrid('setValue', selectedMeterRange);
                    meterRangeInput.attr('data-selected', '');
                } else if (rows.length > 0) {
                    meterRangeInput.combogrid('setText', '');
                }
            }
        });
    }

    function initUtilRow(row) {
        const utilInput = row.find('.util-select');
        const meterRangeInput = row.find('.util-meter-range');
        const selectedUtility = String(utilInput.val() || '');
        const selectedMeterRange = String(meterRangeInput.attr('data-selected') || meterRangeInput.val() || '');

        if (!utilInput.hasClass('combobox-f')) {
            utilInput.combobox({
                width: '100%',
                valueField: 'id',
                textField: 'nama',
                data: utilOptions,
                editable: false,
                panelHeight: 'auto',
                onSelect: function () {
                    meterRangeInput.attr('data-selected', '');
                    loadMeterRange(row);
                },
                onChange: function (newValue) {
                if (!newValue) {
                    meterRangeInput.attr('data-selected', '');
                    meterRangeInput.combogrid('clear');
                    meterRangeInput.combogrid('grid').datagrid('loadData', { total: 0, rows: [] });
                    meterRangeInput.combogrid('disable');
                }
            }
        });
        }

        buildMeterRangeGrid(row);

        if (selectedUtility !== '') {
            utilInput.combobox('setValue', selectedUtility);
            meterRangeInput.attr('data-selected', selectedMeterRange);
            loadMeterRange(row);
        } else {
            utilInput.combobox('clear');
            meterRangeInput.combogrid('clear');
            meterRangeInput.combogrid('grid').datagrid('loadData', { total: 0, rows: [] });
            meterRangeInput.combogrid('disable');
        }
    }

    function parseLocaleNumber(value) {
        return parseFloat(String(value || '0').replace(/\./g, '').replace(',', '.')) || 0;
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value || 0);
    }

    function calculateCharge(row) {
        const idScharge = row.find('.charge-service').val();
        const idPajak = row.find('.charge-tax').val();
        const periode = row.find('.charge-period').val();
        const idUnit = $('#id_unit').val();

        if (!idScharge || !idPajak || !periode || !idUnit) {
            return;
        }

        $.post('<?= site_url('undangan/hitung-fee') ?>', {id_scharge: idScharge, id_unit: idUnit}, function (res) {
            if (!res.status) {
                return;
            }

            const nominalService = parseFloat(res.data.nominal || 0);
            const luasUnit = parseLocaleNumber(res.unit?.luas || 0);
            const flagLuas = res.data.flag_luasunit === true || res.data.flag_luasunit === 't' || Number(res.data.flag_luasunit) === 1;
            const baseValue = flagLuas ? nominalService * parseFloat(periode) * luasUnit : nominalService * parseFloat(periode);

            $.post('<?= site_url('undangan/cari-tarif-pajak') ?>', {id: idPajak, harga_jual: baseValue}, function (taxRes) {
                const ppnNominal = parseFloat(taxRes.data?.ppn_nominal || 0);
                const nilaiPajak = parseFloat(taxRes.data?.nilai_pajak || 0);
                row.find('.charge-fee').val(formatNumber(nominalService));
                row.find('.charge-amount').val(formatNumber(baseValue + ppnNominal));
                row.find('.charge-tax-value').val(nilaiPajak);
            }, 'json');
        }, 'json');
    }

    function recalcAllChargeRows() {
        $('#chargeTable tbody tr').each(function () {
            calculateCharge($(this));
        });
    }

    function beforeSubmitForm() {
        $('#formUndangan .generated-name').remove();
        $('#formUndangan input[name="dutil_deleted[]"], #formUndangan input[name="dcharge_deleted[]"]').remove();

        $('#utilTable tbody tr').each(function (index) {
            const utilInput = $(this).find('.util-select');
            const meterRangeInput = $(this).find('.util-meter-range');
            $(this).find('.util-detail-id').attr('name', 'util[' + index + '][id_dutil]');
            utilInput.combobox('textbox').removeAttr('name');
            meterRangeInput.combogrid('textbox').removeAttr('name');
            utilInput.attr('name', 'util[' + index + '][s_util]');
            utilInput.val(utilInput.combobox('getValue'));
            meterRangeInput.attr('name', 'util[' + index + '][s_meterrange]');
            meterRangeInput.val(meterRangeInput.combogrid('getValue'));
        });

        $('#chargeTable tbody tr').each(function (index) {
            $(this).find('.charge-detail-id').attr('name', 'charge[' + index + '][id_dcharge]');
            $(this).find('.charge-service').attr('name', 'charge[' + index + '][s_scharge]');
            $(this).find('.charge-tax').attr('name', 'charge[' + index + '][s_pajak]');
            $(this).find('.charge-period').attr('name', 'charge[' + index + '][s_periode]');
            $(this).find('.charge-fee').attr('name', 'charge[' + index + '][fee]');
            $(this).find('.charge-amount').attr('name', 'charge[' + index + '][amount]');
            $(this).find('.charge-tax-value').attr('name', 'charge[' + index + '][nilai_pajak]');
        });

        deletedUtilIds.forEach(function (id) {
            $('#formUndangan').append('<input type="hidden" name="dutil_deleted[]" value="' + id + '">');
        });
        deletedChargeIds.forEach(function (id) {
            $('#formUndangan').append('<input type="hidden" name="dcharge_deleted[]" value="' + id + '">');
        });
    }

    $(function () {
        initDatepicker();

        <?php if (session()->getFlashdata('success')): ?>
            $.messager.alert('Sukses', <?= json_encode(session()->getFlashdata('success')) ?>, 'info');
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            $.messager.alert('Gagal', <?= json_encode(strip_tags((string) session()->getFlashdata('error'))) ?>, 'error');
        <?php endif; ?>

        $('#tableOwnerDlg').datagrid({
            fit: true,
            singleSelect: true,
            method: 'post',
            pagination: true,
            url: '<?= site_url('undangan/grid-owner-dlg') ?>',
            columns: [[
                {field: 'nama', title: 'Nama', width: 220},
                {field: 'nik', title: 'NIK', width: 140},
                {field: 'email1', title: 'Email', width: 220},
                {field: 'aksi', title: 'Aksi', width: 90, formatter: function (v, row) { return '<a href="javascript:void(0)" onclick="setOwner(' + JSON.stringify(row).replace(/"/g, '&quot;') + ')">Pilih</a>'; }}
            ]]
        });

        $('#tableUnitDlg').datagrid({
            fit: true,
            singleSelect: true,
            method: 'post',
            pagination: true,
            url: '<?= site_url('undangan/grid-unit-dlg') ?>',
            columns: [[
                {field: 'kode_unit', title: 'Kode Unit', width: 180},
                {field: 'lantai', title: 'Lantai', width: 90},
                {field: 'luas', title: 'Luas', width: 90},
                {field: 'nama_building', title: 'Building', width: 180},
                {field: 'aksi', title: 'Aksi', width: 90, formatter: function (v, row) { return '<a href="javascript:void(0)" onclick="setUnit(' + JSON.stringify(row).replace(/"/g, '&quot;') + ')">Pilih</a>'; }}
            ]]
        });

        $('#tableSalesDlg').datagrid({
            fit: true,
            singleSelect: true,
            method: 'post',
            pagination: true,
            url: '<?= site_url('undangan/grid-sales-dlg') ?>',
            columns: [[
                {field: 'nama', title: 'Nama', width: 220},
                {field: 'email', title: 'Email', width: 220},
                {field: 'nohp', title: 'No HP', width: 140},
                {field: 'aksi', title: 'Aksi', width: 90, formatter: function (v, row) { return '<a href="javascript:void(0)" onclick="setSales(' + JSON.stringify(row).replace(/"/g, '&quot;') + ')">Pilih</a>'; }}
            ]]
        });

        $(document).on('change', '.charge-service, .charge-tax, .charge-period', function () {
            calculateCharge($(this).closest('tr'));
        });

        $('#id_unit').on('change', refreshNomorUndangan);
        $('#formUndangan').on('submit', beforeSubmitForm);

        recalcAllChargeRows();
    });
</script>
