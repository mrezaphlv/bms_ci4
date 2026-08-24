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
   .margin_bawah_add{
       margin-bottom:20px;
   }
   a, a:hover{
       text-decoration: none;
       color: black;
   }
   #tb_pembebanan > thead > tr > th, #tb_pembebanan > tbody > tr > td {
    text-align: center;
   }
</style>

<!-- style stepw -->
<style>
  .step_1{
       width: 49%;
       text-align: center;
       display: inline-block;
    }
    .step_selected{
        border-bottom: 5px solid;
        border-color: rgba(54, 65, 79, 1);
    }
    .step_unselected{
       border-bottom: 5px solid ;
        border-color: rgba(217, 217, 217, 1);
    }
    /*#step_1{*/
    /*    border-bottom: 5px solid;*/
    /*    border-color: rgba(54, 65, 79, 1);*/
    /*}*/
    /*#step_2, #step_3{*/
    /*    border-bottom: 5px solid ;*/
    /*    border-color: rgba(217, 217, 217, 1);*/
    /*}*/
   
</style>

<?php
// print_r(count($dt_detail));die();
$droplist_charge = api('POST','service_charge/dropList_lminv_indah',NULL);
// $droplist_pajak = api('POST','undangan/dropListPajak',NULL);

?>
<div class="container-fluid">
    <?php include_once 'input/stepW.php'; ?>
    <div id="isi_step1">
    <form id="frmStep1">
        <input type="hidden" name="id_thinvoice" id="id_thinvoice" value="<?= @$dthead->id; ?>">
        <div class="row" >
            
            <div class="col-sm-6 mb-3" >
                <label for="inputPassword" class="">Unit  <font style="color:red;">*</font></label>
                <div class="input-group">
                    <input type="hidden" required class="form-control form-control-sm" value="<?= @$dthead->id_unit; ?>" name="id_unit" id="id_unit">
                    <input  type="hidden" readonly class="form-control form-control-sm" name="id_bast" value="<?= @$dthead->id_bast; ?>" id="id_bast">
                  <input required value="<?= @$dthead->kode_unit; ?>" readonly type="text" class="form-control form-control-sm" name="unit_show" id="unit_show">
                  <span class="btn-input-group input-group-text" id="basic-addon2"><a  href="javascript:;" id="ancUnitCari"><i class="bi bi-search"></i></a></span>
                </div>
            </div>
          <div class="col-sm-6">
                <label for="inputPassword" class="">Owner </label>
                <input  type="hidden" readonly class="form-control form-control-sm" name="id_owner" value="<?= @$dthead->id_tenant; ?>" id="id_owner">
               <input  type="text" readonly class="form-control form-control-sm" name="owner_show" value="<?= @$dthead->nama_owner; ?>" id="owner_show">
          </div>

          <div class="col-sm-6 mb-3">
                <label for="inputPassword" class="">Due Date </label>
                <input class="form-control form-control-sm" value="<?= @$dthead->jatuh_tempo; ?>" type="date"  id="due_date" name="due_date" required>
          </div>
          <div class="col-sm-6 ">
               <label for="inputPassword" class="">Periode </label>
                <input class="form-control form-control-sm" value="<?= @$periode; ?>" type="text"  id="periode" name="periode" required>
          </div>
          <div class="col-sm-6">
                <label for="inputPassword" class="">Deskripsi </label>
                <textarea class="form-control form-control-sm" name="deskripsi" id="deskripsi"  value="<?= @$dthead->deskripsi; ?>"><?= @$dthead->deskripsi; ?></textarea>
          </div>
       
        </div>
        <div style="text-align:center;margin-top: 10px;">
        <!--<button class="btn btn_warna3 btn_bentuk1">Cancel</button>-->
        <button type="submit"  class="btn btn_warna1 btn_bentuk1">Next</button>
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
          <!--<div class="modal-footer">-->
          <!--  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
          <!--  <button type="button" class="btn btn-primary">Save changes</button>-->
          <!--</div>-->
        </div>
      </div>
    </div>
    
</div>
    
    </div> <!-- close isi step1 -->
    
    <div style="display:none;" id="isi_step2">
        <div class="row">
        <div class="col-lg-12">
            <div class="repeater">
                <form id="frmStep2">
            <table id="tb_pembebanan" class="table thead_warna1" style="width:100%">
        <thead>
            <tr>
                <th width="10%">Service</th>
                <th width="12%">Harga Jual</th>
                <th width="12%">Tarif Ppn</th>
                <th width="11%">Tax Base PPN</th>
                <th width="12%">PPN amount</th>
                <th width="9%">PPh</th>
                <th width="12%">Total Amount</th>
                <th width="5%">#</th>
            </tr>
        </thead>
        <tbody data-repeater-list="dcharge">
            <tr <?php echo count($dt_detail) > 0 ? 'style="display:none;"' : ''; ?> data-repeater-item>
            <td>
                <input type="hidden" name="id_tdinvoice" value="">
                <select class="my-form-select-sm" onchange="hitung_fee(this, 'id_service','charge')" name="id_service">
                <option value="">- Pilih -</option>
                <?php foreach ($droplist_charge->data as $key => $va): ?>
                    <option value="<?php echo $va->id_concat; ?>"><?php echo $va->nama; ?></option>
                <?php endforeach; ?>
            </select> <input style="text-align:right;display:none" type="text" class="form-control form-control-sm fee" onkeyup="hitung_amount(this, 'fee')" name="fee" id="fee" value=""> </td>
            
            <td><input style="text-align:right;" onkeyup="adjust_dpp(this, 'amount')" type="text" class="form-control form-control-sm amount" name="amount" value=""></td>
            <td><select  name="id_pajak" onchange="hitung_fee(this, 'id_pajak','pajak')" class="my-form-select-sm">
                    <option value="">- Pilih -</option>
                    <?php foreach ($droplist_pajak as $key => $ab): ?>
                    <option value="<?php echo $ab->id; ?>"><?php echo $ab->nama_pajak.' - '.$ab->nilai_pajak; ?></option>
                    <?php endforeach; ?>
                </select><input type="hidden" name="tarif_pajak"><input type="hidden" name="tax_fn_ppn"></td>
                <td><input style="text-align:right;" readonly type="text"  class="form-control form-control-sm amount tax_base_ppn" name="tax_base_ppn" value=""></td>
                <td><input style="text-align:right;" type="text" onkeyup="adjust_pajak(this, 'ppn_amount')"  class="form-control form-control-sm amount" name="ppn_amount" value=""></td>

                <td><select  name="id_pph" onchange="hitung_fee(this, 'id_pph','pajak')" class="my-form-select-sm">
                    <option value="">- Pilih -</option>
                    <?php foreach ($droplist_pph as $key => $ab): ?>
                    <option value="<?php echo $ab->id; ?>"><?php echo $ab->nama_pajak.' - '.$ab->nilai_pajak; ?></option>
                    <?php endforeach; ?>
                </select><input type="hidden" name="tarif_pajak_pph"></td>
               

            <td><input style="text-align:right;" type="text" class="form-control form-control-sm amount" name="total_amount" value=""></td>
            <td><button type="button" name="deleteDetail" data-repeater-delete class="btn btn-sm"><i class="bi bi-trash-fill"></i></button></td>
            </tr>
            <?php if (count($dt_detail) > 0): ?>
                <?php foreach ($dt_detail as $key => $vb): ?>
                    <tr data-repeater-item>
            <td>
                <input type="hidden" name="id_tdinvoice" value="<?php echo $vb->id; ?>">
                <select class="my-form-select-sm" onchange="hitung_fee(this, 'id_service','charge')" name="id_service">
                <option value="">- Pilih -</option>
                <?php $selected1 = ''; ?>
                <?php foreach ($droplist_charge->data as $key => $va): ?>
                <?php $selected1 = ''; ?>
                <?php if($va->id_concat == ($vb->id_service.'#'.$vb->tipe)){$selected1 = 'selected';} ?>
                    <option <?php echo $selected1; ?> value="<?php echo $va->id_concat; ?>"><?php echo $va->nama; ?></option>
                <?php endforeach; ?>
            </select><input style="text-align:right;display:none;" type="text" class="form-control form-control-sm fee" name="fee" onkeyup="hitung_amount(this, 'fee')" value="<?php echo $vb->fee; ?>"></td>

            <td><input style="text-align:right;" onkeyup="adjust_dpp(this, 'amount')" type="text" class="form-control form-control-sm amount" name="amount" value="<?php echo $vb->amount; ?>"></td>
            
            <td><select  name="id_pajak" onchange="hitung_fee(this, 'id_pajak','pajak')" class="my-form-select-sm">
                    <option value="">- Pilih -</option>
                    <?php foreach ($droplist_pajak as $key => $ab): ?>
                    <?php $selected2 = ''; ?>
                    <?php if($vb->id_pajak == $ab->id)
                    {$selected2 = 'selected';}
                     ?>
                    <option <?php echo $selected2; ?> value="<?php echo $ab->id; ?>"><?php echo $ab->nama_pajak.' - '.$ab->nilai_pajak; ?></option>
                    <?php endforeach; ?>
                </select><input type="hidden" name="tarif_pajak" value="<?php echo $vb->nilai_pajak; ?>"></td>
                <td><input style="text-align:right;" readonly type="text"  class="form-control form-control-sm amount tax_base_ppn" name="tax_base_ppn" value="<?php echo $vb->tax_base_ppn; ?>"></td>
                <td><input style="text-align:right;" type="text" class="form-control form-control-sm amount" name="ppn_amount" value="<?php echo $vb->tax_amount; ?>"></td>
            
            <td><select  name="id_pph" onchange="hitung_fee(this, 'id_pph','pajak')" class="my-form-select-sm">
                <option value="">- Pilih -</option>
                <?php foreach ($droplist_pph as $key => $ab): ?>
                <option value="<?php echo $ab->id; ?>"><?php echo $ab->nama_pajak.' - '.$ab->nilai_pajak; ?></option>
                <?php endforeach; ?>
            </select><input type="hidden" name="tarif_pajak_pph" value="<?php echo $vb->pph_tarif; ?>"></td>
           
            <td><input style="text-align:right;" type="text" class="form-control form-control-sm amount" name="total_amount" value="<?php echo $vb->total; ?>"></td>
            <td><button type="button" name="deleteDetail" data-repeater-delete class="btn btn-sm"><i class="bi bi-trash-fill"></i></button></td>
            </tr>
                <?php endforeach; ?>
                
            <?php endif; ?>
        </tbody>
        
    </table>
                </form>
   <div class="row">
        <div class="col-lg-12 ">
             
             <button data-repeater-create type="button"  id="btn_add" style="border-radius:20px;" class="btn btn_warna2"><i class="bi bi-plus"></i> Add New</button>
        </div>
    </div>
     </div>
        </div>
    </div>
    <br>
    <div class="row">
        <!--<button onclick="chooseStep(2)" class="btn btn_warna1 btn_bentuk1">Next</button>-->
        <div style="text-align:center;">
        <button onclick="chooseStep(0)" type="button" class="btn btn_warna3 btn_bentuk1">Previous</button>
        <button id="btnSaveData" type="button" class="btn btn_warna1 btn_bentuk1">Save</button>
        </div> 
    </div>
    </div> <!-- close isi step2 -->
    
   
    
</div>
<!--<script src="<?php echo base_url(); ?>assets/plugins/jquery-clockpicker.js"></script>-->
<script src="http://weareoutman.github.io/clockpicker/dist/jquery-clockpicker.min.js"></script>
<script>
    var index_step = 1;
    $(document).ready(function(){
       $('#jam_undangan').clockpicker({autoclose:true}); 
    });
    function nextStep(){
        var nextIndex = index_step+1;
        switch(index_step) {
          case 1:
            $('#step_2').removeClass('step_unselected');
            $('#step_2').addClass('step_selected');
            $('#isi_step1').css('display','none');
            $('#isi_step2').css('display','initial');
            // isi_table1();
            break;
          case 2:
            $('#step_3').css('border-color',color_set);
            break;
        }
        index_step = nextIndex;
    }
    function prevStep(){
        var prevIndex = index_step-1;
        switch(prev_step) {
          case 1:
            $('#step_2').removeClass('step_unselected');
            $('#step_2').addClass('step_selected');
            $('#isi_step1').css('display','none');
            $('#isi_step2').css('display','initial');
            // isi_table1();
            break;
          case 2:
            $('#step_3').css('border-color',color_set);
            break;
        }
        index_step = prevIndex;
    }
    function chooseStep(p){
        switch(p) {
            case 0:
            
            $('#step_2').addClass('step_unselected');
            $('#step_2').removeClass('step_selected');
            
            $('#isi_step1').css('display','initial');
            $('#isi_step2').css('display','none');
            break;
          case 1:
            $('#step_2').removeClass('step_unselected');
            $('#step_2').addClass('step_selected');
            
            $('#isi_step1').css('display','none');
            $('#isi_step2').css('display','initial');
            // isi_table1();
            break;
        }
        index_step = p;
    }
</script>
<?php include_once 'input/js_input_lminvoice.php'; ?>
<?php if (!empty($dthead)): ?>
    <script>
        $(document).ready(function(){
            
        });
    </script>
<?php endif; ?>
