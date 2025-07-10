<?php

require_once '../connection/connect.php';
$conn = getPDOConnection();



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage schools</title>
    <link rel="stylesheet" href="../CSS/manage_schools.css?v=4.0">
    <link rel="shortcut icon" href="../images/sky_icon.png" type="image/x-icon">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="manage_school_dashboard">
    <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/sky_icon.png" alt="SKYACADEMIA Logo" class="sidebar-logo">
                <span class="sidebar-title">SKYACADEMIA</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="../admins/super_admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_all_schools.php"><i class="fas fa-school"></i> Manage Schools</a></li>
                    <li><a href="#"><i class="fas fa-fw fa-newspaper"></i> Posts <span class="nav-count">24</span></a></li>
                    <li><a href="#"><i class="fas fa-fw fa-users"></i> Users <span class="nav-count">8</span></a></li>
                    <li><a href="#"><i class="fas fa-fw fa-comments"></i> Comments <span class="nav-count">15</span></a></li>
                    <li><a href="#"><i class="fas fa-fw fa-folder"></i> Categories</a></li>
                    <hr>
                    <li><a href="#"><i class="fas fa-fw fa-cogs"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-fw fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
 <div class="main-content-wrapper">
            <header class="topbar">
                <button class="sidebar-toggle-btn"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">SCHOOLS & ORAGNISATIONS MANAGEMENT</h1>
                <div class="topbar-actions">
                    <div class="search-box">
                      <input type="text" id="myInput" placeholder="">

                        <i class="fas fa-search"></i>
                    </div>
                    <div class="user-profile">
                        <i class="fas fa-bell"></i>
                        <i class="bi bi-person-circle"></i>
                    </div>
                </div>
            </header>
    <section class="dashboard-section registered-schools-table">
    <div class="container-fluid"> <h2>Applied Schools and organisations</h2>
        <div class="table-responsive styled-table-container"> <table class="table table-striped table-bordered align-middle styled-table"> <thead class="table-dark">
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
                    // PHP code remains exactly the same as in the previous "Show Less" solution
                    // Ensure $default_display_limit = 7; is set
                    try {
                        $stmt = $conn->query("SELECT * FROM Schools ORDER BY CreatedAt DESC");
                        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $default_display_limit = 7;
                        $i = 1;

                        if (count($schools) > 0) {
                            foreach ($schools as $index => $row) {
                                $row_class = '';
                                if ($index >= $default_display_limit) {
                                    $row_class = 'hidden-school-row';
                                }
                                echo "<tr class='" . $row_class . "'>";
                                echo "<td>" . $i++ . "</td>";
                                echo "<td>" . htmlspecialchars($row['SchoolName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['SchoolEmail']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['SchoolContacts']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ContactPerson']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['AreaOfLocation']) . "</td>";
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
            <div class="text-center mt-3 table-footer-actions"> <a href="#" id="viewAllSchoolsBtn" class="btn btn-primary custom-btn">View All Schools <i class="fas fa-arrow-right ms-2"></i></a>
                <a href="#" id="showLessSchoolsBtn" class="btn btn-secondary custom-btn" style="display: none;">Show Less <i class="fas fa-arrow-up ms-2"></i></a>
            </div>
        </div>
    </div>
</section>

</div>
</div>
<script>
  const input = document.getElementById('myInput');
  const messages = ["Search by Filter..", "eg: Activated", "eg: pending verification", "eg: School name"];
  let messageIndex = 0;
  let charIndex = 0;
  let typing = true;

  function animatePlaceholder() {
    if (typing) {
      input.placeholder = messages[messageIndex].substring(0, charIndex);
      charIndex++;
      if (charIndex > messages[messageIndex].length) {
        typing = false;
        setTimeout(() => {
          typing = true;
          charIndex = 0;
          messageIndex = (messageIndex + 1) % messages.length;
        }, 1500);
      }
    }
    requestAnimationFrame(animatePlaceholder);
  }

  animatePlaceholder();
</script>
<script>
   
document.addEventListener('DOMContentLoaded', function() {
    const viewAllSchoolsBtn = document.getElementById('viewAllSchoolsBtn');
    const hiddenRows = document.querySelectorAll('.hidden-school-row');

    if (viewAllSchoolsBtn) {
        viewAllSchoolsBtn.addEventListener('click', function(e) {
            e.preventDefault(); 

            hiddenRows.forEach(row => {
                row.style.display = 'table-row';
                row.classList.remove('hidden-school-row');
            });

            this.style.display = 'none'; 

            
            const tableSection = document.querySelector('.registered-schools-table');
            if (tableSection) {
                tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

 
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const schoolId = this.dataset.schoolId; 
            alert('Edit school with ID: ' + schoolId);
           
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const schoolId = this.dataset.schoolId;
            if (confirm('Are you sure you want to delete school with ID: ' + schoolId + '?')) {
                alert('Deleting school with ID: ' + schoolId);
               
            }
        });
    });
});
</script>
</body>
</html>