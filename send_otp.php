<?php
include 'includes/session.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'admin/phpmailer/src/Exception.php';
require 'admin/phpmailer/src/PHPMailer.php';
require 'admin/phpmailer/src/SMTP.php';

function sendOTPEmail($email, $firstname, $otp) {
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
        $mail->addAddress($email, $firstname);
        $mail->isHTML(true);
        $mail->Subject = 'Voting System - OTP Verification';

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
                    .otp-box { 
                        background: #8c0a0a; 
                        color: white; 
                        padding: 15px 25px; 
                        font-size: 24px; 
                        font-weight: bold;
                        border-radius: 5px;
                        display: inline-block;
                        margin: 10px 0;
                    }
                    .warning {
                        color: #dc3545;
                        font-weight: bold;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Vote Verification Code</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$firstname},</p>
                        <p>Please use the following OTP (One-Time Password) to verify your vote:</p>
                        
                        <div class='credentials'>
                            <p style='text-align: center;'>Your OTP Code:</p>
                            <div style='text-align: center;'>
                                <div class='otp-box'>{$otp}</div>
                            </div>
                            <p style='text-align: center;'>This code will expire in 10 minutes.</p>
                        </div>
                        
                        <p><strong>Security Notes:</strong></p>
                        <ul>
                            <li>This OTP is valid for one use only</li>
                            <li>Never share this OTP with anyone</li>
                            <li>Our staff will never ask for your OTP</li>
                        </ul>
                        
                        <p class='warning'>If you didn't request this OTP, please ignore this email or contact support immediately.</p>
                    </div>
                    <div class='footer'>
                        <p>Best regards,<br>Voting System Team</p>
                        <small style='color: #666;'>This is an automated message, please do not reply.</small>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Plain text alternative
        $mail->AltBody = "
        Dear {$firstname},

        Your OTP (One-Time Password) for vote verification is: {$otp}

        This code will expire in 10 minutes.

        IMPORTANT:
        - This OTP is valid for one use only
        - Never share this OTP with anyone
        - Our staff will never ask for your OTP

        If you didn't request this OTP, please ignore this email or contact support immediately.

        Best regards,
        Voting System Team
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}

if(isset($_SESSION['voter'])){
    $voter_id = $_SESSION['voter'];
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    
    // Store actual OTP in session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();
    
    // Get voter's email
    $sql = "SELECT * FROM voters WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $voter = $result->fetch_assoc();
    $stmt->close();

    // Store OTP in session message for testing
    $_SESSION['success_message'] = "For testing, your OTP is: " . $otp;
    
    if(sendOTPEmail($voter['email'], $voter['firstname'], $otp)){
        echo json_encode(array(
            'success' => true,
            'message' => 'OTP has been sent to your email'
        ));
    } else {
        // For development/testing
        echo json_encode(array(
            'success' => true,
            'message' => 'OTP has been generated (check session message for OTP)'
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Voter not found'
    ));
}
?>