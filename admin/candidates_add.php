<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/session.php';

if(isset($_POST['add'])){
    // Create images directory if it doesn't exist
    $target_dir = "../images/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Fix permissions on the images directory
    chmod($target_dir, 0777);
    
    $firstname = $conn->real_escape_string($_POST['firstname']); // Escape special characters
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $position = $conn->real_escape_string($_POST['position']);
    $platform = $conn->real_escape_string($_POST['platform']);
    $filename = '';
    
    if(!empty($_FILES['photo']['name'])){
        $filename = $_FILES['photo']['name'];
        $target_file = $target_dir . $filename;
        
        // Attempt to upload file
        if(move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)){
            // File uploaded successfully
            chmod($target_file, 0644); // Set proper file permissions
        } else {
            $_SESSION['error'] = "Failed to upload file. Please check directory permissions.";
            header('location: candidates.php');
            exit();
        }
    }
    
    // Use prepared statements to handle special characters properly
    $stmt = $conn->prepare("INSERT INTO candidates (position_id, firstname, lastname, photo, platform) VALUES (?, ?, ?, ?, ?)");
    
    if($stmt){
        $stmt->bind_param("issss", $position, $firstname, $lastname, $filename, $platform);
        
        if($stmt->execute()){
            $_SESSION['success'] = 'Candidate added successfully';
        } else {
            $_SESSION['error'] = $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = $conn->error;
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: candidates.php');
?>