<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];

// Function to get count from survey responses
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

    @media print {
        footer {
            page-break-after: always;
        }
    }
    </style>
</head>

<body class="bg-dark">

    <?php 
    $notWardedBarangays = array();
    $brgyNoWardingLeaders = array();
$brgys = get_array("SELECT * FROM barangays WHERE municipality = '$mun' ORDER BY barangay ASC");
foreach ($brgys as $key => $brgy) {

    $brgyid = $brgy["id"];
    $brgyname = $brgy["barangay"];

    $brgyWardingCheck = get_value("SELECT COUNT(*) from head_household INNER JOIN v_info ON v_info.v_id = head_household.leader_v_id WHERE barangayId = '$brgyid'");
    if($brgyWardingCheck[0] != 0){
      
    ?>
    <!-- <h2><?php echo $brgyname?></h2>
    <h5>Leaders <strong>with</strong> warding</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Leader Name</th>
                <th scope="col">Type</th>
                <th scope="col">Warded FH</th>
                <th scope="col">Warded FM</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $leaderTotal = 0;
            $leaders = get_array("SELECT leaders.v_id, CONCAT_WS(' ',v_lname, v_fname, v_mname) as leadername, type
    FROM leaders
    INNER JOIN v_info ON leaders.v_id = v_info.v_id INNER JOIN head_household ON head_household.leader_v_id = leaders.v_id
    WHERE barangayId = '$brgyid' AND electionyear = 2025 and status is null and type = 1 GROUP by leaders.v_id ORDER BY leadername ASC");
           if(empty($leaders)){
                    echo "<tr><td colspan='5'>No data</td></tr>";
                }else{
          foreach ($leaders as $key => $leader) {
                $leader_id = $leader["v_id"];
           
                ?>
            <tr>
                <td><?php echo $key +1; ?></td>
                <td><?php echo $leader["leadername"]?></td>
                <td><?php echo getLeaderType($leader["type"])?>
                </td>
                <td>
                    <?php 
                    $wardedHH = get_value("SELECT COUNT(*) from head_household WHERE leader_v_id = '$leader_id'")[0];
                    $wardedFM = get_value("SELECT COUNT(*) from household_warding INNER JOIN head_household ON head_household.fh_v_id = household_warding.fh_v_id WHERE leader_v_id = '$leader_id'")[0];

                    echo $wardedHH;
?>
                </td>
                <td><?php echo $wardedFM; ?></td>
            </tr>
            <?php
            }
             
        }
            ?>
        </tbody>
    </table> -->

    <!-- no warding -->
    <h2><?php echo $brgyname?></h2>
    <h5><i>Leaders <strong>without</strong> warding form</i></h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Leader Name</th>
                <th scope="col">Barangay</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $leaders = get_array("SELECT leaders.v_id, CONCAT(v_lname,', ', v_fname, v_mname) as leadername, type
    FROM leaders
    INNER JOIN v_info ON leaders.v_id = v_info.v_id LEFT JOIN head_household ON head_household.leader_v_id = leaders.v_id
    WHERE barangayId = '$brgyid' AND electionyear = 2025 and status is null and head_household.leader_v_id is null and type = 1 GROUP by leaders.v_id ORDER BY leadername ASC");
            foreach ($leaders as $key => $leader) {
             $leaderTotal++;
                ?>
            <tr>
                <td><?php echo $key +1; ?></td>
                <td><?php echo $leader["leadername"]?></td>
                <td><?php echo $brgyname?></td>
                <td><?php echo getLeaderType($leader["type"])?>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    <?php
     array_push($brgyNoWardingLeaders, ["barangay"=>$brgyname, "leadertotal"=>$leaderTotal, "barangayId"=>$brgyid]);
}
else{
array_push($notWardedBarangays, $brgyname);
}
?>

    <?php
   }

?>
    <footer></footer>
    <h1 class="text-bold text-center mb-2"><?php echo $mun ?></h1>
    <h2>Barangays without warding submission</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Barangay</th>
            </tr>
        </thead>
        <tbody>
            <?php
        foreach ($notWardedBarangays as $key => $brgy) {
        ?>
            <tr>
                <td><?php echo $key +1 ;?></td>
                <td><?php echo $brgy?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <h2>Summary</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Barangay</th>
                <th>Total Leaders</th>
                <th>Leaders without warding</th>
            </tr>
        </thead>
        <tbody>
            <?php 
        foreach ($brgyNoWardingLeaders as $key => $brgy_) {
            $brgyid = $brgy_["barangayId"];
            ?>
            <tr>
                <td><?php echo $key +1 ;?></td>
                <td><?php echo $brgy_["barangay"]?></td>
                <td>
                    <?php 
                       $query = "SELECT COUNT(*) from leaders 
              INNER JOIN v_info ON leaders.v_id = v_info.v_id 
              WHERE v_info.record_type = 1 
              AND electionyear = 2025 
              AND status is null 
              AND barangayId = '$brgyid' 
              GROUP by leaders.v_id";
    $result = mysqli_query($c, $query);
    echo mysqli_num_rows($result);
                    ?>
                </td>
                <td><?php echo $brgy_["leadertotal"]?></td>
            </tr>
            <?php
        }
    ?>
        </tbody>
    </table>
    <footer></footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>

</html>