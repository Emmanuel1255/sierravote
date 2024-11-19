<?php
include 'includes/session.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendCredentialEmail($email, $firstname, $lastname, $voter_id, $password) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'emmanuel@mannie-sl.com';
        $mail->Password = 'Emmanuel12555.';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPDebug = 0;

        $mail->setFrom('emmanuel@mannie-sl.com', 'Voting System');
        $mail->addAddress($email, "$firstname $lastname");
        $mail->isHTML(true);
        $mail->Subject = 'Your Voting System Credentials';

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
                            <p><a href='https://sierravote.mannie-sl.com/'>Click here to login</a></p>
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

        // Plain text alternative
        $mail->AltBody = "Your voting credentials:\nVoter ID: {$voter_id}\nPassword: {$password}\n\nLogin at: https://sierravote.mannie-sl.com/";


        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$response = array('status' => false, 'message' => '');

try {
    if(isset($_POST['id'])) {
        $voter_id = $_POST['id'];
        $stmt = $conn->prepare("SELECT * FROM voters WHERE id = ?");
        $stmt->bind_param("i", $voter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $voter = $result->fetch_assoc();
            if(sendCredentialEmail($voter['email'], $voter['firstname'], $voter['lastname'], $voter['voters_id'], $voter['password'])) {
                $response['status'] = true;
                $response['message'] = 'Email sent successfully';
            } else {
                $response['message'] = 'Failed to send email';
            }
        } else {
            $response['message'] = 'Voter not found';
        }
        $stmt->close();
    } 
    elseif(isset($_POST['send_all'])) {
        $result = $conn->query("SELECT * FROM voters");
        $success = 0;
        $failed = 0;
        
        while($voter = $result->fetch_assoc()) {
            if(sendCredentialEmail($voter['email'], $voter['firstname'], $voter['lastname'], $voter['voters_id'], $voter['password'])) {
                $success++;
            } else {
                $failed++;
            }
            usleep(500000);
        }
        
        $response['status'] = ($success > 0);
        $response['message'] = "Sent: $success, Failed: $failed";
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred';
}

header('Content-Type: application/json');
echo json_encode($response);
?>