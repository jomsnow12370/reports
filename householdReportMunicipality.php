<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warding Summary <?php echo $mun; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style type="text/css">
    @media print {
        footer {
            page-break-after: always;
        }
    }

    .bg-cs {
        color: #4F8A10;
        background-color: #DFF2BF;
        border: solid 1px #4F8A10;
    }

    .bg-cua {
        color: #00529B;
        background-color: #BDE5F8;

    }

    .bg-grabe {
        color: #D8000C;
        background-color: #FFD2D2;
    }

    .canvas {
        margin-left: -120px;
    }

    h5,
    h4,
    h3,
    h2,
    h1 {
        color: #747d8a;
    }
    </style>
</head>

<body>
    <?php 
$summary[] = array();
$total_wl = 0;
$total_bc = 0;
$total_head_of_household = 0;
$total_members = 0;

//cong
$total_laynes = 0;
$total_rodriguez = 0;
$total_alberto = 0;
$total_undecided_cong = 0;

//gov
$total_bosste = 0;
$total_asanza = 0;
$total_undecided_gov = 0;

//vgov
$total_fernandez = 0;
$total_abundo = 0;
$total_undecided_vgov = 0;

$total_op = 0;
$total_na = 0;

// Array to store leader summaries
$leader_summaries = array();
$barangay_summaries = array();
$r = get_array("SELECT 
    leader_v_id,
    v_lname,
    v_fname,
    v_mname,
    purok_st,
    CASE
        WHEN type = 1 THEN 'WL'
        WHEN type = 2 THEN 'BC'
        WHEN type = 3 THEN 'DC'
        WHEN type = 4 THEN 'MC'
    END AS leader_type,
    barangay,
    barangay as brgy,
    (SELECT COUNT(*) from v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 AND barangay = brgy) as totalVoters
FROM
    head_household
        INNER JOIN
    v_info ON v_info.v_id = head_household.leader_v_id
        INNER JOIN
    barangays ON barangays.id = v_info.barangayId
        INNER JOIN
    leaders ON leaders.v_id = head_household.leader_v_id
WHERE
    municipality = '$mun'
    AND record_type = 1
GROUP BY leader_v_id ORDER BY barangay");
$cnt = 1;
foreach ($r as $key => $leader) {
    $leader_id = $leader[0];
    $leader_type = $leader[5];
    $leader_name = $leader[1] . ', ' . $leader[2] . ' ' . $leader[3];
    $leader_barangay = $leader["barangay"];
    $leader_purok = $leader[4];
    $totalVoters = $leader["totalVoters"];

    // Initialize counters for each leader
    $leader_household_count = 0;
    $leader_members_count = 0;

    //cong total by mun
    $leader_laynes_count = 0;
    $leader_rodriguez_count = 0;
    $leader_alberto_count = 0;

    $leader_undecidedcong_count = 0;

    //gov total by mun
    $leader_bosste_count = 0;
    $leader_asanza_count = 0;
    $leader_undecidedgov_count = 0;

    //vgov total by mun
    $leader_fernandez_count = 0;
    $leader_abundo_count = 0;
    $leader_undecidedvgov_count = 0;

    $leader_op = 0;
    $leader_na = 0;

    if ($leader_type == "WL") {
        $total_wl += 1;
    } elseif ($leader_type == "BC") {
        $total_bc += 1;
    }
    $households = get_array("SELECT fh_v_id, fh_v_id as vid, purok_st, v_lname, v_fname, v_mname, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '55' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as cong, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '56' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as gov, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '57' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as vgov,
     (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '52' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as others  
    FROM head_household INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id WHERE leader_v_id = '$leader_id' AND record_type = 1 GROUP BY head_household.fh_v_id ORDER BY purok_st ASC");

    foreach ($households as $key => $household) {
        $hhid = $household[0];
        $hhfullname = $household["v_lname"] . ', ' . $household["v_fname"] . ' ' . $household["v_mname"];

        $total_head_of_household += 1;
        $leader_household_count += 1;

        $members = get_array("SELECT mem_v_id, v_lname, v_fname, v_mname, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '55' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as cong, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '56' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as gov, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '57' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as vgov,
     (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '52' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as others   
    FROM household_warding INNER JOIN v_info ON v_info.v_id = household_warding.mem_v_id WHERE fh_v_id = '$hhid'  AND record_type = 1 GROUP BY household_warding.mem_v_id  ORDER BY v_lname, v_fname, v_mname ");

if ($household["cong"] == "Laynes") {
    $total_laynes += 1;
    $leader_laynes_count += 1;
}

if ($household["cong"] == "Rodriguez") {
    $total_rodriguez += 1;
    $leader_rodriguez_count += 1;
}

if ($household["cong"] == "Alberto") {
    $total_alberto += 1;
    $leader_alberto_count += 1;
}

if ($household["cong"] == "Undecided" || $household["cong"] == "") {
    $total_undecided_cong += 1;
    $leader_undecidedcong_count += 1;
}


if ($household["gov"] == "BossTe") {
    $total_bosste += 1;
    $leader_bosste_count += 1;
}
if ($household["gov"] == "Asanza") {
    $total_asanza += 1;
    $leader_asanza_count += 1;
}
if ($household["gov"] == "Undecided" || $household["gov"] == "") {
    $total_undecided_gov += 1;
    $leader_undecidedgov_count += 1;
}


if ($household["vgov"] == "Fernandez") {
    $total_fernandez += 1;
    $leader_fernandez_count += 1;
}
if ($household["vgov"] == "Abundo") {
    $total_abundo += 1;
    $leader_abundo_count += 1;
}
if ($household["vgov"] == "Undecided" || $household["vgov"] == "") {
    $total_undecided_vgov += 1;
    $leader_undecidedvgov_count += 1;
}

if ($household["others"] == "Outside Province(Household Warding)") {
    $total_op += 1;
    $leader_op += 1;
}

if ($household["others"] == "Needs Assistance(Household Warding)") {
    $total_na += 1;
    $leader_na += 1;
}

        if (!empty($members)) {
            foreach ($members as $key => $member) {
                $memfullname = $member["v_lname"] . ', ' . $member["v_fname"] . ' ' . $member["v_mname"];
                $total_members += 1;
                $leader_members_count += 1;


                if ($member["cong"] == "Laynes") {
                    $total_laynes += 1;
                    $leader_laynes_count += 1;
                }
                if ($member["cong"] == "Rodriguez") {
                    $total_rodriguez += 1;
                    $leader_rodriguez_count += 1;
                }
                if ($member["cong"] == "Alberto") {
                    $total_alberto += 1;
                    $leader_alberto_count += 1;
                }
                if ($member["cong"] == "Undecided" || $member["cong"] == "") {
                    $total_undecided_cong += 1;
                    $leader_undecidedcong_count += 1;
                }
        

                if ($member["gov"] == "BossTe") {
                    $total_bosste += 1;
                    $leader_bosste_count += 1;
                }
                if ($member["gov"] == "Asanza") {
                    $total_asanza += 1;
                    $leader_asanza_count += 1;
                }

                if ($member["gov"] == "Undecided" || $member["gov"] == "") {
                    $total_undecided_gov += 1;
                    $leader_undecidedgov_count += 1;
                }
                
            
                if ($member["vgov"] == "Fernandez") {
                    $total_fernandez += 1;
                    $leader_fernandez_count += 1;
                }
                if ($member["vgov"] == "Abundo") {
                    $total_abundo += 1;
                    $leader_abundo_count += 1;
                }
                if ($member["vgov"] == "Undecided" || $member["vgov"] == "") {
                    $total_undecided_vgov += 1;
                    $leader_undecidedvgov_count += 1;
                }

                if ($member["others"] == "Outside Province(Household Warding)") {
                    $total_op += 1;
                    $leader_op += 1;
                }
        
                if ($member["others"] == "Needs Assistance(Household Warding)") {
                    $total_na += 1;
                    $leader_na += 1;
                }
            }
        }
    }

    // Inside the leader loop, after processing households and members, add this code to update barangay summaries
    if (!isset($barangay_summaries[$leader_barangay])) {
        $barangay_summaries[$leader_barangay] = array(
            'totalVoters' => 0,
            'household_count' => 0,
            'members_count' => 0,
            'laynes_count' => 0,
            'rodriguez_count' => 0,
            'alberto_count' => 0,
            'undecidedcong_count' => 0,
            'bosste_count' => 0,
            'asanza_count' => 0,
            'undecidedgov_count' => 0,
            'fernandez_count' => 0,
            'abundo_count' => 0,
            'undecidedvgov_count' => 0,
            'op'=>0,
            'na'=>0,
            'wl_count' => 0,
            'bc_count' => 0
        );
    }

    // Update barangay totals
    $barangay_summaries[$leader_barangay]['household_count'] += $leader_household_count;
    $barangay_summaries[$leader_barangay]['members_count'] += $leader_members_count;

    $barangay_summaries[$leader_barangay]['laynes_count'] += $leader_laynes_count;
    $barangay_summaries[$leader_barangay]['rodriguez_count'] += $leader_rodriguez_count;
    $barangay_summaries[$leader_barangay]['alberto_count'] += $leader_alberto_count;
    $barangay_summaries[$leader_barangay]['undecidedcong_count'] += $leader_undecidedcong_count;

    $barangay_summaries[$leader_barangay]['bosste_count'] += $leader_bosste_count;
    $barangay_summaries[$leader_barangay]['asanza_count'] += $leader_asanza_count;
    $barangay_summaries[$leader_barangay]['undecidedgov_count'] += $leader_undecidedgov_count;

    $barangay_summaries[$leader_barangay]['fernandez_count'] += $leader_fernandez_count;
    $barangay_summaries[$leader_barangay]['abundo_count'] += $leader_abundo_count;
    $barangay_summaries[$leader_barangay]['undecidedvgov_count'] += $leader_undecidedvgov_count;

    $barangay_summaries[$leader_barangay]['op'] += $leader_op;
    $barangay_summaries[$leader_barangay]['na'] += $leader_na;

    $barangay_summaries[$leader_barangay]['totalVoters'] = $totalVoters;

// Update leader type count
if ($leader_type == "WL") {
    $barangay_summaries[$leader_barangay]['wl_count'] += 1;
} elseif ($leader_type == "BC") {
    $barangay_summaries[$leader_barangay]['bc_count'] += 1;
}
}
?>
    <h2><?php $mun ?></h2>
    <h2>Summary by Barangay - <?php echo $mun; ?></h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">Barangay</th>
                <th rowspan="2">Voters</th>
                <th rowspan="2">WL Leaders</th>
                <th rowspan="2">BC Leaders</th>
                <th rowspan="2">Total Leaders</th>
                <th rowspan="2">Household Total</th>
                <th colspan="4" class="text-center">Congressman</th>
                <th colspan="3" class="text-center">Governor</th>
                <th colspan="3" class="text-center">Vice Governor</th>
                <th colspan="2" class="text-center">Status</th>
            </tr>
            <tr>

                <th>Laynes</th>
                <th>Rodriguez</th>
                <th>Alberto</th>
                <th>Undecided</th>
                <th>BossTe</th>
                <th>Asanza</th>
                <th>Undecided</th>
                <th>Fernandez</th>
                <th>Abundo</th>
                <th>Undecided</th>
                <th>O.P</th>
                <th>N.A</th>
            </tr>
        </thead>
        <tbody>
            <?php
        $cnt = 1;
        $total_wl_leaders = 0;
        $total_bc_leaders = 0;
        $total_leaders = 0;
        $total_voters = 0;
        foreach ($barangay_summaries as $barangay => $data) {
            $total_people = $data['household_count'] + $data['members_count'];
            $total_wl_leaders += $data['wl_count'];
            $total_bc_leaders += $data['bc_count'];
            $total_leaders += $data['wl_count'] + $data['bc_count'];
            $total_voters += $data['totalVoters'];
            echo "<tr>";
            echo "<td>" . $cnt . "</td>";
            echo "<td>" . $barangay. "</td>";
            echo "<td>" . $data['totalVoters'] . "</td>";
            echo "<td>" . $data['wl_count'] . "</td>";
            echo "<td>" . $data['bc_count'] . "</td>";
            echo "<td>" . ($data['wl_count'] + $data['bc_count']) . "</td>";
            echo "<td>" . $total_people . "</td>";

            echo "<td>" . $data['laynes_count'] . "</td>";
            echo "<td>" . $data['rodriguez_count'] . "</td>";
            echo "<td>" . $data['alberto_count'] . "</td>";
            echo "<td>" . $data['undecidedcong_count'] . "</td>";

            echo "<td>" . $data['bosste_count'] . "</td>";
            echo "<td>" . $data['asanza_count'] . "</td>";
            echo "<td>" . $data['undecidedgov_count'] . "</td>";

            echo "<td>" . $data['fernandez_count'] . "</td>";
            echo "<td>" . $data['abundo_count'] . "</td>";
            echo "<td>" . $data['undecidedvgov_count'] . "</td>";
            echo "<td>" . $data['op'] . "</td>";
            echo "<td>" . $data['na'] . "</td>";
            echo "</tr>";
            $cnt++;
        }
        ?>
            <tr>
                <td colspan="2">Total</td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_voters; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_wl_leaders; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_bc_leaders; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_leaders; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900">
                    <?php echo $total_head_of_household + $total_members; ?>
                </td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_laynes; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_rodriguez; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_alberto; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_undecided_cong; ?>
                </td>

                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_bosste; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_asanza; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_undecided_gov; ?>
                </td>

                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_fernandez; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_abundo; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_undecided_vgov; ?>
                </td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_op; ?></td>
                <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_na; ?></td>
            </tr>
        </tbody>
    </table>
    <footer></footer>
</body>

</html>