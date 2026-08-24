<style>
    .col_input_head {
        padding-right: 50px;
    }

    #btnSearchRtype {
        line-height: 0;
    }

    #table_detail>thead>tr>th {
        text-align: center;
    }

    #btnDeleteDetail:hover {
        color: initial;
    }

    #drop_zone {
        /*border: #B980F0 5px dashed;*/
        border: 2px dashed rgba(221, 223, 225, 1);
        width: 100%;
        padding: 40px 0;
        color: silver;
    }

    #drop_zone p {
        font-size: 20px;
        text-align: center;
    }

    #btn_upload,
    #foto_file {
        display: none;
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



$ids = $this->session->userdata('id_user');

// dd($drop_util);
?>
<div class="container-fluid">
    <div class="judul_atas">
        <div>Transaksi / Utility Record / Input </div>
    </div>
    <button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Utility Record</div><br>
    <div class="row">
        <div class="col-1"></div>
        <div class="col-10">
        <form id="frminput" method="post" enctype="multipart/form-data">
        <div class="row" style="margin-bottom:1rem;">
            <div class="col-sm-6 mb-3">
                <label for="inputPassword" class="">Unit <font style="color:red;">*</font></label>
                <div class="input-group">
                    <input type="hidden" class="form-control" value="<?= @$dtunit->id_unit; ?>" name="id_unit" id="id_unit">
                    <input type="hidden" class="form-control" value="<?= $ids ?>" name="ids" id="ids">
                    <input required disabled="" value="<?= @$dtunit->kode_unit; ?>" type="text" class="form-control" name="unit_show" id="unit_show">
                    <span class="btn-input-group input-group-text" id="basic-addon2"><a href="javascript:;" id="ancUnitCari"><i class="bi bi-search"></i></a></span>
                </div>
            </div>
            <div class="col-sm-6">
                <label for="inputPassword" class="">Utility</label>
                <select class="form-select" onchange="gantiMeterId()" name="id_utilities" id="id_utilities">
                    <option value="">--Pilih Utility--</option>
                    <?php if ($droplist_utilities->status) : ?>
                        <?php foreach ($droplist_utilities->data as $key => $ba) : ?>
                            <option value="<?php echo $ba->id; ?>"><?php echo $ba->nama; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-sm-6 mb-3">
                <label for="inputPassword" class="">Period</label>
                <input type="date" onchange="gantiMeterId()" class="form-control" name="periode" id="periode">
            </div>
            <div class="col-sm-6 mb-3">
                <label for="inputPassword" class="">Meter ID</label>
                <input class="form-control" type="text" id="kode_meter" name="kode_meter">
                <input type="hidden" id="id_meter" name="id_meter">
            </div>

            
            <div class="col-sm-6">
                <label for="inputPassword" class="">Start Meter</label>
                <input class="form-control" type="text" id="start_meter" name="start_meter">
                <!-- <input type="text" class="form-control" name="start_meter" id="start_meter"> -->
            </div>
            <div class="col-sm-6">
                <label for="inputPassword" class="">End Meter</label>
                <input type="text" class="form-control" name="end_meter" id="end_meter">
            </div>
            <div class="col-sm-6">
                <label for="inputPassword6" class="col-form-label">Photo</label>
                <div id="drop_zone">
                    <p>Drop Your Files here</p>
                    <p>or</p>
                    <p><button type="button" id="btn_file_pick" class="btn btn_file_pick"><span class="glyphicon glyphicon-folder-open"></span>Choose File</button></p>
                    <p id="utility_file_info"></p>
                    <input type="file" name="foto_file" id="foto_file">
                    <p id="message_info"></p>
                </div>
            </div>
            <div style="text-align:center;position:relative;top:35px;">
            <button type="button" id="btn_cancel" class="btn btn_warna3 btn_bentuk1">Cancel</button>
            <button id="btnSaveUtilityRecord" type="submit" class="btn btn_warna1 btn_bentuk1">Save</button>
        </div>
    </form>
        </div>
    </div>




    <div id="dlg_step1">
        <!-- Modal -->
        <div class="modal fade" id="dlgUnitCari1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Cari Unit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table id="tableUnitDlg" class="" style="width:100%">
                            <thead>
                                <tr>
                                    <th hidden>ID</th>
                                    <th>Kode Unit</th>
                                    <th>Lantai</th>
                                    <th>Luas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <script>
        var table_rtype;
        var table_detail;
        $(document).ready(function() {
            $('#btn_cancel').click(function(){
                window.location = '<?php echo base_url().$this->router->fetch_class(); ?>';
            });
        });

        $('#btn_file_pick').on('click', function() {
            $('#foto_file').click();
        });

        $('#foto_file').on('change', function() {
            var namafile = $(this).val().split('\\').pop();
            $('#message_info').text('Selected file: ' + namafile);
        });
    </script>
    <?php include_once 'js_utility_record.php'; ?>
    <?php if (!empty($dtunit)) : ?>
        <script>
            $(document).ready(function() {
                $('.s_util').trigger('change');
            });
        </script>
    <?php endif; ?>