<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	<div class="content-wrapper">
		<div class="container">

			<!-- Main content -->
			<section class="content">
				<?php
					$parse = parse_ini_file('admin/config.ini', FALSE, INI_SCANNER_RAW);
					$title = $parse['election_title'];
				?>
				<h1 class="page-header text-center title"><b><?php echo strtoupper($title); ?></b></h1>
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<?php
						if(isset($_SESSION['error'])){
							?>
							<div class="alert alert-danger alert-dismissible">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<ul>
									<?php
										foreach($_SESSION['error'] as $error){
											echo "
												<li>".$error."</li>
											";
										}
									?>
								</ul>
							</div>
							<?php
							unset($_SESSION['error']);
						}
						if(isset($_SESSION['success'])){
							echo "
								<div class='alert alert-success alert-dismissible'>
									<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
									<h4><i class='icon fa fa-check'></i> Success!</h4>
								".$_SESSION['success']."
								</div>
							";
							unset($_SESSION['success']);
						}
						?>

						<div class="box box-solid">
							<div class="box-header with-border">
								<h3 class="box-title"><b>Verify Your Vote</b></h3>
							</div>
							<div class="box-body">
								<div id="verification_steps">
									<div class="text-center">
										<h3>Review Your Ballot</h3>
										<?php
										// Fetch and display the voter's selections
										$voter_id = $voter['id'];
										$sql = "SELECT positions.description, candidates.firstname, candidates.lastname 
												FROM votes 
												LEFT JOIN positions ON positions.id=votes.position_id 
												LEFT JOIN candidates ON candidates.id=votes.candidate_id 
												WHERE votes.voters_id = '$voter_id'
												ORDER BY positions.priority ASC";
										$query = $conn->query($sql);
										
										if($query->num_rows > 0){
											echo "<table class='table table-bordered table-striped'>";
											echo "<thead><tr><th>Position</th><th>Candidate</th></tr></thead>";
											echo "<tbody>";
											while($row = $query->fetch_assoc()){
												echo "<tr>";
												echo "<td>".$row['description']."</td>";
												echo "<td>".$row['firstname']." ".$row['lastname']."</td>";
												echo "</tr>";
											}
											echo "</tbody></table>";
											
											// Only show confirm button if there are votes to confirm
											echo '
											<button type="button" class="btn btn-success btn-flat btn-lg" id="confirm_votes">
												<i class="fa fa-check"></i> Confirm Votes
											</button>';
										}
										else{
											echo "<p>No votes found to verify.</p>";
										}
										?>
									</div>
									
									<div id="otp_section" style="display:none;" class="text-center">
										<h3>Enter OTP Verification Code</h3>
										<p>An OTP has been sent to your email address.</p>
										<form id="otp_form" method="POST">
											<div class="form-group">
												<input type="text" class="form-control input-lg" style="width:200px;margin:0 auto;" 
													id="otp_input" name="otp" placeholder="Enter OTP" maxlength="6" required>
											</div>
											<button type="submit" class="btn btn-primary btn-flat btn-lg">
												<i class="fa fa-check-circle"></i> Submit OTP
											</button>
										</form>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</section>
		</div>
	</div>

	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
	// Handle confirm votes button click
	$('#confirm_votes').click(function(){
		$.ajax({
			type: 'POST',
			url: 'send_otp.php',
			dataType: 'json',
			success: function(response){
				if(response.success){
					$('#confirm_votes').hide();
					$('#otp_section').show();
				}
				else{
					alert('Error sending OTP. Please try again.');
				}
			}
		});
	});

	// Handle OTP form submission
	$('#otp_form').submit(function(e){
		e.preventDefault();
		var otp = $('#otp_input').val();
		
		$.ajax({
			type: 'POST',
			url: 'verify_otp.php',
			data: {otp: otp},
			dataType: 'json',
			success: function(response){
				if(response.success){
					window.location = 'submit_final.php';
				}
				else{
					alert('Invalid OTP. Please try again.');
				}
			}
		});
	});
});
</script>
</body>
</html>