<?php
include 'includes/session.php';
include 'includes/slugify.php';

if(isset($_POST['otp'])){
    $submitted_otp = $_POST['otp'];
    
    // Debug line - you can remove this in production
    error_log("Submitted OTP: " . $submitted_otp . ", Stored OTP: " . $_SESSION['otp']);
    
    if(!isset($_SESSION['otp']) || !isset($_SESSION['otp_time'])){
        echo json_encode(array(
            'success' => false,
            'message' => 'OTP session expired. Please request a new OTP.'
        ));
        exit();
    }
    
    // Check if OTP is expired (10 minutes)
    if(time() - $_SESSION['otp_time'] > 600){
        unset($_SESSION['otp']);
        unset($_SESSION['otp_time']);
        echo json_encode(array(
            'success' => false,
            'message' => 'OTP has expired. Please request a new OTP.'
        ));
        exit();
    }
    
    // Direct comparison instead of password_verify since we're storing the actual OTP
    if($submitted_otp === $_SESSION['otp']){
        if(isset($_POST['ballot'])){
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Parse the ballot data
                parse_str($_POST['ballot'], $ballot_data);
                
                // Insert votes into database
                $voter_id = $_SESSION['voter'];
                $sql = "SELECT * FROM positions ORDER BY priority ASC";
                $query = $conn->query($sql);
                
                while($row = $query->fetch_assoc()){
                    $position_id = $row['id'];
                    $position = $row['description'];
                    $slugged_position = slugify($position);
                    
                    if(isset($ballot_data[$slugged_position])){
                        $candidate = $ballot_data[$slugged_position];
                        if(is_array($candidate)){
                            foreach($candidate as $value){
                                $sql = "INSERT INTO votes (voters_id, candidate_id, position_id) VALUES (?, ?, ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('iii', $voter_id, $value, $position_id);
                                $stmt->execute();
                                $stmt->close();
                            }
                        } else {
                            $sql = "INSERT INTO votes (voters_id, candidate_id, position_id) VALUES (?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('iii', $voter_id, $candidate, $position_id);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }

                // Commit transaction
                $conn->commit();
                
                // Clear OTP session
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Vote submitted successfully'
                ));
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Error submitting vote: ' . $e->getMessage()
                ));
            }
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'No ballot data received'
            ));
        }
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Invalid OTP'
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'No OTP submitted'
    ));
}
?>