<style>
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
        margin-bottom: 16px;
    }

    .table-toolbar label {
        margin-right: 12px;
    }

    .action-inline {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .modal-lg {
        max-width: 700px;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.css">
<script src="<?php echo base_url('assets/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.js"></script>

<div class="container-fluid">
    <div class="judul_atas">
        <div>Transaksi / Kirim Undangan</div>
    </div>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display:inline-block;margin-bottom:20px;">Kirim Undangan</div>

    <div class="table-toolbar">
        <div>
            <input type="checkbox" checked onchange="reload_checkbox()" id="check_sent" value="SENT">
            <label for="check_sent">Sent</label>
            <input type="checkbox" checked onchange="reload_checkbox()" id="check_confirm" value="CONFIRM">
            <label for="check_confirm">Confirm</label>
            <input type="checkbox" checked onchange="reload_checkbox()" id="check_review" value="REVIEW">
            <label for="check_review">Review</label>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table id="myTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th hidden>ID</th>
                        <th>No Undangan</th>
                        <th>Tgl Undangan</th>
                        <th>Owner</th>
                        <th>Tipe</th>
                        <th>Unit</th>
                        <th>No. Agreement</th>
                        <th>Status Email</th>
                        <th>Confirm</th>
                        <th>Waktu Hadir</th>
                        <th>Diwakilkan</th>
                        <th>Referensi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
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
    let otable;
    let tb_checkbox = ['SENT', 'CONFIRM', 'REVIEW'];
    const confirmModal = new bootstrap.Modal(document.getElementById('dlgConfirmHadir'));

    $(document).ready(function () {
        $('#jam_hadir').clockpicker({ autoclose: true });

        otable = $('#myTable').DataTable({
            processing: true,
            responsive: true,
            serverSide: true,
            order: [[0, 'desc']],
            ordering: true,
            info: false,
            ajax: {
                url: '<?php echo site_url('kirim_undangan/grid'); ?>',
                type: 'POST',
                data: function (d) {
                    d.tb_checkbox = tb_checkbox;
                }
            },
            aLengthMenu: [[10, 50], [10, 50]],
            columns: [
                { data: 'id', searchable: false, visible: false },
                { data: 'no_undangan', searchable: false },
                { data: 'tgl_undangan', searchable: false },
                { data: 'nama_owner', searchable: false },
                { data: 'tipe_tenant', searchable: false },
                { data: 'kode_unit', searchable: false },
                { data: 'no_agreement', searchable: false, defaultContent: '-' },
                {
                    data: 'status',
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row) {
                        if (row.id_agreement_email == null) {
                            return '<button onclick="kirimEmail(' + row.id + ')" class="btn btn_bentuk1 btn_bputih_warna2" style="border-radius:20px;">Send</button>';
                        }
                        if (row.id_agreement_email != null && row.waktu_hadir != null) {
                            return '<span style="color:grey;">Resend</span>';
                        }
                        return '<a href="javascript:void(0)" onclick="kirimEmail(' + row.id + ')">Resend</a>';
                    }
                },
                {
                    data: 'status',
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row) {
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
                },
                { data: 'waktu_hadir', searchable: false, orderable: false, defaultContent: '-' },
                { data: 'diwakilkan', searchable: false, orderable: false, defaultContent: '-' },
                {
                    data: 'tipe_checklist',
                    searchable: false,
                    render: function (data) {
                        if (!data || data.toString().toUpperCase() === 'SERAH TERIMA') {
                            return 'HAND OVER';
                        }
                        return data;
                    }
                },
                {
                    data: 'id',
                    searchable: false,
                    orderable: false,
                    render: function (data) {
                        let html = '<div class="action-inline">';
                        html += '<button type="button" class="btn btn-sm btn_warna4" onclick="viewDetail(' + data + ')">Detail</button>';
                        html += '<button type="button" class="btn btn-sm btn_warna1" onclick="printDokumen(' + data + ')">Print</button>';
                        html += '</div>';
                        return html;
                    }
                }
            ]
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
                        otable.ajax.reload();
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
        const arr_checkbox = [];
        if ($('#check_sent').is(':checked')) arr_checkbox.push($('#check_sent').val());
        if ($('#check_confirm').is(':checked')) arr_checkbox.push($('#check_confirm').val());
        if ($('#check_review').is(':checked')) arr_checkbox.push($('#check_review').val());
        tb_checkbox = arr_checkbox;
        otable.ajax.reload();
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
                    otable.ajax.reload();
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
</script>
