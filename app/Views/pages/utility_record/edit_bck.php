<style>
    .col_input_head{
        padding-right: 50px;
    }
    #btnSearchRtype{
        line-height: 0;
    }
    
   #table_detail > thead > tr > th {
    text-align: center;
   }
   #btnDeleteDetail:hover{
       color: initial;
   }
#drop_zone {
	/*border: #B980F0 5px dashed;*/
	border: 2px dashed rgba(221, 223, 225, 1);
	width: 100%;
	padding : 40px 0;
	color:silver;
}
#drop_zone p {
	font-size: 20px;
	text-align: center;
}
#btn_upload, #foto_file {
	display: none;
}
.btn_file_pick, .btn_file_pick:hover{
    border-color: rgba(240, 165, 0, 1);
    color: rgba(240, 165, 0, 1);
    width: 200px;
    border-radius: 30px;
}
.modal-lg{
    max-width: 700px;
}
#dlgApprovePinContent{
    padding-left: 3%;
    padding-right: 3%;
}
</style>
<?php
$droplist_building = api('POST','building/dropList',NULL);
$droplist_utilities = api('POST','utility_record/dropListUtilities',NULL);
$droplist_meterid = api('POST','meterid/dropListMeterID',NULL);
?>
<div class="container-fluid">
    <div  class="judul_atas"><div>Transaksi / Utility Record / Input </div></div>
    <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Utility Record</div><br>
    <form>
        <div class="row" style="margin-bottom:1rem;">
            <div class="col-sm-6 mb-3">
                <label for="inputPassword" class="">Unit  <font style="color:red;">*</font></label>
                <div class="input-group">
                    <input type="hidden" class="form-control" value="<?= @$dtunit->id_unit; ?>"  name="id_unit" id="id_unit">
                  <input required value="<?= @$dtunit->kode_unit; ?>" type="text" class="form-control" name="unit_show" id="unit_show">
                  <span class="btn-input-group input-group-text" id="basic-adon2"><a  href="javascript:;" id="ancUnitCari"><i class="bi bi-search"></i></a></span>
                </div>
            </div>
            <!--<div class="col-sm-6">-->
            <!--    <label for="inputPassword" class="">Utility</label>-->
            <!--    <select class="form-select" name="autility" id="autility">-->
            <!--        <option value="">--Pilih Utility--</option>-->
            <!--            <?php if ($droplist_utilities->status): ?>-->
            <!--                <?php foreach ($droplist_utilities->data as $key => $ba): ?>-->
            <!--                    <option value="<?php echo $ba->id; ?>"><?php echo $ba->nama;?></option>-->
            <!--                <?php endforeach; ?>-->
            <!--            <?php endif; ?>-->
            <!--    </select>-->
            <!--</div>-->
             <div class="col-sm-6">
                <label for="inputPassword" class="">Utility</label>
                <select class="form-select" id="tipe">
                  <option value="">--Pilih Utility--</option>
                  <?php if ($droplist_utilities->status): ?>
                      <?php foreach ($droplist_utilities->data as $key => $ba): ?>
                        <?php
                        $selected = '';
                        if($ba->id == $dth->id_utilities){
                            $selected = 'selected';
                        }
                        ?>
                          <option <?php echo $selected; ?> value="<?php echo $ba->id; ?>"><?php echo $ba->nama; ?></option>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </select>
          </div>
          <div class="col-sm-6">
                <label for="inputPassword" class="">Meter ID</label>
                <select class="form-select" id="tipe">
                  <option value="">--Pilih Utility--</option>
                  <?php if ($droplist_meterid->status): ?>
                      <?php foreach ($droplist_meterid->data as $key => $ba): ?>
                        <?php
                        $selected = '';
                        if($ba->id == $dth->id_meter){
                            $selected = 'selected';
                        }
                        ?>
                          <option <?php echo $selected; ?> value="<?php echo $ba->id; ?>"><?php echo $ba->kode; ?></option>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </select>
            </div>
            
            <div class="col-sm-6">
                <label for="inputPassword" class="">Period</label>
                <input  type="date" class="form-control" value="<?php echo $dth->periode; ?>" name="tgl_periode"  id="id_periode">
            </div>
            <div class="col-sm-6">
                <label for="inputPassword" class="">End Meter</label>
                <input  type="text" class="form-control"  value="<?php echo $dth->end_meter; ?>" name="end_meter" id="end_meter">
            </div>
            <div class="col-sm-6">
                <label for="inputPassword6" class="col-form-label">Photo</label>
                 <div id="drop_zone">
                	<p>Drop Your Files here</p>
                	<p>or</p>
                	<p><button type="button" id="btn_file_pick" class="btn btn_file_pick"><span class="glyphicon glyphicon-folder-open"></span>Choose File</button></p>
                	<p id="ppjb_file_info"></p>
                	
                	<input  type="file" name="foto_file" id="foto_file">
                	<p id="message_info"></p>
                </div>
            </div>
    </form>
    
    
    
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
    
    <div id="dlg_step2">
    <!-- Modal -->
    <div class="modal fade" id="dlgMeterIDCari" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cari Meter ID</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <table id="tableUnitDlg" class="" style="width:100%">
        <thead>
            <tr>
                <th hidden>ID</th>
                <th>Nama Utilities</th>
                <th>Kode</th>
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
    <div style="text-align:center;position:relative;top:35px;">
        <button class="btn btn_warna3 btn_bentuk1">Cancel</button>
        <button id="btnSaveUtilityRecord" class="btn btn_warna1 btn_bentuk1">Save</button>
    </div>
</div>
<script>
var table_rtype;
var table_detail;
    $(document).ready(function(){
        table_rtype = $('#table_rtype').DataTable({
        "processing": true,
        "responsive": true,
        "serverSide": true,
        "ordering": true,
        "info": false,
        "dom": '<"table_tool">frtip',
        "ajax": {
                    url: "<?php echo base_url(); ?>unit/grid", // URL file untuk proses select datanya
                    type: "POST",
                    data:function ( d ) {
                        d.kode = $('#akode').val();
                        d.nilai = $('#anilai').val();
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
                        "searchable" : false,
                        "visible":false
                    }, 
                    {
                        "data": "kode",
                         "searchable" : false,
                    },
                    {
                        "data": "nama",
                        "searchable" : false,
                    }, 
                    {
                        "data": "id",
                         "searchable" : false,
                         "render": 
                            function( data, type, row, meta ) {
                               
                                var a = '';
                                a += '<button data-id="'+row['id']+'" data-nama="'+row['nama']+'" data-kode="'+row['kode']+'" onclick="setRtype(this)" class="btn btn_warna1 btn-sm " type="button" >Pilih</button>';
                                return a;
                            }
                    },
                    
                    
                ],
        "languange": {
            "processing": '<span>Loading</span>',
        },
    });
    table_detail = $('#table_detail').DataTable({"dom":'rti',
            "info" : false,});
    $('#table_detail tbody').on( 'click', '#btnDeleteDetail', function () {
    table_detail.row( $(this).parents('tr') ).remove().draw();
            } );
    $('#btnSearchRtype').click(function(){
        $('#dlgUnit').modal('show');
    });
    $('#btnSave').click(function(){
        var data = table_detail.$('input, select').serialize();
         $.ajax({
                type: "POST",
                data: data,
                dataType: "JSON",
                url: '<?php echo base_url(); ?>utility_record/convert_array/',
                success: function(response) {
                    saveData(response);
                    console.log(response);
                }
            });
    });
    });
    function setRtype(data){
        var id = $(data).attr("data-id");
        var kode = $(data).attr("data-kode");
        var nama = $(data).attr("data-nama");
        // console.log(kode);
        $('#kode_unit').val(kode_unit);
        $('#building').val(id_building);
        $('#balkon').val(id_balkon);
        $('#tenant').val(id_tenant);
        $('#view').val(id_view);
        $('#no_urut').val(no_urut);
        $('#daya').val(daya);
        $('#lantai').val(lantai);
        $('#luas').val(luas);
        $('#deskripsi').val(luas);
        $('#twobr').val(twobr);
        $('#dlgUnit').modal('hide');
    }
    function saveData(data){
        // var id_rtype = $('#id_rtype').val();
        var kode_unit = $('#kode_unit').val();
        var id_building = $('#building').val();
        var id_balkon = $('#balkon').val();
        var id_tenant = $('#tenant').val();
        var id_view = $('#view').val();
        var no_urut = $('#no_urut').val();
        var daya = $('#daya').val();
        var deskripsi = $('#deskripsi').val();
        var lantai = $('#lantai').val();
        var luas = $('#luas').val();
        var twobr = $('#twobr').val();
        $.ajax({
                type: "POST",
                data: {
                    kode_unit: kode_unit,
                    id_building: id_building,
                    id_balkon: id_balkon,
                    id_tenant: id_tenant,
                    id_view: id_view,
                    no_urut: no_urut,
                    daya: daya,
                    deskripsi: deskripsi,
                    lantai: lantai,
                    luas: luas,
                    twobr: twobr,
                    dt: data
                },
                dataType: "JSON",
                url: '<?php echo base_url(); ?>unit/saveData/',
                success: function(response) {
                    if(response.status){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        window.location = '<?php echo base_url(); ?>unit';
                    }else{
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
    }
     $(document).ready(function(){
        $('#btnCancelAdd').click(function(){
            window.location = '<?php echo base_url(); ?>unit';
        });
    });
    
    $('#btn_file_pick').on('click', function() {
    $('#foto_file').click();
});

$('#foto_file').on('change', function() {
    var filename = $(this).val().split('\\').pop();
    $('#message_info').text('Selected file: ' + filename);
});

</script>
<?php include_once 'js_utility_record.php'; ?>
<?php if (!empty($dtunit)): ?>
    <script>
        $(document).ready(function(){
            $('.s_util').trigger('change');
        });
</script>
<?php endif; ?>