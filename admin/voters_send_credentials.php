<?php
include 'includes/session.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

try {
    if(isset($_POST['id'])) {
        // Single voter email
        $voter_id = $_POST['id'];
        $sql = "SELECT * FROM voters WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $voter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 0) {
            throw new Exception('Voter not found');
        }

        $voter = $result->fetch_assoc();
        
        if(sendCredentialEmail($voter['email'], $voter['firstname'], $voter['lastname'], $voter['voters_id'], $voter['password'])) {
            $response['success'] = true;
            $response['message'] = 'Credentials sent successfully';
        } else {
            throw new Exception('Failed to send email');
        }
    } 
    elseif(isset($_POST['send_all'])) {
        // Send to all voters
        $sql = "SELECT * FROM voters";
        $result = $conn->query($sql);
        
        $success_count = 0;
        $error_count = 0;
        
        while($voter = $result->fetch_assoc()) {
            if(sendCredentialEmail($voter['email'], $voter['firstname'], $voter['lastname'], $voter['voters_id'], $voter['password'])) {
                $success_count++;
            } else {
                $error_count++;
            }
            // Add small delay between emails
            usleep(500000); // 0.5 second delay
        }
        
        $response['success'] = true;
        $response['success_count'] = $success_count;
        $response['error_count'] = $error_count;
        $response['message'] = "Sent successfully: $success_count, Failed: $error_count";
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Error sending credentials: " . $e->getMessage());
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);

function sendCredentialEmail($email, $firstname, $lastname, $voter_id, $password) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'emmanuel@mannie-sl.com';
        $mail->Password = 'Emmanuel12555.';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPDebug = 0;

        // Recipients
        $mail->setFrom('emmanuel@mannie-sl.com', 'Voting System');
        $mail->addAddress($email, $firstname . ' ' . $lastname);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Your Voting System Credentials';
        
        // Email template
        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { padding: 20px; max-width: 600px; margin: 0 auto; }
                    .header { background: #f8f9fa; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .credentials { background: #f1f1f1; padding: 15px; margin: 20px 0; border-radius: 5px; }
                    .footer { text-align: center; padding-top: 20px; border-top: 1px solid #eee; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Your Voting System Credentials</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$firstname} {$lastname},</p>
                        <p>Here are your voting credentials:</p>
                        
                        <div class='credentials'>
                            <p><strong>Voter ID:</strong> {$voter_id}</p>
                            <p><strong>Password:</strong> {$password}</p>
                            <p><a href='http://localhost/votesystem/login.php'>Click here to login</a></p>
                        </div>
                        
                        <p><strong>Important Security Notes:</strong></p>
                        <ul>
                            <li>Keep these credentials safe and confidential</li>
                            <li>Do not share them with anyone</li>
                            <li>Change your password after first login</li>
                        </ul>
                        
                        <p>If you didn't request these credentials, please contact the administrator immediately.</p>
                    </div>
                    <div class='footer'>
                        <p>Best regards,<br>Voting System Team</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}
?>