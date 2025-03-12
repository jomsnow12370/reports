<?php
ini_set('max_execution_time', 0); 
header('Content-type: application/excel');
$filename = $_GET["mun"] . '_' . " REPORT" . '.xls';
header('Content-Disposition: attachment; filename='.$filename);
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- Favicon icon -->

  <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
  <title></title>

  <style type="text/css">
  @media print {
    footer {page-break-after: always;}
  }
  table, th, td {
    border: 1px solid #ddd;
    border-collapse: collapse;
  }
  tr:nth-child(even) {background-color: #f2f2f2;}
  thead{
    text-align: center;
  }
  .text-warning{
    color:orange;
  }
  .text-center{
    text-align: center;
  }
  th{
    padding:30px;
  }
  td{
    padding:30px;
  }
</style>
</head>