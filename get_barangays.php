<?php
// Include your database connection file
include("conn.php");
include("f.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['municipality'])) {
    $municipality = $_POST['municipality'];

    // Sanitize the input to prevent SQL injection
    $municipality = mysqli_real_escape_string($c, $municipality);

    // Query to fetch barangays based on selected municipality
    $query = "SELECT id, barangay FROM barangays WHERE municipality = '$municipality' ORDER BY barangay ASC";
    $result = mysqli_query($c, $query);

    $barangays = array();

    // Fetch and add results to the array
    while ($row = mysqli_fetch_assoc($result)) {
        $barangays[] = $row;
    }

    // Return results as JSON
    echo json_encode($barangays);
}
?>