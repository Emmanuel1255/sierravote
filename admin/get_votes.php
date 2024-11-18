<?php
include 'includes/session.php';
include 'includes/slugify.php';

header('Content-Type: application/json');

try {
    $response = array();

    // Get positions
    $positions_sql = "SELECT * FROM positions ORDER BY priority ASC";
    $positions_query = $conn->query($positions_sql);

    if (!$positions_query) {
        throw new Exception("Error fetching positions: " . $conn->error);
    }

    while ($position = $positions_query->fetch_assoc()) {
        $position_data = array(
            'id' => $position['id'],
            'description' => slugify($position['description']),
            'candidates' => array(),
            'votes' => array()
        );

        // Get candidates and their votes for this position
        $candidates_sql = "
            SELECT 
                c.id,
                c.lastname,
                COUNT(v.id) as vote_count
            FROM 
                candidates c
                LEFT JOIN votes v ON c.id = v.candidate_id
            WHERE 
                c.position_id = ?
            GROUP BY 
                c.id, c.lastname
            ORDER BY 
                c.lastname ASC";

        $stmt = $conn->prepare($candidates_sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param('i', $position['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($candidate = $result->fetch_assoc()) {
            $position_data['candidates'][] = $candidate['lastname'];
            $position_data['votes'][] = intval($candidate['vote_count']);
        }

        $stmt->close();
        $response[] = $position_data;
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => $e->getMessage()
    ));
}
?>