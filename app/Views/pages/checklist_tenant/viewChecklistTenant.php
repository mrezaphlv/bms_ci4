<style>
    tr.tr_head
{
    cursor:pointer;
    background-color: rgba(73, 83, 113, 1);
    color: white;
}
#gambarItemView{
    max-height: 300px;
    max-width: 300px;
}
</style>
<div class="container-fluid">
<div  class="judul_atas"><div>Transaksi / Checklist Tenant / Input</div></div>
    <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Checklist Tenant - <?php echo $data->kode_unit; ?></div><br>
    <?php if ($data->status == 'NEW' || $data->status == 'EDITED'): ?>
        <div><button id="btn_edit" style="border-radius:20px;" class="btn btn_bentuk1 btn_bputih_warna1">Edit</button></div>
    <?php else: ?>
        <div>Status: <font style="color:<?php echo $data->status == 'REJECTED' ? 'red' : 'green' ?>"><?php echo $data->status; ?></font></div>
    <?php endif; ?>
    <br>
    <div class="row">
        <div class="col-md-12">
            <!--<form enctype="multipart/form-data" method="post" action="<?php echo base_url().$this->uri->segment(1); ?>/saveChecklist" id="frm_checklist">-->
            <form enctype="multipart/form-data" method="post" id="frm_checklist">
                
                <input type="hidden" name="id_checklist" id="id_checklist" value="<?php echo $id_checklist; ?>">
              <div class="repeater">
                <table id="" class="table" style="width:100%">
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
        
            <?php foreach ($item as $key => $aa): ?>
                <?php if ($aa->segmen == 1): ?>
                    <tr class="tr_head"><td colspan="6"><span>-</span> <?php echo $aa->nama_kategori; ?></td></tr>
                <?php else: ?>
            <tr data-repeater-item>
                 <td><?php echo $aa->nama_item; ?></td>
                <td><?php echo $aa->qty; ?></td>
                <td><?php echo $aa->kondisi; ?></td>
                <td><?php echo $aa->keterangan; ?></td>
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
            <?php if ($data->status == 'NEW' ||$data->status == 'EDITED'): ?>
               <div class="row">
                <div class="col-md-12 text-end">
                    <button type="button" id="btnReject" class="btn btn_bentuk1 btn-danger">Reject</button>
                    <button type="button" id="btnApprove" class="btn btn_bentuk1 btn_warna4">Approve</button>
                </div>
            </div>
            <?php endif; ?>
            
            </form>
            
            
        </div>
    </div>
    
</div>
<!-- Modal -->
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
<!-- Modal -->
<div class="modal fade" id="dlgApprove" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
           <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Approve Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="id_approve" name="id_approve" value="<?php echo $id_checklist; ?>">
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
            <button id="pencetApprove" onclick="approveChecklist()"  type="button" class="btn btn_warna1 btn_bentuk1">Submit</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="dlgReject" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
           <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Reject Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="id_reject" name="id_reject" value="<?php echo $id_checklist; ?>">
        <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">PIN</label>
          </div>
          <div class="col-8">
            <input type="password" id="pinReject" name="pinReject" class="form-control" aria-describedby="passwordHelpInline">
          </div>
        </div>
        <br>
        <div id="ketReject" style="display:none;">
         <div class="row g-3 align-items-center">
          <div class="col-4">
            <label for="inputPassword6" class="col-form-label">Keterangan</label>
          </div>
          <div class="col-8">
            <textarea id="keterangan_reject" name="keterangan_reject" class="form-control"></textarea>
          </div>
        </div>   
        </div>
        
        <br>
        <div class="row">
              <div class="col-md-12 text-center">
            <button id="pencetReject" onclick="rejectChecklist(this)" data-pin="0" type="button" class="btn btn_warna1 btn_bentuk1">Submit</button>      
              </div>
          </div>
      </div>
      
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.js"></script>
<script>
    $('.tr_head').click(function(){
  $(this).find('span').text(function(_, value){return value=='-'?'+':'-'});
    $(this).nextUntil('tr.tr_head').slideToggle(100, function(){
    });
});
$(document).ready(function(){
         $('.repeater').repeater();
         $( '#frm_checklist' ).submit(function(e){
             e.preventDefault();
             $.ajax({
                type: "POST",
                data: new FormData( this ),
                cache: false,
                contentType: false,
                processData: false,
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->uri->segment(1); ?>/saveChecklist/',
                success: function(response) {
                   console.log(response);
                }
            });
         });
         $('#btnCancelAdd').click(function(){
             window.location = '<?php echo base_url().$this->uri->segment(1); ?>';
         });
         $('#btn_edit').click(function(){
             window.location = '<?php echo base_url().$this->uri->segment(1); ?>/edit/<?php echo $id_checklist; ?>';
         });
         $('#dlgViewFoto').on('hidden.bs.modal',function(event){
             $('#gambarItemView').attr('src','');
         });
         $('#dlgApprove').on('hidden.bs.modal',function(event){
             $('#pinApprove').val('');
            //  $('')
         });
         $('#dlgReject').on('hidden.bs.modal',function(event){
             $('#pinReject').val('');
            $('#keterangan_reject').val('');
         });
         $('#btnApprove').click(function(){
             $('#dlgApprove').modal('show');
         });
         $('#btnReject').click(function(){
             $('#dlgReject').modal('show');
         });
    });
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
                    id: $('#id_reject').val(),
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
        var id = '<?php echo $id_checklist; ?>';
        var src = '<?php echo base_url(); ?>dokumen/checklist/tenant/'+id+'/'+param;
        $('#dlgViewFoto').modal('show');
        $('#gambarItemView').attr('src',src);
    }
</script>