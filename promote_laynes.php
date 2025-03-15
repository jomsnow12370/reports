<?php
include("../conn/conn.php");
include("../f.php");
include("../enye.php");
ini_set('max_execution_time', 0);
include("rep_header.php");
function getLeaderType($type)
{
    switch ($type) {
        case 1:
            return "WL";
        case 2:
            return "BC";
        case 3:
            return "DC";
        case 4:
            return "MC";
        default:
            return "??";
    }
}

$arr = array();
$cuaBc = get_array("SELECT leaders.v_id, CONCAT(v_lname, ', ', v_fname, ' ', v_mname) as fullname, barangay, municipality, type from leaders INNER JOIN v_info ON v_info.v_id = leaders.v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE status is null and laynes is not null and electionyear = 2025 and type = 2");
foreach ($cuaBc as $key => $bc) {
    $id = $bc[0];

    $isLaynesWl = get_value("SELECT COUNT(*), id from leaders WHERE status is null and laynes is null and electionyear = 2025 and type = 1 and v_id = '$id'");
    if ($isLaynesWl[0] > 0) {
        //echo $isLaynesWl[1] . $bc[1] . '<br>';
        //   $query += "UPDATE leaders SET type = 2 WHERE id = $isLaynesWl[1];"; 

        qr("UPDATE leaders SET type = 2 WHERE id = $isLaynesWl[1]");
        array_push($arr, ['bcData' => $bc, 'wlData' => $isLaynesWl]);
    }
}
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <td>#</td>
            <td>Name</td>
            <td>Municipality</td>
            <td>Barangay</td>
            <td>Remarks</td>
        </tr>
    </thead>
    <?php
    $count = 1;
    foreach ($arr as $key => $value) {
        $bcData = $value['bcData']; // Extract BC data
        ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $bcData["fullname"]; ?></td>
                <td><?php echo $bcData["municipality"]; ?></td>
                <td><?php echo $bcData["barangay"]; ?></td>
                <td>Promoted to Laynes BC</td>
            </tr>
        <?php
        $count++;
    }
    ?>
</table>
<?php
include("rep_footer.php");
?>