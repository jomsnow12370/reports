<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
// Function to get count using a standardized query
if(isset($_GET["mun"])) {
    $mun = $_GET["mun"];
}
else {
    $mun = "";
}

if(isset($_GET["brgy"])) {
 $brgyId = $_GET["brgy"];
 $brgyName = get_value("SELECT barangay from barangays WHERE id = '$brgyId'")[0];
}else{
 $brgyId = "";
}

$munquery = "";
if($mun != "") {
    $munquery = " AND municipality = '$mun'";
}else{
 $munquery = "";
}

$brgyquery = "";
if($brgyId != "") {
    $brgyquery = " AND barangayId = '$brgyId'";
}else{
 $brgyquery = "";
}



$brgyquery2 = "";
if($brgyId != "") {
    $brgyquery2 = " AND id = '$brgyId'";
}else{
 $brgyquery2 = "";
}


function count_leaders($c, $leader_type, $munquery, $brgyquery) {
    $query = "SELECT COUNT(*) from leaders 
              INNER JOIN v_info ON leaders.v_id = v_info.v_id 
              INNER JOIN barangays ON barangays.id = v_info.barangayId 
              WHERE leaders.type = $leader_type 
              AND v_info.record_type = 1 
              AND electionyear = 2025 
              AND status is null 
                $munquery $brgyquery
              GROUP by leaders.v_id";
    $result = mysqli_query($c, $query);
    return mysqli_num_rows($result);
}

function count_household($c, $munquery, $brgyquery) {
    $query = "SELECT SUM(households) AS total_households 
              FROM barangays 
              WHERE id IS NOT NULL 
              $munquery 
              $brgyquery";
    
    $result = mysqli_query($c, $query);
    
    // Fetch the result
    $row = mysqli_fetch_assoc($result);
    
    // Return the total count (handle NULL if no data is found)
    return $row['total_households'] ?? 0;
}



// Function to get count from survey responses
function count_survey_responses($c, $remarks_txt, $is_household_head = true, $munquery, $brgyquery) {
    $table = $is_household_head ? "head_household" : "household_warding";
    $id_field = $is_household_head ? "fh_v_id" : "mem_v_id";
    
    $query = "SELECT COUNT(*)
              FROM $table
              INNER JOIN v_info ON v_info.v_id = $table.$id_field
              INNER JOIN v_remarks ON v_remarks.v_id = $table.$id_field
              INNER JOIN barangays ON barangays.id = v_info.barangayId
              INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id
              WHERE record_type = 1  $munquery $brgyquery
              AND remarks_txt = '$remarks_txt'

              GROUP BY v_remarks.v_id";
    
    $result = mysqli_query($c, $query);
    return mysqli_num_rows($result);
}

// Get leader counts
$total_mc = count_leaders($c, 4, $munquery, $brgyquery);
$total_dc = count_leaders($c, 3, $munquery, $brgyquery);
$total_bc = count_leaders($c, 2, $munquery, $brgyquery);
$total_wl = count_leaders($c, 1, $munquery, $brgyquery);

// Get household counts
$res_head_household = mysqli_query($c, "SELECT COUNT(*) from head_household 
                                        INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id 
                                        INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                        WHERE record_type = 1 $munquery $brgyquery
                                        GROUP BY fh_v_id");
                                        
$res_household_member = mysqli_query($c, "SELECT COUNT(*) from household_warding 
                                          INNER JOIN v_info ON v_info.v_id = household_warding.fh_v_id 
                                          INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                          WHERE record_type = 1  $munquery $brgyquery
                                          GROUP BY mem_v_id");

$head_household = mysqli_num_rows($res_head_household);
$household_member = mysqli_num_rows($res_household_member);
$household_total = $head_household + $household_member;

// Get total voters
// Survey data - Congressional candidates
$candidates = ['Laynes', 'Rodriguez', 'Alberto', 'UndecidedCong'];
$cong_totals = [];
$total_warding_cong = 0;

foreach ($candidates as $candidate) {
    $remarks = $candidate . '(Survey 2025)';
    $head_count = count_survey_responses($c, $remarks, true,  $munquery,$brgyquery);
    $member_count = count_survey_responses($c, $remarks, false, $munquery,$brgyquery);
    
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
    $head_count = count_survey_responses($c, $remarks, true,  $munquery, $brgyquery);
    $member_count = count_survey_responses($c, $remarks, false,  $munquery, $brgyquery);
    
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
    $head_count = count_survey_responses($c, $remarks, true,  $munquery, $brgyquery);
    $member_count = count_survey_responses($c, $remarks, false, $munquery, $brgyquery);
    
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
    <div class="container-fluid mt-4">
        <h3 class="text-light text-center">2025 Warding Dashboard
            <br>
            <small>
                <i>
                    <?php
                if($mun != ""){
                    if(isset($_GET["brgy"]) != ""){
                        echo $brgyName . ', ';
                    }
                    echo $mun;
                }
                ?>
                </i>
            </small>
        </h3>
        <!-- Total Voters Card -->
        <div class="row">
            <div class="col-lg-4 mb-4">

                <div class="card card-voters shadow" id="voterCard" style="cursor: pointer;" data-bs-toggle="modal"
                    data-bs-target="#municipalityModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Voters
                                    <?php 
                                                        // if($mun != "") {
                                                        //     if($brgyId != "")
                                                        //     {
                                                        //         echo " of $brgyName, $mun <br><div class='text-muted' style='font-size:16px;font-weight:600'> <i>" . count($precinct_totals) . ' Precincts</i> </div>';
                                                        //     }
                                                        //     else{
                                                        //         echo " of $mun <br><div class='text-muted' style='font-size:16px;font-weight:600'> <i>" . count($barangay_totals) . ' Barangays</i> </div>';
                                                        //     }
                                                          
                                                        // }
                                                         ?>
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php 
                                                        // Get the total number of voters
                                                        $total_voters = get_value("SELECT COUNT(*) from v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE v_info.record_type = 1 $munquery $brgyquery");              
                                                        echo number_format($total_voters[0]);
                                                        ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users voter-icon"></i>
                            </div>
                        </div>
                    </div> <!-- End of card-body -->
                    <div class="card-footer">
                        <small><i>Click to select <?php 
                                        if($mun != ""){
                                            echo "barangay";
                                        }
                                        else{
                                            echo "municipality";
                                        }
                                        ?></i></small>
                    </div>
                </div>


            </div>
            <div class="col-lg-4 mb-4">
                <div class="card card-voters shadow">
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
                                    Households Warded
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php //echo number_format($household_total); ?>
                                    <?php echo number_format($head_household); ?> / <b class="text-danger"><?php 
                                        echo number_format(count_household($c, $munquery, $brgyquery2));?></b>
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

                                    <div class="category-pill dc-pill me-2 mb-1">
                                        Household Members: <?php echo number_format($household_member); ?>
                                    </div>
                                    <div class="category-pill mc-pill me-2 mb-1">
                                        Total Warded Voters: <?php echo number_format($household_total); ?>
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

        <!-- container -->
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
    <?php 
     if(!isset($_GET['brgy'])){
        ?>
    <div class="modal fade" id="householdModal" tabindex="-1" aria-labelledby="householdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="householdModalLabel">Household Warding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php 
                    // Fetch all barangays data
                        $r = get_array("SELECT barangay, households, id FROM barangays WHERE id IS NOT NULL $munquery $brgyquery");

                        // Initialize counters for turnouts
                        $total_barangays = count($r);
                        $submitted_barangays = 0;

                        //Count barangays with submitted households
                        foreach ($r as $value) {
                            $brgyid = $value[2];
                            $wardedhouseholds = get_value("SELECT COUNT(*) FROM head_household 
                                INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id 
                                INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                WHERE v_info.record_type = 1 $munquery $brgyquery2")[0];

                            // Check if households were submitted
                            if ($wardedhouseholds > 0) {
                                $submitted_barangays++;
                            }
                        }
                        ?>

                    <!-- Show total turnouts -->
                    <h5>Warding Turnouts</h5>
                    <p class="fw-bold text-success">
                        <?php echo $submitted_barangays . '/' . $total_barangays; ?> Barangays submitted
                    </p>

                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-bordered">
                            <thead>
                                <th>#</th>
                                <th>Municipality</th>
                                <th>Barangay</th>
                                <th>Voters</th>
                                <th>Households</th>
                            </thead>
                            <!-- <tbody>
                                <?php 
                            foreach ($r as $key => $value) {
                                $brgyid = $value[2];
                                $wardedhouseholds = get_value("SELECT COUNT(*) FROM head_household 
                                    INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id 
                                    INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                    WHERE v_info.record_type = 1 AND barangayId = '$brgyid'")[0];
                                
                                $voters = get_value("SELECT COUNT(*),municipality  FROM v_info 
                                    INNER JOIN barangays ON barangays.id = v_info.barangayId 
                                    WHERE record_type = 1 AND barangayId = '$brgyid'");
                                ?>
                                <tr class="<?php 
                                if ($wardedhouseholds == "0") {
                                    echo "table-danger";
                                } ?>">
                                    <td><?php echo $key + 1; ?></td>
                                    <td><?php echo $voters[1]; ?></td>
                                    <td><?php echo $value[0]; ?></td>
                                    <td><?php echo $voters[0]; ?></td>
                                    <td><?php echo $wardedhouseholds . '/' . $value[1]; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody> -->
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
     }
     ?>

    <div class="modal fade" id="municipalityModal" tabindex="-1" aria-labelledby="municipalityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="municipalityForm">
                        <div class="mb-3">
                            <?php
                        // Fetch municipalities dynamically from the database
                        $municipalities = get_array("SELECT DISTINCT municipality FROM barangays ORDER BY municipality ASC");
                        ?>
                            <!-- Municipality Dropdown -->
                            <label for="municipality" class="form-label">Municipality</label>
                            <select class="form-select" id="municipality" name="municipality" onchange="loadBarangays()"
                                required>
                                <option value="">Select Municipality</option>
                                <?php
                            foreach ($municipalities as $munItem) {
                                echo "<option value='{$munItem['municipality']}'>{$munItem['municipality']}</option>";
                            }
                            ?>
                            </select>

                            <!-- Barangay Dropdown -->
                            <label for="barangay" class="form-label mt-3">Barangay</label>
                            <select class="form-select" id="barangay" name="barangay" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i>Leave municipality and barangay blank for provincewide dashboard.</i>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="selectMunicipality()">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP implementation example -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
    function selectMunicipality() {
        // Get selected municipality and barangay values
        var selectedMunicipality = document.getElementById('municipality') ? document.getElementById('municipality')
            .value : "";
        var selectedBarangay = document.getElementById('barangay') ? document.getElementById('barangay').value : "";

        // Check if both municipality and barangay are not selected
        if (selectedMunicipality === "" && selectedBarangay === "") {
            // Reload the page without any query parameters
            window.location.href = window.location.pathname;
            return; // Exit the function
        }

        // Build the URL based on selected values
        var urlParams = [];

        if (selectedMunicipality !== "") {
            urlParams.push('mun=' + encodeURIComponent(selectedMunicipality));
        }

        if (selectedBarangay !== "") {
            urlParams.push('brgy=' + encodeURIComponent(selectedBarangay));
        }

        // Generate the new URL with selected parameters or reload if none
        var newUrl = urlParams.length > 0 ? window.location.pathname + '?' + urlParams.join('&') : window.location
            .pathname;

        // Redirect to the updated URL
        window.location.href = newUrl;
    }

    function loadBarangays() {
        var municipality = $("#municipality").val(); // Get selected municipality

        if (municipality === "") {
            $("#barangay").html('<option value="">Select Barangay</option>');
            return;
        }

        // Send AJAX request to get barangays
        $.ajax({
            url: "get_barangays.php", // Backend endpoint to get barangays
            method: "POST",
            data: {
                municipality: municipality
            },
            dataType: "json",
            success: function(response) {
                if (response.length > 0) {
                    var barangayOptions = '<option value="">Select Barangay</option>';
                    // Loop through the response and create options
                    $.each(response, function(index, barangay) {
                        barangayOptions +=
                            `<option value="${barangay.id}">${barangay.barangay}</option>`;
                    });
                    $("#barangay").html(barangayOptions);
                } else {
                    $("#barangay").html('<option value="">No Barangays Found</option>');
                }
            },
            error: function() {
                alert("Error loading barangays. Please try again.");
            }
        });
    }
    </script>
    <!-- Bootstrap & jQuery JS (optional) -->
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