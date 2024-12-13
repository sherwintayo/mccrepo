<?php
// Include the configuration file and database connection class
require_once('../config.php'); // Adjust the path if needed
require_once('../classes/DBConnection.php'); // Adjust the path if needed

// Create a new database connection instance
$db = new DBConnection();
$connection = $db->conn;

if ($connection->connect_error) {
	die("Connection failed: " . $connection->connect_error);
}
?>


<style>
	.img-avatar {
		width: 45px;
		height: 45px;
		object-fit: cover;
		object-position: center center;
		border-radius: 100%;
	}
</style>


<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">List of Activity</h3>
		<div class="card-tools">
			<a href="javascript:void(0)" id="print_button" class="btn btn-flat btn-sm btn-primary">
				<span class="fas fa-print"></span> Print
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
			<div class="container-fluid">
				<table class="table table-hover table-striped">
					<colgroup>
						<!-- <col width="5%"> -->
						<!-- <col width="20%"> -->
						<col width="25%">
						<col width="25%">
						<col width="25%">
						<!-- <col width="10%"> -->
					</colgroup>
					<thead>
						<tr>
							<!-- <th>#</th> -->
							<th>Date</th>
							<th>User</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						// $servername = "localhost";
						// $username = "u510162695_bsit_repo";
						// $password = "1Bsit_repo";
						// $database = "u510162695_bsit_repo";
						// $connection = new mysqli($servername, $username, $password, $database);
						
						// if ($connection->connect_error) {
						//     die("Connection failed: " . $connection->connect_error);
						// }
						$query = mysqli_query($connection, "select * from  activity_log ORDER BY activity_log_id DESC") or die(mysql_error());
						while ($row = mysqli_fetch_array($query)) {
							?>
							<tr>
								<td><?php echo $row['date']; ?></td>
								<td><?php echo $row['username']; ?></td>
								<td><?php echo $row['action']; ?></td>
							</tr>

						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	document.getElementById('print_button').addEventListener('click', function () {
		// Retrieve the table content
		const table = document.getElementById('activity_table').outerHTML;

		// Create a new window for printing
		const printWindow = window.open('', '', 'height=600,width=800');
		printWindow.document.write('<html><head><title>Activity Log</title>');
		printWindow.document.write('<style>');
		printWindow.document.write('table {width: 100%; border-collapse: collapse;}');
		printWindow.document.write('table, th, td {border: 1px solid black; text-align: left; padding: 8px;}');
		printWindow.document.write('th {background-color: #f2f2f2;}');
		printWindow.document.write('</style>');
		printWindow.document.write('</head><body>');
		printWindow.document.write('<h1>Activity Log</h1>');
		printWindow.document.write(table);
		printWindow.document.write('</body></html>');
		printWindow.document.close();

		// Trigger the print dialog
		printWindow.print();
	});
</script>
<!-- <script>
	$(document).ready(function(){
				$('#create_new').click(function(){
			uni_modal("Curriculum Details","curriculum/manage_curriculum.php")
		})
				$('.edit_data').click(function(){
			uni_modal("Curriculum Details","curriculum/manage_curriculum.php?id="+$(this).attr('data-id'))
		})
		$('.delete_data').click(function(){
			_conf("Are you sure to delete this Curriculum permanently?","delete_curriculum",[$(this).attr('data-id')])
		})
		$('.view_data').click(function(){
			uni_modal("curriculum Details","curriculum/view_curriculum.php?id="+$(this).attr('data-id'))
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable({
						columnDefs: [
								{ orderable: false, targets: 5 }
						],
				});
	})
	function delete_curriculum($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_curriculum",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occured.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.reload();
				}else{
					alert_toast("An error occured.",'error');
					end_loader();
				}
			}
		})
	}
</script> -->