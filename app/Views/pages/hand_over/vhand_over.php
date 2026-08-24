<?php
/**
 * View Serah Terima - CodeIgniter 4
 *
 * Controller sebaiknya mengirim variabel $akses dan $utildroplist.
 */
helper('url');

$uri = service('uri');
$controller = $uri->getSegment(1);
$moduleUrl = rtrim(site_url($controller), '/');

// Fail closed apabila data akses belum dikirim dari controller.
$aksesData = $akses ?? null;
if (is_array($aksesData)) {
    $canEdit   = (int) ($aksesData['can_edit'] ?? 0);
    $canDelete = (int) ($aksesData['can_delete'] ?? 0);
} else {
    $canEdit   = (int) ($aksesData->can_edit ?? 0);
    $canDelete = (int) ($aksesData->can_delete ?? 0);
}
$utilDropList = $utildroplist ?? (object) ['status' => false, 'data' => []];
?>

<style>
    body {
        background-color: #ffffff !important;
        color: #1e293b !important;
        font-family: 'Poppins', sans-serif;
        margin: 0 !important;
        padding: 0 !important;
    }
    .container-fluid {
        background: #ffffff !important;
        border-radius: 0 !important;
        padding: 24px !important;
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 100vh !important;
    }
    .judul_atas {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 6px;
        font-weight: 600;
    }
    .warna_teks1 {
        color: #0f172a !important;
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #829460;
        padding-bottom: 8px;
        margin-bottom: 24px !important;
        display: inline-block;
    }
    .handover-toolbar {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        background: #f8fafc;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    .handover-toolbar label {
        color: #475569 !important;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    
    /* EasyUI TextBox/Combobox overrides */
    .textbox {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        height: 34px !important;
    }
    .textbox .textbox-text {
        color: #0f172a !important;
        background: transparent !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 13px !important;
        padding: 4px 8px !important;
    }
    .textbox-addon .textbox-icon {
        color: #64748b !important;
        opacity: 0.8;
    }
    
    /* EasyUI Combo Panel dropdown items list */
    .panel.combo-p {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .combobox-item {
        background-color: #ffffff !important;
        color: #334155 !important;
        padding: 8px 12px !important;
        font-family: 'Poppins', sans-serif !important;
    }
    .combobox-item-hover, .combobox-item-selected {
        background-color: #829460 !important;
        color: #ffffff !important;
    }

    /* Datagrid Light Theme overrides */
    .handover-grid-wrap {
        width: 100%;
        min-height: 420px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .datagrid-wrap {
        border-color: #e2e8f0 !important;
        background-color: #ffffff !important;
    }
    .datagrid-header, .datagrid-td-rownumber {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
    }
    .datagrid-header td {
        border-color: #e2e8f0 !important;
    }
    .datagrid-header span {
        color: #334155 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
    }
    .datagrid-body {
        background-color: #ffffff !important;
    }
    .datagrid-row {
        background-color: #ffffff !important;
        color: #334155 !important;
        height: 48px !important;
    }
    .datagrid-row-alt {
        background-color: #f8fafc !important;
    }
    .datagrid-body td {
        border-color: #e2e8f0 !important;
        color: #334155 !important;
        font-size: 12px !important;
    }
    .datagrid-row-over {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        cursor: pointer;
    }
    .datagrid-row-selected {
        background: #829460 !important;
        color: #ffffff !important;
    }
    .datagrid-row-selected td {
        color: #ffffff !important;
    }
    
    /* Pagination styling */
    .datagrid-pager {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #475569 !important;
        padding: 10px 5px !important;
    }
    .datagrid-pager table {
        color: #475569 !important;
    }
    .pagination-btn-separator {
        border-left: 1px solid #cbd5e1 !important;
        border-right: 1px solid #cbd5e1 !important;
    }
    .l-btn-plain {
        color: #475569 !important;
    }
    .l-btn-plain:hover {
        background: #cbd5e1 !important;
        border-radius: 4px !important;
        color: #0f172a !important;
    }
    .pagination-page-list, .pagination-num {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        border-radius: 4px !important;
        padding: 2px 4px !important;
    }

    /* Buttons */
    .btn_warna1 {
        color: #ffffff !important;
        background-color: #829460 !important;
        border: 1px solid #829460 !important;
        transition: all 0.2s ease;
    }
    .btn_warna1:hover {
        background-color: #95a970 !important;
        border-color: #95a970 !important;
        transform: translateY(-1px);
    }
    .btn_warna2 {
        background: linear-gradient(135deg, #829460 0%, #68794c 100%) !important;
        border: none !important;
        color: white !important;
        padding: 8px 24px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 12px rgba(130, 148, 96, 0.2) !important;
        transition: all 0.3s ease !important;
    }
    .btn_warna2:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(130, 148, 96, 0.4) !important;
        background: linear-gradient(135deg, #95a970 0%, #768a57 100%) !important;
    }
    .btn-sm {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }
    .btn_option {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #475569 !important;
        border-radius: 6px !important;
        transition: all 0.2s ease;
    }
    .btn_option:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .btn-approve {
        background-color: rgba(96, 183, 96, 0.1) !important;
        border: 1px solid rgba(96, 183, 96, 0.3) !important;
        border-radius: 6px !important;
        color: #60B760 !important;
        font-weight: 600 !important;
    }
    .btn-approve:hover {
        background-color: rgba(96, 183, 96, 0.2) !important;
    }
    .btn-unapprove {
        background-color: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 6px !important;
        color: #475569 !important;
    }
    .btn-unapprove:hover {
        background-color: #e2e8f0 !important;
    }

    /* Dropdown Menus */
    .dropdown-menu.bms-dropdown-menu {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
        border-radius: 8px !important;
        padding: 6px 0 !important;
    }
    .bms-dropdown-item {
        color: #475569 !important;
        font-size: 13px !important;
        padding: 8px 16px !important;
        transition: all 0.15s ease;
    }
    .bms-dropdown-item:hover {
        background-color: #829460 !important;
        color: #ffffff !important;
    }
    .bms-dropdown-item i {
        margin-right: 8px;
    }

    /* Modals & Dialogs */
    .modal-content {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08) !important;
        color: #1e293b !important;
        border-radius: 12px !important;
    }
    .modal-header {
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 16px 24px !important;
    }
    .modal-header .modal-title {
        color: #0f172a !important;
        font-weight: 600 !important;
        font-size: 1.1rem;
    }
    .modal-header .btn-close {
        filter: none !important;
        opacity: 0.8;
    }
    .modal-header .btn-close:hover {
        opacity: 1;
    }
    .modal-body {
        padding: 24px !important;
    }
    .form-control, .form-select {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        border-radius: 6px !important;
        height: 38px;
    }
    .form-control:focus, .form-select:focus {
        background-color: #ffffff !important;
        border-color: #829460 !important;
        color: #0f172a !important;
        box-shadow: 0 0 0 0.25rem rgba(130, 148, 96, 0.25) !important;
    }
    .modal-body table td {
        padding: 10px 0;
        vertical-align: middle;
        color: #334155;
    }
    .modal-body table th {
        color: #475569;
        font-weight: 500;
        padding: 10px 0;
    }

    .form-selecttt {
        border-radius: 6px;
    }
    #drop_zone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        width: 100%;
        padding: 40px 0;
        color: #64748b;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    #drop_zone:hover {
        border-color: #829460;
        color: #0f172a;
    }
    #drop_zone p {
        font-size: 16px;
        text-align: center;
        margin: 0;
    }
    .btn_file_pick, .btn_file_pick:hover {
        border-color: #829460;
        color: #829460;
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

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.js"></script>
<div class="container-fluid">
    <div  class="judul_atas"><div>Transaksi / Serah Terima</div></div>
    <button type="button" id="btnCancelAdd"  class="btn btn_back_arrow" style="line-height:10px;display:none;float:left;"><i class="bi bi-x-circle"></i></button>
    <div class="warna_teks1" style="font-weight:900;font-size:1.2rem;display: inline-block;margin-bottom:20px;">Serah Terima</div>
    <div id="toolbarHandOver" class="handover-toolbar">
        <div>
            <label class="">Status</label><br>
            <select class="easyui-combobox" id="gstatus" name="gstatus" style="width:190px;" data-options="editable:false,panelHeight:'auto'">
                <option value="">Pilih..</option>
                <option value="NEW">NEW</option>
                <option value="DONE">DONE</option>
                <option value="SETUP_UTILITIES">SETUP UTILITIES</option>
                <option value="SETUP_CHARGE">SETUP CHARGE</option>
                <option value="DIALIHKAN">DIALIHKAN</option>
            </select>
        </div>
        <button type="button" onclick="reload_grid()" class="btn btn_warna2 btn_bentuk1">Filter</button>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="handover-grid-wrap">
                <table id="dgHandOver"></table>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="editDlg" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--<div class="modal-header">-->
      <!--  <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>-->
      <!--  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
      <!--</div>-->
      <div class="modal-body">
        <form id="frmEdit">
            <input type="hidden" name="d_id" id="d_id">
           <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-2 col-form-label">Utilities</label>
                    <div class="col-sm-8">
                      <select required class="form-select" data-live-search="true" name="dutil" id="dutil">
                          <option value="">- Pilih Utilities -</option>
                          <?php if ($utilDropList->status): ?>
                              <?php foreach ($utilDropList->data as $key => $ba): ?>
                                  <option value="<?php echo $ba->id; ?>" ><?php echo $ba->nama; ?></option>
                              <?php endforeach; ?>
                          <?php endif; ?>
                      </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-2 col-form-label">Kode</label>
                    <div class="col-sm-8">
                      <input required type="text" class="form-control" id="dkode" name="dkode">
                    </div>
                </div>
          <div class="mb-3 row">
            <label for="inputPassword" class="col-sm-2 col-form-label">Start Meter</label>
            <div class="col-sm-8">
              <input required type="number" class="form-control" id="dstart_meter" name="dstart_meter">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12 text-center">
              <button type="submit" id="btnSaveEditUtil"  class="btn btn_warna1 btn_bentuk1">Save</button>
            </div>
          </div>
        </form>
      </div>
      <!--<div class="modal-footer">-->
      <!--  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
        
      <!--</div>-->
    </div>
  </div>
</div>

<!-- Modal Print -->
<div class="modal fade" id="dlgPrint" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" >
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Print Dokumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" value="" id="id_agreement_print" name="id_agreement_print" >
        <table width="100%">
            <tr class="display:none;" id="bast_non_wakil">
                <th>BAST</th>
                <td><button onclick="printDokumen('bast')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="bast_insentive">
                <th>BAST Insentive</th>
                <td><button onclick="printDokumen('bast_insentive')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="bast_insentive_kuasa">
                <th>BAST Insentive Kuasa Direksi</th>
                <td><button onclick="printDokumen('bast_insentive_kuasa')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
             <tr class="display:none;" id="bast_non_wakil_dikuasakan">
                <th>BAST Kuasa Direksi</th>
                <td><button onclick="printDokumen('bast_dikuasakan')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="bast_insentive_wakil">
                <th>BAST Insentive Wakil</th>
                <td><button onclick="printDokumen('bast_insentive_diwakilkan')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="bast_insentive_wakil_kuasa">
                <th>BAST Insentive Wakil Kuasa Direksi</th>
                <td><button onclick="printDokumen('bast_insentive_diwakilkan_kuasa')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="bast_wakil">
                <th>BAST Wakil</th>
                <td><button onclick="printDokumen('bast_diwakilkan')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="bast_wakil_kuasa">
                <th>BAST Wakil Kuasa Direksi</th>
                <td><button onclick="printDokumen('bast_diwakilkan_dikuasakan')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="print_pp">
                <th>Pinjam Pakai</th>
                <td><button onclick="printDokumen('pinjam_pakai')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="print_pp_kuasa">
                <th>Pinjam Pakai Kuasa Direksi</th>
                <td><button onclick="printDokumen('pinjam_pakai_kuasa')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="print_wakil_2">
                <th>Pinjam Pakai Wakil</th>
                <td><button onclick="printDokumen('pinjam_pakai_diwakilkan')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr class="display:none;" id="print_wakil_2_kuasa">
                <th>Pinjam Pakai Wakil</th>
                <td><button onclick="printDokumen('pinjam_pakai_diwakilkan_kuasa')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <!--<tr>-->
            <!--    <th>Surat Izin Huni</th>-->
            <!--    <td><button onclick="printDokumen('izin_huni')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>-->
            <!--</tr>-->
            <tr>
                <th>Serah Terima Utilitas</th>
                <td><button onclick="printDokumen('serah_terima_util')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr>
                <th>Tanda Terima</th>
                <td><button onclick="printDokumen('tanda_terima')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
            <tr>
                <th>Tata Tertib Penghuni</th>
                <td><button onclick="printDokumen('tata_tertib')" class="btn btn_warna1"><i class="bi bi-printer"></i> Print</button></td>
            </tr>
        </table>
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal Print -->
<!-- Modal tgl lunas -->
<div class="modal fade" id="dlgConf" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" >
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Konfirmasi Tgl Bast</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" value="" id="id_agreement_tgl" name="id_agreement_tgl" >
          <input type="hidden" value="" id="id_bast_tgl" name="id_bast_tgl" >
        <table width="100%">
            <tr>
                <th>Tgl Bast</th>
                <td><input class="form-control form-control-sm" type="text" name="tgl_bast" id="tgl_bast" /></td>
            </tr>
            <tr>
                <th>Tgl Lunas</th>
                <td><input class="form-control form-control-sm" type="text" name="tgl_lunas" id="tgl_lunas" /></td>
            </tr>
        </table>
        <br>
        <div style="text-align: center;">
        <button id="btn_save_tgllunas" onclick="input_lunas()" class="btn btn_warna1 btn_bentuk1 btn-sm">Save</button>
        </div>
       
      </div>
      
    </div>
  </div>
</div>
<!-- Close Modal tgl lunas -->
<div class="modal fade" id="dlgConfirmHadir" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div id="dlgApprovePinContent" class="modal-content">
			<div class="modal-body">
				<form id="frmConfirm">
					<input type="hidden" id="id_agreement" name="id_agreement" value="">
					<div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="no_ppjb" class="col-form-label">No. PPJB<span style="color: red;">*</span></label>
						</div>
						<div class="col-8">
							<input type="text" id="no_ppjb" name="no_ppjb" class="form-control" required>
						</div>
					</div>
					<br>
                    <div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="tgl_ppjb" class="col-form-label">Tanggal Lunas<span style="color: red;">*</span></label>
						</div>
						<div class="col-8">
							<input type="date" id="tgl_ppjb" name="tgl_ppjb" class="form-control" required>
						</div>
					</div>
					<br>
					<div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="kode_kir" class="col-form-label">Kode Identitas<span style="color: red;">*</span></label>
						</div>
						<div class="col-8">
							<input type="text" id="kode_kir" name="kode_kir" class="form-control" required>
						</div>
					</div>
					<br>
                    <div class="row g-3 align-items-center">
						<div class="col-4">
							<label for="handover_date" class="col-form-label">Tanggal Serah Terima<span style="color: red;">*</span></label>
						</div>
						<div class="col-8">
							<input type="date" id="handover_date" name="handover_date" class="form-control" required>
						</div>
					</div>
					<br>
					<br>
					<div class="row">
						<div class="col-md-12 text-center">
							<button id="pencet_confirm" type="submit" class="btn btn_warna1 btn_bentuk1">Submit</button>
						</div>
					</div>
				</form>
			</div>

		</div>
	</div>
</div>
<script>
    var tb_checkbox = [];

    function getStatusFilter() {
        if ($('#gstatus').data('combobox')) {
            return $('#gstatus').combobox('getValue');
        }
        return $('#gstatus').val();
    }

    function getGridHeight() {
        var used = $('.judul_atas').outerHeight(true) + $('.warna_teks1').outerHeight(true) + $('#toolbarHandOver').outerHeight(true);
        var padding = 90;
        return Math.max(420, $(window).height() - used - padding);
    }

    function reloadHandOverGrid() {
        $('#dgHandOver').datagrid('load', {
            status: getStatusFilter()
        });
    }

    function formatNoAgreement(value, row) {
        var text = value || '';
        if (row.id_parent == 0 && row.id_bast_new !== null && text !== '') {
            return '<span style="color:red;">' + text + '</span>';
        }
        return text;
    }

    function formatStatusHandOver(value, row) {
        var html = '';

        if (row.id_handover_check == null) {
            if (row.status_checklist == 'APPROVED' && row.status_agreement == 'APPROVED' && row.waktu_hadir != null) {
                html += '<a class="btn btn-sm btn-dark" onclick="inputAgreement(' + row.id + ',1,0)" href="javascript:;">NEW</a>';
            }
        } else {
            if (row.dutil_check == null && row.dcharge_check == null) {
                html += '<a class="btn btn_warna2 btn_bentuk1" onclick="inputAgreement(' + row.id + ',2,' + row.id_handover_check + ')" href="javascript:;">Setup Utilities</a>';
            } else {
                if (row.dcharge_check == null) {
                    html += '<a class="btn btn_warna2 btn_bentuk1" onclick="inputAgreement(' + row.id + ',3,' + row.id_handover_check + ')" href="javascript:;">Setup Charge</a>';
                } else {
                    if (row.id_parent == 0) {
                        if (row.id_bast_new !== null) {
                            html += '<span style="color:green;">DONE</span>';
                            html += '<br><span>(Dialihkan)</span>';
                        } else if (row.id_closed_agreement != null) {
                            html += '<span>Closed Agreement</span>';
                        } else {
                            html += '<span style="color:green;">DONE</span>';
                        }
                    } else {
                        html += '<span>Insentive PPN</span>';
                    }
                }
            }
        }

        return html;
    }

    function formatAksiHandOver(value, row) {
        var data = row.id;
        var ast = row.status_bayar == 'LUNAS' ? 1 : 2;
        var wakil = row.diwakilkan != 'Diwakilkan' ? 2 : 1;
        var insentive = row.id_parent != 0 ? 1 : 0;
        var html = '';

        <?php if ($canEdit !== 1 && $canDelete !== 1): ?>
        html += '<div class="dropdown"><button disabled class="btn btn_option btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button></div>';
        <?php else: ?>
        html += '<div class="dropdown"><button class="btn btn_option btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>';
        html += '<ul class="dropdown-menu bms-dropdown-menu">';

        if (row.id_handover_check == null) {
            if (row.status_checklist == 'APPROVED' && row.status_agreement == 'APPROVED' && row.waktu_hadir != null) {
                html += '<li><a class="dropdown-item bms-dropdown-item" onclick="inputAgreement(' + data + ',1,0)" href="javascript:;"><i class="bi bi-file-earmark-plus"></i>  Serah Terima</a></li>';
            }
        } else {
            html += '<li><a class="dropdown-item bms-dropdown-item" onclick="viewDetail(' + data + ')" href="javascript:;"><i class="bi bi-eye"></i>  Detail</a></li>';
            if (row.step !== null && Number(row.step) <= 2) {
                html += '<li><a class="dropdown-item bms-dropdown-item" onclick="inputAgreement(' + data + ',21,' + row.id_handover_check + ')" href="javascript:;"><i class="bi bi-pencil-square"></i>  Edit Utilities</a></li>';
            }
            if (row.dutil_check != null && row.dcharge_check != null) {
                if (row.no_agreement != '-') {
                    if (row.id_parent == 0 && row.id_insentive == null) {
                        html += '<li><a class="dropdown-item bms-dropdown-item" onclick="insentive(' + data + ')" href="javascript:;"><i class="bi bi-eye"></i>  Insentive ppn</a></li>';
                    }
                }
                html += '<li><a class="dropdown-item bms-dropdown-item" onclick="dlgPrint(' + data + ',' + ast + ',' + wakil + ',' + insentive + ')" href="javascript:;"><i class="bi bi-printer"></i>  Print</a></li>';
                if (row.no_agreement == '-') {
                    html += '<li><a class="dropdown-item bms-dropdown-item" onclick="dlgConf(' + data + ',' + row.id_handover_check + ')" href="javascript:;"><i class="bi bi-input-cursor-text"></i>  Konfirmasi Tgl Bast</a></li>';
                }
            }
        }

        html += '</ul></div>';
        <?php endif; ?>

        return html;
    }

    $(document).ready(function () {
    // set_checkbox();
    $('#dlgPrint').on('hidden.bs.modal',function(event){
        $('#id_agreement_print').val('');
    });
    $('#dlgConf').on('hidden.bs.modal',function(event){
        $('#id_agreement_tgl').val('');
        $('#id_bast_tgl').val('');
        $('#tgl_lunas').val('');
    });
    $( "#tgl_lunas" ).datepicker({
                    format: 'dd-mm-yyyy',
                    }).on('changeDate', function(e){
                            $(this).datepicker('hide');
                    });
     $( "#tgl_bast" ).datepicker({
                    format: 'dd-mm-yyyy',
                    }).on('changeDate', function(e){
                            $(this).datepicker('hide');
                    });
    $('#frmConfirm').on('hidden.bs.modal', function(event) {
			$('#id_agreement').val('');
			$('#no_ppjb').val('');
		})

    $('#dgHandOver').datagrid({
        fit: false,
        height: getGridHeight(),
        method: 'post',
        url: '<?= $moduleUrl ?>/grid',
        toolbar: '#toolbarHandOver',
        singleSelect: true,
        rownumbers: true,
        pagination: true,
        pageSize: 20,
        pageList: [10, 20, 50, 100],
        fitColumns: false,
        striped: true,
        remoteSort: true,
        queryParams: {
            status: getStatusFilter()
        },
        onBeforeLoad: function(param) {
            param.status = getStatusFilter();
        },
        columns: [[
            {field: 'id', title: 'ID', width: 60, sortable: true, hidden: true},
            {field: 'id_parent', title: 'ID Parent', width: 80, hidden: true},
            {field: 'no_agreement', title: 'No Agreement', width: 140, sortable: true, formatter: formatNoAgreement},
            {field: 'no_pinjam_pakai', title: 'No Pinjam Pakai', width: 150, sortable: true},
            {field: 'no_undangan', title: 'No Undangan', width: 150, sortable: true},
            {field: 'handover_date', title: 'HandOver Date', width: 120, sortable: true},
            {field: 'nama_owner', title: 'Owner', width: 210, sortable: true},
            {field: 'tipe_tenant', title: 'Tipe', width: 120, sortable: true},
            {field: 'kode_unit', title: 'Unit', width: 100, sortable: true},
            {field: 'status_bayar', title: 'Status Bayar', width: 120, sortable: true},
            {field: 'diwakilkan', title: 'Confirm', width: 130, sortable: true},
            {field: 'status_grid', title: 'Status', width: 150, formatter: formatStatusHandOver},
            {field: 'aksi', title: 'Aksi', width: 90, formatter: formatAksiHandOver}
        ]]
    });
    $('#frmConfirm').submit(function(e) {
			e.preventDefault();
			$.ajax({
				type: "POST",
				data: new FormData(this),
				cache: false,
				contentType: false,
				processData: false,
				dataType: "JSON",
				async: false,
				url: '<?= $moduleUrl ?>/insentive_ppn/',
				success: function(response) {
					// console.log(response);
					if (response.status == true) {
						Swal.fire({
							icon: 'success',
							title: response.msg,
							showConfirmButton: false,
							timer: 1500
						});
						$('#dlgConfirmHadir').modal('hide');
						reloadHandOverGrid();
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: response.msg,
							footer: ''
						})
					}

				}
			});
		});


});



function decimalValidbck(evt){
    console.log(evt);
}


function editData(id){
    window.location = '<?= $moduleUrl ?>/form/'+id;
}
function viewDetail(id){
    window.location = '<?= $moduleUrl ?>/detail/'+id;
}
function insentive(id){
		$('#id_agreement').val(id);
		$('#dlgConfirmHadir').modal('show');
    // window.location = '<?= $moduleUrl ?>/insentive_ppn/'+id;
}
function dlgPrint(id, sts, wakil, insentive) {
    console.log(insentive);
    if (wakil == 2) {
        if (sts == 1) {
            if(insentive==1){
                $('#bast_insentive').fadeIn();
                $('#bast_insentive_kuasa').fadeIn();
                $('#bast_wakil').fadeOut();
                $('#bast_wakil_kuasa').fadeOut();
                $('#print_wakil_2').fadeOut();
                $('#print_wakil_2_kuasa').fadeOut();
                $('#print_pp').fadeOut();
                $('#print_pp_kuasa').fadeOut();
                $('#bast_non_wakil').fadeOut();
                $('#bast_non_wakil_dikuasakan').fadeOut();
                $('#bast_insentive_wakil').fadeOut();
                $('#bast_insentive_wakil_kuasa').fadeOut();
            } else {
                $('#bast_wakil').fadeOut();
                $('#bast_wakil_kuasa').fadeOut();
                $('#print_wakil_2').fadeOut();
                $('#print_wakil_kuasa').fadeOut();
                $('#print_pp').fadeOut();
                $('#print_pp_kuasa').fadeOut();
                $('#bast_non_wakil').fadeIn();
                $('#bast_non_wakil_dikuasakan').fadeIn();
                $('#bast_insentive').fadeOut();
                $('#bast_insentive_kuasa').fadeOut();
                $('#bast_insentive_wakil').fadeOut();
                $('#bast_insentive_wakil_kuasa').fadeOut();
            }
        } else {
            if(insentive==1){
                $('#bast_insentive').fadeIn();
                $('#bast_insentive_kuasa').fadeIn();
                $('#bast_wakil').fadeOut();
                $('#bast_wakil_kuasa').fadeOut();
                $('#bast_non_wakil').fadeOut();
                $('#bast_non_wakil_dikuasakan').fadeOut();
                $('#print_pp').fadeIn();
                $('#print_pp_kuasa').fadeIn();
                $('#print_wakil_2').fadeOut(); 
                $('#print_wakil_2_kuasa').fadeOut(); 
                $('#bast_insentive_wakil').fadeOut();
                $('#bast_insentive_wakil_kuasa').fadeOut();

            } else {
                $('#bast_wakil').fadeOut();
                $('#bast_wakil_kuasa').fadeOut();
                $('#bast_non_wakil').fadeIn();
                $('#bast_non_wakil_dikuasakan').fadeIn();
                $('#print_pp').fadeIn();
                $('#print_pp_kuasa').fadeIn();
                $('#print_wakil_2').fadeOut();
                $('#print_wakil_2_kuasa').fadeOut();
                $('#bast_insentive').fadeOut();
                $('#bast_insentive_kuasa').fadeOut();
                $('#bast_insentive_wakil').fadeOut();
                $('#bast_insentive_wakil_kuasa').fadeOut();
            }
        }
    } else {
        if (sts === 1) {
            if(insentive==1){
                $('#bast_insentive_wakil').fadeIn();
                $('#bast_insentive_wakil_kuasa').fadeIn();
                $('#bast_non_wakil').fadeOut();
                $('#bast_non_wakil_dikuasakan').fadeOut();
                $('#bast_wakil').fadeOut();
                $('#bast_wakil_kuasa').fadeOut();
                $('#print_pp').fadeOut();
                $('#print_pp_kuasa').fadeOut();
                $('#print_wakil_2').fadeOut();
                $('#print_wakil_2_kuasa').fadeOut();
                $('#bast_insentive').fadeOut();
                $('#bast_insentive_kuasa').fadeOut();

            } else {
                $('#bast_non_wakil').fadeOut();
                $('#bast_non_wakil_dikuasakan').fadeOut();
                $('#bast_wakil').fadeIn();
                $('#bast_wakil_kuasa').fadeIn();
                $('#print_pp').fadeOut();
                $('#print_pp_kuasa').fadeOut();
                $('#print_wakil_2').fadeOut();
                $('#print_wakil_2_kuasa').fadeOut();
                $('#bast_insentive_wakil').fadeOut();
                $('#bast_insentive_wakil_kuasa').fadeOut();
                $('#bast_insentive').fadeOut();
                $('#bast_insentive_kuasa').fadeOut();
            }
           
        } else {
            if(insentive==1){
                $('#bast_insentive_wakil').fadeIn();
                $('#bast_insentive_wakil_kuasa').fadeIn();
                $('#bast_non_wakil').fadeOut();
                $('#bast_non_wakil_dikuasakan').fadeOut();
                $('#bast_wakil').fadeOut();
                $('#bast_wakil_kuasa').fadeOut();
                $('#print_pp').fadeOut();
                $('#print_pp_kuasa').fadeOut();
                $('#print_wakil_2').fadeIn();
                $('#print_wakil_2_kuasa').fadeIn();
                $('#bast_insentive').fadeOut();
                $('#bast_insentive_kuasa').fadeOut();

            } else {
                $('#bast_non_wakil').fadeOut();
                $('#bast_non_wakil_dikuasakan').fadeOut();
                $('#bast_wakil').fadeIn();
                $('#bast_wakil_kuasa').fadeIn();
                $('#print_pp').fadeOut();
                $('#print_pp_kuasa').fadeOut();
                $('#print_wakil_2').fadeIn();
                $('#print_wakil_2_kuasa').fadeIn();
                $('#bast_insentive_wakil_kuasa').fadeOut();
                $('#bast_insentive').fadeOut();
                $('#bast_insentive_kuasa').fadeOut();

            }
           
        }
    }

    $('#id_agreement_print').val(id);
    $('#dlgPrint').modal('show');
}
 

    function reload_checkbox(){
        // console.log();
        // $('#check_new').is(":checked")
        // reloadHandOverGrid();
        var arr_checkbox = [];
        if($('#check_new').is(":checked") == true){
            arr_checkbox.push($('#check_new').val());
        }
        if($('#check_done').is(":checked") == true){
            arr_checkbox.push($('#check_done').val());
        }
		if($('#check_utils').is(":checked") == true){
            arr_checkbox.push($('#check_utils').val());
        }
		if($('#check_charge').is(":checked") == true){
            arr_checkbox.push($('#check_charge').val());
        }

        // console.log(arr_checkbox);
        tb_checkbox = arr_checkbox;
        reloadHandOverGrid();
    }
    function set_checkbox(){
        // console.log();
        // $('#check_new').is(":checked")
        // reloadHandOverGrid();
        var arr_checkbox = [];
        if($('#check_new').is(":checked") == true){
            tb_checkbox.push($('#check_new').val());
        }
        if($('#check_approved').is(":checked") == true){
            tb_checkbox.push($('#check_approved').val());
        }
        if($('#check_rejected').is(":checked") == true){
            tb_checkbox.push($('#check_rejected').val());
        }
        console.log(arr_checkbox);
        tb_checkbox = arr_checkbox;
        // reloadHandOverGrid();
    }
    
    
    function inputAgreement(id, step,id_handover){
       var url = '';
       switch(step) {
          case 1:
            url = '<?= $moduleUrl ?>/form/'+id;
            break;
          case 2:
            url = '<?= $moduleUrl ?>/form_2/'+id+'/'+id_handover;
            break;
            case 3:
            url = '<?= $moduleUrl ?>/form_3/'+id+'/'+id_handover;
            break;
		case 21:
            url = '<?= $moduleUrl ?>/edit_form_2/'+id+'/'+id_handover;
            break;
        } 
       // url = '<?= $moduleUrl ?>/form/'+id;
        window.location = url;
    }
    function printDokumen(param){
        var id = $('#id_agreement_print').val();
        window.open('<?= $moduleUrl ?>/print_dokumen/'+param+'/'+id);
    }
    function create_schedule_tagihan(p){
        var h = confirm("Buat Schedule tagihan ?");
        if(h == true){
            console.log('dd');
            // show_load();
            $.ajax({
				type: "POST",
				url: '<?= $moduleUrl ?>/gen_schedule_tagihan/',
				dataType: 'json',
				data: {
				    id_handover: p,
				},
				beforeSend: function() {show_load();},
				complete: function() {hide_load();},
				success: function(response) {
				if(response.status == true){
                        Swal.fire({
                          icon: 'success',
                          title: response.msg,
                          showConfirmButton: false,
                          timer: 1500
                        });
                        reloadHandOverGrid();
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
    function fnPengalihanHak(id){
        var url = '<?= $moduleUrl ?>/pengalihan_hak/'+id;
        window.location = url;
    }
    
    function reload_grid() {
		reloadHandOverGrid();
}
    function dlgConf(id,id_bast){
        $('#id_agreement_tgl').val(id);
        $('#id_bast_tgl').val(id_bast);
        $('#dlgConf').modal('show');
    }
    function input_lunas(){
        var data = {
            idp_undangan : $('#id_agreement_tgl').val(),
            idp_bast : $('#id_bast_tgl').val(),
            tgl_lunas : $('#tgl_lunas').val(),
            tgl_bast : $('#tgl_bast').val()
        }
        $.ajax({
				type: "POST",
				data: data,
				dataType: "JSON",
				async: false,
                beforeSend: function() {show_load();},
				complete: function() {hide_load();},
				url: '<?= $moduleUrl ?>/input_lunas/',
				success: function(response) {
					if (response.status == true) {
						Swal.fire({
							icon: 'success',
							title: response.msg,
							showConfirmButton: false,
							timer: 1500
						});
						$('#dlgConf').modal('hide');
						reloadHandOverGrid();
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: response.msg,
							footer: ''
						})
					}

				}
			});
    }

    $(window).on('resize', function () {
        $('#dgHandOver').datagrid('resize', {height: getGridHeight()});
    });
</script>
