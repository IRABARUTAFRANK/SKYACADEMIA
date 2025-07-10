<?php
// Ensure this path is correct relative to admin_dashboard.php
require_once '../connection/connect.php';
$conn = getPDOConnection();

$pendingSchoolsCount = 0;
$activeSchoolsCount = 0;

try {
  
    $sqlpending = "SELECT COUNT(*) AS total_pending FROM Schools WHERE SchoolStatus = :status";

    
    $stmtpending = $conn->prepare($sqlpending);


    $statusToCount = 'Pending verification';
    $stmtpending->bindParam(':status', $statusToCount, PDO::PARAM_STR);


    $stmtpending->execute();

    $result = $stmtpending->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['total_pending'])) {
        $pendingSchoolsCount = $result['total_pending'];
    }

    $sqlactive = "SELECT COUNT(*) AS total_active FROM Schools WHERE SchoolStatus = :status";
    $stmtactive = $conn->prepare($sqlactive);
    $activeCount = 'Activated';
    $stmtactive->bindParam(':status', $activeCount,  PDO::PARAM_STR);
    $stmtactive->execute();
    $activeResult = $stmtactive->fetch(PDO::FETCH_ASSOC);

    if($activeResult && isset($activeResult['activated'])) {
        $activeSchoolsCount = $activeResult['activated'];
    }

} catch (PDOException $e) {
   
    error_log("Database Error counting pending schools: " . $e->getMessage());
   
    $pendingSchoolsCount = "N/A";
    $activeCount = "N/A";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKYACADEMIA CONTROL CENTER</title>
    <link rel="stylesheet" href="../CSS/super_admin.css?v=6.0 ">
    <link rel="icon" type="image/png" href="../images/sky_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/sky_icon.png" alt="SKYACADEMIA Logo" class="sidebar-logo">
                <span class="sidebar-title">SKYACADEMIA</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="super_admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_all_schools.php"><i class="fas fa-school"></i> Manage Schools</a></li>
                    <li><a href="#"><i class="fas fa-fw fa-newspaper"></i> Posts <span class="nav-count">24</span></a></li>
                    <li><a href="#"><i class="fas fa-fw fa-users"></i> Users <span class="nav-count">8</span></a></li>
                    <li><a href="#"><i class="fas fa-fw fa-comments"></i> Comments <span class="nav-count">15</span></a></li>
                    <li><a href="#"><i class="fas fa-fw fa-folaader"></i> Categories</a></li>
                    <hr>
                    <li><a href="#"><i class="fas fa-fw fa-cogs"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-fw fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <div class="main-content-wrapper">
            <header class="topbar">
                <button class="sidebar-toggle-btn"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">SKYACADEMIA OVERVIEW</h1>
                <div class="topbar-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Search anything...">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="user-profile">
                        <i class="fas fa-bell"></i>
                        <i class="bi bi-person-circle"></i>
                    </div>
                </div>
            </header>

            <main class="dashboard-content">
                <section class="dashboard-section overview-stats">
                    <div class="stat-card">
                        <div class="icon-circle bg-green"><i class="fas fa-school"></i></div>
                        <div class="card-details">
                            <span class="stat-value"><?= $pendingSchoolsCount?></span>
                            <span class="stat-label">Pending schools</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="icon-circle bg-blue"><i class="fas fa-school"></i></div>
                        <div class="card-details">
                            <span class="stat-value"><?= $activeSchoolsCount?></span>
                            <span class="stat-label">Active schools</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="icon-circle bg-orange"><i class="fas fa-comments"></i></div>
                        <div class="card-details">
                            <span class="stat-value">1,234</span>
                            <span class="stat-label">Comments</span>
                        </div>
                    </div>
                </section>

                <section class="dashboard-section registered-schools-table">
    <div class="container-fluid">
        <h2>Latest School Applications</h2> <div class="table-responsive styled-table-container">
            <table class="table table-striped table-bordered align-middle styled-table">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>School Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Contact Person</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Applied at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Your PHP code here (as provided in the prompt, with LIMIT 2 for dashboard)
                    try {
                        // Fetch only the LATEST 2 schools for the dashboard
                        $stmt = $conn->query("SELECT * FROM Schools ORDER BY CreatedAt DESC LIMIT 2"); // LIMIT added here
                        $i = 1;
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $i++ . "</td>";
                                echo "<td>" . htmlspecialchars($row['SchoolName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['SchoolEmail']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['SchoolContacts']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ContactPerson']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['AreaOfLocation']) . "</td>"; // Corrected column name
                                echo "<td>" . htmlspecialchars($row['SchoolStatus']) . "</td>";
                                echo "<td>" . date('Y-m-d H:i', strtotime($row['CreatedAt'])) . "</td>";
                                echo "<td>";
                                echo "<button class='action-btn edit-btn' title='Edit School' data-school-id='" . htmlspecialchars($row['SchoolID']) . "'><i class='fas fa-edit'></i></button>";
                                echo "<button class='action-btn delete-btn' title='Delete School' data-school-id='" . htmlspecialchars($row['SchoolID']) . "'><i class='fas fa-trash-alt'></i></button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>No new school applications found.</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='9' class='text-danger text-center'>Database Error: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-center mt-3 table-footer-actions">
                <a href="../Backend/manage_schools.php" class="btn btn-primary custom-btn">View All Schools <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
        </div>
    </div>
</section>

                <section class="dashboard-section quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-grid">
                        <div class="action-card">
                            <i class="fas fa-plus-circle"></i>
                            <h3>Create New Post</h3>
                            <p>Write and publish a new news article.</p>
                            <a href="#" class="action-link">Go <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-user-plus"></i>
                            <h3>Add New User</h3>
                            <p>Register a new admin or contributor account.</p>
                            <a href="#" class="action-link">Go <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-chart-line"></i>
                            <h3>View Analytics</h3>
                            <p>Check website traffic and engagement.</p>
                            <a href="#" class="action-link">Go <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-envelope-open-text"></i>
                            <h3>Manage Subscriptions</h3>
                            <p>Handle newsletter subscribers.</p>
                            <a href="#" class="action-link">Go <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarToggleBtn = document.querySelector('.sidebar-toggle-btn');
            const dashboardContainer = document.querySelector('.dashboard-container');
            const sidebar = document.querySelector('.sidebar');

            sidebarToggleBtn.addEventListener('click', () => {
                dashboardContainer.classList.toggle('sidebar-open');
                sidebar.classList.toggle('active'); // Add/remove active class for responsive sidebar
            });

            // Optional: Close sidebar if clicking outside when open on mobile
            dashboardContainer.addEventListener('click', (e) => {
                if (dashboardContainer.classList.contains('sidebar-open') &&
                    !sidebar.contains(e.target) &&
                    !sidebarToggleBtn.contains(e.target)) {
                    dashboardContainer.classList.remove('sidebar-open');
                    sidebar.classList.remove('active');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>