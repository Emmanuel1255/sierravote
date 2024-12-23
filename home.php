<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
   
  <div class="content-wrapper">
    <div class="container">
      <section class="content">
        <?php
          $parse = parse_ini_file('admin/config.ini', FALSE, INI_SCANNER_RAW);
          $title = $parse['election_title'];
        ?>
        <h1 class="page-header text-center title"><b><?php echo strtoupper($title); ?></b></h1>
        <div class="row">
          <div class="col-xs-12">
            <?php
              if(isset($_SESSION['error'])){
                ?>
                <div class="alert alert-danger alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <ul>
                    <?php
                      foreach($_SESSION['error'] as $error){
                        echo "<li>".$error."</li>";
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
 
            <div class="alert alert-danger alert-dismissible" id="alert" style="display:none;">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <span class="message"></span>
            </div>

            <?php
              $sql = "SELECT * FROM votes WHERE voters_id = '".$voter['id']."'";
              $vquery = $conn->query($sql);
              if($vquery->num_rows > 0){
                ?>
                <div class="text-center">
                  <h3>You have already voted for this election.</h3>
                  <a href="#view" data-toggle="modal" class="btn btn-flat btn-primary btn-lg">View Ballot</a>
                </div>
                <?php
              }
              else{
                ?>
                <!-- Voting Ballot -->
                <form method="POST" id="ballotForm" action="javascript:void(0);">
                  <?php
                    include 'includes/slugify.php';

                    $candidate = '';
                    $sql = "SELECT * FROM positions ORDER BY priority ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      $sql = "SELECT * FROM candidates WHERE position_id='".$row['id']."'";
                      $cquery = $conn->query($sql);
                      while($crow = $cquery->fetch_assoc()){
                        $slug = slugify($row['description']);
                        $checked = '';
                        if(isset($_SESSION['post'][$slug])){
                          $value = $_SESSION['post'][$slug];

                          if(is_array($value)){
                            foreach($value as $val){
                              if($val == $crow['id']){
                                $checked = 'checked';
                              }
                            }
                          }
                          else{
                            if($value == $crow['id']){
                              $checked = 'checked';
                            }
                          }
                        }
                        $input = ($row['max_vote'] > 1) ? '<input type="checkbox" class="flat-red '.$slug.'" name="'.$slug."[]".'" value="'.$crow['id'].'" '.$checked.'>' : '<input type="radio" class="flat-red '.$slug.'" name="'.slugify($row['description']).'" value="'.$crow['id'].'" '.$checked.'>';
                        $image = (!empty($crow['photo'])) ? 'images/'.$crow['photo'] : 'images/profile.jpg';
                        $candidate .= '
                          <li class="candidate-item">
                            <div class="candidate-box">
                              '.$input.'
                              <img src="'.$image.'" class="candidate-img" alt="'.$crow['firstname'].' '.$crow['lastname'].'">
                              <span class="candidate-name">'.$crow['firstname'].' '.$crow['lastname'].'</span>
                            </div>
                          </li>
                        ';
                      }

                      $instruct = ($row['max_vote'] > 1) ? 'You may select up to '.$row['max_vote'].' candidates' : 'Select only one candidate';

                      echo '
                        <div class="position-box">
                          <div class="box box-solid" id="'.$row['id'].'">
                            <div class="box-header with-border">
                              <h3 class="box-title"><b>'.$row['description'].'</b></h3>
                            </div>
                            <div class="box-body">
                              <p>'.$instruct.'
                                <span class="pull-right">
                                  <button type="button" class="btn btn-success btn-sm btn-flat reset" data-desc="'.slugify($row['description']).'"><i class="fa fa-refresh"></i> Reset</button>
                                </span>
                              </p>
                              <div class="candidate-list">
                                <ul>
                                  '.$candidate.'
                                </ul>
                              </div>
                            </div>
                          </div>
                        </div>
                      ';

                      $candidate = '';
                    }  
                  ?>
                  <div class="text-center button-container">
                    <button type="button" class="btn btn-success btn-flat" id="preview"><i class="fa fa-file-text"></i> Preview</button> 
                    <button type="button" class="btn btn-primary btn-flat" id="submit_ballot"><i class="fa fa-check-square-o"></i> Submit</button>
                  </div>
                </form>
                <!-- End Voting Ballot -->
                <?php
              }
            ?>
          </div>
        </div>
      </section>
    </div>
  </div>
  
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/ballot_modal.php'; ?>

  <!-- Verification Modal -->
  <div class="modal fade" id="verifyModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><b>Verify Your Vote</b></h4>
        </div>
        <div class="modal-body">
          <div id="verify_body"></div>
          <div id="otp_section" style="display:none;">
            <h4 class="text-center">Enter OTP Verification Code</h4>
            <p class="text-center">An OTP has been sent to your email address.</p>
            <div class="form-group text-center">
              <input type="text" class="form-control input-lg" id="otp_input" placeholder="Enter OTP" maxlength="6">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          <button type="button" class="btn btn-success btn-flat" id="verify_votes"><i class="fa fa-check"></i> Verify & Submit</button>
          <button type="button" class="btn btn-primary btn-flat" id="submit_otp" style="display:none;"><i class="fa fa-check-circle"></i> Submit OTP</button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Mobile-first responsive styles */
.container {
  padding: 15px;
  max-width: 100%;
}

.position-box {
  margin-bottom: 20px;
}

.candidate-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  justify-content: center;
}

.candidate-item {
  flex: 0 1 300px;
  margin-bottom: 15px;
}

.candidate-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.candidate-box:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.candidate-img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  margin: 10px 0;
}

.candidate-name {
  font-size: 16px;
  font-weight: 500;
  text-align: center;
  margin-top: 8px;
}

.button-container {
  margin-top: 30px;
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.button-container .btn {
  margin: 5px;
}

#otp_input {
  max-width: 200px;
  margin: 0 auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .page-header.title {
    font-size: 24px;
    margin: 15px 0;
  }
  
  .box-header .box-title {
    font-size: 18px;
  }
  
  .candidate-img {
    width: 100px;
    height: 100px;
  }
  
  .candidate-name {
    font-size: 14px;
  }
  
  .button-container .btn {
    width: 100%;
    margin-bottom: 10px;
  }
}

@media (max-width: 480px) {
  .candidate-item {
    flex: 0 1 100%;
  }
  
  .box-body {
    padding: 10px;
  }
  
  .modal-dialog {
    margin: 10px;
  }
}
</style>

<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  $('.content').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });

  $(document).on('click', '.reset', function(e){
    e.preventDefault();
    var desc = $(this).data('desc');
    $('.'+desc).iCheck('uncheck');
  });

  $('#preview').click(function(e){
    e.preventDefault();
    var form = $('#ballotForm').serialize();
    if(form == ''){
      $('.message').html('You must vote for at least one candidate');
      $('#alert').show();
    }
    else{
      $.ajax({
        type: 'POST',
        url: 'preview.php',
        data: form,
        dataType: 'json',
        success: function(response){
          if(response.error){
            var errmsg = '';
            var messages = response.message;
            for (i in messages) {
              errmsg += messages[i]; 
            }
            $('.message').html(errmsg);
            $('#alert').show();
          }
          else{
            $('#preview_modal').modal('show');
            $('#preview_body').html(response.list);
          }
        }
      });
    }
  });

  $('#submit_ballot').click(function(e){
    e.preventDefault();
    var form = $('#ballotForm').serialize();
    if(form == ''){
      $('.message').html('You must vote for at least one candidate');
      $('#alert').show();
    }
    else{
      $.ajax({
        type: 'POST',
        url: 'preview.php',
        data: form,
        dataType: 'json',
        success: function(response){
          if(response.error){
            var errmsg = '';
            var messages = response.message;
            for (i in messages) {
              errmsg += messages[i]; 
            }
            $('.message').html(errmsg);
            $('#alert').show();
          }
          else{
            $('#verifyModal').modal('show');
            $('#verify_body').html(response.list);
          }
        }
      });
    }
  });

  $('#verify_votes').click(function(){
    $.ajax({
      type: 'POST',
      url: 'send_otp.php',
      dataType: 'json',
      success: function(response){
        if(response.success){
          $('#verify_votes').hide();
          $('#otp_section').show();
          $('#submit_otp').show();
        }
        else{
          alert('Error sending OTP. Please try again.');
        }
      }
    });
  });

  $('#submit_otp').click(function(){
    var otp = $('#otp_input').val();
    var form = $('#ballotForm').serialize();
    
    $.ajax({
      type: 'POST',
      url: 'verify_otp.php',
      data: {
        otp: otp,
        ballot: form
      },
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