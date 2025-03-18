<?php
include("conn.php");
include("f.php");
ini_set('max_execution_time', 0);
$mun = $_GET["mun"];
include("rep_header.php");

$sql = "SELECT v_id as vid, CONCAT_WS(' ', v_lname, v_fname, v_mname) as fullname, municipality,barangay,

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
        WHEN COUNT(*) > 1 THEN 1 
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
         WHERE v_remarks.v_id = v_info.v_id AND (category_id = 101 or v_remarks.remarks_id = 678)) AS albertoCheck
                          
FROM v_info 
INNER JOIN barangays ON barangays.id = v_info.barangayId 
WHERE municipality = '$mun' 
AND record_type = 1 ORDER BY barangayId, v_lname, v_fname";

$r = get_array($sql);
?>
<table class="table table-bordered" style="font-size:12px">
    <thead>
        <tr>
            <th>#</th>
            <th>Fullname</th>
            <th>Municipality</th>
            <th>Barangay</th>
            <th>Congressman</th>
            <th>Governor</th>
            <th>Vice Governor</th>
            <?php
            if ($mun == "VIRAC") {
                ?>
                <th>Mayor</th>
                <?php
            }
            ?>

            <th>Points</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $cnt = 1;
        foreach ($r as $key => $v) {
            $congressman_judgetment = "";
            $governor_judgetment = "";
            $vicegovernor_judgetment = "";
            $mayor_judgetment = "";
            $vicemayor_judgetment = "";
            $consilor_judgetment = "";

            //supporter
        

            $Cualeader = $v["leaderCheck"];
            $Laynesleader = $v["laynesLeaderCheck"];
            $warding = $v["wardingCheck"];
            $tagging = $v["tagCheck"];
            $laynes = $v["laynesCheck"];
            $household = $v["headHouseholdCheck"];

            //hater
            $hater = $v["haterCheck"];

            //hater gov
            $asanza = $v["asanzaPostCheck"];

            //hatervgov
            $abundo = $v["abundoCheck"];

            //hatercong
            $rodriguez = $v["rodriguezCheck"];
            $alberto = $v["albertoCheck"];

            //hatermayor
            $posoy = $v["posoyCheck"];

            $supporter_points = $Cualeader + $warding + $tagging + $household;
            $hater_points = $abundo + $asanza + $rodriguez + $alberto + $posoy + $hater;

            if ($rodriguez == 1) {
                if ($supporter_points == 0) {
                    $congressman_judgetment = "Rodriguez";
                } else {
                    $congressman_judgetment = "Undecided";
                }
            }
            if ($alberto == 1) {
                if ($supporter_points == 0) {
                    $congressman_judgetment = "Alberto";
                } else {
                    $congressman_judgetment = "Undecided";
                }
            }
            if ($laynes > 0 || $Laynesleader > 0 || $supporter_points > 0) {
                $congressman_judgetment = "Laynes";
            } else {
                $congressman_judgetment = "Undecided";
            }


            //gov
            if ($asanza == 1) {
                if ($supporter_points == 0) {
                    $governor_judgetment = "Asanza";
                } else {
                    $governor_judgetment = "Undecided";
                }
            } else if ($supporter_points > 0) {
                $governor_judgetment = "BossTe";
            } else if ($hater_points > 1) {
                $governor_judgetment = "Asanza";
            } else {
                $governor_judgetment = "Undecided";
            }

            //vgov
            if ($abundo == 1) {
                if ($supporter_points == 0) {
                    $vicegovernor_judgetment = "Abundo";
                } else {
                    $vicegovernor_judgetment = "Undecided";
                }
            } else if ($supporter_points > 0) {
                $vicegovernor_judgetment = "Fernandez";
            } else if ($hater_points > 1) {
                $vicegovernor_judgetment = "Abundo";
            } else {
                $vicegovernor_judgetment = "Undecided";
            }

            //mayor
            if ($posoy == 1) {
                if ($supporter_points == 0) {
                    $mayor_judgetment = "Posoy";
                } else {
                    $mayor_judgetment = "Undecided";
                }
            } else if ($supporter_points > 0) {
                $mayor_judgetment = "Cua";
            } else if ($hater_points > 1) {
                $mayor_judgetment = "Posoy";
            } else {
                $mayor_judgetment = "Undecided";
            }

            ?>
            <tr <?php
            if ($congressman_judgetment === "Laynes" && $governor_judgetment === "BossTe" && $vicegovernor_judgetment === "Fernandez" && $mayor_judgetment === "Cua") {
                echo "class='table-success'";
            }
            ?>>
                <td><?php echo $cnt; ?></td>
                <td><?php echo $v["fullname"]; ?></td>
                <td><?php echo $v["municipality"]; ?></td>
                <td><?php echo $v["barangay"]; ?></td>

                <td <?php if ($congressman_judgetment !== "Undecided" && $congressman_judgetment !== "Laynes") {
                    echo "class='table-danger'";
                } ?>>
                    <?php
                    echo $congressman_judgetment;
                    ?>
                </td>
                <td <?php if ($governor_judgetment !== "Undecided" && $governor_judgetment !== "BossTe") {
                    echo "class='table-danger'";
                } ?>>
                    <?php
                    echo $governor_judgetment;
                    ?>
                </td>
                <td <?php if ($vicegovernor_judgetment !== "Undecided" && $vicegovernor_judgetment !== "Fernandez") {
                    echo "class='table-danger'";
                } ?>>
                    <?php
                    echo $vicegovernor_judgetment;
                    ?>
                </td>
                <?php
                if ($mun == "VIRAC") {
                    ?>
                    <td <?php if ($mayor_judgetment !== "Undecided" && $mayor_judgetment !== "Cua") {
                        echo "class='table-danger'";
                    } ?>>
                        <?php
                        echo $mayor_judgetment;
                        ?>
                    </td>
                    <?php
                }
                ?>
                <td>
                    <?php

                    echo "CuaLeaderPoint: " . $Cualeader . '<br>';
                    echo "LaynesLeaderPoint: " . $Laynesleader . '<br>';
                    echo "WardingPoint: " . $warding . '<br>';
                    echo "LaynesPoint: " . $laynes . '<br>';
                    echo "HouseholdPoint: " . $household . '<br>';
                    echo "TaggingPoint: " . $tagging . '<br>';
                    echo "HaterPoints: " . $hater_points . '<br>';
                    echo "AsanzaPoint: " . $asanza . '<br>';
                    echo "AbundoPoint: " . $abundo . '<br>';
                    echo "RodriguezPoint: " . $rodriguez . '<br>';
                    echo "AlbertoPoint: " . $alberto . '<br>';
                    echo "PosoyPoint: " . $posoy . '<br>';

                    ?>
                </td>
            </tr>
            <?php
            $cnt++;
        }
        ?>
    </tbody>
</table>
<?php
// Add this code before the include("rep_footer.php"); line

// Initialize counters for each category
$summary = [
    'total' => 0,
    'congressman' => [
        'Laynes' => 0,
        'Rodriguez' => 0,
        'Alberto' => 0,
        'Undecided' => 0
    ],
    'governor' => [
        'BossTe' => 0,
        'Asanza' => 0,
        'Undecided' => 0
    ],
    'vicegovernor' => [
        'Fernandez' => 0,
        'Abundo' => 0,
        'Undecided' => 0
    ],
    'mayor' => [
        'Cua' => 0,
        'Posoy' => 0,
        'Undecided' => 0
    ],
    'fullSupport' => 0 // Voters supporting all preferred candidates
];

// Re-execute the query to gather summary data
$r = get_array($sql);
foreach ($r as $v) {
    $summary['total']++;

    // Calculate support points and judgments as in your original code
    $Cualeader = $v["leaderCheck"];
    $Laynesleader = $v["laynesLeaderCheck"];
    $warding = $v["wardingCheck"];
    $tagging = $v["tagCheck"];
    $laynes = $v["laynesCheck"];
    $household = $v["headHouseholdCheck"];
    $hater = $v["haterCheck"];
    $asanza = $v["asanzaPostCheck"];
    $abundo = $v["abundoCheck"];
    $rodriguez = $v["rodriguezCheck"];
    $alberto = $v["albertoCheck"];
    $posoy = $v["posoyCheck"];

    $supporter_points = $Cualeader + $warding + $tagging + $household;
    $hater_points = $abundo + $asanza + $rodriguez + $alberto + $posoy + $hater;

    // Determine congressman support
  
    if ($rodriguez == 1) {
        if ($supporter_points == 0) {
            $congressman_judgetment = "Rodriguez";
        } else {
            $congressman_judgetment = "Undecided";
        }
    }
    if ($alberto == 1) {
        if ($supporter_points == 0) {
            $congressman_judgetment = "Alberto";
        } else {
            $congressman_judgetment = "Undecided";
        }
    }
    if ($laynes > 0 || $Laynesleader > 0 || $supporter_points > 0) {
        $congressman_judgetment = "Laynes";
    } else {
        $congressman_judgetment = "Undecided";
    }



    // Determine governor support
    if ($asanza == 1) {
        if ($supporter_points == 0) {
            $governor_judgetment = "Asanza";
        } else {
            $governor_judgetment = "Undecided";
        }
    } else if ($supporter_points > 0) {
        $governor_judgetment = "BossTe";
    } else if ($hater_points > 1) {
        $governor_judgetment = "Asanza";
    } else {
        $governor_judgetment = "Undecided";
    }

    // Determine vice governor support
    if ($abundo == 1) {
        if ($supporter_points == 0) {
            $vicegovernor_judgetment = "Abundo";
        } else {
            $vicegovernor_judgetment = "Undecided";
        }
    } else if ($supporter_points > 0) {
        $vicegovernor_judgetment = "Fernandez";
    } else if ($hater_points > 1) {
        $vicegovernor_judgetment = "Abundo";
    } else {
        $vicegovernor_judgetment = "Undecided";
    }

    // Determine mayor support
    if ($posoy == 1) {
        if ($supporter_points == 0) {
            $mayor_judgetment = "Posoy";
        } else {
            $mayor_judgetment = "Undecided";
        }
    } else if ($supporter_points > 0) {
        $mayor_judgetment = "Cua";
    } else if ($hater_points > 1) {
        $mayor_judgetment = "Posoy";
    } else {
        $mayor_judgetment = "Undecided";
    }

    // Increment counters
    $summary['congressman'][$congressman_judgetment]++;
    $summary['governor'][$governor_judgetment]++;
    $summary['vicegovernor'][$vicegovernor_judgetment]++;
    $summary['mayor'][$mayor_judgetment]++;

    // Check for full support
    if (
        $congressman_judgetment === "Laynes" &&
        $governor_judgetment === "BossTe" &&
        $vicegovernor_judgetment === "Fernandez" &&
        $mayor_judgetment === "Cua"
    ) {
        $summary['fullSupport']++;
    }
}

// Calculate percentages
$calcPercent = function ($value) use ($summary) {
    return number_format(($value / $summary['total']) * 100, 1) . '%';
};
?>
<footer></footer>
<?php
// Add this code before the include("rep_footer.php"); line
// First, get the unique barangays from the result set
$barangaySummary = [];
$barangayList = [];

// Re-execute the query to gather summary data
$r = get_array($sql);
foreach ($r as $v) {
    $barangay = $v["barangay"];

    // Initialize barangay summary if it doesn't exist
    if (!isset($barangaySummary[$barangay])) {
        $barangaySummary[$barangay] = [
            'total' => 0,
            'congressman' => [
                'Laynes' => 0,
                'Rodriguez' => 0,
                'Alberto' => 0,
                'Undecided' => 0
            ],
            'governor' => [
                'BossTe' => 0,
                'Asanza' => 0,
                'Undecided' => 0
            ],
            'vicegovernor' => [
                'Fernandez' => 0,
                'Abundo' => 0,
                'Undecided' => 0
            ],
            'mayor' => [
                'Cua' => 0,
                'Posoy' => 0,
                'Undecided' => 0
            ],
            'fullSupport' => 0
        ];
        $barangayList[] = $barangay;
    }

    $barangaySummary[$barangay]['total']++;

    // Calculate support points and judgments
    $Cualeader = $v["leaderCheck"];
    $Laynesleader = $v["laynesLeaderCheck"];
    $warding = $v["wardingCheck"];
    $tagging = $v["tagCheck"];
    $laynes = $v["laynesCheck"];
    $household = $v["headHouseholdCheck"];
    $hater = $v["haterCheck"];
    $asanza = $v["asanzaPostCheck"];
    $abundo = $v["abundoCheck"];
    $rodriguez = $v["rodriguezCheck"];
    $alberto = $v["albertoCheck"];
    $posoy = $v["posoyCheck"];

    $supporter_points = $Cualeader + $warding + $tagging + $household;
    $hater_points = $abundo + $asanza + $rodriguez + $alberto + $posoy + $hater;

    // Determine congressman support
 
    if ($rodriguez == 1) {
        if ($supporter_points == 0) {
            $congressman_judgetment = "Rodriguez";
        } else {
            $congressman_judgetment = "Undecided";
        }
    }
    if ($alberto == 1) {
        if ($supporter_points == 0) {
            $congressman_judgetment = "Alberto";
        } else {
            $congressman_judgetment = "Undecided";
        }
    }
    if ($laynes > 0 || $Laynesleader > 0 || $supporter_points > 0) {
        $congressman_judgetment = "Laynes";
    } else {
        $congressman_judgetment = "Undecided";
    }


    // Determine governor support
    if ($asanza == 1) {
        if ($supporter_points == 0) {
            $governor_judgetment = "Asanza";
        } else {
            $governor_judgetment = "Undecided";
        }
    } else if ($supporter_points > 0) {
        $governor_judgetment = "BossTe";
    } else if ($hater_points > 1) {
        $governor_judgetment = "Asanza";
    } else {
        $governor_judgetment = "Undecided";
    }

    // Determine vice governor support
    if ($abundo == 1) {
        if ($supporter_points == 0) {
            $vicegovernor_judgetment = "Abundo";
        } else {
            $vicegovernor_judgetment = "Undecided";
        }
    } else if ($supporter_points > 0) {
        $vicegovernor_judgetment = "Fernandez";
    } else if ($hater_points > 1) {
        $vicegovernor_judgetment = "Abundo";
    } else {
        $vicegovernor_judgetment = "Undecided";
    }

    // Determine mayor support
    if ($posoy == 1) {
        if ($supporter_points == 0) {
            $mayor_judgetment = "Posoy";
        } else {
            $mayor_judgetment = "Undecided";
        }
    } else if ($supporter_points > 0) {
        $mayor_judgetment = "Cua";
    } else if ($hater_points > 1) {
        $mayor_judgetment = "Posoy";
    } else {
        $mayor_judgetment = "Undecided";
    }

    // Increment counters
    $barangaySummary[$barangay]['congressman'][$congressman_judgetment]++;
    $barangaySummary[$barangay]['governor'][$governor_judgetment]++;
    $barangaySummary[$barangay]['vicegovernor'][$vicegovernor_judgetment]++;
    $barangaySummary[$barangay]['mayor'][$mayor_judgetment]++;

    // Check for full support
    if (
        $congressman_judgetment === "Laynes" &&
        $governor_judgetment === "BossTe" &&
        $vicegovernor_judgetment === "Fernandez" &&
        $mayor_judgetment === "Cua"
    ) {
        $barangaySummary[$barangay]['fullSupport']++;
    }
}

// Sort barangays alphabetically
sort($barangayList);

// Function to calculate percentage
$calcBarangayPercent = function ($value, $total) {
    if ($total == 0)
        return "0.0%";
    return number_format(($value / $total) * 100, 1) . '%';
};
?>

<!-- Barangay Level Summary -->
<h3 class="mt-5 text-center">Barangay Level Analysis</h3>

<!-- Barangay Summary Table -->
<table class="table table-bordered table-sm" style="font-size:12px">
    <thead class="thead-light">
        <tr>
            <th rowspan="2">Barangay</th>
            <th rowspan="2">Total Voters</th>
            <th colspan="2">Full Support</th>
            <th colspan="2">Laynes</th>
            <th colspan="2">BossTe</th>
            <th colspan="2">Fernandez</th>
            <?php if ($mun == "VIRAC") { ?>
                <th colspan="2">Cua</th>
            <?php } ?>
        </tr>
        <tr>
            <th>Count</th>
            <th>%</th>
            <th>Count</th>
            <th>%</th>
            <th>Count</th>
            <th>%</th>
            <th>Count</th>
            <th>%</th>
            <?php if ($mun == "VIRAC") { ?>
                <th>Count</th>
                <th>%</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($barangayList as $barangay) {
            $brgy = $barangaySummary[$barangay];
            $total = $brgy['total'];
            ?>
            <tr>
                <td><?php echo $barangay; ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $brgy['fullSupport']; ?></td>
                <td><?php echo $calcBarangayPercent($brgy['fullSupport'], $total); ?></td>
                <td><?php echo $brgy['congressman']['Laynes']; ?></td>
                <td><?php echo $calcBarangayPercent($brgy['congressman']['Laynes'], $total); ?></td>
                <td><?php echo $brgy['governor']['BossTe']; ?></td>
                <td><?php echo $calcBarangayPercent($brgy['governor']['BossTe'], $total); ?></td>
                <td><?php echo $brgy['vicegovernor']['Fernandez']; ?></td>
                <td><?php echo $calcBarangayPercent($brgy['vicegovernor']['Fernandez'], $total); ?></td>
                <?php if ($mun == "VIRAC") { ?>
                    <td><?php echo $brgy['mayor']['Cua']; ?></td>
                    <td><?php echo $calcBarangayPercent($brgy['mayor']['Cua'], $total); ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Detailed Barangay Tabs -->
<div class="mt-5">
    <ul class="nav nav-tabs" id="barangayTabs" role="tablist">
        <?php foreach ($barangayList as $index => $barangay) { ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($index == 0) ? 'active' : ''; ?>"
                    id="<?php echo str_replace(' ', '-', $barangay); ?>-tab" data-toggle="tab"
                    href="#<?php echo str_replace(' ', '-', $barangay); ?>" role="tab">
                    <?php echo $barangay; ?>
                </a>
            </li>
        <?php } ?>
    </ul>

    <div class="tab-content" id="barangayTabContent">
        <?php foreach ($barangayList as $index => $barangay) {
            $brgy = $barangaySummary[$barangay];
            $total = $brgy['total'];
            ?>
            <div class="tab-pane fade <?php echo ($index == 0) ? 'show active' : ''; ?>"
                id="<?php echo str_replace(' ', '-', $barangay); ?>" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h5><?php echo $barangay; ?> Summary</h5>
                        <table class="table table-bordered table-sm">
                            <tr>
                                <td>Total Voters</td>
                                <td><?php echo $total; ?></td>
                            </tr>
                            <tr>
                                <td>Full Support</td>
                                <td><?php echo $brgy['fullSupport']; ?>
                                    (<?php echo $calcBarangayPercent($brgy['fullSupport'], $total); ?>)</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <!-- Congressman -->
                    <div class="col-md-3">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th colspan="3">Congressman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td>Laynes</td>
                                    <td><?php echo $brgy['congressman']['Laynes']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['congressman']['Laynes'], $total); ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <td>Rodriguez</td>
                                    <td><?php echo $brgy['congressman']['Rodriguez']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['congressman']['Rodriguez'], $total); ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <td>Alberto</td>
                                    <td><?php echo $brgy['congressman']['Alberto']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['congressman']['Alberto'], $total); ?></td>
                                </tr>
                                <tr>
                                    <td>Undecided</td>
                                    <td><?php echo $brgy['congressman']['Undecided']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['congressman']['Undecided'], $total); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Governor -->
                    <div class="col-md-3">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th colspan="3">Governor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td>BossTe</td>
                                    <td><?php echo $brgy['governor']['BossTe']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['governor']['BossTe'], $total); ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <td>Asanza</td>
                                    <td><?php echo $brgy['governor']['Asanza']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['governor']['Asanza'], $total); ?></td>
                                </tr>
                                <tr>
                                    <td>Undecided</td>
                                    <td><?php echo $brgy['governor']['Undecided']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['governor']['Undecided'], $total); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vice Governor -->
                    <div class="col-md-3">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th colspan="3">Vice Governor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td>Fernandez</td>
                                    <td><?php echo $brgy['vicegovernor']['Fernandez']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['vicegovernor']['Fernandez'], $total); ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <td>Abundo</td>
                                    <td><?php echo $brgy['vicegovernor']['Abundo']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['vicegovernor']['Abundo'], $total); ?></td>
                                </tr>
                                <tr>
                                    <td>Undecided</td>
                                    <td><?php echo $brgy['vicegovernor']['Undecided']; ?></td>
                                    <td><?php echo $calcBarangayPercent($brgy['vicegovernor']['Undecided'], $total); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mayor (only for VIRAC) -->
                    <?php if ($mun == "VIRAC") { ?>
                        <div class="col-md-3">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="3">Mayor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-success">
                                        <td>Cua</td>
                                        <td><?php echo $brgy['mayor']['Cua']; ?></td>
                                        <td><?php echo $calcBarangayPercent($brgy['mayor']['Cua'], $total); ?></td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td>Posoy</td>
                                        <td><?php echo $brgy['mayor']['Posoy']; ?></td>
                                        <td><?php echo $calcBarangayPercent($brgy['mayor']['Posoy'], $total); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Undecided</td>
                                        <td><?php echo $brgy['mayor']['Undecided']; ?></td>
                                        <td><?php echo $calcBarangayPercent($brgy['mayor']['Undecided'], $total); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<footer></footer>
<!-- Summary Table -->
<h3 class="mt-4 text-center">Voter Analysis Summary</h3>
<div class="row">
    <div class="col-md-6">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th colspan="3">Summary for <?php echo $mun; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Voters</td>
                    <td colspan="2"><?php echo $summary['total']; ?></td>
                </tr>
                <tr>
                    <td>Full Support (All Preferred Candidates)</td>
                    <td><?php echo $summary['fullSupport']; ?></td>
                    <td><?php echo $calcPercent($summary['fullSupport']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <!-- Congressman -->
    <div class="col-md-3">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr class="text-center">
                    <th colspan="3" class="text-center">Congressman</th>
                </tr>
                <tr>
                    <th>Candidate</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <td>Laynes</td>
                    <td><?php echo $summary['congressman']['Laynes']; ?></td>
                    <td><?php echo $calcPercent($summary['congressman']['Laynes']); ?></td>
                </tr>
                <tr class="table-danger">
                    <td>Rodriguez</td>
                    <td><?php echo $summary['congressman']['Rodriguez']; ?></td>
                    <td><?php echo $calcPercent($summary['congressman']['Rodriguez']); ?></td>
                </tr>
                <tr class="table-danger">
                    <td>Alberto</td>
                    <td><?php echo $summary['congressman']['Alberto']; ?></td>
                    <td><?php echo $calcPercent($summary['congressman']['Alberto']); ?></td>
                </tr>
                <tr>
                    <td>Undecided</td>
                    <td><?php echo $summary['congressman']['Undecided']; ?></td>
                    <td><?php echo $calcPercent($summary['congressman']['Undecided']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Governor -->
    <div class="col-md-3">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th colspan="3" class="text-center">Governor</th>
                </tr>
                <tr>
                    <th>Candidate</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <td>BossTe</td>
                    <td><?php echo $summary['governor']['BossTe']; ?></td>
                    <td><?php echo $calcPercent($summary['governor']['BossTe']); ?></td>
                </tr>
                <tr class="table-danger">
                    <td>Asanza</td>
                    <td><?php echo $summary['governor']['Asanza']; ?></td>
                    <td><?php echo $calcPercent($summary['governor']['Asanza']); ?></td>
                </tr>
                <tr>
                    <td>Undecided</td>
                    <td><?php echo $summary['governor']['Undecided']; ?></td>
                    <td><?php echo $calcPercent($summary['governor']['Undecided']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Vice Governor -->
    <div class="col-md-3">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th colspan="3" class="text-center">Vice Governor</th>
                </tr>
                <tr>
                    <th>Candidate</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <td>Fernandez</td>
                    <td><?php echo $summary['vicegovernor']['Fernandez']; ?></td>
                    <td><?php echo $calcPercent($summary['vicegovernor']['Fernandez']); ?></td>
                </tr>
                <tr class="table-danger">
                    <td>Abundo</td>
                    <td><?php echo $summary['vicegovernor']['Abundo']; ?></td>
                    <td><?php echo $calcPercent($summary['vicegovernor']['Abundo']); ?></td>
                </tr>
                <tr>
                    <td>Undecided</td>
                    <td><?php echo $summary['vicegovernor']['Undecided']; ?></td>
                    <td><?php echo $calcPercent($summary['vicegovernor']['Undecided']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mayor -->
    <?php
    if ($mun === "VIRAC") {
        ?>
        <div class="col-md-3">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th colspan="3" class="text-center">Mayor</th>
                    </tr>
                    <tr>
                        <th>Candidate</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-success">
                        <td>Cua</td>
                        <td><?php echo $summary['mayor']['Cua']; ?></td>
                        <td><?php echo $calcPercent($summary['mayor']['Cua']); ?></td>
                    </tr>
                    <tr class="table-danger">
                        <td>Posoy</td>
                        <td><?php echo $summary['mayor']['Posoy']; ?></td>
                        <td><?php echo $calcPercent($summary['mayor']['Posoy']); ?></td>
                    </tr>
                    <tr>
                        <td>Undecided</td>
                        <td><?php echo $summary['mayor']['Undecided']; ?></td>
                        <td><?php echo $calcPercent($summary['mayor']['Undecided']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>
</div>

<?php
include("rep_footer.php");
?>