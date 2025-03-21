<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];
// Function to get count using a standardized query
function count_leaders($c, $leader_type, $mun) {
    $query = "SELECT COUNT(*) from leaders 
              INNER JOIN v_info ON leaders.v_id = v_info.v_id 
              INNER JOIN barangays ON barangays.id = v_info.barangayId 
              WHERE leaders.type = $leader_type 
              AND v_info.record_type = 1 
              AND electionyear = 2025 
              AND status is null 
              AND municipality = '$mun' 
              GROUP by leaders.v_id";
    $result = mysqli_query($c, $query);
    return mysqli_num_rows($result);
}

// Function to get count from survey responses
function count_survey_responses($c, $mun, $remarks_txt, $is_household_head = true) {
    $table = $is_household_head ? "head_household" : "household_warding";
    $id_field = $is_household_head ? "fh_v_id" : "mem_v_id";
    
    $query = "SELECT COUNT(*)
              FROM $table
              INNER JOIN v_info ON v_info.v_id = $table.$id_field
              INNER JOIN v_remarks ON v_remarks.v_id = $table.$id_field
              INNER JOIN barangays ON barangays.id = v_info.barangayId
              INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id
              WHERE record_type = 1
              AND remarks_txt = '$remarks_txt'
              AND municipality = '$mun'
              GROUP BY v_remarks.v_id";
    
    $result = mysqli_query($c, $query);
    return mysqli_num_rows($result);
}

// Get leader counts
$total_mc = count_leaders($c, 4, $mun);
$total_dc = count_leaders($c, 3, $mun);
$total_bc = count_leaders($c, 2, $mun);
$total_wl = count_leaders($c, 1, $mun);

// Get household counts
$res_head_household = mysqli_query($c, "SELECT COUNT(*) from head_household 
                                        INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id 
                                        INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                        WHERE record_type = 1 AND municipality = '$mun' 
                                        GROUP BY fh_v_id");
                                        
$res_household_member = mysqli_query($c, "SELECT COUNT(*) from household_warding 
                                          INNER JOIN v_info ON v_info.v_id = household_warding.fh_v_id 
                                          INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                          WHERE record_type = 1 AND municipality = '$mun' 
                                          GROUP BY mem_v_id");

$head_household = mysqli_num_rows($res_head_household);
$household_member = mysqli_num_rows($res_household_member);
$household_total = $head_household + $household_member;

// Get total voters
$total_voters = get_value("SELECT COUNT(*) from v_info 
                           INNER JOIN barangays ON barangays.id = v_info.barangayId 
                           WHERE v_info.record_type = 1 and municipality = '$mun'");

// Survey data - Congressional candidates
$candidates = ['Laynes', 'Rodriguez', 'Alberto', 'UndecidedCong'];
$cong_totals = [];
$total_warding_cong = 0;

foreach ($candidates as $candidate) {
    $remarks = $candidate . '(Survey 2025)';
    $head_count = count_survey_responses($c, $mun, $remarks, true);
    $member_count = count_survey_responses($c, $mun, $remarks, false);
    
    $cong_totals[$candidate] = [
        'head' => $head_count,
        'member' => $member_count,
        'total' => $head_count + $member_count
    ];
    
    $total_warding_cong += $head_count + $member_count;
}

// Survey data - Governor candidates
$gov_candidates = ['Bosste', 'Asanza', 'UndecidedGov'];
$gov_totals = [];
$total_warding_gov = 0;

foreach ($gov_candidates as $candidate) {
    $remarks = $candidate . '(Survey 2025)';
    $head_count = count_survey_responses($c, $mun, $remarks, true);
    $member_count = count_survey_responses($c, $mun, $remarks, false);
    
    $gov_totals[$candidate] = [
        'head' => $head_count,
        'member' => $member_count,
        'total' => $head_count + $member_count
    ];
    
    $total_warding_gov += $head_count + $member_count;
}

// Survey data - Vice Governor candidates
$vgov_candidates = ['Fernandez', 'Abundo', 'UndecidedVGov'];
$vgov_totals = [];
$total_warding_vgov = 0;

foreach ($vgov_candidates as $candidate) {
    $remarks = $candidate . '(Survey 2025)';
    $head_count = count_survey_responses($c, $mun, $remarks, true);
    $member_count = count_survey_responses($c, $mun, $remarks, false);
    
    $vgov_totals[$candidate] = [
        'head' => $head_count,
        'member' => $member_count,
        'total' => $head_count + $member_count
    ];
    
    $total_warding_vgov += $head_count + $member_count;
}

// Calculate blanks
$cong_blanks = $household_total - $total_warding_cong;
$gov_blanks = $household_total - $total_warding_gov;
$vgov_blanks = $household_total - $total_warding_vgov;

// Now you can access any candidate's data using the arrays
// For example: $cong_totals['Laynes']['total'] or $gov_totals['Bosste']['head']
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    body {
        font-family: 'Montserrat', sans-serif;
    }

    .card-voters {
        border-left: 4px solid #6ea8fe;
        transition: transform 0.3s;
        cursor: pointer;
    }

    .card-voters:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    .voter-icon {
        color: #6ea8fe;
        opacity: 0.7;
        font-size: 2.5rem;
    }

    .category-pill {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        margin-right: 0.3rem;
        border-radius: 12px;
    }

    .mc-pill {
        background-color: #6ea8fe;
        color: #212529;
    }

    .dc-pill {
        background-color: #ea868f;
        color: #212529;
    }

    .bc-pill {
        background-color: #20c997;
        color: #212529;
    }

    .wl-pill {
        background-color: #ffc107;
        color: #212529;
    }

    .category-divider {
        opacity: 0.2;
        margin: 0.5rem 0;
    }

    .profile-img {
        width: 70px;
        /* Adjust the size as needed */
        height: 70px;
        border-radius: 50%;
        /* Makes it circular */
        object-fit: cover;
        /* Ensures the image maintains aspect ratio */
    }
    </style>
</head>

<body class="bg-dark">
    <div class="container mt-4">
        <h1 class="text-light text-center">2025 Election Dashboard <?php echo $mun ?></h1>
        <!-- Total Voters Card -->
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card card-voters shadow">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Voters
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php 
                    // Get the total number of voters
           
                    echo number_format($total_voters[0]);

                    // Get subtotal of voters per municipality
                    $municipality_voters = get_array("SELECT municipality, COUNT(*) as total FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE v_info.record_type = 1 GROUP BY municipality");
                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users voter-icon"></i>
                            </div>
                        </div>

                        <!-- Accordion for displaying municipality voter subtotals -->
                        <hr class="category-divider">
                        <div class="accordion mt-2" id="municipalityAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingMunicipality">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseMunicipality" aria-expanded="false"
                                        aria-controls="collapseMunicipality">
                                        View Municipality Voter Subtotals
                                    </button>
                                </h2>
                                <div id="collapseMunicipality" class="accordion-collapse collapse"
                                    aria-labelledby="headingMunicipality" data-bs-parent="#municipalityAccordion">
                                    <div class="accordion-body">
                                        <!-- Table to display municipality voter subtotals -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Municipality</th>
                                                        <th class="text-center">Total Voters</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                    foreach ($municipality_voters as $key => $municipality) {
                                        $municipality_name = $municipality[0];
                                        $total_voters = $municipality[1];
                                    ?>
                                                    <tr>
                                                        <td><?php echo $municipality_name; ?></td>
                                                        <td class="text-center">
                                                            <?php echo number_format($total_voters); ?></td>
                                                    </tr>
                                                    <?php
                                    }
                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End of card-body -->
                </div>

            </div>
            <div class="col-lg-4 mb-4">
                <div class="card card-voters shadow" data-bs-toggle="modal" data-bs-target="#leadersModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Leaders(BC,WL)
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($total_bc + $total_wl); ?>
                                </div>
                                <!-- <div class="mt-2 text-xs text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <span>3.5% increase since last month</span>
                                </div> -->
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users voter-icon"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                        <hr class="category-divider">
                        <div class="row mt-2">
                            <div class="col-12">

                                <div class="d-flex flex-wrap mb-2">
                                    <div class="category-pill mc-pill me-2 mb-1">
                                        MC: <?php echo number_format($total_mc); ?>
                                    </div>
                                    <div class="category-pill dc-pill me-2 mb-1">
                                        DC: <?php echo number_format($total_dc); ?>
                                    </div>
                                    <div class="category-pill bc-pill me-2 mb-1">
                                        BC: <?php echo number_format($total_bc); ?>
                                    </div>
                                    <div class="category-pill wl-pill me-2 mb-1">
                                        WL: <?php echo number_format($total_wl); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card card-voters shadow" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Household Warding
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($household_total); ?>
                                </div>
                                <!-- <div class="mt-2 text-xs text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <span>1.8% increase since last month</span>
                                </div> -->
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-home voter-icon"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                        <hr class="category-divider">
                        <div class="row mt-2">
                            <div class="col-12">

                                <div class="d-flex flex-wrap mb-2">
                                    <div class="category-pill mc-pill me-2 mb-1">
                                        Household Head: <?php echo number_format($head_household); ?>
                                    </div>
                                    <div class="category-pill dc-pill me-2 mb-1">
                                        Household Members: <?php echo number_format($household_member); ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h5 class="text-light mt-4"><strong>Congressman </strong></h5>
        <div class="row">
            <!-- laynes -->
            <div class="col-lg-3">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Laynes
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($cong_totals['Laynes']['total']); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/sammy.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <!-- rodriguex -->
            <div class="col-lg-3">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Rodriguez
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($cong_totals['Rodriguez']['total']); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/leo.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <!-- alberto -->
            <div class="col-lg-3">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Alberto
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($cong_totals['Alberto']['total']) ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/alberto.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <!-- undecided -->
            <div class="col-lg-3">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Undecided
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($cong_totals['UndecidedCong']['total'] + $cong_blanks); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <i class="fa fa-question-circle" style="font-size:50px"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
        </div>

        <h5 class="text-light mt-4"><strong>Governor</strong></h5>
        <div class="row">
            <!-- laynes -->
            <div class="col-lg-4">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Boss Te
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($gov_totals['Bosste']['total']); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/bosste.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <!-- rodriguex -->
            <div class="col-lg-4">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Asanza
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($gov_totals['Asanza']['total']); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/asanza.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Undecided
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($gov_totals['UndecidedGov']['total']+ $gov_blanks); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <i class="fa fa-question-circle" style="font-size:50px"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
        </div>

        <h5 class="text-light mt-4"><strong>Vice Governor</strong></h5>
        <div class="row">
            <!-- laynes -->
            <div class="col-lg-4">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Fernandez
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($vgov_totals['Fernandez']['total']); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/obet.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <!-- rodriguex -->
            <div class="col-lg-4">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Abundo
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($vgov_totals['Abundo']['total']); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <img src="assets/images/abundo.jpg" alt="Profile" class="profile-img">
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-voters shadow mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Undecided
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($vgov_totals['UndecidedVGov']['total']+ $vgov_blanks); ?>
                                </div>

                            </div>
                            <div class="col-auto">
                                <i class="fa fa-question-circle" style="font-size:50px"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Voters Modal -->
    <div class="modal fade" id="votersModal" tabindex="-1" aria-labelledby="votersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="votersModalLabel">Voters Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add content for voters details here -->
                    <p>Total Voters: <?php echo number_format(0); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaders Modal -->
    <div class="modal fade" id="leadersModal" tabindex="-1" aria-labelledby="leadersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadersModalLabel">Leaders Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add content for leaders details here -->
                    <p>Total Leaders: <?php echo number_format(0); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Household Warding Modal -->
    <div class="modal fade" id="householdModal" tabindex="-1" aria-labelledby="householdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="householdModalLabel">Household Warding Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add content for household warding details here -->
                    <p>Total Households: <?php echo number_format($total_households); ?></p>
                    <p>Household Head: <?php echo number_format($household_head_count); ?></p>
                    <p>Household Members: <?php echo number_format($household_members_count); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



    <!-- PHP implementation example -->
    <?php
    // Example of how to implement this in your PHP code
    
    // Database connection
    // $conn = mysqli_connect("localhost", "username", "password", "database");
    
    // Query to get total voters
    // $query = "SELECT COUNT(*) as total FROM voters";
    // $result = mysqli_query($conn, $query);
    // $row = mysqli_fetch_assoc($result);
    // $total_voters = $row['total'];
    
    // Example static value (replace with actual database query)
    $total_voters = 15782;
    $total_leaders = 1234;
    $total_households = 5678;
    $household_head_count = 1234;
    $household_members_count = 4444;
    $mc_count = 400;
    $dc_count = 300;
    $bc_count = 200;
    $wl_count = 100;
    ?>
    <!-- Bootstrap & jQuery JS (optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('municipalityDropdown').addEventListener('change', function() {
        var municipalityId = this.value;
        if (municipalityId) {
            fetch('get_voters.php?municipality_id=' + municipalityId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('municipalityVoters').innerText = 'Total Voters: ' + data
                        .total_voters;
                });
        } else {
            document.getElementById('municipalityVoters').innerText = '';
        }
    });
    </script>
</body>

</html>