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
<body class="container mt-3" style="max-width: 90%;">
<!-- <h2>Utility Record Template</h2> -->
    <table border="1"  style="font-size: small;" class="table table-bordered">
    <thead>
        <tr style="background: #dee2e6;">
            <th style="color:red;">ID</th>
            <th style="color:red;">id_unit</th>
            <th style="color:red;">kode unit</th>
            <th style="color:red;">ID meter</th>
            <th>kode meter</th>
            <th>start meter</th>
            <th>end meter </th>
            <th>Pemakaian</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1;?>
        <?php foreach ($data as $key => $v): 
        ?>
            <tr>
                <td style="color:red;"><?php echo $v->id_data; ?></td>
                <td style="color:red;"><?php echo $v->id_unit; ?></td>
                <td style="color:red;"><?php echo $v->kode_unit; ?></td>
                <td style="color:red;"><?php echo $v->id_meter; ?></td>
                <td><?php echo $v->kode_meter; ?></td>
                <td><?php echo !empty($v->start_meter) ? number_format($v->start_meter,2,",","") : 0; ?></td>
                <td><?php echo !empty($v->end_meter) ? number_format($v->end_meter,2,",","") : 0; ?></td>
                <td></td>
                <td></td>
            </tr>
        <?php
    $no++;
    endforeach; ?>
    </tbody>
    </table>
</body>
