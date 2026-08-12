<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .page-wrap {
        padding: 18px 22px;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 36px);
        box-sizing: border-box;
    }

    .form-selecttt {
        border-radius: 15px;
    }

    .btn-approve {
        background-color: rgba(206, 233, 206, 1);
        border-radius: 15px;
        color: #60b760;
    }

    .table-toolbar {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 8px 0 14px;
    }

    .table-toolbar label {
        margin-right: 12px;
        font-weight: 600;
    }

    .action-inline {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .judul_atas {
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #6b7280;
        margin-bottom: 12px;
    }

    #dgKirimUndangan {
        flex: 1;
        min-height: 0;
    }

    .easyui-panel,
    .datagrid-wrap {
        border-radius: 10px;
    }

    .modal-lg {
        max-width: 700px;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.css">
<script src="<?php echo base_url('assets/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.js"></script>

<div class="page-wrap">
    <div class="judul_atas">
        <div>Transaksi / Kirim Undangan</div>
    </div>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display:inline-block;margin-bottom:20px;">Kirim Undangan</div>
    <div class="page-subtitle">Daftar kirim undangan dengan filter status, pencarian, dan aksi cepat.</div>

    <div id="toolbarKirimUndangan">
        <div class="table-toolbar">
        <div>
            <input type="checkbox" checked onchange="reload_checkbox()" id="check_sent" value="SENT">
            <label for="check_sent">Sent</label>
            <input type="checkbox" checked onchange="reload_checkbox()" id="check_confirm" value="CONFIRM">
            <label for="check_confirm">Confirm</label>
            <input type="checkbox" checked onchange="reload_checkbox()" id="check_review" value="REVIEW">
            <label for="check_review">Review</label>
        </div>
            <input id="searchKirimUndangan" class="easyui-searchbox" style="width:320px"
                data-options="prompt:'Cari no undangan / owner / unit',searcher:doSearchKirimUndangan">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" onclick="reloadKirimUndanganGrid()">Reload</a>
        </div>
    </div>

    <table id="dgKirimUndangan"></table>
</div>

<div class="modal fade" id="dlgConfirmHadir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <form id="frmConfirm">
                    <input type="hidden" id="id_conf_kehadiran" name="id_conf_kehadiran" value="">
                    <input type="hidden" id="act_confirm" name="act_confirm" value="">
                    <div class="row g-3 align-items-center">
                        <div class="col-4"><label class="col-form-label">Tanggal</label></div>
                        <div class="col-8"><input type="date" id="tgl_hadir" name="tgl_hadir" class="form-control"></div>
                    </div>
                    <br>
                    <div class="row g-3 align-items-center">
                        <div class="col-4"><label class="col-form-label">Jam</label></div>
                        <div class="col-8"><input type="text" id="jam_hadir" name="jam_hadir" class="form-control clockpicker"></div>
                    </div>
                    <br>
                    <div class="row g-3 align-items-center">
                        <div class="col-4"><label class="col-form-label">Diwakilkan</label></div>
                        <div class="col-8">
                            <select id="diwakilkan" onchange="get_form_diwakilkan()" name="diwakilkan" class="form-select">
                                <option value=""> --- Pilih Perwakilan ---</option>
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div style="display:none;" id="frmAdd">
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">Nama</label></div>
                            <div class="col-8"><input type="text" id="nama_wakil" name="nama_wakil" class="form-control"></div>
                        </div>
                        <br>
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">Tempat Lahir</label></div>
                            <div class="col-8"><input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control"></div>
                        </div>
                        <br>
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">Tanggal Lahir</label></div>
                            <div class="col-8"><input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control"></div>
                        </div>
                        <br>
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">NIK</label></div>
                            <div class="col-8"><input type="text" id="nik_wakil" name="nik_wakil" class="form-control"></div>
                        </div>
                        <br>
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">NPWP</label></div>
                            <div class="col-8"><input type="text" id="npwp_wakil" name="npwp_wakil" class="form-control"></div>
                        </div>
                        <br>
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">Pekerjaan</label></div>
                            <div class="col-8"><input type="text" id="pekerjaan_wakil" name="pekerjaan_wakil" class="form-control"></div>
                        </div>
                        <br>
                        <div class="row g-3 align-items-center">
                            <div class="col-4"><label class="col-form-label">Alamat</label></div>
                            <div class="col-8"><textarea class="form-control" id="alamat_wakil" name="alamat_wakil"></textarea></div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button id="pencet_confirm" type="submit" class="btn btn_warna1 btn_bentuk1">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let tb_checkbox = ['SENT', 'CONFIRM', 'REVIEW'];
    const confirmModal = new bootstrap.Modal(document.getElementById('dlgConfirmHadir'));

    function selectedCheckboxes() {
        const arrCheckbox = [];
        if ($('#check_sent').is(':checked')) arrCheckbox.push($('#check_sent').val());
        if ($('#check_confirm').is(':checked')) arrCheckbox.push($('#check_confirm').val());
        if ($('#check_review').is(':checked')) arrCheckbox.push($('#check_review').val());
        return arrCheckbox;
    }

    function getGridHeight() {
        var wrap = $('.page-wrap');
        var toolbar = $('#toolbarKirimUndangan');
        var title = wrap.find('.warna_teks1');
        var header = wrap.find('.judul_atas');
        var subtitle = wrap.find('.page-subtitle');
        var used = toolbar.outerHeight(true) + title.outerHeight(true) + header.outerHeight(true) + subtitle.outerHeight(true);
        var padding = parseInt(wrap.css('padding-top')) + parseInt(wrap.css('padding-bottom'));
        return $(window).height() - used - padding - 12;
    }

    function reloadKirimUndanganGrid() {
        tb_checkbox = selectedCheckboxes();
        $('#dgKirimUndangan').datagrid('load', {
            tb_checkbox: tb_checkbox,
            search_value: $('#searchKirimUndangan').searchbox('getValue')
        });
    }

    function doSearchKirimUndangan() {
        reloadKirimUndanganGrid();
    }

    function formatStatusEmail(value, row) {
        if (row.id_agreement_email == null) {
            return '<button onclick="kirimEmail(' + row.id + ')" class="btn btn_bentuk1 btn_bputih_warna2" style="border-radius:20px;">Send</button>';
        }
        if (row.id_agreement_email != null && row.waktu_hadir != null) {
            return '<span style="color:grey;">Resend</span>';
        }
        return '<a href="javascript:void(0)" onclick="kirimEmail(' + row.id + ')">Resend</a>';
    }

    function formatConfirm(value, row) {
        if (row.waktu_hadir == null) {
            if (row.no_agreement == null) {
                return '<button onclick="confirmDlg(' + row.id + ')" class="btn btn_bentuk1 btn_warna5" style="border-radius:20px;">Confirm</button>';
            }
            return '<button onclick="viewDetail(' + row.id + ')" class="btn btn_bentuk1 btn-success" style="border-radius:20px;">Review</button>';
        }

        if (row.no_agreement == null) {
            return '<button onclick="reconfirmDlg(' + row.id + ')" class="btn btn_bentuk1 btn_warna5" style="border-radius:20px;">Re-Confirm</button>';
        }
        return '<button onclick="viewDetail(' + row.id + ')" class="btn btn_bentuk1 btn-success" style="border-radius:20px;">Review</button>';
    }

    function formatReferensi(value) {
        if (!value || value.toString().toUpperCase() === 'SERAH TERIMA') {
            return 'HAND OVER';
        }
        return value;
    }

    function formatAksi(value, row) {
        let html = '<div class="action-inline">';
        html += '<button type="button" class="btn btn-sm btn_warna4" onclick="viewDetail(' + row.id + ')">Detail</button>';
        html += '<button type="button" class="btn btn-sm btn_warna1" onclick="printDokumen(' + row.id + ')">Print</button>';
        html += '</div>';
        return html;
    }

    $(document).ready(function () {
        $('#jam_hadir').clockpicker({ autoclose: true });

        $('#dgKirimUndangan').datagrid({
            fit: false,
            height: getGridHeight(),
            method: 'post',
            url: '<?php echo site_url('kirim_undangan/grid'); ?>',
            toolbar: '#toolbarKirimUndangan',
            singleSelect: true,
            rownumbers: true,
            pagination: true,
            pageSize: 10,
            pageList: [10, 25, 50],
            fitColumns: true,
            striped: true,
            remoteSort: true,
            queryParams: {
                tb_checkbox: tb_checkbox,
                search_value: ''
            },
            onBeforeLoad: function (param) {
                param.tb_checkbox = tb_checkbox;
                param.search_value = $('#searchKirimUndangan').searchbox('getValue');
            },
            columns: [[
                { field: 'id', title: 'ID', width: 60, sortable: true, hidden: true },
                { field: 'no_undangan', title: 'No Undangan', width: 160, sortable: true },
                { field: 'tgl_undangan', title: 'Tgl Undangan', width: 100, sortable: true },
                { field: 'nama_owner', title: 'Owner', width: 180, sortable: true },
                { field: 'tipe_tenant', title: 'Tipe', width: 100, sortable: true },
                { field: 'kode_unit', title: 'Unit', width: 90, sortable: true },
                { field: 'no_agreement', title: 'No. Agreement', width: 130, sortable: true },
                { field: 'status_email', title: 'Status Email', width: 110, formatter: formatStatusEmail },
                { field: 'status_confirm', title: 'Confirm', width: 110, formatter: formatConfirm },
                { field: 'waktu_hadir', title: 'Waktu Hadir', width: 120 },
                { field: 'diwakilkan', title: 'Diwakilkan', width: 90 },
                { field: 'tipe_checklist', title: 'Referensi', width: 110, formatter: formatReferensi },
                { field: 'aksi', title: 'Aksi', width: 140, formatter: formatAksi }
            ]]
        });

        $('#dlgConfirmHadir').on('hidden.bs.modal', function () {
            $('#frmConfirm')[0].reset();
            $('#frmAdd').hide();
        });

        $('#frmConfirm').submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'JSON',
                url: '<?php echo site_url('kirim_undangan/submitConfirm'); ?>',
                success: function (response) {
                    if (response.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: response.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        confirmModal.hide();
                        reloadKirimUndanganGrid();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.msg || 'Gagal menyimpan konfirmasi.'
                        });
                    }
                }
            });
        });
    });

    function printDokumen(id) {
        window.location = '<?php echo site_url('kirim_undangan/printFileEmail'); ?>/' + id;
    }

    function viewDetail(id) {
        window.location = '<?php echo site_url('kirim_undangan/detail'); ?>/' + id;
    }

    function get_form_diwakilkan() {
        if ($('#diwakilkan').val() === '1') {
            $('#frmAdd').fadeIn();
        } else {
            $('#frmAdd').fadeOut();
        }
    }

    function reload_checkbox() {
        reloadKirimUndanganGrid();
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
                    reloadKirimUndanganGrid();
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

    function confirmDlg(id) {
        $('#id_conf_kehadiran').val(id);
        $('#act_confirm').val('add');
        confirmModal.show();
    }

    function reconfirmDlg(id) {
        $('#id_conf_kehadiran').val(id);
        $('#act_confirm').val('edit');
        $.ajax({
            type: 'POST',
            data: { id: id },
            dataType: 'JSON',
            url: '<?php echo site_url('kirim_undangan/load_reconfirm'); ?>',
            success: function (resp) {
                if (resp.status == true) {
                    const dt = resp.data;
                    $('#tgl_hadir').val(dt.tgl_hadir);
                    $('#jam_hadir').val(dt.jam_hadir);
                    if (dt.diwakilkan === 'true') {
                        $('#diwakilkan').val(1);
                        $('#diwakilkan').trigger('change');
                        $('#nama_wakil').val(dt.nama_wakil);
                        $('#tempat_lahir').val(dt.tempat_lahir_wakil);
                        $('#tgl_lahir').val(dt.tgl_lahir_wakil);
                        $('#nik_wakil').val(dt.nik_wakil);
                        $('#alamat_wakil').val(dt.alamat_wakil);
                        $('#npwp_wakil').val(dt.npwp_wakil);
                        $('#pekerjaan_wakil').val(dt.pekerjaan_wakil);
                    } else {
                        $('#diwakilkan').val(0);
                    }
                }
                confirmModal.show();
            }
        });
    }

    $(window).on('resize', function () {
        $('#dgKirimUndangan').datagrid('resize', { height: getGridHeight() });
    });
</script>
