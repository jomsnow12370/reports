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
include("rep_header.php");

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
}

$sql = "SELECT 
    v_info.v_id AS vid,
    CONCAT_WS(' ', v_lname, v_fname, v_mname) AS fullname,
    v_gender,
    YEAR(CURDATE()) - YEAR(v_birthday) AS age,
    municipality,
    barangay,

    -- Check for leader where status and laynes are null
    MAX(CASE WHEN leaders.v_id IS NOT NULL AND status IS NULL AND laynes IS NULL AND leaders.electionyear = 2025 THEN 1 ELSE 0 END) AS leaderCheck,

    -- Check for leader where laynes is not null
    MAX(CASE WHEN leaders.v_id IS NOT NULL AND status IS NULL AND laynes IS NOT NULL AND leaders.electionyear = 2025 THEN 1 ELSE 0 END) AS laynesLeaderCheck,

    -- Check warding membership
    IF(COUNT(wardingtbl.member_v_id) > 0, 1, 0) AS wardingCheck,

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
-- Group to prevent duplicate rows
GROUP BY v_info.v_id

-- Order for better performance on sorted results
ORDER BY barangayId, v_lname, v_fname";

// Gender and Age Group Totals
$male_total = 0;
$female_total = 0;
$no_data =0;

// Barangay total counts
$barangay_totals = [];

$age_groups = [
    '0-25' => 0,
    '26-35' => 0,
    '36-45' => 0,
    '46-55' => 0,
    '56-65' => 0,
    '66+'   => 0
];



$r = get_array($sql);


foreach ($r as $key => $value) {
      $gender = $value['v_gender'];
    $age = $value['age'];

// Count gender
    if ($gender == 'M') {
        $male_total++;
    } elseif ($gender == 'F') {
        $female_total++;
    }
    else{
        $no_data++;
    }

    // Count age groups
    if ($age >= 0 && $age <= 25) {
        $age_groups['0-25']++;
    } elseif ($age >= 26 && $age <= 35) {
        $age_groups['26-35']++;
    } elseif ($age >= 36 && $age <= 45) {
        $age_groups['36-45']++;
    } elseif ($age >= 46 && $age <= 55) {
        $age_groups['46-55']++;
    } elseif ($age >= 56 && $age <= 65) {
        $age_groups['56-65']++;
    } elseif ($age >= 66) {
        $age_groups['66+']++;
    }

    $id = $value["vid"];
    $fullname = $value['fullname'];
    $barangay = $value['barangay'];
    $leaderCheck = $value['leaderCheck'];
    $laynesLeaderCheck = $value['laynesLeaderCheck'];
    $wardingCheck = $value['wardingCheck'];
    $tagCheck = $value['tagCheck'];
    $asanzaPostCheck = $value['asanzaPostCheck'];
    $abundoCheck = $value['abundoCheck'];
    $posoyCheck = $value['posoyCheck'];
    $laynesCheck = $value['laynesCheck'];
    $rodriguezCheck = $value['rodriguezCheck'];
    $albertoCheck = $value['albertoCheck'];
    $haterCheck = $value['haterCheck'];
    

     // Count voters per barangay
    if (!isset($barangay_totals[$barangay])) {
        $barangay_totals[$barangay] = 0;
    }
    $barangay_totals[$barangay]++;

    $supportercheck = $leaderCheck + $laynesLeaderCheck + $wardingCheck + $tagCheck;
    $calculation_cong = $supportercheck - $haterCheck - $rodriguezCheck - $albertoCheck;

    if($calculation_cong > 0) {
    $laynes_total++;
    }
    elseif($calculation_cong <= 0) {
        if($rodriguezCheck > 0) {
            $rodriguez_total++;
        }
        elseif ($albertoCheck >0) {
            $alberto_total++;
        }
        else {
            $undecidedcong_total++;
        }
    }

    $calculation_gov = $supportercheck - $haterCheck - $asanzaPostCheck;
    if($calculation_gov > 0) {
    $bosste_total++;
    }
    elseif($calculation_gov <= 0) {
        if($asanzaPostCheck > 0) {
            $asanza_total++;
        }
        else {
            $undecidedgov_total++;
        }
    }

     $calculation_vgov = $supportercheck - $haterCheck - $abundoCheck;
    if($calculation_vgov > 0) {
    $fernandez_total++;
    }
    elseif($calculation_vgov <= 0) {
        if($abundoCheck > 0) {
            $abundo_total++;
        }
        else {
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

<body class="bg-dark">
    <div class="container-fluid mt-4">
        <h1 class="text-light text-center">2025 Catseye Data Report</h1>
        <div class="row">
            <!-- <div class="col-2">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="text-center">Municipalities</h4>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="col-12">

                <!-- Total Voters Card -->
                <div class="row">
                    <div class="col-8">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-voters shadow">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                                    Voters
                                                    <?php 
                                        if($mun != "") {
                                            echo " of ($mun)";

                                        }
                                    ?>
                                                </div>
                                                <div class="h5 mb-0 fw-bold">
                                                    <?php 
                                       // Get the total number of voters
                                    $total_voters = get_value("SELECT COUNT(*) from v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE v_info.record_type = 1 $munquery");              
                                    echo number_format($total_voters[0]);
                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-users voter-icon"></i>
                                            </div>
                                        </div>
                                    </div> <!-- End of card-body -->
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <canvas id="barangayChart" width="400" height="200"></canvas>
                        </div>

                    </div>

                    <div class="col-4">
                        <div class="row">

                            <div class="col-12">
                                <div style="width: 50%; margin: auto;">
                                    <canvas id="genderChart"></canvas>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="width: 50%; margin: auto;">
                                    <canvas id="ageGroupChart"></canvas>
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
        // Get data from PHP
        const barangayNames = <?php echo $barangay_names; ?>;
        const barangayCounts = <?php echo $barangay_counts; ?>;

        // Create the chart
        const ctx = document.getElementById('barangayChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: barangayNames,
                datasets: [{
                    label: 'Total Voters per Barangay',
                    data: barangayCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Blue shade
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
    });
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>