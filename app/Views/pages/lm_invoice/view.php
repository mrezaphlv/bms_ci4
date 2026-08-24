<style>
    .baris_head{
        margin-bottom:0.5rem;
    }
    .label_head{
        font-weight: 900;
    }
</style>
<div class="container-fluid">
    <div  class="judul_atas"><div>Transaksi / Invoice Manager / Detail</div></div>

        <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:initial;float:left;"><i class="bi bi-arrow-left-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;"><?php echo 'Unit:'.$dthead->kode_unit ?></div><br>
    <!-- <div class="row">
        <div class="col-md-12"><button type="button" onclick="editData(<?php echo $id_param; ?>)" class="btn btn_bputih_warna1 btn_bentuk1">Edit</button></div>
    </div> -->
    <br>
    <div style="padding:0 20px 0;" class="row">
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
                    <div class="label_head">Tipe: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo $dthead->tipe_tenant; ?></div>
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
                    <div class="label_head">HP: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo $dthead->hp_owner; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
        <div class="row baris_head">
                <div class="col-md-6">
                    <div class="label_head">Invoice Group: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo $dthead->no_group_invoice; ?></div>
                </div>
            </div>
            <div class="row baris_head">
                <div class="col-md-6">
                    <div class="label_head">Due Date: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo $dthead->jatuh_tempo; ?></div>
                </div>
            </div>
            <div class="row baris_head">
                <div class="col-md-6">
                    <div class="label_head">Periode: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo $dthead->periode; ?></div>
                </div>
            </div>
            <div class="row baris_head">
                <div class="col-md-6">
                    <div class="label_head">Deskripsi: </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="isi_head"><?php echo $dthead->deskripsi; ?></div>
                </div>
            </div>
        </div>
    </div>
      <div class="row">
        <table id="" class="table thead_warna1" style="width:100%">
        <thead>
            <tr>
                <th>No Invoice</th>
                <th>Service</th>
            
                <th>Tax</th>
                <th>Amount</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalAmount = 0; 
            foreach ($dt_detail as $key => $va):
                $totalAmount += $va->amount; ?>
                <tr>
                    <td><?php echo $va->no_invoice; ?></td>
                    <td>
                        <?php
                            if($va->tipe === 'SERVICE') {
                                echo $va->nm_service;
                            } else {
                                echo $va->nm_utilities;
                            }
                        ?>
                    </td>
                   
                    <td><?php echo $va->nama_pajak; ?></td>
                    <td style="text-align: right;"><?php echo number_format($va->amount,0,",","."); ?></td>
                    <td style="text-align: right;"><?php echo fmt_currency($va->total); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th style="text-align:right;"><?php echo number_format($dthead->total_amount,0,",","."); ?></th>
            </tr>
        </tfoot>
    </table>
    </div>
    <h3>Jurnal</h3>
    <div class="row">
        <table id="" class="table thead_warna1" style="width:100%">
        <thead>
            <tr>
                <th>COA</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sum_debit = 0;
            $sum_credit = 0;
            foreach ($dtjurnal as $key => $fa):
                if($fa->jenis == 'debit'){
                    $sum_debit += $fa->nominal;
                }else{
                    $sum_credit += $fa->nominal;
                }
                ?>
               <tr>
                <td><?php echo $fa->kode_coa.' - '.$fa->nama_coa; ?></td>
                <td><?php echo $fa->keterangan; ?></td>
                <td style="text-align:right;"><?php echo $fa->jenis == 'debit' ? fmt_currency($fa->nominal) : NULL; ?></td>
                <td style="text-align:right;"><?php echo $fa->jenis == 'credit' ? fmt_currency($fa->nominal) : NULL; ?></td>
               </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th style="text-align:right;"><?php echo number_format($sum_debit,0,",","."); ?></th>
                <th style="text-align:right;"><?php echo number_format($sum_credit,0,",","."); ?></th>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#btnCancelAdd').click(function(){
            window.location = '<?php echo base_url().$this->router->fetch_class(); ?>';
        })
    });
    function editData(id){
        window.location = '<?php echo base_url().$this->router->fetch_class(); ?>/edit/'+id;
    }
</script>
