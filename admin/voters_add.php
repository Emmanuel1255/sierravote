<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/session.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function generatePassword($length = 8)
{
    // Include special characters for stronger passwords
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function sendCredentialEmail($email, $firstname, $lastname, $voter_id, $password)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'emmanuel@mannie-sl.com';
        $mail->Password = 'Emmanuel12555.';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Changed to SSL since port is 465
        $mail->Port = 465;
        $mail->SMTPDebug = 0; // Disable debug output

        // Set timeout
        $mail->Timeout = 60; // 60 seconds timeout
        $mail->SMTPKeepAlive = true; // Keep connection alive for bulk emails

        // Recipients
        $mail->setFrom('emmanuel@mannie-sl.com', 'Voting System');
        $mail->addAddress($email, $firstname . ' ' . $lastname);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
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
                        <h2>Welcome to the Voting System</h2>
                    </div>
                    <div class='content'>
                        <p>Dear $firstname $lastname,</p>
                        <p>Your voting credentials have been created. Please find your login details below:</p>
                        
                        <div class='credentials'>
                            <p><strong>Voter ID:</strong> $voter_id</p>
                            <p><strong>Password:</strong> $password</p>
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

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "
            Welcome to the Voting System
            
            Dear $firstname $lastname,
            
            Your voting credentials have been created. Please find your login details below:
            
            Voter ID: $voter_id
            Password: $password
            Click here to login: https://sierravote.mannie-sl.com/
            
            Please keep these credentials safe and do not share them with anyone.
            
            Best regards,
            Voting System Team
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage()); // Log the error
        return false;
    }
}

if (isset($_POST['add'])) {
    try {
        $firstname = $conn->real_escape_string(trim($_POST['firstname']));
        $lastname = $conn->real_escape_string(trim($_POST['lastname']));
        $email = $conn->real_escape_string(trim($_POST['email']));

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address format');
        }

        $password = generatePassword(10); // Increased to 10 characters

        // Handle photo upload
        $filename = '';
        if (!empty($_FILES['photo']['name'])) {
            $allowed = array('jpg', 'jpeg', 'png');
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid photo format. Only JPG, JPEG, and PNG are allowed.');
            }

            // Generate unique filename
            $filename = time() . '_' . $filename;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
        }

        // Generate voter ID
        $set = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $voter = substr(str_shuffle($set), 0, 5);

        // Check if voter ID already exists
        do {
            $check = $conn->query("SELECT * FROM voters WHERE voters_id = '$voter'");
            if ($check->num_rows > 0) {
                $voter = substr(str_shuffle($set), 0, 5);
            }
        } while ($check->num_rows > 0);

        $sql = "INSERT INTO voters (voters_id, password, firstname, lastname, email, photo) 
                VALUES ('$voter', '$password', '$firstname', '$lastname', '$email', '$filename')";

        if ($conn->query($sql)) {
            if (isset($_POST['send_email'])) {
                if (sendCredentialEmail($email, $firstname, $lastname, $voter, $password)) {
                    $_SESSION['success'] = 'Voter added successfully and credentials sent via email.';
                } else {
                    $_SESSION['success'] = 'Voter added successfully but failed to send email. Voter ID: ' . $voter . ' Password: ' . $password;
                }
            } else {
                $_SESSION['success'] = 'Voter added successfully. Voter ID: ' . $voter . ' Password: ' . $password;
            }
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} elseif (isset($_POST['upload'])) {
    try {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please upload a valid CSV file');
        }

        $file = $_FILES['csv_file']['tmp_name'];

        if (!is_uploaded_file($file)) {
            throw new Exception('Failed to upload file');
        }

        // Verify file is CSV
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file);
        finfo_close($fileInfo);

        if (!in_array($mimeType, ['text/csv', 'text/plain', 'application/vnd.ms-excel'])) {
            throw new Exception('File must be a CSV');
        }

        $handle = fopen($file, "r");
        if ($handle === false) {
            throw new Exception('Failed to open file');
        }

        $success_count = 0;
        $error_count = 0;
        $created_voters = array();
        $row_count = 0;

        // Skip header row
        fgetcsv($handle);

        while(($data = fgetcsv($handle)) !== FALSE) {
            $row_count++;
            
            // Validate row data
            if(count($data) < 3) {
                throw new Exception("Invalid data format in row $row_count");
            }
        
            $firstname = trim($data[0]);
            $lastname = trim($data[1]);
            $email = trim($data[2]);
        
            if(empty($firstname) || empty($lastname) || empty($email)) {
                $error_count++;
                continue;
            }
        
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_count++;
                continue;
            }
        
            $password = generatePassword(10);
            $voter = substr(str_shuffle('123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
            
            // Check for unique voter ID
            do {
                $check = $conn->query("SELECT * FROM voters WHERE voters_id = '$voter'");
                if($check->num_rows > 0) {
                    $voter = substr(str_shuffle('123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
                }
            } while($check->num_rows > 0);
            
            // Use prepared statement
            $stmt = $conn->prepare("INSERT INTO voters (voters_id, password, firstname, lastname, email, photo) VALUES (?, ?, ?, ?, ?, ?)");
            $default_photo = 'user-avatar.png';
            
            $stmt->bind_param("ssssss", $voter, $password, $firstname, $lastname, $email, $default_photo);
            
            if($stmt->execute()){
                $success_count++;
                $created_voters[] = array(
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'voter_id' => $voter,
                    'password' => $password
                );
            } else {
                $error_count++;
            }
        
            $stmt->close();
        
            // Commit every 100 records
            if($success_count % 100 == 0) {
                $conn->commit();
            }
        }

        fclose($handle);

        if ($success_count == 0) {
            throw new Exception('No valid records were found in the CSV file');
        }

        // Create CSV with credentials
        $output = fopen('php://temp', 'w+');
        fputcsv($output, array('Firstname', 'Lastname', 'Email', 'Voter ID', 'Password'));
        foreach ($created_voters as $voter) {
            fputcsv($output, $voter);
        }

        rewind($output);
        $csv_data = stream_get_contents($output);
        fclose($output);

        $_SESSION['csv_data'] = $csv_data;
        $_SESSION['created_voters'] = $created_voters;
        $_SESSION['success'] = "Upload complete. Successfully added: $success_count voters. Failed: $error_count";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} elseif (isset($_POST['send_bulk_email'])) {
    try {
        $success_count = 0;
        $error_count = 0;
        $error_messages = array();

        if (!isset($_SESSION['created_voters'])) {
            throw new Exception('No voter data available for sending emails');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'emmanuel@mannie-sl.com';
        $mail->Password = 'Emmanuel12555.';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPKeepAlive = true; // Important for bulk email

        foreach ($_SESSION['created_voters'] as $voter) {
            try {
                if (sendCredentialEmail($voter['email'], $voter['firstname'], $voter['lastname'], $voter['voter_id'], $voter['password'])) {
                    $success_count++;
                } else {
                    $error_count++;
                    $error_messages[] = "Failed to send to: " . $voter['email'];
                }

                // Add delay to prevent overwhelming the mail server
                usleep(500000); // 0.5 second delay

            } catch (Exception $e) {
                $error_count++;
                $error_messages[] = $e->getMessage();
            }
        }

        if ($success_count > 0) {
            $_SESSION['success'] = "Email sending complete. Successful: $success_count, Failed: $error_count";
        } else {
            throw new Exception("Failed to send any emails. Please check your email configuration.");
        }

        // Log errors if any
        if (!empty($error_messages)) {
            error_log("Bulk email errors: " . implode("\n", $error_messages));
        }

        unset($_SESSION['created_voters']); // Clear the stored data

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        error_log("Bulk email error: " . $e->getMessage());
    }
}

header('location: voters.php');
