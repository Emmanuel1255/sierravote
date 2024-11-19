<?php
include 'includes/session.php';

if(isset($_SESSION['voter'])){
    $_SESSION['success'] = 'Ballot submitted successfully.';
    unset($_SESSION['post']);
    header('location: home.php');
} else {
    header('location: index.php');
}
?>