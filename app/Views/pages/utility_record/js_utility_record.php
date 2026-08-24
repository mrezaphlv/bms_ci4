<script>
var otable_unit;
var otable_utility;
// var otable_sales;
// var otable_owner;
var table_detail;

$(document).ready(function() {
        otable_unit = $('#tableUnitDlg').DataTable({
            "processing": true,
            "responsive": true,
            "serverSide": true,
            "ordering": true,
            "info": false,
            "dom": '<"table_tool">frtip',
            "ajax": {
                url: "<?php echo base_url(); ?>utility_record/unitList", // URL file untuk proses select datanya
                type: "POST",
                data: function(d) {
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
                    "searchable": false,
                    "visible": false
                },
                {
                    "data": "kode_unit",
                    "name": "kode_unit",
                    "searchable": false,
                },
                {
                    "data": "lantai",
                    "name": "lantai",
                    "searchable": false,
                },
                {
                    "data": "luas",
                    "name": "luas",
                    "searchable": false,
                },
                {
                    "data": "id",
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row, meta) {

                        var a = '';
                        a += '<button data-id="' + row['id'] + '"  data-kode="' + row['kode_unit'] + '" data-building="' + row['nama_building'] + '"  onclick="setUnitDlg(this)" class="btn btn_warna1 btn-sm " type="button" >Pilih</button>';
                        return a;
                    }
                },
            ],
            "languange": {
                "processing": '<span>Loading</span>',
            },
        });


    $('#ancUnitCari').click(function(){
        $('#dlgUnitCari1').modal('show');
        otable_unit.ajax.reload();
    });
    
    $('#frminput').submit(function(e){
       e.preventDefault();
       saveNewUR();
    });
    $('#frmEdit').submit(function(e){
       e.preventDefault();
       updateUR();
    });
    
    });


        function saveNewUR(data) {
            var id_unit = $('#id_unit').val();
            var id_utilities = $('#id_utilities').val();
            var periode = $('#periode').val();
            var end_meter = $('#end_meter').val();
            var end_meter = $('#end_meter').val();
            var id_meter = $('#id_meter').val();
            var kode_meter = $('#kode_meter').val();
            var start_meter = $('#start_meter').val();
            var ids = $('#ids').val();

            // Buat objek FormData
            var formData = new FormData();
            formData.append('id_unit', id_unit);
            formData.append('id_utilities', id_utilities);
            formData.append('periode', periode);
            formData.append('end_meter', end_meter);
            formData.append('id_meter', id_meter);
            formData.append('start_meter', start_meter);
            formData.append('ids', ids);
            formData.append('foto_file', $('#foto_file')[0].files[0]); // Ambil file
            if($('#ids').val() != ''){
            $.ajax({
                type: "POST",
                data: formData,
                processData: false,  // Important!
                contentType: false,  // Important!
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/saveNewUR/',
                success: function(response) {
                    console.log(response);
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.msg,
                            showConfirmButton: false,
                        }).then((result) => {
                            window.location = '<?php echo base_url().$this->router->fetch_class(); ?>';
                        });
                        
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Warning!',
                            text: response.msg,
                            footer: '',
                            timer: 1500
                        });
                    }
                }
            });
            }else{
                Swal.fire({
                            icon: 'warning',
                            title: 'Pemberitahuan!',
                            text: 'Schedule penagihan unit ini tidak ada',
                            footer: '',
                        });  
            }
        }

        function updateUR(data) {
            console.log('ok');
            var id_unit = $('#id_unit').val();
            var id_utilities = $('#id_utilities').val();
            var periode = $('#periode').val();
            var end_meter = $('#end_meter').val();
            var end_meter = $('#end_meter').val();
            var id_meter = $('#id_meter').val();
            var start_meter = $('#start_meter').val();
            var id = $('#id').val();
            var ids = $('#ids').val();

            // Buat objek FormData
            var formData = new FormData();
            formData.append('id_unit', id_unit);
            formData.append('id_utilities', id_utilities);
            formData.append('periode', periode);
            formData.append('end_meter', end_meter);
            formData.append('id_meter', id_meter);
            formData.append('start_meter', start_meter);
            formData.append('id', id);
            formData.append('ids', ids);
            formData.append('foto_file', $('#foto_file')[0].files[0]); // Ambil file
            $.ajax({
                type: "POST",
                data: formData,
                dataType: "JSON",
                processData: false,  // Important!
                contentType: false,  // Important!
                url: '<?php echo base_url().$this->router->fetch_class(); ?>/updateData/',
                beforeSend: function() {show_load();},
		        complete: function() {hide_load();},
                success: function(response) {
                    console.log(response);
                    if (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text:response.msg,
                            showConfirmButton: false,
                        }).then((result) => {
                            window.location = '<?php echo base_url().$this->router->fetch_class(); ?>';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Warning!',
                            text: response.msg,
                            footer: '',
                            timer: 1500
                        });
                    }
                }
            });
        }


     $(document).ready(function(){
        $('#btnCancelAdd').click(function(){
            window.location = '<?php echo base_url().$this->router->fetch_class(); ?>';
        });
    });
    function setUnitDlg(data){
        // console.log(data);
        var id = $(data).attr('data-id');
        var kode = $(data).attr('data-kode');
        var building = $(data).attr('data-building'); 
        var kode_meter = $(data).attr('data-meter'); 
        $('#unit_show').val(kode);
        $('#id_unit').val(id);
        $('#nm_building').val(building);
        $('#kode_meter').val(kode_meter);
        // console.log(id);
        $('#dlgUnitCari1').modal('hide');
          gantiMeterId();
        //   console.log('dadwa');
        
    }


   
function gantiMeterId() {
    var id_utilities = $('#id_utilities').val();
    var id_unit = $('#id_unit').val();
    var periode = $('#periode').val();
    // console.log(id_unit, id_util);
    if(id_unit != '' && id_utilities != '' && periode != ''){
        $.ajax({
        type: "POST",
        data: {
            id_unit: id_unit,
            id_utilities: id_utilities,
            periode: periode
        },
        dataType: "JSON",
        url: '<?php echo base_url().$this->router->fetch_class(); ?>/cariUtilities/',
    	beforeSend: function() {show_load();},
		complete: function() {hide_load();},
        success: function(response) {
            // console.log(response); // log the response object for debugging

            if (response.status == true) {
                // var id_meter = response.id_util.id_meter; 
                $('#kode_meter').val(response.query.kode_meter); 
                $('#id_meter').val(response.query.id_meter); 
                $('#start_meter').val(response.query.last_end_meter); 
                $('#ids').val(response.id_schedule); 
                //kalo ini datanya object, untuk ngambbil value dari object adalah panggil object dahulu kemudian field valuenya
            } else {
                $('#kode_meter').val(''); 
                $('#id_meter').val(''); 
                $('#start_meter').val(''); 
                $('#ids').val(''); 
                Swal.fire({
                    icon: 'error',
                    title: 'Warning',
                    text: response.msg,
                    footer: '',
                    timer: 1500
                })
            }
        },
        error: function(xhr, status, error) {
            console.log(xhr); 
            console.log(status);
            console.log(error); 
        }
    });
    }
    
}
        
</script>