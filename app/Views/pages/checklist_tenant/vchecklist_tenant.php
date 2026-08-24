<style>
    .form-selecttt {
        border-radius: 15px;
    }

    .btn-unapprove {
        background-color: rgba(191, 191, 191, 1);
        border-radius: 15px;
        color: white;
    }

    .btn-approve {
        background-color: rgba(206, 233, 206, 1);
        border-radius: 15px;
        color: #60B760;
        ;
    }

    #drop_zone {
        /*border: #B980F0 5px dashed;*/
        border: 1px solid rgba(221, 223, 225, 1);
        width: 100%;
        padding: 40px 0;
        color: silver;
    }

    #drop_zone p {
        font-size: 20px;
        text-align: center;
    }

    #btn_upload,
    #ppjb_file {
        display: initial;
    }

    .btn_file_pick,
    .btn_file_pick:hover {
        border-color: rgba(240, 165, 0, 1);
        color: rgba(240, 165, 0, 1);
        width: 200px;
        border-radius: 30px;
    }

    .modal-lg {
        max-width: 700px;
    }

    #dlgApprovePinContent {
        padding-left: 3%;
        padding-right: 3%;
    }
</style>
<?php
$utildroplist = api('POST', 'utilities/droplist', null);

?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.js"></script>
<div class="container-fluid">
    <div class="judul_atas">
        <div>Transaksi / Checklist Tenant</div>
    </div>
    <button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;display:none;float:left;"><i class="bi bi-x-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Checklist Tenant</div><br>
    <div class="row">
        <div class="col-lg-12">

            <table id="myTable" class="" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>No Agreement</th>
                        <th>No Undangan</th>
                        <th>Hand Over Date</th>
                        <th>Order Date</th>
                        <th>Owner</th>
                        <th>Unit</th>
                        <th>Reff</th>
                        <th>Checklist</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>

            </table>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="editDlg" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--<div class="modal-header">-->
            <!--  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>-->
            <!--  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
            <!--</div>-->
            <div class="modal-body">
                <form id="frmEdit">
                    <input type="hidden" name="d_id" id="d_id">
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-sm-2 col-form-label">Utilities</label>
                        <div class="col-sm-8">
                            <select required class="form-select" data-live-search="true" name="dutil" id="dutil">
                                <option value="">- Pilih Utilities -</option>
                                <?php if ($utildroplist->status) : ?>
                                    <?php foreach ($utildroplist->data as $key => $ba) : ?>
                                        <option value="<?php echo $ba->id; ?>"><?php echo $ba->nama; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-sm-2 col-form-label">Kode</label>
                        <div class="col-sm-8">
                            <input required type="text" class="form-control" id="dkode" name="dkode">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-sm-2 col-form-label">Start Meter</label>
                        <div class="col-sm-8">
                            <input required type="number" class="form-control" id="dstart_meter" name="dstart_meter">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <button type="submit" id="btnSaveEditUtil" class="btn btn_warna1 btn_bentuk1">Save</button>
                        </div>
                    </div>
                </form>
            </div>
            <!--<div class="modal-footer">-->
            <!--  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->

            <!--</div>-->
        </div>
    </div>
</div>


<!-- Modal Print -->
<div class="modal fade" id="dlgPrint" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Print Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" value="" id="id_print" name="id_print">
                <table width="100%">
                    <tr>
                        <th>BAST/Pinjam Pakai</th>
                        <td><button onclick="printDokumen('bast')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                    <tr>
                        <th>Surat Kuasa</th>
                        <td><button onclick="printDokumen('surat_kuasa')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                    <tr>
                        <th>Surat Izin Huni</th>
                        <td><button onclick="printDokumen('izin_huni')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                    <tr>
                        <th>Serah Terima Utilitas</th>
                        <td><button onclick="printDokumen('serah_terima_util')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                    <tr>
                        <th>Tanda Terima</th>
                        <td><button onclick="printDokumen('tanda_terima')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                    <tr>
                        <th>Tata Tertib Penghuni</th>
                        <td><button onclick="printDokumen('tata_tertib')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                    <tr>
                        <th>Checklist Tenant</th>
                        <td><button onclick="printDokumen('checklist_tenant')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>
<!-- Close Modal Print -->


<script>
    var otable;
    var tb_checkbox = ['NEW', 'EDITED', 'REJECTED', 'DONE'];
    var table_tool = '';
    table_tool += '<input type="checkbox" checked onchange="reload_checkbox()" id="check_new" name="check_new" value="NEW"><label> NEW</label>&nbsp;<input onchange="reload_checkbox()" type="checkbox" id="check_approved" name="check_approved" checked value="APPROVED"><label> APPROVED</label>&nbsp;<input onchange="reload_checkbox()" type="checkbox" id="check_rejected" name="check_rejected" checked value="REJECTED"><label> REJECTED</label>&nbsp;<input onchange="reload_checkbox()" type="checkbox" id="check_edited" name="check_edited" checked value="EDITED"><label> EDITED</label>';
    $(document).ready(function() {
        // set_checkbox();
        $('#dlgPrint').on('hidden.bs.modal', function(event) {
            $('#id_print').val('');
        });
        otable = $('#myTable').DataTable({
            "processing": true,
            "responsive": true,
            "serverSide": true,
			"order": [
				[0, 'desc']
			], 
            "ordering": true,
            "info": false,
            "dom": '<"table_tool">frtip',
            "ajax": {
                url: "<?php echo base_url() . $this->uri->segment(1); ?>/grid", // URL file untuk proses select datanya
                type: "POST",
                data: function(d) {
                    d.tb_checkbox = tb_checkbox;
                }
            },
            "deferRender": true,
            "aLengthMenu": [
                [10, 50],
                [10, 50]
            ],
            "columns": [

                {
                    "data": "id",
                    "searchable": false,
                    "visible": false,
                    "name": "id",
                },
                {
                    "data": "no_agreement",
                    "name": "no_agreement",
                    "searchable": false,
                },
                {
                    "data": "no_undangan",
                    "name": "no_undangan",
                    "searchable": false,
                },
                {
                    "data": "handover_date",
                    "name": "handover_date",
                    "searchable": false,
                    "render": function(data, type, row, meta) {
                        if (data == null) {
                            return '-'
                        } else {
                            return formatDate(new Date(data));
                        }
                    }
                },
                {
                    "data": "order_date",
                    "name": "order_date",
                    "searchable": false,
                    "render": function(data, type, row, meta) {
                        if (data == null) {
                            return '-'
                        } else {
                            return formatDate(new Date(data));
                        }
                    }
                },

                {
                    "data": "nama_owner",
                    "name": "nama_owner",
                    "searchable": false,
                },

                {
                    "data": "kode_unit",
                    "name": "kode_unit",
                    "searchable": false,
                },
                {
                    data: "tipe_checklist",
                    searchable: false,
                    render: function (data) {
                        if (!data) return 'HAND OVER';

                        if (data.toString().toUpperCase() === 'SERAH TERIMA') {
                            return 'HAND OVER';
                        }
                        return data;
                    }
                },
                {
                    "data": "id_ctenant",
                    "name": "id_ctenant",
                    "searchable": false,
                    "orderable": false,
                    "render": function(data, type, row, meta) {
                        var a = '';
                        if (data == null) {
                             <?php if ($this->akses->can_create == 1): ?>
                            a += '<a href="javascript:;" class="btn btn-primary btn_bentuk1" onclick="add_checklist(' + row.id + ')" >NEW</a>';
                            <?php else: ?>
                            a += 'NEW';
                             <?php endif; ?>
                        } else {
                            // a += '<button class="btn btn-success btn_bentuk1">Done</button>';
                            a = '<a href="javascript:;" style="color:#30A64A;" onclick="viewChecklist(' + row.id + ')"><u style="font-weight: bold;">DONE</u></a>';
                        }
                        return a;
                    }
                },

                {
                    "data": "p_id",
                    "searchable": false,
                    "orderable": false,
                    "render": function(data, type, row, meta) {
                        // console.log(row);
                        var a = '';
                        <?php if ($this->akses->can_view != 1 && $this->akses->can_edit != 1 && $this->akses->can_delete != 1 && $this->akses->can_approve != 1) : ?>
                            a += '<div class="dropdown"><button disabled class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>';
                        <?php else : ?>
                        a += '<div class="dropdown"><button  class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>';
                            if (row.no_agreement !== null) {
                                
                                a += '<ul class="dropdown-menu bms-dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                                a += '<li><a class="dropdown-item bms-dropdown-item" onclick="viewDetail(' + data + ')" href="javascript:;" ><i class="bi bi-eye"></i>  Detail</a></li>';
                                a += '<li><a class="dropdown-item bms-dropdown-item" onclick="dlgPrint(' + data + ')" href="javascript:;" ><i class="bi bi-printer"></i>  Print</a></li>';
                            }
                            <?php if ($this->akses->can_approve == 1) : ?>
                                if (row.status == 'NEW' || row.status == 'EDITED') {
                                    a += '<li><a class="dropdown-item bms-dropdown-item" onclick="approvedat(' + data + ')" href="javascript:;" ><i class="bi bi-check2-circle"></i>  Approve</a></li>';
                                    a += '<li><a class="dropdown-item bms-dropdown-item"  onclick="rejectdat(' + data + ')" href="javascript:;" ><i class="bi bi-folder-x"></i>  Reject</a></li>';
                                }

                            <?php endif; ?>

                            if (row.file_ppjb != null) {
                                a += '<li><a class="dropdown-item bms-dropdown-item" href="javascript:;" ><i class="bi bi-file-earmark-arrow-down"></i>  Lihat File PPJB</a></li>';
                            }
                            a += '</ul>';
                        <?php endif; ?>
                        a += '</div>';
                        return a;
                    }
                },


            ],
            "languange": {
                "processing": '<span>Loading</span>',
            },
        });
        $('div.table_tool').html(table_tool);
        $('#frmAdd').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                data: {
                    utilities: $('#autil').val(),
                    kode: $('#akode').val(),
                    start_meter: $('#astart_meter').val(),
                },
                dataType: "JSON",
                url: '<?php echo base_url(); ?>meterid/saveNewData/',
                beforeSend: function() {
                    // show_load();
                    $('#btnSave').html('Loading');
                },
                complete: function() {
                    // hide_load();
                    $('#btnSave').html('<i class="bi bi-save"></i>&nbsp;Save');
                },
                success: function(response) {
                    if (response.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: response.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#frmAdd').fadeOut();
                        $('#btn_add').show();
                        otable.ajax.reload();
                        resetFormAdd();
                        $('#btnCancelAdd').hide();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.msg,
                            footer: '',
                            timer: 1500
                        })
                    }
                }
            });
        });


        $("#drop_zone").on("dragover", function(event) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        });
        $("#drop_zone").on("drop", function(event) {
            event.preventDefault();
            event.stopPropagation();
            fileobj = event.originalEvent.dataTransfer.files[0];
            var fname = fileobj.name;
            var fsize = fileobj.size;
            var ftype = fileobj.type;
            var flast_mod = fileobj.lastModified;

            if (fname.length > 0) {
                document.getElementById('ppjb_file_info').innerHTML = "File name : " + fname + ' <br>File size : ' + bytesToSize(fsize);
            }
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fileobj);
            document.getElementById('ppjb_file').files = dataTransfer.files;
        });
        $('#btn_file_pick').click(function() {
            /*normal file pick*/
            document.getElementById('ppjb_file').click();
            document.getElementById('ppjb_file').onchange = function() {
                fileobj = document.getElementById('ppjb_file').files[0];
                var fname = fileobj.name;
                var fsize = fileobj.size;
                if (fname.length > 0) {
                    document.getElementById('ppjb_file_info').innerHTML = "File name : " + fname + ' <br>File size : ' + bytesToSize(fsize);
                }

            };
        });
    });


    function dlgPrint(id) {
        $('#id_print').val(id);
        $('#dlgPrint').modal('show');
    }

    function add_checklist(id) {
        window.location = '<?php echo base_url() . $this->uri->segment(1); ?>/add_new/' + id;
    }

    function viewDetail(id) {
        window.location = '<?php echo base_url() . $this->uri->segment(1); ?>/detailUndangan/' + id;
    }

    function editData(id) {
        window.location = '<?php echo base_url() . $this->uri->segment(1); ?>/edit/' + id;
    }

    function viewChecklist(id) {
        window.location = '<?php echo base_url() . $this->uri->segment(1); ?>/view/' + id;
    }

    function reload_checkbox() {
        var arr_checkbox = [];
        if ($('#check_new').is(":checked") == true) {
            arr_checkbox.push($('#check_new').val());
        }
        if ($('#check_done').is(":checked") == true) {
            arr_checkbox.push($('#check_done').val());
        }
        //   console.log(arr_checkbox);
        tb_checkbox = arr_checkbox;
        otable.ajax.reload();
    }

    function printDokumen(param) {
        var id = $('#id_print').val();
        window.open('<?php echo base_url() . $this->router->fetch_class(); ?>/print_dokumen/' + param + '/' + id);
    }

    function set_checkbox() {

        var arr_checkbox = [];
        if ($('#check_new').is(":checked") == true) {
            tb_checkbox.push($('#check_new').val());
        }
        if ($('#check_approved').is(":checked") == true) {
            tb_checkbox.push($('#check_approved').val());
        }
        if ($('#check_rejected').is(":checked") == true) {
            tb_checkbox.push($('#check_rejected').val());
        }

        tb_checkbox = arr_checkbox;

    }
</script>
