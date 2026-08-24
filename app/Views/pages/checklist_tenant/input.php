<style>
    tr.tr_head {
        cursor: pointer;
        background-color: rgba(73, 83, 113, 1);
        color: white;
    }
</style>
<div class="container-fluid">
    <div class="judul_atas">
        <div>Transaksi / Checklist Tenant / Input</div>
    </div>
    <button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">Checklist Tenant - <?php echo $data->kode_unit; ?></div><br>
    <div><button id="btn_add" style="border-radius:20px;" class="btn btn_warna2"><i class="bi bi-plus"></i> Add Item</button></div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <!--<form enctype="multipart/form-data" method="post" action="<?php echo base_url() . $this->uri->segment(1); ?>/saveChecklist" id="frm_checklist">-->
            <form enctype="multipart/form-data" method="post" id="frm_checklist">
                <input type="hidden" name="id_agreement" id="id_agreement" value="<?php echo $id_agreement; ?>">
                <div class="repeater">
                    <table id="" class="table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="25%">Item</th>
                                <th width="10%">Jumlah</th>
                                <th>Kondisi</th>
                                <th width="15%">Foto Item</th>
                                <th>Keterangan</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody data-repeater-list="chkitem">

                            <?php foreach ($item as $key => $aa) : ?>
                                <?php if ($aa->segmen == 1) : ?>
                                    <tr class="tr_head">
                                        <td colspan="6"><span>-</span> <?php echo $aa->nama_kategori; ?></td>
                                    </tr>
                                <?php else : ?>
                                    <tr data-repeater-item>
                                        <td><?php echo $aa->nama_item; ?><input type="hidden" name="id_mitem" class="form-control" value="<?php echo $aa->id_item; ?>"><input type="hidden" name="nama_item" class="form-control" value="<?php echo $aa->nama_item; ?>"></td>
                                        <td><input type="number" name="qty" class="form-control" value="1"></td>
                                        <td><select class="form-select" name="s_kondisi">
                                                <option value="GOOD">Good</option>
                                                <option value="NOT GOOD">Not Good</option>
                                            </select></td>
                                        <td><input type="file" class="form-control" name="fotoitem" value=""></td>
                                        <td><input type="text" class="form-control" name="keterangan" id="keterangan" value=""></td>
                                        <td><button type="button" data-repeater-delete class="btn btn-sm"><i class="bi bi-trash-fill"></i></button></td>
                                    </tr>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col-md-12 text-end">
                        <button type="button" id="btnBack" class="btn btn_bentuk1 btn-danger">Cancel</button>
                        <button type="submit" class="btn btn_bentuk1 btn_warna1">Save</button>
                    </div>
                </div>
            </form>


        </div>
    </div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.js"></script>
<script>
    $('.tr_head').click(function() {
        $(this).find('span').text(function(_, value) {
            return value == '-' ? '+' : '-'
        });
        $(this).nextUntil('tr.tr_head').slideToggle(100, function() {});
    });
    $(document).ready(function() {
        $('.repeater').repeater();
        $('#frm_checklist').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url() . $this->uri->segment(1); ?>/saveChecklist/',
                success: function(response) {
                    //   console.log(response);
                    if (response.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: response.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        window.location = '<?php echo base_url() . $this->uri->segment(1); ?>';
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
        $('#btnCancelAdd').click(function() {
            window.location = '<?php echo base_url() . $this->uri->segment(1); ?>';
        });
		$('#btn_add').click(function() {
            window.location = '<?php echo base_url()?>item';
        });
        $('#btnBack').click(function() {
            window.location = '<?php echo base_url(); ?>closed_agreement';
        });
    });
</script>
