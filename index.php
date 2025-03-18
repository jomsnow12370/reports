<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Voters Card - Dark Mode</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .card-voters {
            border-left: 4px solid #6ea8fe;
            transition: transform 0.3s;
            cursor: pointer;
        }
        .card-voters:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .voter-icon {
            color: #6ea8fe;
            opacity: 0.7;
            font-size: 2.5rem;
        }
        .category-pill {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            margin-right: 0.3rem;
            border-radius: 12px;
        }
        .mc-pill {
            background-color: #6ea8fe;
            color: #212529;
        }
        .dc-pill {
            background-color: #ea868f;
            color: #212529;
        }
        .bc-pill {
            background-color: #20c997;
            color: #212529;
        }
        .wl-pill {
            background-color: #ffc107;
            color: #212529;
        }
        .category-divider {
            opacity: 0.2;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="container mt-4">
        <h1 class="text-center text-light">2025 Election Dashboard</h1>    
        <!-- Total Voters Card -->
        <div class="row">
            <div class="col-lg-4 mb-4">
            <div class="card card-voters shadow" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Voters
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($total_households); ?>
                                </div>
                                <div class="mt-2 text-xs text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <span>1.8% increase since last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-home voter-icon"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                        <hr class="category-divider">
                        <div class="row mt-2">
                            <div class="col-12">
                                <select id="municipalityDropdown" class="form-select">
                                    <option value="">Select Municipality</option>
                                    <option value="1">Municipality 1</option>
                                    <option value="2">Municipality 2</option>
                                    <option value="3">Municipality 3</option>
                                    <!-- Add more municipalities as needed -->
                                </select>
                                <div id="municipalityVoters" class="mt-2 text-light"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card card-voters shadow" data-bs-toggle="modal" data-bs-target="#leadersModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Leaders
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($total_leaders); ?>
                                </div>
                                <div class="mt-2 text-xs text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <span>3.5% increase since last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users voter-icon"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                        <hr class="category-divider">
                        <div class="row mt-2">
                            <div class="col-12">
                            
                                <div class="d-flex flex-wrap mb-2">
                                    <div class="category-pill mc-pill me-2 mb-1">
                                        MC: <?php echo number_format($mc_count); ?>
                                    </div>
                                    <div class="category-pill dc-pill me-2 mb-1">
                                        DC: <?php echo number_format($dc_count); ?>
                                    </div>
                                    <div class="category-pill bc-pill me-2 mb-1">
                                        BC: <?php echo number_format($bc_count); ?>
                                    </div>
                                    <div class="category-pill wl-pill me-2 mb-1">
                                        WL: <?php echo number_format($wl_count); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card card-voters shadow" data-bs-toggle="modal" data-bs-target="#householdModal">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    Household Warding
                                </div>
                                <div class="h5 mb-0 fw-bold">
                                    <?php echo number_format($total_households); ?>
                                </div>
                                <div class="mt-2 text-xs text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    <span>1.8% increase since last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-home voter-icon"></i>
                            </div>
                        </div>
                        <!-- Category Breakdown -->
                        <hr class="category-divider">
                        <div class="row mt-2">
                            <div class="col-12">
                            
                                <div class="d-flex flex-wrap mb-2">
                                    <div class="category-pill mc-pill me-2 mb-1">
                                        Household Head: <?php echo number_format($household_head_count); ?>
                                    </div>
                                    <div class="category-pill dc-pill me-2 mb-1">
                                        Household Members: <?php echo number_format($household_members_count); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Voters Modal -->
    <div class="modal fade" id="votersModal" tabindex="-1" aria-labelledby="votersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="votersModalLabel">Voters Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add content for voters details here -->
                    <p>Total Voters: <?php echo number_format($total_voters); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaders Modal -->
    <div class="modal fade" id="leadersModal" tabindex="-1" aria-labelledby="leadersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadersModalLabel">Leaders Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add content for leaders details here -->
                    <p>Total Leaders: <?php echo number_format($total_leaders); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Household Warding Modal -->
    <div class="modal fade" id="householdModal" tabindex="-1" aria-labelledby="householdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="householdModalLabel">Household Warding Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add content for household warding details here -->
                    <p>Total Households: <?php echo number_format($total_households); ?></p>
                    <p>Household Head: <?php echo number_format($household_head_count); ?></p>
                    <p>Household Members: <?php echo number_format($household_members_count); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP implementation example -->
    <?php
    // Example of how to implement this in your PHP code
    
    // Database connection
    // $conn = mysqli_connect("localhost", "username", "password", "database");
    
    // Query to get total voters
    // $query = "SELECT COUNT(*) as total FROM voters";
    // $result = mysqli_query($conn, $query);
    // $row = mysqli_fetch_assoc($result);
    // $total_voters = $row['total'];
    
    // Example static value (replace with actual database query)
    $total_voters = 15782;
    $total_leaders = 1234;
    $total_households = 5678;
    $household_head_count = 1234;
    $household_members_count = 4444;
    $mc_count = 400;
    $dc_count = 300;
    $bc_count = 200;
    $wl_count = 100;
    ?>
    <!-- Bootstrap & jQuery JS (optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>