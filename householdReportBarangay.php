<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];
$brgy = $_GET["brgy"];
include("rep_header.php");


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
    END AS leader_type
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
        AND barangay = '$brgy'
GROUP BY leader_v_id ORDER BY v_lname, v_fname");
$cnt = 1;
foreach ($r as $key => $leader) {
    $leader_id = $leader[0];
    $leader_type = $leader[5];
    $leader_name = $leader[1] . ', ' . $leader[2] . ' ' . $leader[3];
    $leader_purok = $leader[4];

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
    ?>
    <h3 class="text-center"><strong><?php echo $leader_name; ?></strong> [<?php echo $leader_type; ?>]<br><i
            style="font-size: 12px;"><?php echo $brgy . ',' . $mun; ?></i></h3>

    <?php
    $households = get_array("SELECT fh_v_id, fh_v_id as vid, purok_st, v_lname, v_fname, v_mname, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '55' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as cong, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '56' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as gov, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '57' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as vgov,
      (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '52' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as others   
    FROM head_household INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id WHERE leader_v_id = '$leader_id' GROUP BY head_household.fh_v_id ORDER BY purok_st ASC");

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
    FROM household_warding INNER JOIN v_info ON v_info.v_id = household_warding.mem_v_id WHERE fh_v_id = '$hhid' GROUP BY household_warding.mem_v_id  ORDER BY v_lname, v_fname, v_mname ");



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
        ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Family Head</th>
                    <th>Purok/St</th>
                    <th>Congressman</th>
                    <th>Governor</th>
                    <th>Vice-Governor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?php echo $key + 1; ?>.</strong> <?php echo $hhfullname; ?></td>
                    <td><?php echo $household["purok_st"]; ?></td>
                    <td><?php
                    if ($household["cong"] == "") {
                        echo "Undecided";
                    } else {
                        echo $household["cong"];
                    }
                    ?></td>
                    <td><?php
                    if ($household["gov"] == "") {
                        echo "Undecided";
                    } else {
                        echo $household["gov"];
                    } ?>
                    </td>
                    <td><?php
                    if ($household["vgov"] == "") {
                        echo "Undecided";
                    } else {
                        echo $household["vgov"];
                    } ?>
                    </td>
                    <td>
                        <?php
                        if ($household["others"] == "Outside Province(Household Warding)") {
                            echo "O.P";
                        } else if ($household["others"] == "Needs Assistance(Household Warding)") {
                            echo "N.A";
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        if (!empty($members)) {
            ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Family Members</th>
                        <th>Congressman</th>
                        <th>Governor</th>
                        <th>Vice-Governor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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
                        ?>
                        <tr>
                            <td><?php echo $key + 1; ?>.</td>
                            <td><?php echo $memfullname; ?></td>
                            <td><?php
                            if ($member["cong"] == "") {
                                echo "Undecided";
                            } else {
                                echo $member["cong"];
                            } ?>
                            </td>
                            <td><?php
                            if ($member["gov"] == "") {
                                echo "Undecided";
                            } else {
                                echo $member["gov"];
                            } ?>
                            </td>
                            <td><?php
                            if ($member["vgov"] == "") {
                                echo "Undecided";
                            } else {
                                echo $member["vgov"];
                            } ?>
                            </td>

                            <td>
                                <?php
                                if ($member["others"] == "Outside Province(Household Warding)") {
                                    echo "O.P";
                                } else if ($member["others"] == "Needs Assistance(Household Warding)") {
                                    echo "N.A";
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>

                </tbody>
            </table>
            <?php
        }
    }

    // Store leader summary in the array
    $leader_summaries[] = array(
        'name' => $leader_name,
        'purok' => $leader_purok,
        'type' => $leader_type,
        'households' => $leader_household_count,
        'members' => $leader_members_count,
        'total' => $leader_household_count + $leader_members_count,
        'laynes' => $leader_laynes_count,
        'rodriguez' => $leader_rodriguez_count,
        'alberto' => $leader_alberto_count,
        'undecidedcong' => $leader_undecidedcong_count,
        'bosste' => $leader_bosste_count,
        'asanza' => $leader_asanza_count,
        'undecidedgov' => $leader_undecidedgov_count,
        'fernandez' => $leader_fernandez_count,
        'abundo' => $leader_abundo_count,
        'undecidedvgov' => $leader_undecidedvgov_count,
        'op' => $leader_op,
        'na' => $leader_na,
    );

    ?>
    <footer></footer>
    <?php
    $cnt++;
}
?>
<footer></footer>
<h2><?php echo $brgy . ', ', $mun ?></h2>
<table class=" table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Leader Name</th>
            <th>Purok</th>
            <th>Type</th>
            <th>Household Total</th>
            <th>Laynes</th>
            <th>Undecided</th>
            <th>BossTe</th>
            <th>Undecided</th>
            <th>Fernandez</th>
            <th>Undecided</th>
            <th>O.P</th>
            <th>N.A</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $cnt = 1;
        foreach ($leader_summaries as $summary) {
            echo "<tr>";
            echo "<td>" . $cnt . "</td>";
            echo "<td>" . $summary['name'] . "</td>";
            echo "<td>" . $summary['purok'] . "</td>";
            echo "<td>" . $summary['type'] . "</td>";
            echo "<td>" . $summary['total'] . "</td>";
            echo "<td>" . $summary['laynes'] . "</td>";
            echo "<td>" . $summary['undecidedcong'] . "</td>";
            echo "<td>" . $summary['bosste'] . "</td>";
            echo "<td>" . $summary['undecidedgov'] . "</td>";
            echo "<td>" . $summary['fernandez'] . "</td>";
            echo "<td>" . $summary['undecidedvgov'] . "</td>";
            echo "<td>" . $summary['op'] . "</td>";
            echo "<td>" . $summary['na'] . "</td>";
            echo "</tr>";
            $cnt++;
        }
        ?>
        <tr>
            <td colspan="3">Total</td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $cnt - 1; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900">
                <?php echo $total_head_of_household + $total_members; ?>
            </td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_laynes; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_undecided_cong; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_bosste; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_undecided_gov; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_fernandez; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_undecided_vgov; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_op; ?></td>
            <td class="text-danger" style="font-size: 20px;font-weight: 900"><?php echo $total_na; ?></td>
        </tr>
    </tbody>
</table>
<?php
include("rep_footer.php");
?>