<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

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



//congressman
$laynes_total = 0;
$rodriguez_total = 0;
$alberto_total = 0;
$undecidedcong_total=0;

//governor
$bosste_total=0;
$asanza_total=0;
$undecidedgov_total=0;

//vicegovernor
$fernandez_total = 0;
$abundo_total = 0;
$undecidedvice_total = 0;

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



$sql = "SELECT 
    v_info.v_id AS vid,
    CONCAT_WS(' ', v_lname, v_fname, v_mname) AS fullname,
    v_gender,
    YEAR(CURDATE()) - YEAR(v_birthday) AS age,
    municipality,
    barangay,
    v_precinct_no,

    -- Check for leader where status and laynes are null
    MAX(CASE WHEN leaders.v_id IS NOT NULL AND status IS NULL AND laynes IS NULL AND leaders.electionyear = 2025 THEN 1 ELSE 0 END) AS leaderCheck,

    -- Check for leader where laynes is not null
    MAX(CASE WHEN leaders.v_id IS NOT NULL AND status IS NULL AND laynes IS NOT NULL AND leaders.electionyear = 2025 THEN 1 ELSE 0 END) AS laynesLeaderCheck,

    -- Check warding membership
    IF(COUNT(wardingtbl.member_v_id) > 1, 1, 0) AS wardingCheck,

    -- Check for specific categories in v_remarks
    MAX(CASE WHEN quick_remarks.category_id IN (104, 102) THEN 1 ELSE 0 END) AS tagCheck,
    MAX(CASE WHEN quick_remarks.category_id IN (103) OR v_remarks.remarks_id = 663 THEN 1 ELSE 0 END) AS asanzaPostCheck,
    MAX(CASE WHEN quick_remarks.candidate = 7 THEN 1 ELSE 0 END) AS abundoCheck,
    MAX(CASE WHEN quick_remarks.category_id = 100 THEN 1 ELSE 0 END) AS posoyCheck,
    MAX(CASE WHEN candidate = 1 THEN 1 ELSE 0 END) AS laynesCheck,
    MAX(CASE WHEN candidate = 2 THEN 1 ELSE 0 END) AS rodriguezCheck,
    MAX(CASE WHEN candidate = 3 THEN 1 ELSE 0 END) AS albertoCheck,
    MAX(CASE WHEN quick_remarks.category_id = 106 THEN 1 ELSE 0 END) AS haterCheck,

    -- Check household relationships
    IF(COUNT(head_household.fh_v_id) > 0, 1, 0) AS headHouseholdCheck,
    IF(COUNT(household_warding.mem_v_id) > 0, 1, 0) AS memberCheck

FROM v_info
-- Join barangay
INNER JOIN barangays ON barangays.id = v_info.barangayId

-- Join leaders for leader checks
LEFT JOIN leaders ON leaders.v_id = v_info.v_id

-- Join warding table
LEFT JOIN wardingtbl ON wardingtbl.member_v_id = v_info.v_id

-- Join remarks with quick remarks
LEFT JOIN v_remarks ON v_remarks.v_id = v_info.v_id
LEFT JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id

-- Join head and member household
LEFT JOIN head_household ON head_household.fh_v_id = v_info.v_id
LEFT JOIN household_warding ON household_warding.mem_v_id = v_info.v_id

WHERE 
     record_type = 1
     $munquery 
     $brgyquery
-- Group to prevent duplicate rows
GROUP BY v_info.v_id

-- Order for better performance on sorted results
ORDER BY barangayId, v_lname, v_fname";

// Gender and Age Group Totals
$male_total = 0;
$female_total = 0;
$no_data =0;

// Barangay total counts
$municipality_totals = [];
$barangay_totals = [];

$precinct_totals = [];

$age_groups = [
    '0-25' => 0,
    '26-35' => 0,
    '36-45' => 0,
    '46-55' => 0,
    '56-65' => 0,
    '66+'   => 0
];



$r = get_array($sql);
$all_data = []; // Array to store all data
foreach ($r as $key => $value) {
    // Prepare row data
    $row = [
        'id' => $value["vid"],
        'fullname' => $value['fullname'],
        'gender' => $value['v_gender'],
        'age' => $value['age'],
        'municipality' => $value['municipality'],
        'barangay' => $value['barangay'],
        'precinct' => $value['v_precinct_no'],
        'leaderCheck' => $value['leaderCheck'],
        'laynesLeaderCheck' => $value['laynesLeaderCheck'],
        'wardingCheck' => $value['wardingCheck'],
        'tagCheck' => $value['tagCheck'],
        'asanzaPostCheck' => $value['asanzaPostCheck'],
        'abundoCheck' => $value['abundoCheck'],
        'posoyCheck' => $value['posoyCheck'],
        'laynesCheck' => $value['laynesCheck'],
        'rodriguezCheck' => $value['rodriguezCheck'],
        'albertoCheck' => $value['albertoCheck'],
        'haterCheck' => $value['haterCheck'],
    ];

    // Add row to master array
    $all_data[] = $row;

    // Count gender
    if ($row['gender'] == 'M') {
        $male_total++;
    } elseif ($row['gender'] == 'F') {
        $female_total++;
    } else {
        $no_data++;
    }

    // Count age groups
    if ($row['age'] >= 0 && $row['age'] <= 25) {
        $age_groups['0-25']++;
    } elseif ($row['age'] >= 26 && $row['age'] <= 35) {
        $age_groups['26-35']++;
    } elseif ($row['age'] >= 36 && $row['age'] <= 45) {
        $age_groups['36-45']++;
    } elseif ($row['age'] >= 46 && $row['age'] <= 55) {
        $age_groups['46-55']++;
    } elseif ($row['age'] >= 56 && $row['age'] <= 65) {
        $age_groups['56-65']++;
    } elseif ($row['age'] >= 66) {
        $age_groups['66+']++;
    }

    // Count voters per barangay
    if (!isset($barangay_totals[$row['barangay']])) {
        $barangay_totals[$row['barangay']] = 0;
    }
    $barangay_totals[$row['barangay']]++;

    // Count voters per municipality
    if (!isset($municipality_totals[$row['municipality']])) {
        $municipality_totals[$row['municipality']] = 0;
    }
    $municipality_totals[$row['municipality']]++;

    // Count voters per precinct
    if (!isset($precinct_totals[$row['precinct']])) {
        $precinct_totals[$row['precinct']] = 0;
    }
    $precinct_totals[$row['precinct']]++;

    // Calculate support checks
    $supportercheck = $row['leaderCheck'] + $row['laynesLeaderCheck'] + $row['wardingCheck'];
    $calculation_cong = $supportercheck - $row['haterCheck'] - $row['rodriguezCheck'] - $row['albertoCheck'];

    // Determine congressional results
    if ($calculation_cong > 0) {
        $laynes_total++;
    } elseif ($calculation_cong <= 0) {
        if ($row['rodriguezCheck'] > 0) {
            $rodriguez_total++;
        } elseif ($row['albertoCheck'] > 0) {
            $alberto_total++;
        } else {
            $undecidedcong_total++;
        }
    }

    // Determine gubernatorial results
    $calculation_gov = $supportercheck - $row['haterCheck'] - $row['asanzaPostCheck'];
    if ($calculation_gov > 0) {
        $bosste_total++;
    } elseif ($calculation_gov <= 0) {
        if ($row['asanzaPostCheck'] > 0) {
            $asanza_total++;
        } else {
            $undecidedgov_total++;
        }
    }

    // Determine vice gubernatorial results
    $calculation_vgov = $supportercheck - $row['haterCheck'] - $row['abundoCheck'];
    if ($calculation_vgov > 0) {
        $fernandez_total++;
    } elseif ($calculation_vgov <= 0) {
        if ($row['abundoCheck'] > 0) {
            $abundo_total++;
        } else {
            $undecidedvice_total++;
        }
    }
}

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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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


<!-- Main Content -->


<body class="bg-dark">
    <!-- Loading Screen -->
    <!-- <div id="loadingScreen" class="d-flex justify-content-center align-items-center vh-100 bg-dark">
        <div class="text-center text-light">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <h5>Loading, please wait...</h5>
        </div>
    </div> -->

    <div id="mainContent">
        <div class="container-fluid mt-4">
            <h1 class="text-light text-center">2025 Catseye Data Report</h1>
            <div class="row">
                <div class="col-12">
                    <!-- Total Voters Card -->
                    <div class="row">
                        <div class="col-8">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-voters shadow" id="voterCard" style="cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#municipalityModal">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                                        Voters
                                                        <?php 
                                                        if($mun != "") {
                                                            if($brgyId != "")
                                                            {
                                                                echo " of $brgyName, $mun <br><div class='text-muted' style='font-size:16px;font-weight:600'> <i>" . count($precinct_totals) . ' Precincts</i> </div>';
                                                            }
                                                            else{
                                                                echo " of $mun <br><div class='text-muted' style='font-size:16px;font-weight:600'> <i>" . count($barangay_totals) . ' Barangays</i> </div>';
                                                            }
                                                          
                                                        }
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
                            </div>
                            <div class="col-12">
                                <?php 
                            if(isset($_GET["mun"])) {

                                if(isset($_GET["brgy"])){
                                     ?>
                                <div class="card card-voters shadow mt-2 mb-2">

                                    <div class="mb-3 p-3">
                                        <div class="form-group">
                                            <label>Search Voter</label>
                                            <input type="text" id="searchInput" class="form-control"
                                                placeholder="Search by any column..." onkeyup="filterTable()">
                                        </div>

                                    </div>

                                    <div class="table-responsive" style="max-height: 400px;">
                                        <table class="table table-bordered table-striped" id="voterTable">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>ID</th>
                                                    <th>Full Name</th>
                                                    <th>Gender</th>
                                                    <th>Age</th>
                                                    <th>Municipality</th>
                                                    <th>Barangay</th>
                                                    <th>Precinct</th>
                                                    <th>Leader Check</th>
                                                    <th>Laynes Leader Check</th>
                                                    <th>Warding Check</th>
                                                    <th>Positive Tag Check</th>
                                                    <th>Asanza Post Check</th>
                                                    <th>Abundo Check</th>
                                                    <th>Posoy Check</th>
                                                    <th>Laynes Check</th>
                                                    <th>Rodriguez Check</th>
                                                    <th>Alberto Check</th>
                                                    <th>Hater Check</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt =1;
                // Loop through $all_data and populate the table
                foreach ($all_data as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($cnt) . "</td>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['age']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['municipality']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['barangay']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['precinct']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['leaderCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['laynesLeaderCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['wardingCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tagCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['asanzaPostCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['abundoCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['posoyCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['laynesCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['rodriguezCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['albertoCheck']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['haterCheck']) . "</td>";
                    echo "</tr>";
                    $cnt++;
                }
                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php
                                }
                                else{
   echo '<canvas id="barangayChart" width="400" height="200"></canvas>';
                                }
                            }
                             else{           
                    
                             echo ' <canvas id="municipalityChart" width="400" height="200"></canvas>';
                               }
                            
                            ?>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="row">
                                <div class="col-12">
                                    <div style="width:50%; margin: auto;">
                                        <canvas id="genderChart" width="350" height="170"></canvas>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div style="width:50%; margin: auto;">
                                        <canvas id="ageGroupChart" width="350" height="170"></canvas>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Laynes
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($laynes_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Rodriguez
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($rodriguez_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Alberto
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($alberto_total) ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Undecided
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($undecidedcong_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Boss Te
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($bosste_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Asanza
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($asanza_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Undecided
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($undecidedgov_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Fernandez
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($fernandez_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Abundo
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($abundo_total); ?>
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
                        <div class="card card-voters shadow mb-2" data-bs-toggle="modal"
                            data-bs-target="#householdModal">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Undecided
                                        </div>
                                        <div class="h5 mb-0 fw-bold">
                                            <?php echo number_format($undecidedvice_total); ?>
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
    <!-- Bootstrap & jQuery JS (optional) -->
    <?php 
$data = [
    'gender' => [
        'male' => $male_total,
        'female' => $female_total,
          'no_data' => $no_data
    ],
    'age_groups' => $age_groups
];

// Prepare data for the chart
$barangay_names = json_encode(array_keys($barangay_totals));  // Barangay names
$barangay_counts = json_encode(array_values($barangay_totals));  // Voter counts

// Prepare data for municipality chart
$municipality_names = json_encode(array_keys($municipality_totals));  // Municipality names
$municipality_counts = json_encode(array_values($municipality_totals));

// Prepare data for municipality chart
$precinct_names = json_encode(array_keys($precinct_totals));  // Municipality names
$precinct_counts = json_encode(array_values($precinct_totals));
?>
    <script>
    var voterData = <?php echo json_encode($data); ?>;
    </script>

    <script>
    // Get data from PHP


    var genderData = voterData.gender;
    var ageGroupData = voterData.age_groups;

    // Pie Chart for Gender
    var ctxGender = document.getElementById('genderChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female', 'No Data'],
            datasets: [{
                label: 'Voter Gender Distribution',
                data: [genderData.male, genderData.female, genderData.no_data],
                backgroundColor: ['#3498db', '#e74c3c', '#e67e22'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Voter Gender Distribution'
                }
            }
        }
    });

    // Pie Chart for Age Groups
    var ctxAge = document.getElementById('ageGroupChart').getContext('2d');
    new Chart(ctxAge, {
        type: 'pie',
        data: {
            labels: Object.keys(ageGroupData),
            datasets: [{
                label: 'Voter Age Groups',
                data: Object.values(ageGroupData),
                backgroundColor: ['#3498db', '#1abc9c', '#f1c40f', '#e67e22', '#9b59b6', '#34495e'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Voter Age Group Distribution'
                }
            }
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if barangay canvas exists before rendering
        const ctxBarangayElem = document.getElementById('barangayChart');
        if (ctxBarangayElem) {
            const ctxBarangay = ctxBarangayElem.getContext('2d');
            const barangayNames = <?php echo $barangay_names; ?>;
            const barangayCounts = <?php echo $barangay_counts; ?>;

            // Render Barangay Chart if data is not empty
            if (barangayNames.length > 0 && barangayCounts.length > 0) {
                new Chart(ctxBarangay, {
                    type: 'bar',
                    data: {
                        labels: barangayNames,
                        datasets: [{
                            label: 'Total Voters per Barangay',
                            data: barangayCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        // Check if municipality canvas exists before rendering
        const ctxMunicipalityElem = document.getElementById('municipalityChart');
        if (ctxMunicipalityElem) {
            const ctxMunicipality = ctxMunicipalityElem.getContext('2d');
            const municipalityNames = <?php echo $municipality_names; ?>;
            const municipalityCounts = <?php echo $municipality_counts; ?>;

            // Render Municipality Chart if data is not empty
            if (municipalityNames.length > 0 && municipalityCounts.length > 0) {
                new Chart(ctxMunicipality, {
                    type: 'bar',
                    data: {
                        labels: municipalityNames,
                        datasets: [{
                            label: 'Total Voters per Municipality',
                            data: municipalityCounts,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)', // Green shade
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        const ctPrecinctElem = document.getElementById('precinctChart');
        if (ctPrecinctElem) {
            const ctxPrecinct = ctPrecinctElem.getContext('2d');
            const precinctNames = <?php echo $precinct_names; ?>;
            const precinctCounts = <?php echo $precinct_counts; ?>;

            // Render Municipality Chart if data is not empty
            if (precinctNames.length > 0 && precinctCounts.length > 0) {
                new Chart(ctxPrecinct, {
                    type: 'bar',
                    data: {
                        labels: precinctNames,
                        datasets: [{
                            label: 'Total Voters per Precinct #',
                            data: precinctCounts,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)', // Green shade
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    });
    </script>
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
    </script>



    <script>
    $(document).ready(function() {
        $("#loadingScreen").fadeOut(); // or use .hide()
    });
    // document.addEventListener("DOMContentLoaded", function() {
    //     // Simulate data loading (replace with actual logic if necessary)
    //     setTimeout(function() {
    //         document.getElementById("loadingScreen").style.visibility = "invisible";
    //         document.getElementById("mainContent").style.display = "block"; // Show main content
    //         document.getElementById("mainContent").style.zIndex = "9999"; // Show main content
    //     }, 5000); // Simulating 2 seconds loading, change to actual condition
    // });

    function filterTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("voterTable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows and hide those that don't match
        for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
            tr[i].style.display = "none"; // Hide row initially
            td = tr[i].getElementsByTagName("td");

            // Loop through all columns
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;

                    // Check if any column matches the search query
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = ""; // Show row if a match is found
                        break;
                    }
                }
            }
        }
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>