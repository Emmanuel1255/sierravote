<!-- Preview Modal -->
<div class="modal fade" id="preview_modal">
    <div class="modal-dialog modal-responsive">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Vote Preview</h4>
            </div>
            <div class="modal-body">
                <div id="preview_body" class="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Platform Modal -->
<div class="modal fade" id="platform">
    <div class="modal-dialog modal-responsive">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b><span class="candidate"></span></b></h4>
            </div>
            <div class="modal-body">
                <p id="plat_view" class="platform-content"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Ballot Modal -->
<div class="modal fade" id="view">
    <div class="modal-dialog modal-responsive">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Your Votes</h4>
            </div>
            <div class="modal-body">
                <div class="ballot-list">
                    <?php
                    $id = $voter['id'];
                    $sql = "SELECT *, candidates.firstname AS canfirst, candidates.lastname AS canlast 
                           FROM votes 
                           LEFT JOIN candidates ON candidates.id=votes.candidate_id 
                           LEFT JOIN positions ON positions.id=votes.position_id 
                           WHERE voters_id = '$id' 
                           ORDER BY positions.priority ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                        echo "
                        <div class='vote-item'>
                            <div class='position-label'><b>".$row['description'].":</b></div>
                            <div class='candidate-name'>".$row['canfirst']." ".$row['canlast']."</div>
                        </div>
                        ";
                    }
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Responsive Styles */
.modal-responsive {
    width: 95%;
    max-width: 600px;
    margin: 20px auto;
}

.modal-content {
    border-radius: 8px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e5e5e5;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e5e5e5;
}

/* Preview Content Styles */
.preview-content,
.platform-content {
    font-size: 16px;
    line-height: 1.5;
}

/* Ballot List Styles */
.ballot-list {
    margin: -10px;
}

.vote-item {
    display: flex;
    flex-wrap: wrap;
    padding: 12px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    border-radius: 6px;
}

.position-label {
    flex: 0 0 100%;
    margin-bottom: 5px;
    color: #2c3e50;
}

.candidate-name {
    flex: 0 0 100%;
    padding-left: 15px;
    color: #34495e;
}

/* Button Styles */
.btn {
    padding: 8px 20px;
    border-radius: 4px;
}

/* Media Queries */
@media (min-width: 768px) {
    .position-label {
        flex: 0 0 30%;
        margin-bottom: 0;
        padding-right: 15px;
        text-align: right;
    }

    .candidate-name {
        flex: 0 0 70%;
    }
}

@media (max-width: 480px) {
    .modal-responsive {
        margin: 10px;
    }

    .modal-title {
        font-size: 18px;
    }

    .preview-content,
    .platform-content {
        font-size: 14px;
    }

    .vote-item {
        padding: 10px;
        margin-bottom: 8px;
    }

    .btn {
        width: 100%;
        margin-bottom: 5px;
    }

    .modal-footer {
        text-align: center;
    }
}
</style>