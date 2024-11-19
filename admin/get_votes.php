<?php
// get_total_votes.php
include 'includes/session.php';

header('Content-Type: application/json');

try {
    $total_voters_sql = "SELECT COUNT(*) as total FROM voters";
    $voters_voted_sql = "SELECT COUNT(DISTINCT voters_id) as voted FROM votes";
    
    $total_voters_result = $conn->query($total_voters_sql);
    $voters_voted_result = $conn->query($voters_voted_sql);
    
    $total_voters = $total_voters_result->fetch_assoc()['total'];
    $voters_voted = $voters_voted_result->fetch_assoc()['voted'];
    
    echo json_encode([
        'error' => false,
        'total_voters' => $total_voters,
        'voters_voted' => $voters_voted,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>