<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];
$brgy = $_GET["brgy"];
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
    <?php 
$r = get_array("SELECT v_id as vid, CONCAT_WS(' ', v_lname, v_fname, v_mname) as fullname, municipality,barangay,

EXISTS(SELECT 1 FROM leaders WHERE v_id = vid AND status is null and laynes is null) as leaderCheck,

EXISTS(SELECT 1 FROM leaders WHERE v_id = vid AND status is null and laynes is not null) as laynesLeaderCheck,

(SELECT CASE 
        WHEN COUNT(*) > 1 THEN 1 
         ELSE 0 
        END 
         FROM wardingtbl 
         WHERE member_v_id = v_info.v_id
       ) AS wardingCheck,

(SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND (category_id = 104 or category_id = 102)) AS tagCheck,

(SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE v_remarks.v_id = v_info.v_id AND category_id = 106) AS haterCheck,

(SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM household_warding 
         WHERE (fh_v_id = v_info.v_id or mem_v_id = v_info.v_id)) AS headHouseholdCheck,

(SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND (category_id = 103 or v_remarks.remarks_id = 663))  AS asanzaPostCheck,

         (SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND category_id = 57) AS abundoCheck,
         
            (SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND category_id = 100)  AS posoyCheck,

            (SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND (category_id = 107 or v_remarks.remarks_id = 660)) AS laynesCheck,

            (SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND (category_id = 105 or v_remarks.remarks_id = 661)) AS rodriguezCheck,

            (SELECT CASE 
        WHEN COUNT(*) > 0 THEN 1 
         ELSE 0 
        END 
         FROM v_remarks 
         INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
         WHERE v_remarks.v_id = v_info.v_id AND (category_id = 101 or v_remarks.remarks_id = 678)) AS albertoCheck,

     
    (SELECT shortcut_txt 
     FROM v_remarks 
     INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
     WHERE category_id = '55' AND v_remarks.v_id = vid
     ORDER BY v_remarks_id DESC LIMIT 1) as cong, 

    (SELECT shortcut_txt 
     FROM v_remarks 
     INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
     WHERE category_id = '56' AND v_remarks.v_id = vid
     ORDER BY v_remarks_id DESC LIMIT 1) as gov,

       (SELECT shortcut_txt 
     FROM v_remarks 
     INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
     WHERE category_id = '57' AND v_remarks.v_id = vid
     ORDER BY v_remarks_id DESC LIMIT 1) as vgov 
                          
FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
WHERE municipality = '$mun'
AND record_type = 1 ORDER BY barangayId, v_lname, v_fname, v_mname");
?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Fullname</th>
                <th>Municipality</th>
                <th>Barangay</th>
                <th>Congressman</th>
                <th>Governor</th>
                <th>Vice Governor</th>
                <th>Checking</th>
            </tr>
        </thead>
        <tbody>
            <?php
foreach ($r as $key => $value) {
    $v_id = $value['vid'];
    $fullname = $value['fullname'];
    $barangay = $value['barangay'];     
    $municipality = $value['municipality'];
    $leaderCheck = $value['leaderCheck'];
    $laynesLeaderCheck = $value['laynesLeaderCheck'];
    $wardingCheck = $value['wardingCheck'];
    $tagCheck = $value['tagCheck'];
    $haterCheck = $value['haterCheck'];
    $headHouseholdCheck = $value['headHouseholdCheck'];
  
    $posoyCheck = $value['posoyCheck'];
   
    $laynesCheck = $value['laynesCheck'];
    $rodriguezCheck = $value['rodriguezCheck'];
    $albertoCheck = $value['albertoCheck'];


    $abundoCheck = $value['abundoCheck'];
    $asanzaPostCheck = $value['asanzaPostCheck'];

    $congWarding = $value["cong"];
    $govWarding = $value["gov"];
    $vgovWarding = $value["vgov"];

    $congressman_judgement = ""; 
    $governor_judgement = ""; 
    $vicegovernor_judgement = ""; 

    if($leaderCheck > 0 || $laynesLeaderCheck > 0 || $tagCheck >  0){
    $congressman_judgement = "Laynes=Leader&Tag";
    }else{
        if($congWarding == "Rodriguez"){
                $congressman_judgement = "Rodriguez=Leader&Tag";
        }
        else if($congWarding == "Alberto"){
                $congressman_judgement = "Alberto=Warding";
        }
            else if($congWarding == "Laynes"){
                $congressman_judgement = "Laynes=Warding";
        }
            else if($congWarding == "" || $congWarding == "Undecided"){
                $congressman_judgement = "Undecided=Warding";
        }
    }

      if($leaderCheck > 0 || $laynesLeaderCheck > 0 || $tagCheck >  0){
    $governor_judgement = "BossTe=Leader&Tag";
    }else{
        if($govWarding == "Asanza"){
                $governor_judgement = "Asanza=Leader&Tag";
        }
        else if($govWarding == "BossTe"){
                $governor_judgement = "BossTe=Warding";
        }
            else if($govWarding == "" || $govWarding == "Undecided"){
                $governor_judgement = "Undecided=Warding";
        }
    }

       if($leaderCheck > 0 || $laynesLeaderCheck > 0 || $tagCheck >  0){
    $vicegovernor_judgement = "Fernandez=Leader&Tag";
    }else{
        if($vgovWarding == "Abundo"){
                $vicegovernor_judgement = "Abundo=Leader&Tag";
        }
        else if($vgovWarding == "Fernandez"){
                $vicegovernor_judgement = "Fernandez=Warding";
        }
            else if($vgovWarding == "" || $vgovWarding == "Undecided"){
                $vicegovernor_judgement = "Undecided=Warding";
        }
    }
    ?>
            <tr>
                <td><?php echo $key+1; ?></td>
                <td><?php echo $fullname?></td>
                <td><?php echo $municipality?></td>
                <td><?php echo $barangay?></td>
                <td>
                    <!-- if leader > 0 || leaderlaynes > 0 then  cong = laynes
    else if wardingchceck > 0 and hater = 0 then cong = laynes
    if wardingcheck = 0 and rodriguez > 0 then cong = rodriguez
    if wardingcheck = 0 and alberto > 0 then cong = alberto
    if wardingcheck = 0 and laynes > 0 then cong = laynes -->
                    <?php
                    echo $congressman_judgement;
                    ?>
                </td>
                <td>
                    <?php
                    echo $governor_judgement;
                    ?>
                </td>
                <td>
                    <?php
                    echo $vicegovernor_judgement;
                    ?>
                </td>
                <td style="font-size:8px">
                    <?php 
                echo "LeaderCheck: " . $leaderCheck . "<br>"  
                . "LaynesLeaderCheck: " . $laynesLeaderCheck . "<br>" 
                . "WardingCheck: " . $wardingCheck . "<br>"
                . "TagCheck: " . $tagCheck . "<br>"
                . "HaterCheck: " . $haterCheck . "<br>"
                . "HeadHouseholdCheck: " . $headHouseholdCheck . "<br>"
                . "AsanzaPostCheck: " . $asanzaPostCheck . "<br>"
                . "AbundoCheck: " . $abundoCheck . "<br>"
                . "PosoyCheck: " . $posoyCheck . "<br>"
                . "LaynesCheck: " . $laynesCheck . "<br>"
                . "RodriguezCheck: " . $rodriguezCheck . "<br>"
                . "AlbertoCheck: " . $albertoCheck . "<br>";

                echo "Cong". $value["cong"] . "<br>";
                  ?>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    <!-- 
    cong
    if leader > 0 || leaderlaynes > 0 then  cong = laynes
    else if wardingchceck > 0 and hater = 0 then cong = laynes
    if wardingcheck = 0 and rodriguez > 0 then cong = rodriguez
    if wardingcheck = 0 and alberto > 0 then cong = alberto
    if wardingcheck = 0 and laynes > 0 then cong = laynes


    else{
     wardingCheck > 1 and hater = 0 then cong = laynes 
     else {
     cong = UNDECIDED
     }
}   
    -->
</body>

</html>