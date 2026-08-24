<style>
    .form-selecttt{
        border-radius: 15px;
    }
    .btn-unapprove{
        background-color: rgba(191, 191, 191, 1);
        border-radius: 15px;
        color:white;
    }
    .btn-approve{
        background-color: rgba(206, 233, 206, 1);
        border-radius: 15px;
        color:#60B760;;
    }
    #checkbox-color {
      accent-color: #30A64A;
    }
    #dlgApprovePinContent{
    padding-left: 3%;
    padding-right: 3%;
    
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

	.popover {
		z-index: 9999;
	}
</style>
<?php 
$utildroplist = api('POST','utilities/droplist',null);
$util = "SELECT * from m_utilities where flag_id = true and id = 2";
$dtutil =  $this->db->query($util)->result();


?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.js"></script>
<div class="container-fluid">
    <div  class="judul_atas"><div>Transaksi / Utility Record</div></div>
    <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:none;float:left;"><i class="bi bi-x-circle"></i></button>
    <!-- <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Utility Record</div><br> -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <form autocomplete="false" autofill="false">
        <div class="row">
        <div class="col-sm-3 mb-3">
                <label class="">Unit</label>
                <input type="text" class="form-control form-control-sm" name="gunit" id="gunit">
            </div>
            <div class="col-sm-3 mb-3">
                <label class="">Periode</label>
                <input type="text" class="form-control form-control-sm" name="gperiode" id="gperiode">
            </div>
            <div class="col-sm-3 mb-3">
                <label class="">Status</label>
                <select class="form-select form-select-sm short-select" id="gstatus" name="gstatus">
                    <option value=''>Pilih..</option>
                    <option value="NEW">NEW</option>
                    <option value="DONE">DONE</option>
                    <option value="EDITED">EDITED</option>
                </select>
            </div>
            <div class="col-sm-2 mb-3">
                <label class=""></label>
                <button type="button" onclick="reload_grid()" class="btn btn_warna2 ">Search</button>
                </div>
            </div></form>
    <div style="text-align: right;">
        <?php if ($this->akses->can_create == 1) : ?>
            <button type="button" id="btn_add" style="margin-right: 3px;" class="btn btn_warna2 btn-sm">
            <i class="bi bi-plus"></i> Add New
            </button>
            <button type="button" onclick="printData()" id="btnPrintData" name="cetak" value="print" style="margin-right: 3px;" class="btn btn-success btn-sm">
            <i class="bi bi-download"></i>   Print
            </button>
            <button type="button"  id="btnPrintTemplate" name="cetak" value="print" style=" margin-right: 3px;" class="btn btn-primary btn-sm">
            <i class="bi bi-download"></i> Template
            </button>
            <button type="button" onclick="upload()" id="btn_upload" style="margin-right: 3px;" class="btn btn-warning btn-sm">
            <i class="bi bi-upload"></i> Upload
            </button>
        <?php endif; ?>
    </div>
    </div>
    <?php
    $berhasilUploadData = $this->session->flashdata('Berhasil Upload Data');
    $gagalUploadData = $this->session->flashdata('Gagal Upload Data');

    if (!empty($berhasilUploadData)) {
        echo '<div id="success-alert" class="alert alert-success">' . $berhasilUploadData . '</div>';
    } elseif (!empty($gagalUploadData)) {
        echo '<div id="error-alert" class="alert alert-danger">' . $gagalUploadData . '</div>';
    }
    ?>
    <div class="row">
        <div class="col-lg-12">
            <table id="myTable" class="" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th></th>
                <th>Owner</th>
                <th>Pembebanan</th>
                <th>Unit</th>
                <th>Utility</th>
                <th>Meter ID</th>
                <th>Period</th>
                <th>Start Meter</th>
                <th>End Meter</th>
                <!--<th>Photo</th>-->
                <th>Status</th>
                <th>No Invoice</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        
    </table>
        </div>
    </div>
</div>

<!-- Modal Approve 1 -->
<div class="modal fade" id="dlgApprovePin" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div id="dlgApprovePinContent" class="modal-content">
      <div class="modal-body">
          <input type="hidden" id="id_approve" name="id_approve" value=""> 
        <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">PIN</label>
          </div>
          <div class="col-8">
            <input type="password" id="pinApprove" name="pinApprove" class="form-control" aria-describedby="passwordHelpInline">
          </div>
        </div>
        <br>
        
        <div class="row">
              <div class="col-md-12 text-center">
            <button id="clickApprove" onclick="submitApprove()" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Submit</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Approve 1 -->
<!-- Modal Detail 1 -->
<div class="modal fade" id="dlg_detail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 500px;"> <!-- Set a specific max-width -->
    <div id="dlgApprovePinContent" class="modal-content">
      <div class="modal-body">
         <div class="row">
             <div class="col-md-12 text-center mb-3" style="font-weight: bold;">
                 <div id="de_kodeunit">Unit:</div>
             </div>
         </div>
        <table style="width:100%">
            <tbody>
                <tr>
                    <td style="text-align:left;">Utility</td>
                    <td id="mutil_d" style="text-align:right;"></td>
                </tr>
                 <tr>
                    <td style="text-align:left;">Meter ID</td>
                    <td id="meterid_d" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td style="text-align:left;">Periode</td>
                    <td id="periode_d" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td style="text-align:left;">Start Meter</td>
                    <td id="start_meter_d" style="text-align:right;"></td>
                </tr>
                 <tr>
                    <td style="text-align:left;">End Meter</td>
                    <td id="end_meter_d" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td style="text-align:left;">Input By</td>
                    <td id="input_by_d" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td style="text-align:left;">Input Date</td>
                    <td id="input_date_d" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td id="foto_meter_id" >
                        <img src="" alt="" style="max-width: 100%; max-height: 400px; margin-top:20px;"> <!-- Adjust max-width and max-height -->
                    </td>
                </tr>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<!-- Close Modal Detail 1 -->

<!-- Modal Approve Array -->
<div class="modal fade" id="dlgArrApprovePin" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div id="dlgApprovePinContent" class="modal-content">
      <div class="modal-body">
          <input type="hidden" id="id_approve" name="id_approve" value=""> 
        <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">PIN</label>
          </div>
          <div class="col-8">
            <input type="password" id="pinApprove" name="pinApprove" class="form-control" aria-describedby="passwordHelpInline">
          </div>
        </div>
        <br>
        
        <div class="row">
              <div class="col-md-12 text-center">
            <button id="clickApprove" onclick=" submitArrApprove()" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Submit</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Approve Array -->

<!-- Modal Reject 1 -->
<div class="modal fade" id="dlgRejectPin" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div id="dlgApprovePinContent" class="modal-content">
      <div class="modal-body">
          <input type="hidden" id="id_reject" name="id_reject" value=""> 
        <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">PIN</label>
          </div>
          <div class="col-8">
            <input type="password" id="pinReject" name="pinReject" class="form-control" aria-describedby="passwordHelpInline">
          </div>
        </div>
        <br>
        <div style="display:none;" id="isi_data_reject">
          <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">Keterangan Reject</label>
          </div>
          <div class="col-8">
            <textarea class="form-control" id="keteranganReject" name="keteranganReject"></textarea>
          </div>
        </div>  
        </div>
        <br>
        <div class="row">
              <div class="col-md-12 text-center">
            <button id="clickReject" onclick="submitReject(this)" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Check</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Reject 1 -->

<!-- Modal Detail -->
<div class="modal fade" id="dlgDetail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div id="dlgApprovePinContent" class="modal-content">
      <div class="modal-body">
          <input type="hidden" id="id_reject" name="id_reject" value=""> 
        <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">PIN</label>
          </div>
          <div class="col-8">
            <input type="password" id="pinReject" name="pinReject" class="form-control" aria-describedby="passwordHelpInline">
          </div>
        </div>
        <br>
        <div style="display:none;" id="isi_data_reject">
          <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">Keterangan Reject</label>
          </div>
          <div class="col-8">
            <textarea class="form-control" id="keteranganReject" name="keteranganReject"></textarea>
          </div>
        </div>  
        </div>
        <br>
        <div class="row">
              <div class="col-md-12 text-center">
            <button id="clickReject" onclick="submitReject(this)" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Check</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Reject 1 -->
<!--  Modal Print Data -->
<div class="modal fade" id="dlgPrintData" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div id="dlgApprovePinContent" class="modal-content">
			<div class="modal-body">
             <form id="frmConfirm" target="_blank" method="POST" enctype="multipart/form-data" action="utility_record/print_data">
					<input type="hidden" id="id_agreement" name="id_agreement" value="">
                    <div class="row mt-3">
                        <br>
                             <div class="col-lg-12">
                                <label class="">Period</label>
                                 <input type="text" class="form-control form-control" name="speriode"  id="speriode">
                            </div> 
                        </div>
                    </div>
                    <div class="card-body">
                            <button  id="btnSave" class="btn btn_warna1 btn_bentuk1" name="cetak" value="print"><i class="bi bi-printer"></i> Print</button>
                            <button  id="btnSave" class="btn btn_warna1 btn_bentuk1" name="cetak" value="preview"><i class="bi bi-eye"></i> Preview</button>
                    </div>
                    <br>
				</form>
			</div>

		</div>
	</div>
    <!--  Modal Print template -->
<div class="modal fade" id="dlgPrintTemplate" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
    
		<div class="modal-content">
        <div class="modal-header">
        <p class="modal-title"><strong>Download Template<strong></p>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
			<div class="modal-body">
             <form id="frmConfirm" target="_blank" method="POST" enctype="multipart/form-data" action="utility_record/print_template">
					<input type="hidden" id="id_agreement" name="id_agreement" value="">
                    <div class="row mt-3">
                        <br>
                             <div class="col-lg-12">
                                <label class="">Utilities</label>
                                 <!-- <input type="text" class="form-control form-control" name="print_template_id_utilities" required id="print_template_id_utilities"> -->
                                <select required class="form-select" name="print_template_id_utilities" id="print_template_id_utilities">
                                 <?php foreach ($utility_list as $key => $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->nama; ?></option>
                               <?php } ?>
                               </select>
                            </div> 
                    </div>
                    <div class="row mt-3">
                        <br>
                             <div class="col-lg-12">
                                <label class="">Period</label>
                                 <input type="text" class="form-control form-control" name="pperiode_template" required id="pperiode_template">
                            </div> 
                    </div>
                    </div>
                    <div class="card-body">
                            <button class="btn btn_warna1 btn_bentuk1" name="cetak" value="print"><i class="bi bi-printer"></i> Print</button>
                            <button class="btn btn_warna1 btn_bentuk1" name="cetak" value="preview"><i class="bi bi-eye"></i> Preview</button>
                    </div>
                    <br>
				</form>
			</div>

		</div>
	</div>
    <!-- close modal print template -->
</div>
<!--  Modal Upload -->
<div class="modal fade" id="dlgUpload" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div id="dlgApprovePinContent" class="modal-content">
			<div class="modal-body">
             <form id="frmConfirm" method="POST" enctype="multipart/form-data" action="<?php echo base_url().$this->router->fetch_class() ?>/imp_temp_xlsx">
					<input type="hidden" id="id_agreement" name="id_agreement" value="">
                    <div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="start_date" class="col-form-label">Start Date</label>
						</div>
						<div class="col-8">
							<input type="date" id="start_date" name="start_date" class="form-control" required>
						</div>
					</div>
					<br>
					<div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="end_date" class="col-form-label">End Date</label>
						</div>
						<div class="col-8">
							<input type="date" id="end_date" name="end_date" class="form-control" required>
						</div>
					</div><br><div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="speriode" class="col-form-label">Period</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control" name="uperiode" id="uperiode" required>
						</div>
					</div><br>
                    <div class="row g-3 align-items-center">
                        <div class="col-4">
                            <label class="col-form-label" for="utility">Utility</label>
                        </div>
                        <div class="col-8">
                            <select class="form-select short-select" id="utility" name="utility" required>
                                <?php foreach ($dtutil as $item): ?>
                                    <option value="<?= $item->id; ?>"><?= $item->nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="inputPassword6" class="col-form-label">Upload File (XLSX)</label>
						</div>
						<div class="col-8">
								<input required class="form-control" type="file" name="data" id="ppjb_file">
								<p id="message_info"></p>
						</div>
					</div>
                     <br>
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
var otable;
var arr_checkbox = [];
var akses_add = '<?php echo $this->akses->can_create; ?>';
var akses_edit = '<?php echo $this->akses->can_edit; ?>';
var akses_delete = '<?php echo $this->akses->can_delete; ?>';
var button_checkbox = ['NEW','APPROVED','REJECTED'];
var table_tool = '<div id="table_tool">';

    table_tool += '</div>';
    $(document).ready(function () {
        $( "#speriode" ).datepicker({
        format: 'mm-yyyy',
        minViewMode: 1,
        maxViewMode: 2,
        }).on('changeDate', function(e){
            $(this).datepicker('hide');
        });
        $( "#uperiode" ).datepicker({
        format: 'mm-yyyy',
        minViewMode: 1,
        maxViewMode: 2,
        }).on('changeDate', function(e){
            $(this).datepicker('hide');
        });
        $( "#pperiode_template" ).datepicker({
        format: 'mm-yyyy',
        minViewMode: 1,
        maxViewMode: 2,
        }).on('changeDate', function(e){
            $(this).datepicker('hide');
        });
        // setTimeout(function () {
        //     document.getElementById('success-alert').style.display = 'none';
        //      document.getElementById('error-alert').style.display = 'none';
        //  }, 3000);
        $(document).ready(function() {
            $( "#gperiode" ).datepicker({
            format: 'mm-yyyy',
            minViewMode: 1,
            maxViewMode: 2,
            }).on('changeDate', function(e){
                    $(this).datepicker('hide');
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

    otable = $('#myTable').DataTable({
        "processing": true,
        "responsive": true,
        "serverSide": true,
        "preDrawCallback": function() {
                let el = $('div.dataTables_filter label');
                if(!el.parent('form').length) {
                    el.wrapAll('<form></form>').parent()
                    .attr('autocomplete', false)
                    .attr('autofill', false);
                }
            },
        "ordering": true,
        "scrollY":"500px",
        "scrollX":"60%",
        "pageLength": 20,
        "order": [
				[0, 'desc']
				],
        "searching": false,
        "info": false,
        "dom": '<"table_tool">frtip',
        "ajax": {
                    url: "<?php echo base_url(); ?>utility_record/grid", // URL file untuk proses select datanya
                    type: "POST",
                    data:function ( d ) {
                        d.tb_checkbox = '';
                        d.unit = $('#gunit').val();
                        d.periode = $('#gperiode').val();
                        d.status = $('#gstatus').val();
                        d.kode = $('#akode').val();
                        d.nilai = $('#anilai').val();
                        d.button_checkbox = button_checkbox;
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
                        "data": "status",
                        "searchable": false,
                        "visible":false,
                        "render": function (data, type, row) {
                            var idp = row.id;
                            if (data === "DONE" || data === "REJECTED") {
                                return '<input type="checkbox" disabled ' + (data ? 'checked' : '') + '>';
                            } else {
                                // arr_checkbox[] disini
                                if(arr_checkbox.includes(idp.toString()) == true){
                                return '<input data-id="'+row.id+'" onchange="bundle_checkbox(this)" checked type="checkbox">';    
                                }else{
                                    return '<input data-id="'+row.id+'" onchange="bundle_checkbox(this)" type="checkbox">'; 
                                }
                                

                                // return '<input value="'+row.id+'" type="checkbox"  onchange="bundle_checkbox(this)">';
                            }
                        }
                    },
                    
                    {
                        "data": "nama_owner",
                         "searchable" : false,
                    },
                    {
                        "data": "nama_customer",
                         "searchable" : false,
                    },
                    {
                        "data": "kode_unit",
                         "searchable" : false,
                    },
                    {
                        "data": "nama_utilities",
                         "searchable" : false,
                    },
                    {
                        "data": "kode_meter",
                         "searchable" : false,
                    },
                    {
                        "data": "periode",
                        "searchable": false,
                        "render": function(data, type, row, meta) {
                            if (type === 'display') {
                                var date = new Date(data);
                                var month = date.toLocaleString('default', { month: 'long' });
                                var year = date.getFullYear();
                                return month + '/' + year;
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        "data": "start_meter",
                         "searchable" : false,
                    },
                    {
                        "data": "end_meter",
                         "searchable" : false,
                    },
                    // {
                    //       "data": "namafile",
                    //       "searchable": false,
                    //       "render": function(data, type, full, meta) {
                    //         return '<button onclick="viewPhoto(\'' + full.foto + '\')" type="button" style="border-radius:20px;" class="btn btn_warna4 btn_bentuk1">View</button>';
                    //   }
                    // },
                     {
                        "data": "status",
                         "searchable" : false,
                         "render": 
                            function( data, type, row, meta ){
                                var a = '';
                                if(row.id_utilitystatus == null && data == 'NEW'){
                                    a= '<font style="color:#1E90FF">'+data+'</font>';
                                }else{
                                    if(data == 'NEW'){
                                       a= '<font style="color:#1E90FF" >'+data+'</font>';
                                    }else if(data == 'EDITED'){
                                        a= '<font style="color:#FFA500;">'+data+'</font>';
                                    }
                                    else if(data == 'DONE'){
                                           a= '<font style="color:green;">'+data+'</font>';
                                    }
                                    else if(data == 'REJECTED'){
                                           a= '<font style="color:red;">'+data+'</font>';
                                    }
                                    else{
                                         a= '<font style="color:#1E90FF">'+data+'</font>';
                                    }
                                }
                                return a;
                            }
                    },
                    {
                        "data": "no_invoice",
                         "searchable" : false,
                    },
                    {
                        "data": "id",
                         "searchable" : false,
                         "render": 
                            function( data, type, row, meta ) {
                                var a = '';
                                <?php if($this->akses->can_edit !=1 && $this->akses->can_delete !=1 && $this->akses->can_approve !=1 && $this->akses->can_view != 1): ?>
                                a += '<div class="dropdown"><button class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>';
                                <?php else: ?>
                                 a += '<div class="dropdown"><button  class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>'; 
                                  a += '<ul class="dropdown-menu bms-dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                                a += '<li><a class="dropdown-item bms-dropdown-item" onclick="viewDetail('+data+')" href="javascript:;" ><i class="bi bi-eye"></i>  Detail</a></li>';
                                if(row.no_invoice == null){
                                      a += '';
                                      <?php if($this->akses->can_edit ==1) : ?>
                                      a+= '<li><a class="dropdown-item bms-dropdown-item" href="javascript:;" onclick="editData('+data+')"><i class="bi bi-pencil-square"></i>  Edit Data</a></li>';
                                      <?php endif; ?>
                                      
                                      <?php if($this->akses->can_delete ==1) : ?>
                                        if(row.status != 'REJECTED'){
                                    a+= '<li><a class="dropdown-item bms-dropdown-item" href="javascript:;" onclick="delete_confirm('+data+')"><i class="bi bi-trash"></i>  Reject</a></li>';
                                        }
                                    a += '';
                                        <?php endif; ?>
                                }
                             
                                if(row.status != 'DONE'){
                                  a += '<li><a class="dropdown-item bms-dropdown-item" onclick="approvedat('+data+')" href="javascript:;" onchange="arr_checkbox[]" ><i class="bi bi-check2-circle"></i>  Approve</a></li>';
                                }
                                <?php endif; ?>
                                 a += '</ul>';
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
    $('#frmAdd').submit(function(e){
        e.preventDefault();
        $.ajax({
                type: "POST",
                data: {
                    nama: $('#anama').val(),
                },
                dataType: "JSON",
                url: '<?php echo base_url(); ?>utility_record/saveNewData/',
                beforeSend: function() {
                    // show_load();
                    $('#btnSave').html('Loading');
                },
                complete: function() {
                    // hide_load();
                    $('#btnSave').html('<i class="bi bi-save"></i>&nbsp;Save');
                },
                success: function(response) {
                    if(response.status == true){
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
    });
    $('#btn_add').click(function(){
        window.location = '<?php echo base_url().$this->uri->segment(1); ?>/input';
    });
    $('#btn_edit').click(function(){
        window.location = '<?php echo base_url().$this->uri->segment(1); ?>/edit';
    });

    $('#btnPrintTemplate').click(function(){
        $('#dlgPrintTemplate').modal('show');
    });
    $('#btnCancelAdd').click(function(){
       $('#frmAdd').fadeOut();
       $('#btn_add').fadeIn();
       $('#btnCancelAdd').hide();
       resetFormAdd();
    });
     $('#dlgApprovePin').on('hidden.bs.modal',function(event){
        $('#id_approve').val('');
        $('#pinApprove').val('');
        $('#clickApprove').html('Submit');
    });
    
     $('#dlgRejectPin').on('hidden.bs.modal',function(event){
        $('#id_reject').val('');
        $('#pinReject').val('');
        $('#clickReject').html('Check');
        document.getElementById("pinReject").readOnly = false;
        $('#clickReject').attr('data-pin','0');
    });


    // $('#btn_approve').click(function(){
    //     $('#dlgArrApprovePin').modal('show');
    //     console.log(arr_checkbox);
    // })
    
      $('#dlgArrApprovePin').on('hidden.bs.modal',function(event){
        $('#id_approve').val('');
        $('#pinApprove').val('');
        $('#clickApprove').html('Submit');
    });
    
    
    
    // arr_checkbox
    $('#btn_approve').click(function(){
        $('#dlgArrApprovePin').modal('show');
    $.ajax({
        type: 'POST',
        dataType: "JSON",
        url: '<?php echo base_url().$this->uri->segment(1); ?>/approveArrUtility/',
        data: {
            ids: arr_checkbox.map(x => x.id), // to create an array of ids from the objects in arr_checkbox
            approved_date: 'approved_date',
            approved_user: 'approved_user',
        },
        success: function(response) {
           console.log(response);
            // to handle success response
        },
        error: function(xhr, textStatus, errorThrown) {
            alert(xhr.responseText);
            // to handle error response
        }
    });
    });
});
function filteringStatusbyCheckbox(){
     var status_checkbox = [];
        if($('#new').is(":checked") == true){
            status_checkbox.push($('#new').val());
        }
        if($('#approved').is(":checked") == true){
            status_checkbox.push($('#approved').val());
        }
        if($('#rejected').is(":checked") == true){
            status_checkbox.push($('#rejected').val());
        }
        // console.log(arr_checkbox);
        button_checkbox = status_checkbox;
        otable.ajax.reload();
}
 function setStatusByCheckbox(){
        // console.log();
        var status_checkbox = [];
        if($('#new').is(":checked") == true){
            button_checkbox.push($('#new').val());
        }
        if($('#approved').is(":checked") == true){
            button_checkbox.push($('#approved').val());
        }
        if($('#rejected').is(":checked") == true){
            button_checkbox.push($('#rejected').val());
        }
        // console.log(button_checkbox);
        button_checkbox = status_checkbox;
        // otable.ajax.reload();
    }


//ygakan dipakai
function bundle_checkbox(btn){
    var val = $(btn).val();
    var id = $(btn).attr('data-id'); // get the ID from the checkbox data-id attribute
    if(btn.checked == true){
        arr_checkbox.push({id: id}); // add the selected ID to the array as an object
    }else{
        var i = arr_checkbox.findIndex(x => x.id === id); // find the index of the object with the matching ID
        if(i !== -1){
            arr_checkbox.splice(i, 1); // remove the object from the array
        }
    }
}
function printDokumen(param){
    var s = $('#btnSave').val();
    console.log(s);
    window.open('<?php echo base_url().$this->router->fetch_class(); ?>/print_dokumen/'+s);
}

function submitArrApprove(){
    $.ajax({
    type: 'POST',
    data: { 
        ids: arr_checkbox 
    },
    dataType: "JSON",
    async: false,
    url: '<?php echo base_url().$this->uri->segment(1); ?>/approveArrUtility/',
    // method: "POST",
    beforeSend: function() {
        // show_load();
        $('#clickApprove').attr('disabled',true);
        $('#clickApprove').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    },
    complete: function() {
         // hide_load();
        $('#clickApprove').html('Submit');
        $('#clickApprove').attr('disabled',false);
    },
    // success: function(response) {
    //   console.log(response);
    // },
     error: function(xhr, status, error) {
      console.error(xhr.responseText);
    },
     success: function(response) {
                //   console.log(response);
                    if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        otable.ajax.reload();
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

 function submitApprove(){
            $.ajax({
                type: "POST",
                data: {
                    pin: $('#pinApprove').val(),
                    id: $('#id_approve').val(), 
                },
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->uri->segment(1); ?>/approveUtility/',
                beforeSend: function() {show_load();},
				complete: function() {hide_load();},
                success: function(response) {
                //   console.log(response);
                    if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        otable.ajax.reload();
                        $('#dlgApprovePin').modal('hide');
                    }else{
                        Swal.fire({
                          icon: 'error',
                          title: 'Warning !',
                          text: response.msg,
                          footer: '',
                          timer: 1500
                        })
                    }
                }
            });
    }
      function submitReject(data){
        var pinflag = $(data).attr('data-pin');
        if(pinflag == '0'){
            $.ajax({
                type: "POST",
                data: {
                    pin: $('#pinReject').val(),
                },
                dataType: "JSON",
                url: '<?php echo base_url(); ?>utility_record/cekPin/',
                beforeSend: function() {show_load();},
				complete: function() {hide_load();},
                success: function(response) {
                   if(response.status == false){
                       alert('Pin Anda Salah');
                   }else{
                    document.getElementById("pinReject").readOnly = true; 
                    $('#isi_data_reject').css('display','initial');
                    $(data).attr('data-pin','1');
                   }
                }
            });
        }else{
            // console.log(form_data);
            $.ajax({
                type: "POST",
                data: {
                    id_reject: $('#id_reject').val(),
                    keterangan: $('#keteranganReject').val(),
                    pin: $('#pinReject').val(),
                },
                dataType: "JSON",
                url: '<?php echo base_url(); ?>utility_record/rejectUtilityRecord/',
                beforeSend: function() {
                    $('#clickApprove').attr('disabled',true);
                },
                complete: function() {
                    $('#clickApprove').attr('disabled',false);
                },
                success: function(response) {
                //   console.log(response);
                    if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        $('#dlgRejectPin').modal('hide');
                        otable.ajax.reload();
                    }else{
                        Swal.fire({
                          icon: 'error',
                          title: 'Oops...',
                          text: response.msg,
                          footer: ''
                        })
                    }
                }
            });
        }
    }
function paramTables(){
    var p = {
        kode: $('#akode').val()
    }
    return p;
}

function decimalValidbck(evt){
    console.log(evt);
}

function editData(id){
   window.location = '<?php echo base_url(); ?>utility_record/edit/'+id;
}
function upload(){
		// $('#id_agreement').val(id);
		$('#dlgUpload').modal('show');
}

function printData(){
		// $('#id_agreement').val(id);
		$('#dlgPrintData').modal('show');
}

function reload_grid() {
		otable.ajax.reload();
}
function viewDetail(id){
    $.ajax({
                type: "GET",
                dataType: "JSON",
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/viewDetail/'+id,
                success: function(resp) {
                var r = resp.data;
                var user = resp.user_create;
                $('#de_kodeunit').html('Unit:'+r.kode_unit);
                $('#end_meter_d').html(r.end_meter);
                $('#start_meter_d').html(r.start_meter);
                $('#periode_d').html(r.periode);
                $('#meterid_d').html(r.kodemeter);
                if(r.foto_meter != null){
                    var imageUrl = '<?php echo base_url("dokumen/utility_record/"); ?>' + r.foto_meter;
                }else{
                    var imageUrl = '';
                }
                
                $('#foto_meter_id img').attr('src', imageUrl);
                $('#mutil_d').html(r.nama_util);
                $('#input_by_d').html(resp.user_create);
                var createdDate = new Date(r.created_date);
                var formattedDate = createdDate.getDate() + '-' + (createdDate.getMonth() + 1) + '-' + createdDate.getFullYear() + ' ' + createdDate.getHours() + ':' + createdDate.getMinutes() + ':' + createdDate.getSeconds();
                
                $('#input_date_d').html(formattedDate);
                  $('#dlg_detail').modal('show');  
                   
                }
            }); 
    
}
    

    function resetFormAdd(){
        $('#aemail').val('');
        $('#anohp').val('');
        $('#anama').val('');
    }
    
    
    function approvedat(data){
        $('#id_approve').val(data);
        $('#dlgApprovePin').modal('show');
        
    }
    
    
    function approvedat_arr(data){
        $('btn_approve').val(data);
        $('#dlgArrApprovePin').modal('show');
        
    }
    function rejectdat(data){
        swalPromptButton.fire({
          title: 'Are you sure?',
          text: "To Reject this data ?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes',
          cancelButtonText: 'No',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
             $('#id_reject').val(data);
            $('#dlgRejectPin').modal('show');
          } 
        })
    }
    function delete_confirm(id){
        Swal.fire({
            title: "Anda Yakin",
            text: "Tindakan ini tidak bisa dibatalkan",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes"
            }).then((result) => {
            if (result.isConfirmed) {
               
                $.ajax({
                type: "POST",
                data: {
                   id:id
                },
                dataType: "JSON",
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/rejected_data/',
                beforeSend: function() {
                    show_load();
                },
                complete: function() {
                    hide_load();
                },
                success: function(response) {
                //   console.log(response);
                    if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        otable.ajax.reload();
                    }else{
                        Swal.fire({
                          icon: 'error',
                          title: 'Warning',
                          text: response.msg,
                          footer: ''
                        })
                    }
                }
            });
            }
        });
    }
</script>

