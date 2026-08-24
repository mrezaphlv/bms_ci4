<style>
    tr.tr_head
{
    cursor:pointer;
    background-color: rgba(73, 83, 113, 1);
    color: white;
}
.toggle-switch, .toggle-switch .toggle-knob {
    -moz-transition: all 0.2s ease-in;
    -webkit-transition: all 0.2s ease-in;
    -o-transition: all 0.2s ease-in;
    transition: all 0.2s ease-in;
}

.toggle-switch {
    height: 30px;
    width: 55px;
    display: inline-block;
    background-color: #ffffff;
    margin: 2px;
    margin-bottom: 5px;
    border-radius: 30px;
    cursor: pointer;
    border: solid 1px #d2d6de;
    box-shadow: inset 1px 1px 9px -3px rgba(4, 4, 4, 0.08), 1px 2px 6px -2px rgba(0, 0, 0, 0.01);
}

.toggle-switch .toggle-knob {
    width: 28px;
    height: 26px;
    display: inline-block;
    background-color: #ffffff;
    border: solid 1px rgba(126, 126, 126, 0.07);
    box-shadow: 0 1px 3px rgba(107, 106, 106, 0.26), 0 5px 1px rgba(107, 106, 106, 0.13);
    border-radius: 26px;
    margin: 1px 1px;
}

.toggle-switch.active {
    background-color: #FED049;
    border: solid 1px transparent;
}

.toggle-switch.active .toggle-knob {
    margin-left: 24px;
}
</style>
<?php 
$droplist_kategori_item = api('POST','kategori_item/dropListKategoriItem',NULL);

?>
<div class="container-fluid">
<div  class="judul_atas"><div>Transaksi / Checklist Tenant / Input</div></div>
    <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Checklist Tenant - <?php echo $data->kode_unit; ?></div><br>
    <div><button id="btn_add_item" style="border-radius:20px;" class="btn btn_warna2"><i class="bi bi-plus"></i> Add Item</button></div>
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
                <th width="15%">Foto Item</th>
                <th>#</th>
            </tr>
        </thead>
        <tbody data-repeater-list="chkitem">
        
            <?php foreach ($item as $key => $aa): ?>
                <?php if ($aa->segmen == 1): ?>
                    <tr class="tr_head"><td colspan="6"><span>-</span> <?php echo $aa->nama_kategori; ?></td></tr>
                <?php else: ?>
            <tr data-repeater-item>
                 <td><?php echo $aa->nama_item; ?><input type="hidden" name="id_mitem" class="form-control" value="<?php echo $aa->id_item; ?>"><input type="hidden" name="nama_item" class="form-control" value="<?php echo $aa->nama_item; ?>"></td>
                <td><input type="number" name="qty" class="form-control" value="<?php echo $aa->qty; ?>"></td>
                <td><select class="form-select" name="s_kondisi">
                    <option <?php echo $aa->kondisi == 'GOOD' ? 'selected' : '' ?> value="GOOD">Good</option>
                    <option <?php echo $aa->kondisi == 'NOT GOOD' ? 'selected' : '' ?> value="NOT GOOD">Not Good</option>
                </select></td>
                <td><input type="text" class="form-control" name="keterangan" id="keterangan" value="<?php echo $aa->keterangan; ?>"></td>
                <td><input type="hidden" class="form-control" name="fotonow" value="<?php echo $aa->foto; ?>"><input type="file" class="form-control" name="fotoitem" value=""></td>
                <td><button type="button" onclick="hapusItem(this)" name="deleteItem" data-repeater-delete class="btn btn-sm"><i class="bi bi-trash-fill"></i></button></td>
            </tr>
                <?php endif; ?>
                
            <?php endforeach; ?>
        </tbody>
            </table>
            </div>
            <div class="row">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn_bentuk1 btn-danger">Cancel</button>
                    <button type="submit" class="btn btn_bentuk1 btn_warna1">Save</button>
                </div>
            </div>
            </form>
            
            
        </div>
    </div>
    <!-- Modal -->
<div class="modal fade" id="dlgAddItem" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
           <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       <form id="frmAddItem">
           <input type="hidden" id="id_checklist_item" name="id_checklist_item" value="<?php echo $id_checklist; ?>">
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-3 col-form-label">Nama</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" value="" id="anama" name="anama">
                    </div>
                </div>
                 <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-3 col-form-label">Kategori</label>
                    <div class="col-sm-8">
                         <select class="form-select" name="akategori" id="akategori">
                  <option value="">--Pilih Kategori--</option>
                  <?php if ($droplist_kategori_item->status): ?>
                      <?php foreach ($droplist_kategori_item->data as $key => $ba): ?>
                          <option value="<?php echo $ba->id; ?>"><?php echo $ba->nama; ?></option>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-3 col-form-label">Nilai</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" value="" id="anilai" name="anilai">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-3 col-form-label">No Urut</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" value="" id="ano_urut" name="ano_urut">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-3 col-form-label">Tenant Check</label>
                    <div class="col-sm-4">
                        <span class="toggle-switch" value="0">
                            <span class="toggle-knob" value="1"></span>
                        </span>
                        <input type="hidden" id="atenant_check" name="atenant_check" value="false">
                    </div>
                </div>
                <div class="row">
                  <div class="col-sm-6 text-center">
                    <button class="btn btn_warna1 btn_bentuk1" type="submit" id="btnSave"><i class="bi bi-save"></i>&nbsp;Save</button>
                    <!--<button class="btn btn_warna1 btn_bentuk1" type="button" id="btnCoba">Save</button>-->
                </div>
              </div>
            </form>
      </div>
      
    </div>
  </div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.js"></script>
<script>
var id_item_deleted = [];
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
                url: '<?php echo base_url().$this->uri->segment(1); ?>/updateChecklist/',
                success: function(response) {
                if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
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
         });
    $('#frmAddItem').submit(function(e){
        e.preventDefault();
        var tenant_check;
        if($('.toggle-switch').hasClass('active')){
            tenant_check = 1;
        }else{
            tenant_check = 0;
        }
        $.ajax({
                type: "POST",
                data: {
                    nama: $('#anama').val(),
                    kategori_item_id: $('#akategori').val(),
                    no_urut: $('#ano_urut').val(),
                    nilai: $('#anilai').val(),
                    tenant_check: tenant_check,
                    id_checklist: $('#id_checklist_item').val(),
                },
                dataType: "JSON",
                url: '<?php echo base_url().$this->uri->segment(1); ?>/addnewItem/',
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
    });
         $('#btnCancelAdd').click(function(){
             window.location = '<?php echo base_url().$this->uri->segment(1); ?>';
         });
         $('#btn_add_item').click(function(){
             $('#dlgAddItem').modal('show');
         });
    });
    function hapusItem(data){
        var nganu = data.name.indexOf("[deleteItem]");
        var id_mitem =  $("[name='" + data.name.substring(0, nganu) + "[id_mitem]" + "']").val();
        if(id_mitem != ''){
            id_item_deleted.push(id_mitem);
        }
    }
    var toggler = document.querySelector('.toggle-switch');
toggler.onclick = function(){
  toggler.classList.toggle('active');
}
</script>