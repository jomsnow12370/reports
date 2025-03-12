<?php
include("../conn/conn.php");
include("../f.php");
include("../enye.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];
include("rep_header.php");
?>
<h1><?php echo $mun; ?></h1>

<?php
$brgys = get_array("SELECT barangay FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE municipality = '$mun' GROUP BY barangay");
?>
<table class="table table-bordered">
    <thead>
        <th>#</th>
        <th>Barangay</th>
        <th>Voters</th>
        <th>BOSSTE</th>
        <th>ASANZA</th>
        <th>RODRIGUEZ</th>
        <th>LAYNES</th>
        <th>ALBERTO</th>
        <?php
        if ($mun == "VIRAC") {
            ?>
            <th>GOVCUA</th>
            <th>POSOY</th>
            <?php
        }
        ?>

    </thead>
    <tbody>
        <?php
        $cnt = 1;

        $total_voters_sum = 0;
        $total_bosste_sum = 0;
        $total_asanza_sum = 0;
        $total_rodriguez_sum = 0;
        $total_laynes_sum = 0;
        $total_alberto_sum = 0;

        if ($mun == "VIRAC") {
            $total_govcua_sum = 0;
            $total_posoy_sum = 0;
        }


        foreach ($brgys as $key => $barangay) {
            $brgy = $barangay[0];

            $bosste_supporters = get_value("SELECT COUNT(*) FROM v_info 
    INNER JOIN barangays ON barangays.id = v_info.barangayId 
    INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
    INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
    WHERE municipality = '$mun' AND quick_remarks.category_id = '102' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");

            $asanza_supporters = get_value("SELECT COUNT(*) FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '103' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");

            $laynes_supporters = get_value("SELECT COUNT(*) FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '107' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");

            $rodriguez_supporters = get_value("SELECT COUNT(*) FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '105' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");

            $alberto_supporters = get_value("SELECT COUNT(*) FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '101' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");

            if ($mun == "VIRAC") {
                $govcua_supporters = get_value("SELECT COUNT(*) FROM v_info 
    INNER JOIN barangays ON barangays.id = v_info.barangayId 
    INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
    INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
    WHERE municipality = '$mun' AND quick_remarks.category_id = '104' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");

                $posoy_supporters = get_value("SELECT COUNT(*) FROM v_info 
    INNER JOIN barangays ON barangays.id = v_info.barangayId 
    INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
    INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
    WHERE municipality = '$mun' AND quick_remarks.category_id = '100' AND barangay = '$brgy' AND record_type  = 1 GROUP BY v_remarks.v_id");
            }
            $totalVoters = get_value("SELECT COUNT(*) FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE municipality = '$mun' AND record_type = 1 AND barangay = '$brgy'");

            // Increment the total sums
            $total_voters_sum += $totalVoters[0];
            $total_bosste_sum += $bosste_supporters[0];
            $total_asanza_sum += $asanza_supporters[0];
            $total_rodriguez_sum += $rodriguez_supporters[0];
            $total_laynes_sum += $laynes_supporters[0];
            $total_alberto_sum += $alberto_supporters[0];

            if ($mun == "VIRAC") {
                $total_govcua_sum += $govcua_supporters[0];
                $total_posoy_sum += $posoy_supporters[0];
            }
            ?>
            <tr>
                <td><?php echo $cnt ?></td>
                <td><?php echo $brgy; ?></td>
                <td><?php echo number_format($totalVoters[0]); ?></td>
                <td><?php echo number_format($bosste_supporters[0]); ?></td>
                <td><?php echo number_format($asanza_supporters[0]); ?></td>
                <td><?php echo number_format($rodriguez_supporters[0]); ?></td>
                <td><?php echo number_format($laynes_supporters[0]); ?></td>
                <td><?php echo number_format($alberto_supporters[0]); ?></td>
                <?php
                if ($mun == "VIRAC") {
                    ?>
                    <td><?php echo $govcua_supporters[0]; ?></td>
                    <td><?php echo $posoy_supporters[0]; ?></td>
                    <?php
                }
                ?>
            </tr>
            <?php
            $cnt++;
        }
        ?>
        <!-- Total Row -->
        <tr>
            <td colspan="2"><strong>Total</strong></td>
            <td><strong><?php echo number_format($total_voters_sum)  ?></strong></td>
            <td><strong><?php echo number_format($total_bosste_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_asanza_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_rodriguez_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_laynes_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_alberto_sum); ?></strong></td>
            <?php
            if ($mun == "VIRAC") {
                ?>
                <td><strong><?php echo number_format($total_govcua_sum); ?></strong></td>
                <td><strong><?php echo number_format($total_posoy_sum); ?></strong></td>
                <?php
            }
            ?>

        </tr>
    </tbody>
</table>

<?php
include("rep_footer.php");
?>