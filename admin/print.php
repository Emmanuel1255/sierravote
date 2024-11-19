<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/session.php';
date_default_timezone_set('UTC');

if (!file_exists('../tcpdf/tcpdf.php')) {
    die("Error: TCPDF library not found. Please make sure it's installed in the correct directory.");
}

require_once('../tcpdf/tcpdf.php');

class VotePDF extends TCPDF {
    protected $headerTitle = '';
    
    public function setHeaderTitle($title) {
        $this->headerTitle = $title;
    }

    public function Header() {
        // Add a modern gradient background
        $this->Rect(0, 0, $this->getPageWidth(), 45, 'F', array(), array(
            array(41, 128, 185),  // Light blue
            array(44, 62, 80)     // Dark blue
        ));
        
        // Title with enhanced styling
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 24);
        $this->SetY(15);
        $this->Cell(0, 15, $this->headerTitle, 0, false, 'C', 0);
        
        // Add a subtitle with date
        $this->SetFont('helvetica', 'I', 12);
        $this->SetY(25);
        $this->Cell(0, 15, 'Election Results Report - ' . date('F d, Y'), 0, false, 'C', 0);
        
        // Reset text color and add spacing
        $this->SetTextColor(0, 0, 0);
        $this->Ln(40);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        
        // Enhanced footer with multiple elements
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L');
        $this->Cell(0, 10, 'Generated on: ' . date('F d, Y h:i A'), 0, false, 'R');
    }
}

function generateStatisticsSection($conn) {
    // Get voting statistics
    $total_voters_sql = "SELECT COUNT(*) as total FROM voters";
    $voters_voted_sql = "SELECT COUNT(DISTINCT voters_id) as voted FROM votes";
    
    $total_voters_result = $conn->query($total_voters_sql);
    $voters_voted_result = $conn->query($voters_voted_sql);
    
    $total_voters = $total_voters_result->fetch_assoc()['total'];
    $voters_voted = $voters_voted_result->fetch_assoc()['voted'];
    $turnout = $total_voters > 0 ? round(($voters_voted / $total_voters) * 100, 1) : 0;
    $remaining = $total_voters - $voters_voted;

    // Create statistics cards with modern styling
    return '
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
            Voting Statistics Overview
        </h2>
        <table cellspacing="10" style="width: 100%;">
            <tr>
                <td style="width: 25%; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #3498db;">
                    <div style="color: #7f8c8d; font-size: 12px;">Total Registered Voters</div>
                    <div style="color: #2c3e50; font-size: 24px; font-weight: bold; margin: 5px 0;">'.$total_voters.'</div>
                    <div style="color: #95a5a6; font-size: 11px;">Eligible to vote</div>
                </td>
                <td style="width: 25%; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #2ecc71;">
                    <div style="color: #7f8c8d; font-size: 12px;">Votes Cast</div>
                    <div style="color: #2c3e50; font-size: 24px; font-weight: bold; margin: 5px 0;">'.$voters_voted.'</div>
                    <div style="color: #95a5a6; font-size: 11px;">Voters participated</div>
                </td>
                <td style="width: 25%; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #f1c40f;">
                    <div style="color: #7f8c8d; font-size: 12px;">Voter Turnout</div>
                    <div style="color: #2c3e50; font-size: 24px; font-weight: bold; margin: 5px 0;">'.$turnout.'%</div>
                    <div style="color: #95a5a6; font-size: 11px;">Participation rate</div>
                </td>
                <td style="width: 25%; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #e74c3c;">
                    <div style="color: #7f8c8d; font-size: 12px;">Remaining Voters</div>
                    <div style="color: #2c3e50; font-size: 24px; font-weight: bold; margin: 5px 0;">'.$remaining.'</div>
                    <div style="color: #95a5a6; font-size: 11px;">Yet to vote</div>
                </td>
            </tr>
        </table>
    </div>';
}

function generateRow($conn) {
    $contents = '';
    
    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    $query = $conn->query($sql);
    
    while($row = $query->fetch_assoc()) {
        $position_id = $row['id'];
        
        // Enhanced position header with modern styling
        $contents .= '
            <div style="margin-bottom: 30px;">
                <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="4" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 20px; border-radius: 8px 8px 0 0;">
						 
                            
                            <div style="margin-top: 8px;">
                               <h2 class="section-title2" style="margin-buttom: 8px;">Position: '.$row['description'].'</h2>
                            </div>
                        </td>
                    </tr>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 15px; color: #2c3e50; font-size: 14px; border-bottom: 2px solid #3498db; width: 15%;">Rank</th>
                        <th style="padding: 15px; color: #2c3e50; font-size: 14px; border-bottom: 2px solid #3498db; width: 35%;">Candidate</th>
                        <th style="padding: 15px; color: #2c3e50; font-size: 14px; border-bottom: 2px solid #3498db; width: 25%;">Votes</th>
                        <th style="padding: 15px; color: #2c3e50; font-size: 14px; border-bottom: 2px solid #3498db; width: 25%;">Share</th>
                    </tr>';

        // Get total votes
        $total_votes_sql = "SELECT COUNT(*) as total FROM votes WHERE position_id = '$position_id'";
        $total_votes_query = $conn->query($total_votes_sql);
        $total_votes_row = $total_votes_query->fetch_assoc();
        $total_votes = max(1, $total_votes_row['total']);

        // Get candidates with optimized query
        $candidates_sql = "SELECT 
                            c.*, 
                            COUNT(v.id) as vote_count
                          FROM candidates c
                          LEFT JOIN votes v ON c.id = v.candidate_id
                          WHERE c.position_id = '$position_id'
                          GROUP BY c.id
                          ORDER BY vote_count DESC, c.lastname ASC";
        
        $candidates_query = $conn->query($candidates_sql);
        $rank = 1;
        
        while($candidate = $candidates_query->fetch_assoc()) {
            $vote_percentage = round(($candidate['vote_count'] / $total_votes) * 100, 1);
            
            // Enhanced rank styling
            $rankStyles = array(
                1 => array('color' => '#27ae60', 'icon' => '★', 'label' => 'Leading', 'bg' => '#eafaf1'),
                2 => array('color' => '#2980b9', 'icon' => '⭐', 'label' => 'Runner-up', 'bg' => '#ebf5fb'),
                3 => array('color' => '#f39c12', 'icon' => '○', 'label' => 'Third', 'bg' => '#fef9e7')
            );
            
            $rankStyle = isset($rankStyles[$rank]) ? $rankStyles[$rank] : 
                        array('color' => '#95a5a6', 'icon' => '•', 'label' => 'Position '.$rank, 'bg' => '#f8f9fa');
            
            $rowBackground = $rank % 2 == 0 ? '#ffffff' : '#f8f9fa';

            $contents .= '
                <tr style="background-color: '.$rowBackground.';">
                    <td style="padding: 15px; border-bottom: 1px solid #ecf0f1;">
                        <div style="background: '.$rankStyle['bg'].'; padding: 8px; border-radius: 6px; text-align: center;">
                            <span style="color: '.$rankStyle['color'].'; font-weight: bold;">'.$rankStyle['icon'].' '.$rankStyle['label'].'</span>
                        </div>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #ecf0f1;">
                        <div style="color: #2c3e50; font-weight: bold; font-size: 14px;">
                            '.$candidate['firstname'].' '.$candidate['lastname'].'
                        </div>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #ecf0f1;">
                        <div style="font-weight: bold; color: '.$rankStyle['color'].'; font-size: 16px;">
                            '.$candidate['vote_count'].'
                        </div>
                        <div style="font-size: 12px; color: #95a5a6;">of '.$total_votes.' total votes</div>
                    </td>
                    <td style="padding: 15px; border-bottom: 1px solid #ecf0f1;">
                        <div style="position: relative;">
                            <div style="background: #ecf0f1; border-radius: 20px; height: 24px; width: 100%;">
                                <div style="background: '.$rankStyle['color'].'; border-radius: 20px; height: 24px; width: '.$vote_percentage.'%;"></div>
                                <div style="position: absolute; top: 4px; right: 10px; color: #2c3e50; font-weight: bold;">
                                    '.$vote_percentage.'%
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>';
            $rank++;
        }
        
        $contents .= '</table></div>';
    }
    
    return $contents;
}

try {
    if (!file_exists('config.ini')) {
        throw new Exception('Config file not found');
    }

    $parse = parse_ini_file('config.ini', FALSE, INI_SCANNER_RAW);
    $title = $parse['election_title'] ?? 'Election Results';

    // Initialize PDF
    $pdf = new VotePDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setHeaderTitle($title);

    // Document properties
    $pdf->SetCreator('VoteSystem');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($title . ' - Election Results');

    // PDF settings
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(15, 50, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Add first page
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    // Generate HTML content
    $html = '
    <style>
        table { margin-bottom: 20px; }
        th, td { padding: 8px; }
        .section-title {
            color: #2c3e50;
            font-size: 18px;
            margin: 20px 0;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
			.section-title2 {
            color: #2c3e50;
            font-size: 18px;
            margin: 20px 0;
            padding-bottom: 10px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
    
    <!-- Statistics Section -->
    '.generateStatisticsSection($conn).'
    
    <!-- Results Section -->
    <div style="margin-top: 20px;">
        <h2 class="section-title">Detailed Results by Position</h2>
        '.generateRow($conn).'
    </div>';

    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Generate and output PDF
    $pdf->Output('election_results_'.date('Y-m-d').'.pdf', 'I');

} catch (Exception $e) {
    error_log("PDF Generation Error: " . $e->getMessage());
    $_SESSION['error'] = "There was an error generating the PDF: " . $e->getMessage();
    header('location: home.php');
    exit();
}