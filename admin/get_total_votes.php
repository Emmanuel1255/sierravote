<?php
include 'includes/session.php';

header('Content-Type: application/json');

try {
    $response = array();

    // Get total number of voters
    $voters_sql = "SELECT COUNT(*) as total FROM voters";
    $voters_result = $conn->query($voters_sql);
    
    if (!$voters_result) {
        throw new Exception("Error counting voters: " . $conn->error);
    }
    
    $voters_row = $voters_result->fetch_assoc();
    $response['total_voters'] = intval($voters_row['total']);

    // Get number of voters who have voted
    $voted_sql = "SELECT COUNT(DISTINCT voters_id) as total FROM votes";
    $voted_result = $conn->query($voted_sql);
    
    if (!$voted_result) {
        throw new Exception("Error counting votes: " . $conn->error);
    }
    
    $voted_row = $voted_result->fetch_assoc();
    $response['voters_voted'] = intval($voted_row['total']);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => $e->getMessage()
    ));
}
?>