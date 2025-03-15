<?php
include("conn.php");
include("f.php");
include("enye.php");
ini_set('max_execution_time', 0);
// $mun = $_GET["mun"];
include("rep_header.php");
?>
<h1>Provincewide Tagging Summary</h1>

<?php
$municipality = get_array("SELECT municipality FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId GROUP BY municipality LIMIT 1");
?>
<table class="table table-bordered">
    <thead>
        <th>#</th>
        <th>Municipality</th>
        <th>Voters</th>
        <th>Bosste</th>
        <th>Asanza</th>
        <th>Rodriguez</th>
        <th>Laynes</th>
        <th>Alberto</th>
        <th>GovCua</th>
        <th>Posoy</th>
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


        $total_govcua_sum = 0;
        $total_posoy_sum = 0;

        foreach ($municipality as $k => $muns) {
            $mun = $muns[0];

            $bosste_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
    INNER JOIN barangays ON barangays.id = v_info.barangayId 
    INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
    INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
    WHERE municipality = '$mun' AND quick_remarks.category_id = '102' AND record_type  = 1");

            $asanza_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '103' AND record_type  = 1");

            $laynes_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '107' AND record_type  = 1");

            $rodriguez_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '105' AND record_type  = 1");

            $alberto_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
WHERE municipality = '$mun' AND quick_remarks.category_id = '101' AND record_type  = 1");

            $govcua_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
    INNER JOIN barangays ON barangays.id = v_info.barangayId 
    INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
    INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
    WHERE municipality = '$mun' AND quick_remarks.category_id = '104' AND record_type  = 1");

            $posoy_supporters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info 
    INNER JOIN barangays ON barangays.id = v_info.barangayId 
    INNER JOIN v_remarks ON v_info.v_id = v_remarks.v_id 
    INNER JOIN quick_remarks ON quick_remarks.remarks_id = v_remarks.remarks_id 
    WHERE municipality = '$mun' AND quick_remarks.category_id = '100' AND record_type  = 1");

            $totalVoters = get_value("SELECT COUNT(DISTINCT v_info.v_id)  FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE municipality = '$mun' AND record_type = 1");

            // Increment the total sums
            $total_voters_sum += $totalVoters[0];
            $total_bosste_sum += $bosste_supporters[0];
            $total_asanza_sum += $asanza_supporters[0];
            $total_rodriguez_sum += $rodriguez_supporters[0];
            $total_laynes_sum += $laynes_supporters[0];
            $total_alberto_sum += $alberto_supporters[0];
            $total_govcua_sum += $govcua_supporters[0];
            $total_posoy_sum += $posoy_supporters[0];

            ?>
            <tr>
                <td><?php echo $cnt ?></td>
                <td><?php echo $mun; ?></td>
                <td><?php echo number_format($totalVoters[0]); ?></td>
                <td><?php echo number_format($bosste_supporters[0]); ?></td>
                <td><?php echo number_format($asanza_supporters[0]); ?></td>
                <td><?php echo number_format($rodriguez_supporters[0]); ?></td>
                <td><?php echo number_format($laynes_supporters[0]); ?></td>
                <td><?php echo number_format($alberto_supporters[0]); ?></td>
                <td><?php echo number_format($govcua_supporters[0]); ?></td>
                <td><?php echo number_format($posoy_supporters[0]); ?></td>

            </tr>
            <?php
            $cnt++;
        }
        ?>
        <!-- Total Row -->
        <tr>
            <td colspan="2"><strong>Total</strong></td>
            <td><strong><?php echo number_format($total_voters_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_bosste_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_asanza_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_rodriguez_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_laynes_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_alberto_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_govcua_sum); ?></strong></td>
            <td><strong><?php echo number_format($total_posoy_sum); ?></strong></td>
        </tr>
    </tbody>
</table>

<?php
include("rep_footer.php");
?>