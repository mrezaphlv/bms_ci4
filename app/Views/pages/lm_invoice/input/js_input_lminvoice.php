<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.js"></script>-->
<script src="<?php echo base_url(); ?>assets/plugins/jquery_repeater/jquery_repeater.js"></script>
<script>
var otable_unit;
var otable_sales;
var otable_owner;
var table_detail;

    $(document).ready(function(){
        $('.fee, .amount, .tax_base_ppn').autoNumeric('init', {
        aSep: '.', aDec: ',', vMax: '999999999999.99',  mDec: '0'
    });
     $('.repeater').repeater({
 
            show: function () {
                $(this).slideDown();
                autoNumeric_refresh();
            },
            hide: function (deleteElement) {
                if(confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            },
        
     });
     $( "#periode" ).datepicker({
         format: 'mm-yyyy',
         minViewMode: 1,
    maxViewMode: 2,
     }).on('changeDate', function(e){
            $(this).datepicker('hide');
        });
    $('#frmStep1').submit(function(e){
       e.preventDefault();
       chooseStep(1);
    });
    $('#btnCancelAdd').click(function(){
            window.location = '<?php echo base_url().$this->uri->segment(1); ?>';
        });
    
    });
    $('#btnSaveData').click(function(){
         <?php if(!empty($this->uri->segment(3))): ?>
        updateInvoice();
        <?php else: ?>
        saveNewInvoice();
        <?php endif; ?>
    });
    $('#ancUnitCari').click(function(){
        $('#dlgUnitCari1').modal('show');
        // otable_unit.ajax.reload();
    });
     otable_unit = $('#tableUnitDlg').DataTable({
        "processing": true,
        "responsive": true,
        "serverSide": true,
        "ordering": true,
        "info": false,
        "dom": '<"table_tool">frtip',
        "ajax": {
                    url: "<?php echo base_url(); ?>izin_huni/lookup_unit_lminvoice", // URL file untuk proses select datanya
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
                        "name": "id",
                        "searchable" : false,
                        "visible":false
                    }, 
                    {
                        "data": "kode_unit",
                        "name":"kode_unit",
                         "searchable" : false,
                    },
                    {
                        "data": "lantai",
                        "name": "lantai",
                        "searchable" : false,
                    }, 
                     {
                        "data": "luas",
                        "name": "luas",
                        "searchable" : false,
                    }, 
                    {
                        "data": "id",
                        "orderable": false,
                         "searchable" : false,
                         "render": 
                            function( data, type, row, meta ) {
                               
                                var a = '';
                                a += '<button data-id="'+row['id']+'"  data-kode="'+row['kode_unit']+'" data-building="'+row['nama_building']+'" onclick="setUnitDlg(this)" class="btn btn_warna1 btn-sm " type="button" >Pilih</button>';
                                return a;
                            }
                    },
                    
                    
                ],
        "languange": {
            "processing": '<span>Loading</span>',
        },
    });
   
</script>

<script>
    function setUnitDlg(data){
        // console.log(data);
        var id = $(data).attr('data-id');
        var kode = $(data).attr('data-kode');
        var building = $(data).attr('data-building');
        $('#unit_show').val(kode);
        $('#id_unit').val(id);
        // console.log(id);
        $('#dlgUnitCari1').modal('hide');
        load_biaya(id);
        
    }

    // function load_dtTenant(id){
        
    // }
    function load_biaya(id_unit){
        $.ajax({
                type: "POST",
                data: {id_unit: id_unit},
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/load_biaya/',
                success: function(resp) {
                    // console.log(resp);
                    if(resp.status == true){
                        var dt = resp.data;
                        var agg = resp.agreement;
                        var a = '';
                        $('#id_owner').val(agg.id_tenant);
                        $('#id_bast').val(agg.id_handover);
                        $('#owner_show').val(agg.nama_tenant);
                    }
                }
            });
    }
    function serializeArray(data){
        var ret;
        $.ajax({
                type: "POST",
                data: data,
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url(); ?>dashboard/convert_array/',
                success: function(response) {
                    ret = response;
                }
            });
        return ret;
    }

      function saveNewInvoice(){
        var dcharge = serializeArray($('#frmStep2').serialize()).dcharge;
        var data = {
            id_unit: $('#id_unit').val(),
            periode: $('#periode').val(),
            due_date: $('#due_date').val(),
            deskripsi: $('#deskripsi').val(),
            id_tenant: $('#id_owner').val(),
            id_bast: $('#id_bast').val(),
            dcharge: dcharge,
        }
        $.ajax({
                type: "POST",
                data: data,
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/saveNewInvoice/',
                success: function(response) {
                    // console.log(response);
                    ret = response;
                    if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        window.location = '<?php echo base_url().$this->router->fetch_class();?>/view/'+response.header_id;
                    }else{
                        Swal.fire({
                          icon: 'error',
                          title: 'Warning',
                          text: response.msg,
                          footer: '',
                          timer: 1500
                        })
                    }
                }
            });
    }
    var dcharge = [];
    function updateInvoice(){
        var dcharge = serializeArray($('#frmStep2').serialize()).dcharge;
        var data = {
            id_thinvoice: $('#id_thinvoice').val(),
            id_unit: $('#id_unit').val(),
            id_tenant: $('#id_owner').val(),
            id_bast: $('#id_bast').val(),
            periode: $('#periode').val(),
            due_date: $('#due_date').val(),
            deskripsi: $('#deskripsi').val(),
            dcharge: dcharge,
        }
        $.ajax({
                type: "POST",
                data: data,
                dataType: "JSON",
                async: false,
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/updateData/',
                success: function(response) {
                    // console.log(response);
                    ret = response;
                    if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 300
                        });
                        window.location = '<?php echo base_url().$this->router->fetch_class();?>';
                    }else{
                        Swal.fire({
                          icon: 'error',
                          title: 'Warning!',
                          text: response.msg,
                          footer: '',
                          timer: 1500
                        })
                    }
                }
            });
    }
    function autoNumeric_refresh(){
        
        $('.fee, .amount, .tax_base_ppn').autoNumeric('init', {
        aSep: '.', aDec: ',', vMax: '999999999999.99',  mDec: '0'
    });
    }
    function hitung_fee(data,form_name,evn){
        var nganu = data.name.indexOf("["+form_name+"]");
        var id_scharge = $("[name='" + data.name.substring(0, nganu) + "[id_service]" + "']").val();
        // console.log(id_scharge);
        var id_pajak = $("[name='" + data.name.substring(0, nganu) + "[id_pajak]" + "']").val();
        var id_pph = $("[name='" + data.name.substring(0, nganu) + "[id_pph]" + "']").val();
        var nominal_input = $("[name='" + data.name.substring(0, nganu) + "[amount]" + "']").autoNumeric('get');
        var amount_input = $("[name='" + data.name.substring(0, nganu) + "[amount]" + "']").autoNumeric('get');
        var nominal_service;
        var nilai_pajak = 0;
        var nilai_pph = 0;
        var tarif_pajak_asli = 0;
        var tarif_pph_asli = 0;
        var jumlah = 0;
        var dtCharge;
        var amount;
        var getDtPajak;
        var getDtPph;
        var flag_luasunit;
        var dtunit;
        var luas_unit;
        var nominal_service_input;

           if(evn == 'charge'){
            $.ajax({
				type: "POST",
				url: '<?php echo base_url().$this->router->fetch_class(); ?>/hitung_fee_indah/',
				dataType: 'json',
				async: false,
				data: {
				    id_unit: $('#id_unit').val(),
				    id_scharge: id_scharge,
				},
				beforeSend: function() {show_load();},
				complete: function() {hide_load();},
				success: function(hasil) {
                nominal_service_input = nominal_input;
                    // if(nominal_input == '')
                        if(hasil.status == true){
                            var dt = hasil.data;
                            dtunit = hasil.unit;
                            luas_unit = dtunit.luas;
                            dtCharge = hasil.data;
                            nominal_service = dt.nominal;
                            flag_luasunit = dt.flag_luasunit;
                            $("[name='" + data.name.substring(0, nganu) + "[fee]" + "']").autoNumeric('set',dt.nominal);
                            amount = nominal_service;
                        
                        }
                        
                    
    				// autoNumeric_refresh();
				}
			});
        }
        else{
            nominal_service = amount_input;
        }
        var tax_fn = null;
        var dpp_ppn = null;
        if(id_pajak != ''){
           getDtPajak = cariTarifPajak(id_pajak);
           nilai_pajak = getDtPajak.tarif_display;
           tarif_pajak_asli = getDtPajak.nilai_pajak;
           tax_fn = getDtPajak.tax_fn;

           
        }
        if(id_pph != ''){
           getDtPph = cariTarifPajak(id_pph);
           nilai_pph = getDtPph.nilai_pajak;
           tarif_pph_asli = getDtPph.nilai_pajak;
        }
       
            if(flag_luasunit == true){
            amount = (parseFloat(nominal_service)*parseFloat(luas_unit.replace(',', '.')));
            }else{
            amount = parseFloat(nominal_service);
            }
          
            var hit_pph = Math.round((amount*parseFloat(nilai_pph))/100);
            if(id_pajak != '' && tax_fn != null){
            var hitung_ppn_amount = fn_hitung_ppn_amount(amount,nilai_pajak,tax_fn);
            dpp_ppn = hitung_ppn_amount.dpp_ppn;
            var hit_pajak = hitung_ppn_amount.ppn_amount;
            }else{
                var hit_pajak = Math.floor((amount*parseFloat(nilai_pajak))/100);
            }
            
            var total = amount + hit_pajak + hit_pph;
            // console.log(Math.round(hit_pph));
            $("[name='" + data.name.substring(0, nganu) + "[ppn_amount]" + "']").autoNumeric('set',hit_pajak);
            $("[name='" + data.name.substring(0, nganu) + "[amount]" + "']").autoNumeric('set',amount);
            $("[name='" + data.name.substring(0, nganu) + "[total_amount]" + "']").autoNumeric('set',total);
            $("[name='" + data.name.substring(0, nganu) + "[tarif_pajak]" + "']").val(tarif_pajak_asli);
            $("[name='" + data.name.substring(0, nganu) + "[tax_fn_ppn]" + "']").val(tax_fn);
            $("[name='" + data.name.substring(0, nganu) + "[tarif_pajak_pph]" + "']").val(tarif_pph_asli);
            if(id_pajak != '' && tax_fn != null){
                $("[name='" + data.name.substring(0, nganu) + "[tax_base_ppn]" + "']").autoNumeric('set',dpp_ppn);
            }else{
                $("[name='" + data.name.substring(0, nganu) + "[tax_base_ppn]" + "']").val(null);
            }
            
           
    }
    function cariTarifPajak(id){
        var ret;
        $.ajax({
				type: "POST",
				url: '<?php echo base_url(); ?>pajak/cariTarif/',
				dataType: 'json',
				async: false,
				data: {
				    id: id,
				},
				beforeSend: function() {show_load();},
				complete: function() {hide_load();},
				success: function(hasil) {
    				if(hasil.status == true){
    				    ret = hasil.data;
    				}
				}
			});
        return ret;
    }
    function adjust_dpp(data, form_name){
        var p = data.name.indexOf("["+form_name+"]");
        var id_scharge = $("[name='" + data.name.substring(0, p) + "[id_service]" + "']").val();
        // console.log(id_scharge);
        var id_pajak = $("[name='" + data.name.substring(0, p) + "[id_pajak]" + "']").val();
        var id_pph = $("[name='" + data.name.substring(0, p) + "[id_pph]" + "']").val();
        var tarif_pajak = $("[name='" + data.name.substring(0, p) + "[tarif_pajak]" + "']").val();
        var tarif_pph = $("[name='" + data.name.substring(0, p) + "[tarif_pajak_pph]" + "']").val();
        var amount = $("[name='" + data.name.substring(0, p) + "[amount]" + "']").autoNumeric('get');
        
        var hit_pph = Math.round((parseFloat(amount)*parseFloat(tarif_pph))/100);
        var hit_pajak = Math.floor((parseFloat(amount)*parseFloat(tarif_pajak))/100);
        var total = parseFloat(amount) + parseFloat(hit_pajak) + parseFloat(hit_pph);
        $("[name='" + data.name.substring(0, p) + "[ppn_amount]" + "']").autoNumeric('set',hit_pajak);
        // $("[name='" + data.name.substring(0, p) + "[pph_amount]" + "']").autoNumeric('set',hit_pph);
        $("[name='" + data.name.substring(0, p) + "[total_amount]" + "']").autoNumeric('set',total);
        // console.log(amount);
        hitung_fee(data,form_name,'pajak');
    }
    function adjust_pajak(data, form_name){
        var p = data.name.indexOf("["+form_name+"]");
        var id_scharge = $("[name='" + data.name.substring(0, p) + "[id_service]" + "']").val();
        var id_pph = $("[name='" + data.name.substring(0, p) + "[id_pph]" + "']").val();
        var tarif_pph = $("[name='" + data.name.substring(0, p) + "[tarif_pajak_pph]" + "']").val();
        var amount = $("[name='" + data.name.substring(0, p) + "[amount]" + "']").autoNumeric('get');
        
        var hit_pph = Math.round((parseFloat(amount)*parseFloat(tarif_pph))/100);
        var hit_pajak = $("[name='" + data.name.substring(0, p) + "[ppn_amount]" + "']").autoNumeric('get');
        var total = parseFloat(amount) + parseFloat(hit_pajak) + parseFloat(hit_pph);

        $("[name='" + data.name.substring(0, p) + "[total_amount]" + "']").autoNumeric('set',total);
        // console.log(amount);
    }
    function hitung_amount(data,form_name){
        var nganu = data.name.indexOf("["+form_name+"]");
        var tarif = $("[name='" + data.name.substring(0, nganu) + "[tarif_pajak]" + "']").val();
        var amount = $("[name='" + data.name.substring(0, nganu) + "[amount]" + "']").val();
        var fee = $("[name='" + data.name.substring(0, nganu) + "[fee]" + "']").autoNumeric('get');
        if(tarif != ''){
            pajak = (parseFloat(fee)*parseFloat(tarif))/100;
            hitung = parseFloat(fee)+pajak;
            $("[name='" + data.name.substring(0, nganu) + "[amount]" + "']").autoNumeric('set', hitung);

        }
    }
</script>
