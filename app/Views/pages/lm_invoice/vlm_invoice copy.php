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
  #drop_zone {
	/*border: #B980F0 5px dashed;*/
	border: 1px solid rgba(221, 223, 225, 1);
	width: 100%;
	padding : 40px 0;
	color:silver;
}
#drop_zone p {
	font-size: 20px;
	text-align: center;
}
#btn_upload, #ppjb_file {
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
$utildroplist = api('POST','utilities/droplist',null);

?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.js"></script>
<div class="container-fluid">
    <div  class="judul_atas"><div>Transaksi / LM Invoice</div></div>
    <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:none;float:left;"><i class="bi bi-x-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">LM Invoice</div><br>
    <div class="row">
        <div class="col-lg-12">
             <?php if($this->akses->can_create == 1): ?>
<div><button id="btn_add" style="border-radius:20px;" class="btn btn_warna2"><i class="bi bi-plus"></i> Add New</button></div><br>
    <?php endif; ?>
 
            <table id="myTable" class="" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>No Invoice</th>
                <th>Invoice Group</th>
                <th>Unit</th>
                <th>Owner</th>
                <th>Due Date</th>
                <th>Periode</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
        
    </table>
        </div>
    </div>
</div>

<script>
    var otable;
    var table_tool = '';
    table_tool += '<div>';
   
    table_tool += '<input id="gsearch" type="text" class="form-control">';
    table_tool += '</div><br>';
    $(document).ready(function(){
       otable = $('#myTable').DataTable({
           "processing": true,
        "responsive": true,
        "serverSide": true,
        "ordering": true,
        "searching": true,
        "info": false,
        "dom": '<"table_tool">frtip',
        "ajax": {
                    url: "<?php echo base_url().$this->router->fetch_class(); ?>/grid", // URL file untuk proses select datanya
                    type: "POST",
                    data:function ( d ) {
                        d.tb_checkbox = '';
                    }
                },
        "deferRender": true,
                "aLengthMenu": [
                    [10, 50],
                    [10, 50]
                ],
        "columns":[
                    {
                        "data": "id",
                        "name": "id",
                        "searchable" : false,
                        "visible":false
                    },
                     {
                        "data": "no_invoice",
                        "name": "no_invoice",
                        "searchable" : false,
                        "visible":true
                    },
                    {
                        "data": "no_group_invoice",
                        "name": "no_group_invoice",
                        "searchable" : false,
                        "visible":true
                    },
                     {
                        "data": "kode_unit",
                        "name": "kode_unit",
                        "searchable" : false,
                        "visible":true
                    },
                     {
                        "data": "nama_owner",
                        "name": "nama_owner",
                        "searchable" : false,
                        "visible":true
                    },
                     {
                        "data": "jatuh_tempo",
                        "name": "jatuh_tempo",
                        "searchable" : false,
                        "visible":true,
                        "render": function(data, type, row, meta){
                            return format_tanggal(data,'-');
                        }
                    },
                     {
                        "data": "periode",
                        "name": "periode",
                        "searchable" : false,
                        "visible":true,
                        "render": function(data, type, row, meta){
                            return fmt_periode(data,' ');
                        }
                    },
                    {
                        "data": "id_header",
                         "searchable" : false,
                         "orderable": false,
                         "render": 
                            function( data, type, row, meta ) {
                                // console.log(row);
                                var a = '';
                                <?php if($this->akses->can_edit != 1 && $this->akses->can_delete != 1): ?>
                                a += '<div class="dropdown"><button disabled class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>'; 
                                <?php else: ?>
                                 a += '<div class="dropdown"><button class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>'; 
                                  a += '<ul class="dropdown-menu bms-dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                                 a+= '<li><a class="dropdown-item bms-dropdown-item" onclick="viewDetail('+data+')" href="javascript:;" ><i class="bi bi-eye"></i>  Detail</a></li>';
                                 a+= '<li><a class="dropdown-item bms-dropdown-item" onclick="editData('+data+')" href="javascript:;" ><i class="bi bi-pencil-square"></i>  Edit Data</a></li>';
                                 a += '</ul>';
                                <?php endif; ?>
                                a += '</div>';
                                return a;
                            }
                    },
        ],
        });
        // $('div.table_tool').html(table_tool);
        $('#btn_add').click(function(){
        window.location = '<?php echo base_url().$this->router->fetch_class(); ?>/input';
        });
    });
    function viewDetail(id){
        window.location = '<?php echo base_url().$this->router->fetch_class(); ?>/view/'+id;
    }
    function editData(id){
        window.location = '<?php echo base_url().$this->router->fetch_class(); ?>/edit/'+id;
    }
    function reload_grid(){
        otable.ajax.reload();
    }
</script>
