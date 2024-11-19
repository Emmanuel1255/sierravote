<?php
include 'includes/session.php';
include 'includes/slugify.php';

$output = array('error'=>false, 'list'=>'');

// Start the preview container
$output['list'] = '
<style>
    .preview-container {
        padding: 15px;
        max-width: 100%;
    }
    
    .votelist {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .position-label {
        flex: 0 0 100%;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .candidate-name {
        flex: 0 0 100%;
        padding-left: 15px;
        color: #34495e;
    }
    
    @media (min-width: 768px) {
        .position-label {
            flex: 0 0 30%;
            text-align: right;
            margin-bottom: 0;
            padding-right: 15px;
        }
        
        .candidate-name {
            flex: 0 0 70%;
        }
        
        .votelist {
            padding: 15px;
        }
    }
    
    @media (max-width: 480px) {
        .preview-container {
            padding: 10px;
        }
        
        .votelist {
            padding: 8px;
            margin-bottom: 8px;
        }
    }
</style>
<div class="preview-container">';

$sql = "SELECT * FROM positions";
$query = $conn->query($sql);

while($row = $query->fetch_assoc()){
    $position = slugify($row['description']);
    $pos_id = $row['id'];
    
    if(isset($_POST[$position])){
        if($row['max_vote'] > 1){
            if(count($_POST[$position]) > $row['max_vote']){
                $output['error'] = true;
                $output['message'][] = '<li>You can only choose '.$row['max_vote'].' candidates for '.$row['description'].'</li>';
            }
            else{
                foreach($_POST[$position] as $key => $values){
                    $sql = "SELECT * FROM candidates WHERE id = '$values'";
                    $cmquery = $conn->query($sql);
                    $cmrow = $cmquery->fetch_assoc();
                    $output['list'] .= "
                        <div class='votelist'>
                            <div class='position-label'>".$row['description'].":</div>
                            <div class='candidate-name'>".$cmrow['firstname']." ".$cmrow['lastname']."</div>
                        </div>
                    ";
                }
            }
        }
        else{
            $candidate = $_POST[$position];
            $sql = "SELECT * FROM candidates WHERE id = '$candidate'";
            $csquery = $conn->query($sql);
            $csrow = $csquery->fetch_assoc();
            $output['list'] .= "
                <div class='votelist'>
                    <div class='position-label'>".$row['description'].":</div>
                    <div class='candidate-name'>".$csrow['firstname']." ".$csrow['lastname']."</div>
                </div>
            ";
        }
    }
}

// Close the preview container
$output['list'] .= '</div>';

echo json_encode($output);
?>