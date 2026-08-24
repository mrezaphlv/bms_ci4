<style>
	.form-selecttt {
		border-radius: 15px;
	}

	.btn-unapprove {
		background-color: rgba(191, 191, 191, 1);
		border-radius: 15px;
		color: white;
	}

	.btn-approve {
		background-color: rgba(206, 233, 206, 1);
		border-radius: 15px;
		color: #60B760;
		;
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
		display: none;
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
</style>
<?php
$utildroplist = api('POST', 'utilities/droplist', null);

?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.js"></script>
<div class="container-fluid">
	<div class="judul_atas">
		<div>Transaksi / LM Invoice</div>
	</div>
	<button type="button" id="btnCancelAdd" class="btn btn_back_arrow" style="line-height:10px;display:none;float:left;"><i class="bi bi-x-circle"></i></button>
	<div class="warna_teks1" style="font-weight:900;font-size:25px;display: inline-block;margin-bottom:20px;">LM Invoice</div><br>
	<div class="row">
		<div class="col-lg-12">
			<?php if ($this->akses->can_create == 1) : ?>
				<div><button id="btn_add" style="border-radius:20px;" class="btn btn_warna2"><i class="bi bi-plus"></i> Add New</button></div><br>
			<?php endif; ?>
			<div class ="row">
			<div class="col-sm-2 mb-3">
				<label class="">Search</label>
				<input type="text" class="form-control form-control-sm" name="gsearch" id="gsearch">
			</div>
			<div class="col-sm-2 mb-3">
				<label class="">Tenant</label>
				<input type="text" class="form-control form-control-sm" name="tenant" id="tenant">
			</div>
			<div class="col-sm-2 mb-3">
				<label class="">Due Date</label>
				<input type="date" class="form-control form-control-sm" name="sduedate" id="sduedate">
			</div>
			<div class="col-sm-2 mb-3">
				<label class="">Periode</label>
				<input type="text" class="form-control form-control-sm" name="speriode" id="speriode">
			</div>
			<div class="col-sm-2 mb-3">
				<label class=""></label>
				<button type="button" onclick="reload_grid()" class="btn btn_warna2 btn_bentuk1" style="position:relative;top:40%;">Search</button>
        	</div>
		</div>
		<br>
			<table id="myTable" class="" style="width:100%">
				<thead>
					<tr>
						<th>ID</th>
						<th>Invoice Group</th>
						<th>No. Invoice</th>
						<!-- <th>Invoice Group</th> -->
						<th>Unit</th>
						<th>Tenant</th>
						<th>Service</th>
						<th>Due Date</th>
						<th>Periode</th>
						<th>Remaining Amount</th>
						<th>Amount</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>

				</tbody>

			</table>
		</div>
	</div>
</div>
<!-- Modal Detail -->
<div class="modal fade" id="dlgDetail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form id="frmEdit">
			<h5>Edit LM Invoice</h5><hr>
            <input type="hidden" name="id_primary" id="id">
            <input type="hidden" name="id_header" id="id_header">
            <input type="hidden" name="id_schedule" id="id_schedule">
            <input type="hidden" name="id_service" id="id_service">
            <input type="hidden" name="tipe" id="tipe">
            <input type="hidden" name="flag_id" id="flag_id">
            <input type="hidden" name="created_date" id="created_date">
            <input type="hidden" name="created_user" id="created_user">
            <input type="hidden" name="jatuh_tempo" id="jatuh_tempo">
            <input type="hidden" name="start_meter" id="start_meter">
            <input type="hidden" name="end_meter" id="end_meter">
            <input type="hidden" name="virtual_account" id="virtual_account">
            <input type="hidden" name="status_bayar" id="status_bayar">
            <input type="hidden" name="activated" id="activated">
            <input type="hidden" name="tgl_bayar" id="tgl_bayar">
            <input type="hidden" name="id_pembelian" id="id_pembelian">
            <input type="hidden" name="deskripsi" id="deskripsi">
            <input type="hidden" name="cara_bayar" id="cara_bayar">
            <input type="hidden" name="bukti_bayar" id="bukti_bayar">
            <input type="hidden" name="payment_inquiry" id="payment_inquiry">
            <input type="hidden" name="inquiry_id" id="inquiry_id">
            <input type="hidden" name="novoucher" id="novoucher">
            <input type="hidden" name="no_bast" id="no_bast">
            <input type="hidden" name="id_bast" id="id_bast">
            <input type="hidden" name="id_voucher" id="id_voucher">
            <input type="hidden" name="addition" id="addition">
            <input type="hidden" name="id_unit" id="id_unit">
            <input type="hidden" name="id_tenant" id="id_tenant">
            <input type="hidden" name="id_parent" id="id_parent">
            <input type="hidden" name="periode_end" id="periode_end">
            <input type="hidden" name="deleted_user" id="deleted_user">
            <input type="hidden" name="deleted_date" id="deleted_date">
            <input type="hidden" name="periode_start" id="periode_start">
            <input type="hidden" name="pph_amount" id="pph_amount">
            <input type="hidden" name="flag_denda" id="flag_denda">
            <table class="table_detail" width="100%">
                <tbody>
                <tr>
					<td style="">No Invoice</td>
					<td>:</td>
					<td><input type="text" id="no_invoice" name="no_invoice" class="form-control" readonly></div></td>
				</tr>
                <tr>
					<td>Unit</td>
					<td>:</td>
					<td><input type="text" id="kode_unit" class="form-control" readonly></div></td>
				</tr>
				<tr>
					<td>Tenant</td>
					<td>:</td>
					<td><input type="text" id="nama" class="form-control" readonly></div></td>
				</tr>
				<tr>
					<td>DPP</td>
					<td>:</td>
					<td><input type="text" id="amount" name="amount" class="form-control" ></td>
				</tr>
				<tr>
					<td>Periode Invoice</td>
					<td>:</td>
					<td><input type="text" id="periode" name="periode" class="form-control" ></td>
				</tr>
				<tr>
					<td>PPN</td>
					<td>:</td>
					<td>
						<input type="hidden" id="fee" name="fee" class="form-control" >
						<select id="ppn_select" class="form-control" name="ppn"></select>
						<input type="hidden" id="nilai_pajak" name="nilai_pajak" class="form-control">
					</td>
				</tr>
				<tr>
					<td>PPN Amount</td>
					<td>:</td>
					<td><input type="text" id="tax_amount" name="tax_amount" class="form-control" ></td>
				</tr>
				<tr>
					<td>PPH</td>
					<td>:</td>
					<td>
						<select id="pph_select" class="form-control" name="pph"></select>
						<input type="hidden" id="pph_tarif" name="pph_tarif" class="form-control">
					</td>
				</tr>
				<tr>
					<td>Total Amount</td>
					<td>:</td>
					<td><input type="text" id="total" name="total" class="form-control" ></td>
				</tr>
				<tr>
					<td>Notes</td>
					<td>:</td>
					<td><textarea id="notes_edit" name="notes_edit" class="form-control" ></textarea></td>
				</tr>
				<tr>
					<td><br><button data-pin="0" type="button" onclick="saveEditedData()" class="btn btn_warna1 btn_bentuk1">Save</button></td>
				</tr>
                </tbody>
                
            </table>
            
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Close Modal Detail -->

<script>
	
	var otable;
	var table_tool = '';
	// table_tool += '<div>';

	// table_tool += '<input id="gsearch" type="text" class="form-control">';
	// table_tool += '</div><br>';
	$(document).ready(function() {
		$('#ppn_select').change(function() {
			var selectedPpn = $(this).val();
			var nilaiPajakURL = '<?php echo base_url() . $this->router->fetch_class(); ?>/select_ppn/' + selectedPpn;
			
			$.get(nilaiPajakURL, function(data) {
				var pph_tarif = parseFloat($('#pph_tarif').val().replace(',', ''));
				
				$('#nilai_pajak').val(data);
				var total = parseFloat($('#amount').val().replace('.', ''));
				var pph = (pph_tarif / 100) * total;
				$('#pph_amount').val(pph);
				var nilaiPajak = parseFloat(data);
				var ppnAmount = (data / 100) * total;
				
				var grand = total + ppnAmount + pph;
				$('#tax_amount').autoNumeric('set', ppnAmount);
				$('#total').autoNumeric('set', grand);
			});
		
    	});
		
		$('#pph_select').change(function() {
			var selectedPph = $(this).val();
			var nilaiPajakURL = '<?php echo base_url() . $this->router->fetch_class(); ?>/select_ppn/' + selectedPph;

			$.get(nilaiPajakURL, function(data) {
				$('#pph_tarif').val(data);

				var nilai_pajak = parseFloat($('#nilai_pajak').val().replace(',', ''));
				var total = parseFloat($('#amount').val().replace('.', ''));
				var ppnAmount = (nilai_pajak / 100) * total;
				var nilaiPPH = parseFloat(data);
				var pph = (nilaiPPH / 100) * total;
				$('#pph_amount').val(pph);
				var grand = total + pph + ppnAmount;
				$('#total').autoNumeric('set', grand);
			});
		});


		$("#speriode").datepicker({
			format: 'yyyy-mm',
			minViewMode: 1,
			maxViewMode: 2,
		}).on('changeDate', function(e) {
			$(this).datepicker('hide');
		});
		$("#periode").datepicker({
			format: 'yyyy-mm-dd',
			minViewMode: 1,
			maxViewMode: 2,
		}).on('changeDate', function(e) {
			$(this).datepicker('hide');
		});
        $('#amount, #total, #tax_amount').autoNumeric('init', {
            aSep: '.',
            aDec: ',',
            vMax: '999999999999.99',
            mDec: '0'
        });
		otable = $('#myTable').DataTable({
			"processing": true,
			// "paging": false,
			"responsive": true,
			"serverSide": true,
			"order": [
				[0, 'desc']
			],
			"ordering": true,
			"searching": false,
			"scrollY": '500px',
			"info": false,
			"dom": '<"table_tool">frtip',
			"ajax": {
				url: "<?php echo base_url() . $this->router->fetch_class(); ?>/grid", // URL file untuk proses select datanya
				type: "POST",
				data: function(d) {
					d.tb_checkbox = '';
					d.gsearch = $('#gsearch').val();
					d.tenant = $('#tenant').val();
					d.duedate = $('#sduedate').val();
					d.periode = $('#speriode').val();
				}
			},
			"deferRender": true,
			"aLengthMenu": [
				[10, 50],
				[10, 50]
			],
			"columns": [{
					"data": "id",
					"name": "id",
					"searchable": false,
					"visible": false
				},
				{
				    "data": "no_group_invoice",
				    "name": "no_group_invoice",
				    "searchable" : false,
				    "visible":true
				},
				{
					"data": "no_invoice",
					"name": "no_invoice",
					"searchable": false,
					"visible": true
				},
				{
					"data": "kode_unit",
					"name": "kode_unit",
					"searchable": false,
					"visible": true
				},
				{
					"data": "nama_owner",
					"name": "nama_owner",
					"searchable": false,
					"visible": true
				},
				{
					"data": "nm_service",
					"name": "nm_service",
					"searchable": false,
					"visible": true,
					"render": function(data, type, row, meta) {
						if (!data) {
							return row.nm_utilities;
						} else {
							return data;
						}
					}
				},
				{
					"data": "jatuh_tempo",
					"name": "jatuh_tempo",
					"searchable": false,
					"visible": true,
					"render": function(data, type, row, meta) {
						return format_tanggal(data, '-');
					}
				},
				{
                    "data": "periode",
                    "searchable": false,

                },
				{
					"data": "remaining",
					"searchable": false,
					"className": 'dt-body-right',
				},
				{
					"data": "fmt_total",
					"searchable": false,
					"className": 'dt-body-right',
				},
				{
					"data": "id_header",
					"searchable": false,
					"orderable": false,
					"render": function(data, type, row, meta) {
						// console.log(row);
						var a = '';
						<?php if ($this->akses->can_edit != 1 && $this->akses->can_delete != 1 && $this->akses->can_view != 1) : ?>
							a += '<div class="dropdown"><button disabled class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>';
						<?php else : ?>
							a += '<div class="dropdown"><button class="btn btn_option btn-sm " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>';
							a += '<ul class="dropdown-menu bms-dropdown-menu" aria-labelledby="dropdownMenuButton1">';
							a += '<li><a class="dropdown-item bms-dropdown-item" onclick="viewDetail(' + data + ')" href="javascript:;" ><i class="bi bi-eye"></i>  Detail</a></li>';
							a += '<li><a class="dropdown-item bms-dropdown-item" onclick="editData(' + data + ')" href="javascript:;" ><i class="bi bi-pencil-square"></i>  Edit Data</a></li>';
							// a += '<li><a class="dropdown-item bms-dropdown-item" onclick="editData(' + row.id + ')" href="javascript:;" ><i class="bi bi-pencil-square"></i>  Edit Data</a></li>';
							<?php if ($this->akses->can_delete == 1 ) : ?>
								if(row.remaining_amount == row.total){
									a += '<li><a class="dropdown-item bms-dropdown-item" onclick="deleteDok(' + row.id + ')" href="javascript:;" ><i class="bi bi-trash"></i>  Delete</a></li>';
								}
							<?php endif; ?>
							a += '</ul>';
						<?php endif; ?>
						a += '</div>';
						return a;
					}
				},
			],
		});
		// $('div.table_tool').html(table_tool);
		$('#btn_add').click(function() {
			window.location = '<?php echo base_url() . $this->router->fetch_class(); ?>/input';
		});


	});

	function viewDetail(id) {
		window.location = '<?php echo base_url() . $this->router->fetch_class(); ?>/view/' + id;
	}
	function editData(id) {
		$.ajax({
			type: "POST",
			data: {
				id: id,
			},
			dataType: "JSON",
		url: '<?php echo base_url().$this->router->fetch_class(); ?>/cek_periode_close/',
			beforeSend: function () {
				show_load();
			},
			complete: function () {
				hide_load();
			},
			success: function (response) {
				if(response.status == true){
					window.location = '<?php echo base_url() . $this->router->fetch_class(); ?>/edit/' + id;
				}else{
					Swal.fire({
						icon: 'error',
						title: 'Warning',
						text: response.msg,
						footer: '',
						timer: 1500
					});
				}
			}
		});
		
	}
	function editData_bck(id) {
		$.ajax({
			type: "POST",
			data: {
				id: id,
			},
			dataType: "JSON",
			url: '<?php echo base_url(); ?>lm_invoice/editDialog/' + id,
			beforeSend: function () {
				show_load();
			},
			complete: function () {
				hide_load();
			},
			success: function (response) {
				if (response.status == true) {
					var dt = response.data;
					$('#id').val(dt.id);
					$('#no_invoice').val(dt.no_invoice);
					$('#kode_unit').val(dt.kode_unit);
					$('#nama').val(dt.nama);
					$('#periode').val(dt.periode);
					$('#id_header').val(dt.id_header);
					$('#id_schedule').val(dt.id_schedule);
					$('#id_service').val(dt.id_service);
					$('#tipe').val(dt.tipe);
					$('#flag_id').val(dt.flag_id);
					$('#created_date').val(dt.created_date);
					$('#created_user').val(dt.created_user);
					$('#jatuh_tempo').val(dt.jatuh_tempo);
					$('#start_meter').val(dt.start_meter);
					$('#end_meter').val(dt.end_meter);
					$('#no_invoice').val(dt.no_invoice);
					$('#virtual_account').val(dt.virtual_account);
					$('#fee').val(dt.fee);
					$('#nilai_pajak').val(dt.nilai_pajak);
					$('#pph_tarif').val(dt.pph_tarif);
					$('#status_bayar').val(dt.status_bayar);
					$('#activated').val(dt.activated);
					$('#tgl_bayar').val(dt.tgl_bayar);
					$('#id_pembelian').val(dt.id_pembelian);
					$('#deskripsi').val(dt.deskripsi);
					$('#cara_bayar').val(dt.cara_bayar);
					$('#bukti_bayar').val(dt.bukti_bayar);
					$('#payment_inquiry').val(dt.payment_inquiry);
					$('#inquiry_id').val(dt.inquiry_id);
					$('#tax_amount').val(dt.tax_amount);
					$('#novoucher').val(dt.novoucher);
					$('#no_bast').val(dt.no_bast);
					$('#id_bast').val(dt.id_bast);
					$('#id_voucher').val(dt.id_voucher);
					$('#addition').val(dt.addition);
					$('#id_unit').val(dt.id_unit);
					$('#id_tenant').val(dt.id_tenant);
					$('#id_parent').val(dt.id_parent);
					$('#periode_end').val(dt.periode_end);
					$('#deleted_user').val(dt.deleted_user);
					$('#deleted_date').val(dt.deleted_date);
					$('#periode_start').val(dt.periode_start);
					$('#pph_tarif').val(dt.pph_tarif);
					$('#tax_amount').val(dt.tax_amount);
					$('#flag_denda').val(dt.flag_denda);

					var ppnSelect = $('#ppn_select');
					ppnSelect.empty();
					ppnSelect.append($('<option>', {
						value: '',
						text: 'Pilih PPN'
					}));
					$.each(response.droplist_pajak, function (index, option) {
						ppnSelect.append($('<option>', {
							value: option.id,
							text: option.nama_pajak + ' - ' + option.nilai_pajak
						}));
					});
					

					if (dt.ppn === null) {
						ppnSelect.val('');
					} else {
						ppnSelect.val(dt.id_ppn);
					}

					var pphSelect = $('#pph_select');
					pphSelect.empty();
					pphSelect.append($('<option>', {
						value: '',
						text: 'Pilih PPh'
					}));
					$.each(response.droplist_pph, function (index, option) {
						pphSelect.append($('<option>', {
							value: option.id,
							text: option.nama_pajak + ' - ' + option.nilai_pajak
						}));
					});

					if (dt.pph === null) {
						pphSelect.val('');
					} else {
						pphSelect.val(dt.id_pph);
					}

					// console.log(dt.tax_amount);
					$('#amount').autoNumeric('set', dt.amount);
					$('#total').autoNumeric('set', dt.total);
					$('#tax_amount').val(dt.tax_amount);
					if(dt.tax_amount === null) {
						$('#tax_amount').val('0');
					}else{
						$('#tax_amount').autoNumeric('set', dt.tax_amount);
					}

				}
				$('#dlgDetail').modal('show');
			}
		});
	}

	function saveEditedData() {
		var formData = $('#frmEdit').serialize();
		
		$.ajax({
			type: 'POST',
			url: '<?php echo base_url() . $this->router->fetch_class(); ?>/saveEditLMInvoice',
			data: formData,
			dataType: 'json',
			success: function(response) {
				if (response.status == true) {
					Swal.fire({
						icon: 'success',
						title: response.msg,
						showConfirmButton: false,
						timer: 1500
					});
					$('#dlgDetail').modal('hide');
					otable.ajax.reload();
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: response.msg,
						footer: '',
						timer: 1500
					});
				}
			}
		});
	}

	function reload_grid() {
		otable.ajax.reload();
	}
	function deleteDok(id) {
    $.ajax({
      type: "POST",
      data: {
        id: id,
      },
      dataType: "JSON",
      url: '<?php echo base_url() . $this->router->fetch_class(); ?>/deleteDok/',
      beforeSend: function() {
        show_load();
      },
      complete: function() {
        hide_load();
      },
      success: function(response) {
        if (response.status == true) {
          Swal.fire({
            icon: 'success',
            title: response.msg,
            showConfirmButton: false,
            timer: 1500
          });
          otable.ajax.reload();
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
  }

</script>
