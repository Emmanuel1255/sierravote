<?php include 'includes/session.php'; ?>
<?php include 'includes/slugify.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>Dashboard</h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <?php
        date_default_timezone_set('UTC');
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
        ?>

        <!-- Improved Stats Boxes -->
        <div class="row">
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua hover-effect">
              <div class="inner">
                <?php
                $sql = "SELECT * FROM positions";
                $query = $conn->query($sql);
                echo "<h3>" . $query->num_rows . "</h3>";
                ?>
                <p>No. of Positions</p>
              </div>
              <div class="icon">
                <i class="fa fa-tasks"></i>
              </div>
              <a href="positions.php" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green hover-effect">
              <div class="inner">
                <?php
                $sql = "SELECT * FROM candidates";
                $query = $conn->query($sql);
                echo "<h3>" . $query->num_rows . "</h3>";
                ?>
                <p>No. of Candidates</p>
              </div>
              <div class="icon">
                <i class="fa fa-black-tie"></i>
              </div>
              <a href="candidates.php" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow hover-effect">
              <div class="inner">
                <h3 id="total_voters">0</h3>
                <p>Total Voters</p>
              </div>
              <div class="icon">
                <i class="fa fa-users"></i>
              </div>
              <a href="voters.php" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red hover-effect">
              <div class="inner">
                <h3 id="voters_voted">0</h3>
                <p>Voters Voted</p>
              </div>
              <div class="icon">
                <i class="fa fa-edit"></i>
              </div>
              <a href="votes.php" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- Vote Tally Section -->
        <div class="row">
          <div class="col-xs-12">
            <div class="box-header with-border" style="padding: 20px; background: white; border-radius: 5px; margin-bottom: 20px;">
              <h3 style="margin: 0;">
                <i class="fa fa-chart-bar"></i> Live Vote Tally
                <span class="pull-right">
                  <a href="print.php" class="btn btn-success btn-sm btn-flat">
                    <i class="fa fa-print"></i> Print Report
                  </a>
                </span>
              </h3>
            </div>
          </div>
        </div>

        <!-- Enhanced Vote Tally Cards -->
        <?php
        $sql = "SELECT * FROM positions ORDER BY priority ASC";
        $query = $conn->query($sql);
        $inc = 2;
        while ($row = $query->fetch_assoc()) {
          $inc = ($inc == 2) ? 1 : $inc + 1;
          if ($inc == 1) echo "<div class='row'>";
          echo "
                        <div class='col-sm-6'>
                            <div class='box box-solid vote-tally-card'>
                                <div class='box-header with-border'>
                                    <h4 class='box-title'><b>" . $row['description'] . "</b></h4>
                                    <div class='pull-right'>
                                        <span class='badge bg-blue'>Max Votes: " . $row['max_vote'] . "</span>
                                    </div>
                                </div>
                                <div class='box-body'>
                                    <div class='candidates-list'>";

          // Get candidates and their votes
          $pos_id = $row['id'];
          $can_sql = "SELECT 
                                c.*, 
                                COUNT(v.id) as vote_count,
                                (SELECT COUNT(*) FROM votes WHERE position_id = '$pos_id') as total_votes
                            FROM candidates c
                            LEFT JOIN votes v ON v.candidate_id = c.id
                            WHERE c.position_id = '$pos_id'
                            GROUP BY c.id
                            ORDER BY vote_count DESC";
          $can_query = $conn->query($can_sql);

          while ($can_row = $can_query->fetch_assoc()) {
            $vote_percentage = $can_row['total_votes'] > 0 ?
              round(($can_row['vote_count'] / $can_row['total_votes']) * 100, 1) : 0;

            $image = (!empty($can_row['photo'])) ? '../images/' . $can_row['photo'] : '../images/profile.jpg';

            echo "
                            <div class='candidate-item'>
                                <div class='row'>
                                    <div class='col-xs-2 text-center'>
                                        <img src='" . $image . "' class='candidate-photo'>
                                    </div>
                                    <div class='col-xs-10'>
                                        <h4 class='candidate-name'>" . $can_row['firstname'] . " " . $can_row['lastname'] . "</h4>
                                        <div class='progress'>
                                            <div class='progress-bar progress-bar-striped active' role='progressbar' 
                                                aria-valuenow='" . $vote_percentage . "' aria-valuemin='0' aria-valuemax='100' 
                                                style='width: " . $vote_percentage . "%'>
                                                " . $vote_percentage . "%
                                            </div>
                                        </div>
                                        <div class='candidate-stats'>
                                            <span class='votes-badge'>" . $can_row['vote_count'] . " votes</span>
                                            <a href='#platform' data-toggle='modal' class='platform-link' data-id='" . $can_row['id'] . "'>
                                                <i class='fa fa-info-circle'></i> View Platform
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>";
          }

          echo "
                                    </div>
                                </div>
                                <div class='box-footer'>
        <small class='text-muted last-updated'>Last updated: ".date('h:i A')."</small>
    </div>
                            </div>
                        </div>";

          if ($inc == 2) echo "</div>";
        }
        if ($inc == 1) echo "<div class='col-sm-6'></div></div>";
        ?>

      </section>
    </div>

    <?php include 'includes/footer.php'; ?>
  </div>

  <?php include 'includes/scripts.php'; ?>

  <style>
    /* Enhanced Styling */
    .hover-effect {
      transition: transform 0.2s ease;
    }

    .hover-effect:hover {
      transform: translateY(-5px);
    }

    .vote-tally-card {
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }

    .vote-tally-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .vote-tally-card .box-header {
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
      border-radius: 8px 8px 0 0;
      padding: 15px;
    }

    .vote-tally-card .box-body {
      padding: 20px;
    }

    .candidate-item {
      margin-bottom: 20px;
      background: #f8f9fa;
      border-radius: 6px;
      padding: 15px;
      transition: all 0.2s ease;
    }

    .candidate-item:hover {
      background: #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .candidate-photo {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .candidate-name {
      margin: 0 0 5px 0;
      color: #2c3e50;
      font-size: 16px;
    }

    .progress {
      height: 25px;
      margin: 10px 0;
      border-radius: 15px;
      background: #e9ecef;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
      background: linear-gradient(45deg, #3498db, #2980b9);
      line-height: 25px;
      font-size: 12px;
      font-weight: bold;
    }

    .votes-badge {
      background: #3498db;
      color: white;
      padding: 5px 10px;
      border-radius: 12px;
      font-size: 12px;
      margin-right: 10px;
    }

    .platform-link {
      color: #3498db;
      text-decoration: none;
      font-size: 13px;
    }

    .platform-link:hover {
      color: #2980b9;
      text-decoration: none;
    }

    .box-footer {
      background: #f8f9fa;
      border-radius: 0 0 8px 8px;
      padding: 10px 20px;
      border-top: 1px solid #eee;
    }

    @media (max-width: 768px) {
      .candidate-item .col-xs-2 {
        width: 25%;
      }

      .candidate-item .col-xs-10 {
        width: 75%;
      }
    }
  </style>

<script>
$(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Update vote counts
    function updateVoteCounts() {
        // Update total voters and voters who voted
        $.ajax({
            url: 'get_total_votes.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#total_voters').html(response.total_voters);
                $('#voters_voted').html(response.voters_voted);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching vote counts:', error);
            }
        });

        // Update candidate votes for each position
        $.ajax({
            url: 'get_votes.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.positions) {
                    response.positions.forEach(function(position) {
                        updatePositionVotes(position);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching position votes:', error);
            }
        });
    }

    // Function to update a single position's candidates
    function updatePositionVotes(position) {
        const positionDiv = $(`#position-${position.id}`);
        const candidatesList = positionDiv.find('.candidates-list');

        position.candidates.forEach(function(candidate) {
            const candidateItem = candidatesList.find(`[data-candidate-id="${candidate.id}"]`);
            if (candidateItem.length) {
                // Update vote count and percentage
                candidateItem.find('.votes-badge').text(`${candidate.vote_count} votes`);
                const progressBar = candidateItem.find('.progress-bar');
                progressBar.css('width', `${candidate.percentage}%`);
                progressBar.text(`${candidate.percentage}%`);
            }
        });

        // Update last updated time
        positionDiv.find('.last-updated').text(`Last updated: ${new Date().toLocaleTimeString()}`);
    }

    // Initial update
    updateVoteCounts();

    // Update every 30 seconds
    setInterval(updateVoteCounts, 30000);

    // Platform modal handler
    $(document).on('click', '.platform-link', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        // Add your platform modal code here
    });

    // Hover effects for progress bars
    $('.candidate-item').hover(
        function() {
            $(this).find('.progress-bar').css('background', 'linear-gradient(45deg, #2ecc71, #27ae60)');
        },
        function() {
            $(this).find('.progress-bar').css('background', 'linear-gradient(45deg, #3498db, #2980b9)');
        }
    );
});
</script>
</body>

</html>