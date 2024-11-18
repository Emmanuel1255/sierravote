<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Voters List
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Voters</li>
        </ol>
      </section>
      <!-- Main content -->
      <section class="content">
        <?php
        if (isset($_SESSION['error'])) {
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              " . $_SESSION['error'] . "
            </div>
          ";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              " . $_SESSION['success'] . "
            </div>
          ";
          unset($_SESSION['success']);
        }

        // Display bulk upload actions if voters were just uploaded
        if(isset($_SESSION['created_voters'])) {
          echo '
          <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-info"></i> Bulk Upload Complete!</h4>
            <div class="btn-group">
              <form method="POST" action="voters_add.php" style="display: inline;">
                <button type="submit" class="btn btn-primary" name="send_bulk_email">
                  <i class="fa fa-envelope"></i> Send Credentials via Email
                </button>
              </form>
              <a href="download_csv.php" class="btn btn-success">
                <i class="fa fa-download"></i> Download Credentials CSV
              </a>
            </div>
          </div>';
        }
        ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
                <a href="#uploadCSV" data-toggle="modal" class="btn btn-success btn-sm btn-flat"><i class="fa fa-upload"></i> Bulk Upload</a>
                <?php if($query->num_rows > 0): ?>
                <button class="btn btn-info btn-sm btn-flat" id="sendAllEmails">
                  <i class="fa fa-envelope"></i> Send All Credentials
                </button>
                <?php endif; ?>
              </div>
              <div class="box-body">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Lastname</th>
                    <th>Firstname</th>
                    <th>Email</th>
                    <th>Photo</th>
                    <th>Voters ID</th>
                    <th>Tools</th>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT * FROM voters";
                    $query = $conn->query($sql);
                    while ($row = $query->fetch_assoc()) {
                      $image = (!empty($row['photo'])) ? '../images/' . $row['photo'] : '../images/profile.jpg';
                      echo "
                        <tr>
                          <td>" . $row['lastname'] . "</td>
                          <td>" . $row['firstname'] . "</td>
                          <td>" . $row['email'] . "</td>
                          <td>
                            <img src='" . $image . "' width='30px' height='30px'>
                            <a href='#edit_photo' data-toggle='modal' class='pull-right photo' data-id='" . $row['id'] . "'><span class='fa fa-edit'></span></a>
                          </td>
                          <td>" . $row['voters_id'] . "</td>
                          <td>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='" . $row['id'] . "'><i class='fa fa-edit'></i> Edit</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='" . $row['id'] . "'><i class='fa fa-trash'></i> Delete</button>
                            <button class='btn btn-info btn-sm send-credentials btn-flat' data-id='" . $row['id'] . "'><i class='fa fa-envelope'></i> Send</button>
                          </td>
                        </tr>
                      ";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/voters_modal.php'; ?>
  </div>
  <?php include 'includes/scripts.php'; ?>
  <script>
    $(function() {
      $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        getRow(id);
      });

      $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        getRow(id);
      });

      $(document).on('click', '.photo', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        getRow(id);
      });

      // Send credentials to individual voter
      $(document).on('click', '.send-credentials', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var button = $(this);
        
        // Disable button and show loading state
        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            type: 'POST',
            url: 'voters_send_credentials.php',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Credentials sent successfully');
                } else {
                    alert('Error sending credentials: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error in sending credentials: ' + error);
                console.error(xhr.responseText);
            },
            complete: function() {
                // Re-enable button and restore original text
                button.prop('disabled', false).html('<i class="fa fa-envelope"></i> Send');
            }
        });
    });

      // Send credentials to all voters
      $('#sendAllEmails').click(function(e) {
        e.preventDefault();
        var button = $(this);
        
        if(confirm('Are you sure you want to send credentials to all voters?')) {
            // Disable button and show loading state
            button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            
            $.ajax({
                type: 'POST',
                url: 'voters_send_credentials.php',
                data: {send_all: true},
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Credentials sent to all voters\nSuccess: ' + response.success_count + '\nFailed: ' + response.error_count);
                    } else {
                        alert('Error sending credentials: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error in sending credentials: ' + error);
                    console.error(xhr.responseText);
                },
                complete: function() {
                    // Re-enable button and restore original text
                    button.prop('disabled', false).html('<i class="fa fa-envelope"></i> Send All Credentials');
                }
            });
        }
    });

      // CSV file validation
      $('#csv_file').change(function() {
        var file = this.files[0];
        var fileType = file.type;
        var match = ['text/csv', 'application/vnd.ms-excel'];
        if(!match.includes(fileType)) {
          alert('Please upload a valid CSV file');
          $(this).val('');
          return false;
        }
      });
    });

    function getRow(id) {
      $.ajax({
        type: 'POST',
        url: 'voters_row.php',
        data: {
          id: id
        },
        dataType: 'json',
        success: function(response) {
          $('.id').val(response.id);
          $('#edit_firstname').val(response.firstname);
          $('#edit_lastname').val(response.lastname);
          $('#edit_email').val(response.email);
          $('.fullname').html(response.firstname + ' ' + response.lastname);
        }
      });
    }
  </script>
</body>
</html>