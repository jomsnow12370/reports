<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];
include("rep_header.php");


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
GROUP BY leader_v_id ORDER BY v_lname, v_fname");
$cnt = 1;
foreach ($r as $key => $leader) {
    $leader_id = $leader[0];
    $leader_type = $leader[5];
    $leader_name = $leader[1] . ', ' . $leader[2] . ' ' . $leader[3];
    ?>
    <h3 class="text-center"><strong><?php echo $leader_name; ?></strong> [<?php echo $leader_type; ?>]<br><i
            style="font-size: 12px;"><?php echo $brgy . ',' . $mun; ?></i></h3>

    <?php
    $households = get_array("SELECT fh_v_id, fh_v_id as vid, purok_st, v_lname, v_fname, v_mname, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '55' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as cong, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '56' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as gov, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '57' and v_id = vid ORDER BY v_remarks_id DESC LIMIT 1) as vgov 
    FROM head_household INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id WHERE leader_v_id = '$leader_id' GROUP BY head_household.fh_v_id ORDER BY purok_st ASC");

    foreach ($households as $key => $household) {
        $hhid = $household[0];
        $hhfullname = $household["v_lname"] . ', ' . $household["v_fname"] . ' ' . $household["v_mname"];

        $members = get_array("SELECT mem_v_id, v_lname, v_fname, v_mname, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '55' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as cong, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '56' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as gov, 
    (SELECT shortcut_txt from v_remarks INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id WHERE category_id = '57' and v_id = mem_v_id ORDER BY v_remarks_id DESC LIMIT 1) as vgov 
    FROM household_warding INNER JOIN v_info ON v_info.v_id = household_warding.mem_v_id WHERE fh_v_id = '$hhid' GROUP BY household_warding.mem_v_id  ORDER BY v_lname, v_fname, v_mname ");
        ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Family Head</th>
                    <th>Purok/St</th>
                    <th>Congressman</th>
                    <th>Governor</th>
                    <th>Vice-Governor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?php echo $key + 1; ?>.</strong> <?php echo $hhfullname; ?></td>
                    <td><?php echo $household["purok_st"]; ?></td>
                    <td><?php echo $household["cong"]; ?></td>
                    <td><?php echo $household["gov"]; ?></td>
                    <td><?php echo $household["vgov"]; ?></td>
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
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($members as $key => $member) {
                        $memfullname = $member["v_lname"] . ', ' . $member["v_fname"] . ' ' . $member["v_mname"];
                        ?>
                        <tr>
                            <td><?php echo $key + 1; ?>.</td>
                            <td><?php echo $memfullname; ?></td>
                            <td><?php echo $member["cong"]; ?></td>
                            <td><?php echo $member["gov"]; ?></td>
                            <td><?php echo $member["vgov"]; ?></td>
                        </tr>
                        <?php
                    }
                    ?>

                </tbody>
            </table>
            <?php
        }
    }
    ?>
    <!-- <table class="table table-bordered">

    </table> -->
    <footer></footer>
    <?php
    $cnt++;
}
include("rep_footer.php");
?>