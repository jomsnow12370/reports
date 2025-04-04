<?php
// Include your database connection
require_once 'conn.php';
require_once 'f.php';
// Get the lastname from the request
$lastname = isset($_GET['lastname']) ? $_GET['lastname'] : '';
$munquery = isset($_GET['munquery']) ? $_GET['munquery'] : '';
$brgyquery = isset($_GET['brgyquery']) ? $_GET['brgyquery'] : '';
// // Sanitize the input to prevent SQL injection
$lastname = mysqli_real_escape_string($c, $lastname);

// // Now you can use this PHP variable to query your database
$query = get_array("SELECT municipality, barangay, COUNT(*) as cnt from v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 AND v_lname = '$lastname' $munquery GROUP BY barangay ORDER BY COUNT(*) DESC");
// $result = mysqli_query($c, $query);
//echo $munquery . '<br>' . $brgyquery;

if($brgyquery == ""){
?>
<div class="table-responsive" style="max-height:60vh">
    <table class="table table-bordered">
        <thead>
            <th>#</th>
            <th>Municipality</th>
            <th>Barangay</th>
            <th>Total</th>
            <th>Warded</th>
        </thead>
        <tbody>
            <?php
foreach ($query as $key => $value) {
     $brgy = $value["barangay"];
     $mun = $value["municipality"];
    
     $mnames = get_value("SELECT COUNT(*) AS cnt FROM v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 AND v_mname = '$lastname' $munquery AND barangay = '$brgy'")[0];
     $household = get_value("SELECT COUNT(*) from head_household INNER JOIN v_info ON v_info.v_id = head_household.fh_v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE (TRIM(v_lname) = '$lastname' OR TRIM(v_mname) = '$lastname') and record_type = 1 $munquery AND barangay = '$brgy'")[0];
     $members = get_value("SELECT COUNT(*) from household_warding INNER JOIN v_info ON v_info.v_id = household_warding.mem_v_id INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE (TRIM(v_lname) = '$lastname' OR TRIM(v_mname) = '$lastname') and record_type = 1 $munquery AND barangay = '$brgy'")[0];
                        
    ?>
            <tr>
                <td>
                    <?php echo $key + 1;?>
                </td>
                <td>
                    <?php echo $value["municipality"];?>
                </td>
                <td>
                    <?php echo $value["barangay"];?>
                </td>
                <td><?php echo $value["cnt"] + $mnames;?></td>
                <td><?php echo $household + $members?></td>
            </tr>
            <?php
    }
    ?>
        </tbody>
    </table>
</div>
<?php
}
else{
    ?>
<div class="table-responsive" style="max-height:60vh">
    <table class="table table-bordered">
        <thead>
            <th>#</th>
            <th>Fullname</th>
            <th>Birthday</th>
            <th>Gender</th>
            <th>Facebook</th>
            <th>Warding</th>
        </thead>
        <tbody>
            <?php 
        $voters = get_array("SELECT v_info.v_id, CONCAT(v_lname,', ', v_fname, ' ', v_mname) as fullname, v_birthday, v_gender from v_info INNER JOIN barangays ON barangays.id = v_info.barangayId WHERE record_type = 1 AND (v_lname = '$lastname' OR v_mname = '$lastname') $munquery $brgyquery ORDER BY v_lname, v_mname");
        foreach ($voters as $key => $value) {
            $id = $value["v_id"];
            ?>
            <tr>
                <td><?php echo $key + 1 ?></td>
                <td><?php echo $value["fullname"]?></td>
                <td>
                    <?php echo $value["v_birthday"]?>
                </td>
                <td>
                    <?php 
                    if($value["v_gender"] != ""){
                        echo $value["v_gender"];
                    }else{
                        echo "No Data";
                    }
                    ?>
                </td>
                <td width="100px">
                    <?php 
                    $fb = get_value("SELECT facebook_id, nofb, locked, inactive from facebook WHERE v_id = '$id'");
                    ?>
                    <a href="<?php echo $fb[0]; ?>" target="_blank">Open</a>
                    <?php

                    if(!empty($fb)){
                        if($fb["nofb"] == 1){
                           echo "<span class='badge bg-danger'>INACTIVE</span>";
                        }
                        else if($fb["locked"] == 1){
                            echo "<span class='badge bg-danger'>LOCKED</span>";
                        }
                        else if($fb["inactive"] == 1){
                            echo "<span class='badge bg-warning'>INACTIVE</span>";
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    $head = get_value("SELECT COUNT(*) from household_warding WHERE fh_v_id = '$id'");
                    $mem = get_value("SELECT COUNT(*) from household_warding WHERE mem_v_id = '$id'");
                    if($head[0] > 0 || $mem[0] > 0){
                         echo "<span class='badge bg-success'>Warded</span>";
                    }
                    else{
                         echo "<span class='badge bg-danger'>Not Warded</span>";
                    }
                    
                    ?>
                </td>
            </tr>
            <?php
        }
      ?>
        </tbody>
    </table>

</div>
<?php
}
 mysqli_close($c);
?>