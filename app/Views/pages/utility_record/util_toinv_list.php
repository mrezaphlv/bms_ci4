<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title></title>
	<style>
		table {
			border-collapse: collapse;
			-webkit-border: 1px solid black;
		}

		th,
		td,
		tr {
			padding: 5px;
		}
		.amount_align{
		    text-align: right;
		}
	</style>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<script type="text/javascript">
	// window.onload = function() {
	// 	window.print();
	// }
</script>
<?php $time = date('d-m-Y H:i:s'); ?>
<body class="container mt-3" style="max-width: 90%;">
<h2>Utility Record</h2>
<b><p>Generate at : <?= $time ?></p></b>
    <table width="100%" border="1" style="font-size: small;" class="table table-bordered">
    <thead>
        <tr>
            <th>Periode</th>
            <th>Nama Customer</th>
            <th>Kode Unit</th>
            <th>Service</th>
            <th>Start Meter</th>
            <th>End Meter</th>
            <th>Total Pemakaian</th>
            <th>Amount</th>
            <th>Abodemen</th>
            <th>Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $key => $v): 
        $total_use = round($v->total_pemakaian, 1);
        ?>
            <tr>
                <td><?php echo !empty($v->utility_periode) ? date('F/Y', strtotime($v->utility_periode)) : NULL; ?></td>
                <td><?php echo $v->nama_tenant; ?></td>
                <td><?php echo $v->kode_unit; ?></td>
                <td><?php echo $v->nama_tagihan; ?></td>
                <td style="text-align:right;"><?php echo ($v->start_meter); ?></td>
                <td style="text-align:right;"><?php echo ($v->end_meter); ?></td>
                <td style="text-align:right;"><?php echo ($total_use); ?></td>
                <td style="text-align:right;"><?php echo ($v->amount); ?></td>
                <td style="text-align:right;"><?php echo round($v->abodemen); ?></td>
                <td style="text-align:right;"><?php echo ($v->total_amount); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
</body>
