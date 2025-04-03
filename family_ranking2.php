<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
// Function to get count using a standardized query
if(isset($_GET["mun"])) {
    $mun = $_GET["mun"];
}
else {
    $mun = "";
}
$limit = 50;
if(isset($_GET["brgy"])) {
 $brgyId = $_GET["brgy"];
 $brgyName = get_value("SELECT barangay from barangays WHERE id = '$brgyId'")[0];
}else{
 $brgyId = "";
}
$munquery = "";
if($mun != "") {
    $munquery = " AND municipality = '$mun'";
       $limit = 150;
}else{
 $munquery = "";
}

$brgyquery = "";
if($brgyId != "") {
    $brgyquery = " AND barangayId = '$brgyId'";
    $limit = 300;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
        box-: 0 10px 20px rgba(0, 0, 0, 0.3);
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

    @media print {
        footer {
            page-break-after: always;

        }

        #myTab {
            display: none;
        }

        #printBtn {
            display: none;
        }
    }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="maindashboard-tab" data-bs-toggle="tab"
                    data-bs-target="#maindashboard" type="button" role="tab" aria-controls="maindashboard"
                    aria-selected="true">Dashboard</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                    type="button" role="tab" aria-controls="dashboard" aria-selected="false">Families</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="families-tab" data-bs-toggle="tab" data-bs-target="#families" type="button"
                    role="tab" aria-controls="families" aria-selected="false">Turnouts</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="maindashboard" role="tabpanel"
                aria-labelledby="maindashboard-tab">
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

                        <div class="card card-voters " id="voterCard" style="cursor: pointer;" data-bs-toggle="modal"
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
                        <div class="card card-voters ">
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
                        <div class="card card-voters " data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
                        <div class="card card-voters  mb-2" data-bs-toggle="modal" data-bs-target="#householdModal">
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
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <div class="dashboard-header">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-body py-3 bg-dark">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h2 class="mb-1">
                                                        <?php
                                    if(isset($_GET["mun"])){
                                        if(isset($_GET["brgy"])){
                                        echo $brgyName . ', ' . $mun;
                                        }
                                        else{
                                        echo $mun;
                                        }
                                    } else {
                                        echo "Barangay Survey Dashboard";
                                    }
                                    ?>
                                                    </h2>
                                                    <button class="btn btn-sm btn-outline-success"
                                                        style="cursor: pointer;" data-bs-toggle="modal"
                                                        data-bs-target="#municipalityModal">
                                                        <i class="fa fa-repeat"></i> Select Address
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex justify-content-md-end">
                                                        <!-- Button moved from here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-md-end">
                                                <button class="btn btn-sm btn-outline-primary" id="printBtn"
                                                    onclick="window.print()">
                                                    <i class="fa fa-print"></i> Print Report
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mt-3 mb-3">
                            <h5>SEARCH LASTNAME</h5>
                        </div>
                        <div class="col-md-4 mt-3 mb-3">
                            <div class="search-box">
                                <input type="text" id="search" class="form-control"
                                    placeholder="Search by family name...">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card bg-primary text-white">
                            <h5>Total Families</h5>
                            <pre style="font-size: small;">WHERE COUNT(*) > 10</pre>
                            <h2 id="totalFamilies">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-warning text-dark">
                            <h5>Avg. Warding Rate</h5>
                            <h2 id="avgWardingRate">0%</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card bg-danger text-white">
                            <h5>Low Warding Families</h5>
                            <h2 id="lowWardingCount">0</h2>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="btn-group">
                        <button class="btn btn-outline-primary active" id="card-view-btn">Card View</button>
                        <button class="btn btn-outline-primary" id="table-view-btn">Table View</button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Sort By
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                            <li><a class="dropdown-item sort-option" data-sort="warding-asc" href="#">Warding Rate (Low
                                    to
                                    High)</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="warding-desc" href="#">Warding Rate
                                    (High to
                                    Low)</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="family-asc" href="#">Family Name
                                    (A-Z)</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="voters-desc" href="#">Total Voters (High
                                    to
                                    Low)</a></li>
                        </ul>
                    </div>
                </div>

                <div id="card-container" class="row">
                    <?php
                    $r = get_array("SELECT v_lname AS lname, COUNT(*) AS cnt FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 $munquery $brgyquery GROUP BY v_lname HAVING COUNT(*) > 10 ORDER BY COUNT(*) DESC LIMIT $limit");
                    
                    $totalFamilies = 0;
                    $totalWardingRate = 0;
                    $lowWardingCount = 0;
                        
                    foreach ($r as $key => $value) {
                        $lname = trim($value["lname"]);
                        $mnames = get_value("SELECT COUNT(*) AS cnt FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 AND v_mname = '$lname' $munquery $brgyquery")[0];
                            
                        $totalVoters = $value["cnt"] + $mnames;
                        
                        $household = get_value("SELECT COUNT(*) from head_household INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE (TRIM(v_lname) = '$lname' OR TRIM(v_mname) = '$lname') and record_type = 1 $munquery $brgyquery")[0];
                        $members = get_value("SELECT COUNT(*) from household_warding INNER JOIN v_info ON v_info.v_id = household_warding.mem_v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE (TRIM(v_lname) = '$lname' OR TRIM(v_mname) = '$lname') and record_type = 1 $munquery $brgyquery")[0];
                        
                        $wardedTotal = $household + $members;
                        $wardingPercent = ($totalVoters > 0) ? round(($wardedTotal / $totalVoters) * 100) : 0;
                        
                        $cardClass = "";
                        if ($wardingPercent < 30) {
                            $cardClass = "warding-rate-low";
                            $lowWardingCount++;
                        } else if ($wardingPercent < 70) {
                            $cardClass = "warding-rate-medium";
                        } else {
                            $cardClass = "warding-rate-high";
                        }
                        
                        $totalFamilies++;
                        $totalWardingRate += $wardingPercent;
                    ?>
                    <div class="col-md-4 family-card mb-2" data-lastname="<?php echo $lname; ?>"
                        data-voters="<?php echo $totalVoters; ?>" data-warded="<?php echo $wardedTotal; ?>"
                        data-percent="<?php echo $wardingPercent; ?>">
                        <div class="card <?php echo $cardClass; ?>" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#familyDataModal">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title"><strong><?php echo $lname; ?></strong></h5>
                                    <span
                                        class="badge <?php echo ($wardingPercent < 30) ? 'bg-danger' : (($wardingPercent < 70) ? 'bg-warning text-dark' : 'bg-success'); ?>">
                                        <?php echo $wardingPercent; ?>%
                                    </span>
                                </div>
                                <div class="card-text mt-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Total Voters:</span>
                                        <strong><?php echo $totalVoters; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Warded Voters:</span>
                                        <strong><?php echo $wardedTotal; ?></strong>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar <?php echo ($wardingPercent < 30) ? 'bg-danger' : (($wardingPercent < 70) ? 'bg-warning' : 'bg-success'); ?>"
                                            role="progressbar" style="width: <?php echo $wardingPercent; ?>%"
                                            aria-valuenow="<?php echo $wardingPercent; ?>" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>

                <div id="table-container" class="row" style="display: none;">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Family Name</th>
                                        <th>Total Voters</th>
                                        <th>Warded</th>
                                        <th>Warding Rate</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody">
                                    <?php
                                    $counter = 1;
                                    foreach ($r as $key => $value) {
                                        $lname = trim($value["lname"]);
                                        $mnames = get_value("SELECT COUNT(*) AS cnt FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 AND v_mname = '$lname' $munquery $brgyquery")[0];
                                            
                                        $totalVoters = $value["cnt"] + $mnames;
                                        
                                        $household = get_value("SELECT COUNT(*) from head_household INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE (TRIM(v_lname) = '$lname' OR TRIM(v_mname) = '$lname') and record_type = 1 $munquery $brgyquery")[0];
                                        $members = get_value("SELECT COUNT(*) from household_warding INNER JOIN v_info ON v_info.v_id = household_warding.mem_v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE (TRIM(v_lname) = '$lname' OR TRIM(v_mname) = '$lname') and record_type = 1 $munquery $brgyquery")[0];
                                        
                                        $wardedTotal = $household + $members;
                                        $wardingPercent = ($totalVoters > 0) ? round(($wardedTotal / $totalVoters) * 100) : 0;
                                        
                                        $statusClass = "";
                                        $statusText = "";
                                        if ($wardingPercent < 30) {
                                            $statusClass = "text-danger";
                                            $statusText = "Low";
                                        } else if ($wardingPercent < 70) {
                                            $statusClass = "text-warning";
                                            $statusText = "Medium";
                                        } else {
                                            $statusClass = "text-success";
                                            $statusText = "High";
                                        }
                                    ?>
                                    <tr class="family-row" data-lastname="<?php echo $lname; ?>"
                                        data-voters="<?php echo $totalVoters; ?>"
                                        data-warded="<?php echo $wardedTotal; ?>"
                                        data-percent="<?php echo $wardingPercent; ?>">
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo $lname; ?></td>
                                        <td><?php echo $totalVoters; ?></td>
                                        <td><?php echo $wardedTotal; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 100px;">
                                                    <div class="progress-bar <?php echo ($wardingPercent < 30) ? 'bg-danger' : (($wardingPercent < 70) ? 'bg-warning' : 'bg-success'); ?>"
                                                        role="progressbar"
                                                        style="width: <?php echo $wardingPercent; ?>%"
                                                        aria-valuenow="<?php echo $wardingPercent; ?>" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <span><?php echo $wardingPercent; ?>%</span>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge <?php echo ($wardingPercent < 30) ? 'bg-danger' : (($wardingPercent < 70) ? 'bg-warning text-dark' : 'bg-success'); ?>"><?php echo $statusText; ?></span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="families" role="tabpanel" aria-labelledby="families-tab">
                <?php
    // Fetch all barangays data - ORIGINAL QUERY PRESERVED
    $r = get_array("SELECT barangay, households, id FROM barangays WHERE id IS NOT NULL $munquery $brgyquery2");
    
    // Initialize counters for turnouts
    $total_barangays = count($r);
    $submitted_barangays = 0;
    $total_households = 0;
    $submitted_households = 0;
    
    // Count barangays with submitted households and calculate totals
    foreach ($r as $value) {
        $brgyid = $value[2];
        $total_households += intval($value[1]); // Add each barangay's households to the total
        
        // Get households count for this specific barangay
        $wardedhouseholds = get_value("SELECT COUNT(*) FROM head_household
            INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id
            INNER JOIN barangays ON barangays.id = v_info.barangayId
            WHERE v_info.record_type = 1 AND barangayId = '$brgyid'")[0];
            
        // Add this barangay's submitted households to the running total
        $submitted_households += intval($wardedhouseholds);
        
        // Check if households were submitted for this barangay
        if ($wardedhouseholds > 0) {
            $submitted_barangays++;
        }
    }
    
    // Calculate percentages from corrected data
    $barangay_submission_percent = $total_barangays > 0 ? ($submitted_barangays / $total_barangays) * 100 : 0;
    $household_submission_percent = $total_households > 0 ? ($submitted_households / $total_households) * 100 : 0;
    ?>

                <!-- Enhanced Header Section -->
                <div class="card mb-4">
                    <div class="card-body py-3 bg-dark">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h2 class="mb-1">
                                            <?php
                                    if(isset($_GET["mun"])){
                                        if(isset($_GET["brgy"])){
                                        echo $brgyName . ', ' . $mun;
                                        }
                                        else{
                                        echo $mun;
                                        }
                                    } else {
                                        echo "Barangay Survey Dashboard";
                                    }
                                    ?>
                                        </h2>
                                        <button class="btn btn-sm btn-outline-success" style="cursor: pointer;"
                                            data-bs-toggle="modal" data-bs-target="#municipalityModal">
                                            <i class="fa fa-repeat"></i> Select Address
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-md-end">
                                            <!-- Button moved from here -->
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="d-flex justify-content-md-end">
                                    <button class="btn btn-sm btn-outline-primary" id="printBtn"
                                        onclick="window.print()">
                                        <i class="fa fa-print"></i> Print Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <!-- Barangay submission card -->
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100 border-left-success ">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-xs text-uppercase text-success fw-bold">Barangay Submission</div>
                                    <div><i class="fa fa-map-marker text-gray-300"></i></div>
                                </div>
                                <div class="h4 mb-1 fw-bold">
                                    <?php echo $submitted_barangays . '/' . $total_barangays; ?>
                                </div>
                                <div class="progress mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: <?php echo round($barangay_submission_percent, 1); ?>%"
                                        aria-valuenow="<?php echo $submitted_barangays; ?>" aria-valuemin="0"
                                        aria-valuemax="<?php echo $total_barangays; ?>">
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <?php echo round($barangay_submission_percent, 1); ?>% submitted
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Household submission card -->
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100 border-left-primary ">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-xs text-uppercase text-primary fw-bold">Household Submission</div>
                                    <div><i class="fa fa-home text-gray-300"></i></div>
                                </div>
                                <div class="h4 mb-1 fw-bold">
                                    <?php echo $submitted_households . '/' . $total_households; ?>
                                </div>
                                <div class="progress mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: <?php echo round($household_submission_percent, 1); ?>%"
                                        aria-valuenow="<?php echo $submitted_households; ?>" aria-valuemin="0"
                                        aria-valuemax="<?php echo $total_households; ?>">
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <?php echo round($household_submission_percent, 1); ?>% of households covered
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Average Households Per Barangay -->
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100 border-left-info ">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-xs text-uppercase text-info fw-bold">Avg. Households/Brgy</div>
                                    <div><i class="fa fa-calculator text-gray-300"></i></div>
                                </div>
                                <div class="h4 mb-1 fw-bold">
                                    <?php echo round($total_households / ($total_barangays > 0 ? $total_barangays : 1), 1); ?>
                                </div>
                                <div class="small text-muted">
                                    Average number of households per barangay
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated Card -->
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100 border-left-warning ">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-xs text-uppercase text-warning fw-bold">Last Updated</div>
                                    <div><i class="fa fa-calendar text-gray-300"></i></div>
                                </div>
                                <div class="h4 mb-1 fw-bold">
                                    <?php echo date('M d, Y'); ?>
                                </div>
                                <div class="small text-muted">
                                    As of today
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Status Message -->
                <div
                    class="alert alert-<?php echo $barangay_submission_percent > 75 ? 'success' : ($barangay_submission_percent > 50 ? 'info' : ($barangay_submission_percent > 25 ? 'warning' : 'danger')); ?> mb-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i
                                class="fa fa-<?php echo $barangay_submission_percent > 75 ? 'check-circle' : ($barangay_submission_percent > 50 ? 'info-circle' : ($barangay_submission_percent > 25 ? 'exclamation-triangle' : 'exclamation-circle')); ?> fa-2x"></i>
                        </div>
                        <div>
                            <h5
                                class="fw-bold text-<?php echo $barangay_submission_percent > 75 ? 'success' : ($barangay_submission_percent > 50 ? 'info' : ($barangay_submission_percent > 25 ? 'warning' : 'danger')); ?> mb-1">
                                <?php echo $submitted_barangays . '/' . $total_barangays; ?> Barangays submitted
                            </h5>
                            <div>
                                <?php 
                    if($barangay_submission_percent > 75) {
                        echo "Great progress! Most barangays have submitted their data.";
                    } elseif($barangay_submission_percent > 50) {
                        echo "Good progress. More than half of barangays have submitted data.";
                    } elseif($barangay_submission_percent > 25) {
                        echo "Progress is ongoing. Follow up with remaining barangays.";
                    } else {
                        echo "Submission rate is low. Immediate follow-up recommended.";
                    }
                    ?>
                            </div>
                        </div>
                    </div>
                </div>
                <footer></footer>
                <!-- Enhanced Data Table -->
                <div class="card  mb-4">
                    <div class="card-header bg-dark py-3">
                        <h5 class="mb-0 fw-bold">Barangay Submission Report</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="turnoutTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Municipality</th>
                                        <th>Barangay</th>
                                        <th>Voters</th>
                                        <th>Households</th>
                                        <th>Completion</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $table_total_households = 0;
                        $table_submitted_households = 0;
                        
                        foreach ($r as $key => $value) {
                            $brgyid = $value[2];
                            $brgy_total_households = intval($value[1]);
                            $table_total_households += $brgy_total_households;
                            
                            // Using original query for this specific barangay
                            $wardedhouseholds = get_value("SELECT COUNT(*) FROM head_household
                                INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id
                                INNER JOIN barangays ON barangays.id = v_info.barangayId
                                WHERE v_info.record_type = 1 AND barangayId = '$brgyid'")[0];
                            
                            $brgy_submitted_households = intval($wardedhouseholds);
                            $table_submitted_households += $brgy_submitted_households;
                                
                            // Using original query
                            $voters = get_value("SELECT COUNT(*),municipality FROM v_info
                                INNER JOIN barangays ON barangays.id = v_info.barangayId
                                WHERE record_type = 1 AND barangayId = '$brgyid'");
                                
                            // Calculate completion percentage for this barangay
                            $completion_percent = $brgy_total_households > 0 ? 
                                round(($brgy_submitted_households / $brgy_total_households) * 100, 1) : 0;
                            
                            // Determine status based on completion percentage
                            $status_class = "";
                            $status_text = "";
                            if ($brgy_submitted_households == 0) {
                                $status_class = "danger";
                                $status_text = "Not Started";
                            } elseif ($completion_percent < 30) {
                                $status_class = "warning";
                                $status_text = "Just Started";
                            } elseif ($completion_percent < 70) {
                                $status_class = "info";
                                $status_text = "In Progress";
                            } elseif ($completion_percent < 100) {
                                $status_class = "primary";
                                $status_text = "Almost Complete";
                            } else {
                                $status_class = "success";
                                $status_text = "Complete";
                            }
                        ?>
                                    <tr class="<?php echo $brgy_submitted_households == 0 ? "table-danger" : ""; ?>">
                                        <td><?php echo $key + 1; ?></td>
                                        <td><?php echo $voters[1]; ?></td>
                                        <td><?php echo $value[0]; ?></td>
                                        <td><?php echo $voters[0]; ?></td>
                                        <td><?php echo $brgy_submitted_households . '/' . $brgy_total_households; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-<?php echo $status_class; ?>"
                                                        role="progressbar"
                                                        style="width: <?php echo $completion_percent; ?>%"
                                                        aria-valuenow="<?php echo $completion_percent; ?>"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-muted small"><?php echo $completion_percent; ?>%</span>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                    </tr>
                                    <?php
                        }
                        
                        // Double-check the table totals match our earlier calculations
                        if ($table_total_households != $total_households || $table_submitted_households != $submitted_households) {
                            // Use the table totals as they're calculated directly from each row
                            $total_households = $table_total_households;
                            $submitted_households = $table_submitted_households;
                            $household_submission_percent = $total_households > 0 ? 
                                ($submitted_households / $total_households) * 100 : 0;
                        }
                        ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold bg-light">
                                        <td colspan="4" class="text-end">Overall:</td>
                                        <td><?php echo $submitted_households . '/' . $total_households; ?></td>
                                        <td colspan="2">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: <?php echo round($household_submission_percent, 1); ?>%"
                                                        aria-valuenow="<?php echo round($household_submission_percent, 1); ?>"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-muted small"><?php echo round($household_submission_percent, 1); ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add simple script for sorting and filtering -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Add sorting functionality if jQuery and DataTables are available
                    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                        $('#turnoutTable').DataTable({
                            "paging": true,
                            "ordering": true,
                            "info": true,
                            "searching": true,
                            "pageLength": 10,
                            "order": [
                                [5, "desc"]
                            ] // Sort by completion column by default
                        });
                    }
                });
                </script>
            </div>
        </div>
    </div>

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

    <div class="modal fade" id="familyDataModal" tabindex="-1" aria-labelledby="familyDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="familyDataModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="familyModalContent">
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

    <div class="modal fade" id="municipalityModal" tabindex="-1" aria-labelledby="municipalityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <?php 
                    if ($mun == "") {
                    ?>
                    <h5 class="modal-title" id="municipalityModalLabel">Select Municipality</h5>
                    <?php
                    } else {
                    ?>
                    <h5 class="modal-title" id="municipalityModalLabel">Select Barangay</h5>
                    <?php
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="municipalityForm">
                        <div class="mb-3">
                            <?php
                            $municipalities = get_array("SELECT DISTINCT municipality FROM barangays ORDER BY municipality ASC");
                            ?>
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
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function selectMunicipality() {
        var selectedMunicipality = document.getElementById('municipality') ? document.getElementById('municipality')
            .value : "";
        var selectedBarangay = document.getElementById('barangay') ? document.getElementById('barangay').value : "";

        if (selectedMunicipality === "" && selectedBarangay === "") {
            window.location.href = window.location.pathname;
            return;
        }

        var urlParams = [];

        if (selectedMunicipality !== "") {
            urlParams.push('mun=' + encodeURIComponent(selectedMunicipality));
        }

        if (selectedBarangay !== "") {
            urlParams.push('brgy=' + encodeURIComponent(selectedBarangay));
        }

        var newUrl = urlParams.length > 0 ? window.location.pathname + '?' + urlParams.join('&') : window.location
            .pathname;

        window.location.href = newUrl;
    }

    function loadBarangays() {
        var municipality = $("#municipality").val();
        if (municipality === "") {
            $("#barangay").html('<option value="">Select Barangay</option>');
            return;
        }
        $.ajax({
            url: "get_barangays.php",
            method: "POST",
            data: {
                municipality: municipality
            },
            dataType: "json",
            success: function(response) {
                if (response.length > 0) {
                    var barangayOptions = '<option value="">Select Barangay</option>';
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
    $(document).ready(function() {
        const totalFamilies = <?php echo $totalFamilies; ?>;
        const avgWardingRate =
            <?php echo ($totalFamilies > 0) ? round($totalWardingRate / $totalFamilies) : 0; ?>;
        const lowWardingCount = <?php echo $lowWardingCount; ?>;

        $('#totalFamilies').text(totalFamilies);
        $('#avgWardingRate').text(avgWardingRate + '%');
        $('#lowWardingCount').text(lowWardingCount);

        $('#card-view-btn').click(function() {
            $(this).addClass('active');
            $('#table-view-btn').removeClass('active');
            $('#card-container').show();
            $('#table-container').hide();
        });

        $('#table-view-btn').click(function() {
            $(this).addClass('active');
            $('#card-view-btn').removeClass('active');
            $('#table-container').show();
            $('#card-container').hide();
        });

        $('#search').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.family-card').filter(function() {
                $(this).toggle($(this).data('lastname').toLowerCase().indexOf(value) > -1);
            });
            $('.family-row').filter(function() {
                $(this).toggle($(this).data('lastname').toLowerCase().indexOf(value) > -1);
            });
        });

        $('.sort-option').click(function(e) {
            e.preventDefault();
            const sortType = $(this).data('sort');

            const cards = $('.family-card').get();
            cards.sort(function(a, b) {
                switch (sortType) {
                    case 'warding-asc':
                        return $(a).data('percent') - $(b).data('percent');
                    case 'warding-desc':
                        return $(b).data('percent') - $(a).data('percent');
                    case 'family-asc':
                        return $(a).data('lastname').localeCompare($(b).data('lastname'));
                    case 'voters-desc':
                        return $(b).data('voters') - $(a).data('voters');
                    default:
                        return 0;
                }
            });

            $('#card-container').append(cards);

            const rows = $('.family-row').get();
            rows.sort(function(a, b) {
                switch (sortType) {
                    case 'warding-asc':
                        return $(a).data('percent') - $(b).data('percent');
                    case 'warding-desc':
                        return $(b).data('percent') - $(a).data('percent');
                    case 'family-asc':
                        return $(a).data('lastname').localeCompare($(b).data('lastname'));
                    case 'voters-desc':
                        return $(b).data('voters') - $(a).data('voters');
                    default:
                        return 0;
                }
            });

            const tbody = $('#tbody');
            tbody.empty();
            $.each(rows, function(index, row) {
                $(row).find('td:first').text(index + 1);
                tbody.append(row);
            });

            $('#sortDropdown').text('Sort By: ' + $(this).text());
        });

        $('.sort-option[data-sort="warding-asc"]').click();
    });
    </script>
    <script>
    // Add this JavaScript code
    document.addEventListener('DOMContentLoaded', function() {
        const familyCards = document.querySelectorAll('.family-card');

        familyCards.forEach(card => {
            card.querySelector('.card').addEventListener('click', function() {
                const lastname = this.closest('.family-card').getAttribute('data-lastname');
                const voters = this.closest('.family-card').getAttribute('data-voters');
                const warded = this.closest('.family-card').getAttribute('data-warded');
                const percent = this.closest('.family-card').getAttribute('data-percent');

                // Update modal title with lastname
                document.getElementById('familyDataModalLabel').textContent = lastname +
                    ' Family';

                // Make AJAX request to get PHP-generated content
                fetch('get_family_details.php?lastname=' + encodeURIComponent(lastname) +
                        '&munquery=' + encodeURIComponent("<?php echo $munquery; ?>") +
                        '&brgyquery=' + encodeURIComponent("<?php echo $brgyquery; ?>"))
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('familyModalContent').innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Error fetching family details:', error);
                        document.getElementById('familyModalContent').innerHTML =
                            'Error loading data';
                    });
            });
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
</body>

</html>