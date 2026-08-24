<?php   //  print_r($this->router->fetch_class());die(); 
?>
<style>
	.baris_head {
		margin-bottom: 0.5rem;
	}

	.label_head {
		font-weight: 900;
	}

	#drop_zone {
		/*border: #B980F0 5px dashed;*/
		border: 1px solid rgba(221, 223, 225, 1);
		width: 100%;
		padding: 40px 0;
		color: silver;
	}

	#tb_utilities>thead>tr>th,
	#tb_checklist_eng>thead>tr>th,
	#tb_dokumen>thead>tr>th {
		text-align: center;
	}

	#tb_utilities>tbody>tr>td,
	#tb_checklist_eng>tbody>tr>td,
	#tb_dokumen>tbody>tr>td {
		text-align: center;
	}

	#drop_zone p {
		font-size: 20px;
		text-align: center;
	}

	#btn_upload,
	#dok_file {
		display: none;
	}

	.btn_file_pick,
	.btn_file_pick:hover {
		border-color: rgba(240, 165, 0, 1);
		color: rgba(240, 165, 0, 1);
		width: 200px;
		border-radius: 30px;
	}

	tr.tr_head {
		cursor: pointer;
		background-color: rgba(73, 83, 113, 1);
		color: white;
		/*text-align: left;*/
	}

	#gambarItemView {
		max-height: 300px;
		max-width: 300px;
	}
</style>
<?php
$warna_status = 'black';
switch ($dthead->status) {
	case "NEW":
		$warna_status = "black";
		break;
	case "APPROVED":
		$warna_status = "green";
		break;
	case "REJECTED":
		$warna_status = "red";
		break;
}
$warna_status_checklist = 'black';
$next_step = '';

?>
<div class="container-fluid">
	<div class="judul_atas">
		<div>Transaksi / Serah Terima / Detail</div>
	</div>
	<button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
	<div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;"><?php echo 'No Undangan:' . $dthead->no_undangan ?></div><br>

	<div style="text-align: right; margin-bottom: 10px;">

		<!--<button class="btn btn_warna1"  onclick="dlgPrint(<?php echo $id; ?>)"  ><i class="bi bi-printer"></i> Print</button> -->
	</div>
	<div style="padding:0 20px 0;" class="row">
		<div class="col-md-6">
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">No Agreement: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo !empty($dthead->no_agreement) ? $dthead->no_agreement : "-" ; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">No Undangan: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo !empty($dthead->no_undangan) ? $dthead->no_undangan : "-"; ?></div>
				</div>
			</div>
			<!-- <div class="row baris_head">
                <div class="col-md-6">
                    <div class="label_head">Order Date: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo date('d-m-Y', strtotime($dthead->order_date)); ?></div>
                </div>
            </div>
            <div class="row baris_head">
                <div class="col-md-6">
                    <div class="label_head">Accept Date: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo date('d-m-Y', strtotime($dthead->accept_date)); ?></div>
                </div>
            </div> -->
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Fitting Out Date: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo !empty($dthead->fito_date) ? date('d-m-Y', strtotime($dthead->fito_date)) : '-'; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Paid Date: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo !empty($dthead->tgl_bayar) ? date('d-m-Y', strtotime($dthead->tgl_bayar)) : '-'; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Hand Over Date: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo !empty($dthead->handover_date) ? date('d-m-Y', strtotime($dthead->handover_date)) : '-'; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Status Pembayaran: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->status_bayar; ?></div>
				</div>
			</div>


		</div>
		<div class="col-md-6">
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Owner: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->nama_owner; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">No Hp Owner: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo  $dthead->no_hp != 0 ? $dthead->no_hp : '-'; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Tipe: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->tipe_tenant; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Sales: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->nama_sales; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Unit: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->kode_unit; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Building: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->nama_building; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Email: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo $dthead->email_owner; ?></div>
				</div>
			</div>
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Confirmation: </div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"><?php echo !empty($dthead->waktu_hadir) ? date('d-m-Y', strtotime($dthead->waktu_hadir)) . ' ' . date('H:i', strtotime($dthead->waktu_hadir)) : ''; ?> - <?php echo $dthead->diwakilkan == 't' ? 'Diwakilkan' : 'Tidak Diwakilkan'; ?></div>
				</div>
			</div>
            
			<div class="row baris_head">
				<div class="col-md-6">
					<div class="label_head">Status</div>
				</div>
				<div class="col-md-6 text-end">
					<div class="isi_head"></div>
					<?php if (empty($dtutil)) {
						echo '<a class="btn btn_warna2 btn_bentuk1" href="javascript:;" onclick="inputAgreement(' . (is_numeric($dthead->id) ? $dthead->id : 0) . ',2,' . $dthead->id_handover . ')" style="color:white;">Setup Utilities</a>';
					} else {
						echo !empty($dthead->id_agreement_ctenant) ? '<a href="javascript:;" style="color:green;">Done</a>' : '<a href="javascript:;" style="color:green;">CHECKLIST TENANT</a>';
					} ?>
				</div>
				<!-- <div class="isi_head"><?php echo !empty($dthead->id_agreement_ctenant) ? '<a href="javascript:;" style="color:green;">Hand Over</a>' : '<a href="javascript:;" style="color:green;">CHECKLIST TENANT</a>'; ?></div> -->
			</div>
		</div>

	</div>
</div>
   
     <?php if (count($citem) > 0): ?>
     <div style="display:inline-block;margin-bottom:15px;margin-top:20px;" class="judul_h2">Checklist Tenant</div>

    
     <div class="row">
         
            <div class="repeater">
                <table id="" class="table thead_warna1" style="width:100%">
                <thead>
            <tr>
                <th width="25%">Item</th>
                <th width="10%">Jumlah</th>
                <th>Kondisi</th>
                <th>Keterangan</th>
                <th width="20%">Foto Item</th>

            </tr>
        </thead>
        <tbody data-repeater-list="chkitem">
        
            <?php foreach ($citem as $key => $aa): ?>
                <?php if ($aa->segmen == 1): ?>
                    <tr class="tr_head"><td style="text-align:left;" colspan="6"><span>-</span> <?php echo $aa->nama_kategori; ?></td></tr>
                <?php else: ?>
            <tr data-repeater-item>
                 <td><?php echo $aa->nama_item; ?></td>
                <td><?php echo $aa->qty; ?></td>
                <td><?php echo $aa->kondisi; ?></td>
                <td class="text-align: left;"><?php echo $aa->keterangan; ?></td>
                <td> <?php if (!empty($aa->foto)): ?>
                   <button onclick="viewPhoto('<?php echo $aa->foto; ?>')" type="button" style="border-radius:20px;" class="btn btn_warna4 btn_bentuk1">View</button>
                <?php endif; ?>
                 
                 </td>
                
            </tr>
                <?php endif; ?>
                
            <?php endforeach; ?>
        </tbody>
            </table>
            </div>
         
     </div>

     <?php endif; ?>
    <br>
    <div class="judul_h2">Utilities</div>
    <div class="row">
        <table id="" class="table thead_warna1" style="width:100%">
            <thead>
                <tr>
                    <th width="45%">Utilities</th>
                    <th width="45%">Meter Range</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dtutil) > 0) : ?>

                    <?php foreach ($dtutil as $key => $ha) : ?>
                        <tr>
                            <td><?php echo $ha->nama_utilities; ?></td>
                            <td><?php echo $ha->nama_rangetype; ?></td>
                      
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>


            </tbody>

        </table>
    </div>
     <div class="judul_h2">Charge</div>
    <div class="row">
        <table id="" class="table thead_warna1" style="width:100%">
        <thead>
            <tr>
                <th>Service</th>
                <th>Tax</th>
                <th>Periode</th>
                <th>Fee</th>
                <th colspan="2">Invoice Amount</th>
            </tr>
        </thead>
        <tbody>
                <?php if (count($dtcharge) > 0): ?>
           
                    <?php foreach ($dtcharge as $key => $ha): ?>
                        <tr>
                            <td><?php echo $ha->nama_service; ?></td>
                            <td><?php echo $ha->nama_pajak; ?></td>
                            <td><?php echo $ha->periode; ?></td>
                            <td><?php echo fmt_currency($ha->fee); ?></td>
                            <td colspan="2"><?php echo fmt_currency($ha->amount); ?></td>
                        </tr> 
                    <?php endforeach ?>
                <?php endif ?>
        </tbody>
         <tfoot>
                <tr>
                    <th colspan="3" class="centered-text" style="text-align: center;">TOTAL</th>
                    <th style="text-align: center;"><?php $totalAmount = 0;
                                                    foreach ($dtcharge as $ha) {
                                                        $totalAmount += $ha->fee;
                                                    }
                                                    echo  fmt_currency($totalAmount); ?></th>
                    <th style="text-align: center;"><?php $totalAmount = 0;
                                                    foreach ($dtcharge as $ha) {
                                                        $totalAmount += $ha->amount;
                                                    }
                                                    echo fmt_currency($totalAmount);  ?></th>
                    <th></th>

                </tr>
            </tfoot>
        
    </table>
    </div>
   
</div>
<!-- Modal Approve 1 -->
<div class="modal fade" id="dlgApprovePin" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div id="dlgApprovePinContent" class="modal-content">
      <!--<div class="modal-header">-->
      <!--  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>-->
      <!--  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
      <!--</div>-->
      <div class="modal-body">
          <input type="hidden" id="id_approve" name="id_approve" value="<?php echo $id; ?>"> 
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
            <button id="pencetApprove" onclick="approveChecklist()" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Submit</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Approve 1 -->

<!-- Modal Reject 1 -->
<div class="modal fade" id="dlgRejectPin" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" >
    <div id="dlgApprovePinContent" class="modal-content">
      <!--<div class="modal-header">-->
      <!--  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>-->
      <!--  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
      <!--</div>-->
      <div class="modal-body">
          <input type="hidden" id="id_reject" name="id_reject" value="<?php echo $id; ?>"> 
        <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">PIN</label>
          </div>
          <div class="col-8">
            <input type="password" id="pinReject" name="pinReject" class="form-control" aria-describedby="passwordHelpInline">
          </div>
        </div>
        <br>
        <div style="display:none;" id="ketReject">
          <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">Keterangan Reject</label>
          </div>
          <div class="col-8">
            <!--<input type="text" id="keteranganReject" name="keteranganReject" class="form-control" >-->
            <textarea class="form-control" id="keterangan_reject" name="keterangan_reject"></textarea>
          </div>
        </div>  
        </div>
        <br>
        <div class="row">
              <div class="col-md-12 text-center">
            <button id="pencetReject" onclick="rejectChecklist(this)" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Check</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Reject 1 -->
<!-- Modal VIew foto -->
<div class="modal fade" id="dlgViewFoto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--<div class="modal-header">-->
      <!--  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>-->
      <!--  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
      <!--</div>-->
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12 text-center">
                <img id="gambarItemView">
            </div>
        </div>
        
      </div>
      <!--<div class="modal-footer">-->
      <!--  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
      <!--</div>-->
    </div>
  </div>
</div>
<!-- Close Modal View Foto -->
<script>
$('.tr_head').click(function(){
  $(this).find('span').text(function(_, value){return value=='-'?'+':'-'});
    $(this).nextUntil('tr.tr_head').slideToggle(100, function(){
    });
});
    $(document).ready(function(){
        $('#btnCancelAdd').click(function(){
            window.location = '<?php echo base_url().$this->uri->segment(1); ?>';
        })
    $('#dlgApprovePin').on('hidden.bs.modal',function(event){
        $('#pinApprove').val('');
        $('#pencetApprove').html('Check');
        document.getElementById("pinApprove").readOnly = false;
        $('#pencetApprove').attr('data-pin','0');
        clearFileInput(document.getElementById("ppjb_file"));
        document.getElementById('ppjb_file_info').innerHTML = '';
        $('#tgl_lunas').val('');
        $('#isi_data_approve').css('display','none');
    });
    $('#dlgRejectPin').on('hidden.bs.modal',function(event){
        $('#id_reject').val('');
        $('#pinReject').val('');
        $('#pencetReject').html('Check');
        document.getElementById("pinReject").readOnly = false;
        $('#pencetReject').attr('data-pin','0');
        
    });
    $('#btn_edit_citem').click(function(){
             window.location = '<?php echo base_url().$this->uri->segment(1); ?>/edit/<?php echo $id; ?>';
         });
    $('#dlgViewFoto').on('hidden.bs.modal',function(event){
             $('#gambarItemView').attr('src','');
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
            document.getElementById('ppjb_file_info').innerHTML = "File name : " + fname +' <br>File size : ' + bytesToSize(fsize);
        }
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(fileobj);
        document.getElementById('ppjb_file').files = dataTransfer.files;
    });
    $('#btn_file_pick').click(function(){
        /*normal file pick*/
        document.getElementById('ppjb_file').click();
        document.getElementById('ppjb_file').onchange = function() {
        fileobj = document.getElementById('ppjb_file').files[0];
        var fname  = fileobj.name;
        var fsize = fileobj.size;
        if (fname.length > 0) {
            document.getElementById('ppjb_file_info').innerHTML = "File name : " + fname +' <br>File size : ' + bytesToSize(fsize);
        }

        };
    });
    });
    function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return '0 Byte';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}
    function approvedat(){
        // $('#id_approve').val(data);
        $('#dlgApprovePin').modal('show');
    }
     function rejectdat(){
        // $('#id_reject').val(data);
        // $('#dlgRejectPin').modal('show');
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
            $('#dlgRejectPin').modal('show');
          } 
        //   else if (
        //     /* Read more about handling dismissals below */
        //     result.dismiss === Swal.DismissReason.cancel
        //   ) {
        //     swalPromptButton.fire(
        //       'Cancelled',
        //       'Your imaginary file is safe :)',
        //       'error'
        //     )
        //   }
        })
    }
   
    function approveChecklist(){
        $.ajax({
                type: "POST",
                data: {
                    pin: $('#pinApprove').val(),
                    id: $('#id_approve').val(), 
                },
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->uri->segment(1); ?>/approveChecklist/',
                beforeSend: function() {
                    // show_load();
                    $('#pencetApprove').attr('disabled',true);
                    $('#pencetApprove').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                },
                complete: function() {
                    // hide_load();
                    $('#pencetApprove').html('Submit');
                    $('#pencetApprove').attr('disabled',false);
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
                        // location.reload();
                        window.location = '<?php echo base_url().$this->uri->segment(1); ?>';
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
    function rejectChecklist(btn){
        var pinflag = $(btn).attr('data-pin');
        if(pinflag == 0){
            $.ajax({
                type: "POST",
                data: {
                    pin: $('#pinReject').val(),
                },
                dataType: "JSON",
                url: '<?php echo base_url().$this->uri->segment(1); ?>/cekPin/',
                beforeSend: function() {
                    $('#pencetReject').attr('disabled',true);
                },
                complete: function() {
                    $('#pencetReject').attr('disabled',false);
                },
                success: function(response) {
                   if(response.status == false){
                       alert('Pin Anda Salah');
                   }else{
                    // document.getElementById("pinApprove").readOnly = true; 
                    $('#ketReject').css('display','initial');
                    $(btn).attr('data-pin','1');
                   }
                }
            }); 
        }else{
           $.ajax({
                type: "POST",
                data: {
                    pin: $('#pinReject').val(),
                    id_reject: $('#id_reject').val(),
                    keterangan: $('#keterangan_reject').val(),
                },
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->uri->segment(1); ?>/rejectChecklist/',
                beforeSend: function() {
                    // show_load();
                    $('#pencetReject').attr('disabled',true);
                    $('#pencetReject').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                },
                complete: function() {
                    // hide_load();
                    $('#pencetReject').html('Submit');
                    $('#pencetReject').attr('disabled',false);
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
                        location.reload();
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
        
    }
    function viewPhoto(param){
        var id = '<?php echo $id; ?>';
        var src = '<?php echo base_url(); ?>dokumen/checklist/tenant/'+id+'/'+param;
        $('#dlgViewFoto').modal('show');
        $('#gambarItemView').attr('src',src);
    }
</script>

