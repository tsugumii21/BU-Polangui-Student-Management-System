<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.html");
    exit();
}
require_once '../database/config.php';

// Get student counts per department
try {
    $stmt = $pdo->query("SELECT department, COUNT(*) as count FROM students GROUP BY department");
    $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $counts = [];
}

// Define departments and their colors/icons
$departments = [
    'Computer Studies Department' => [
        'color' => '#e91e63', // Pink
        'icon' => 'fa-laptop-code',
        'count' => $counts['Computer Studies Department'] ?? 0
    ],
    'Engineering Department' => [
        'color' => '#dc3545', // Red
        'icon' => 'fa-cogs',
        'count' => $counts['Engineering Department'] ?? 0
    ],
    'Nursing Department' => [
        'color' => '#6f42c1', // Purple
        'icon' => 'fa-user-nurse',
        'count' => $counts['Nursing Department'] ?? 0
    ],
    'Entrepreneurship Department' => [
        'color' => '#28a745', // Green
        'icon' => 'fa-chart-line',
        'count' => $counts['Entrepreneurship Department'] ?? 0
    ],
    'Technology Department' => [
        'color' => '#ffc107', // Yellow
        'icon' => 'fa-tools',
        'count' => $counts['Technology Department'] ?? 0
    ],
    'Education Department' => [
        'color' => '#007bff', // Blue
        'icon' => 'fa-book-reader',
        'count' => $counts['Education Department'] ?? 0
    ]
];

$target_page = ($_SESSION['role'] === 'admin') ? 'students_list_admin.php' : 'students_list_user.php';
$dashboard_link = ($_SESSION['role'] === 'admin') ? 'index_admin.php' : 'index_user.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Department | BU SMS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 2rem;
        }
        .dept-card {
            border-radius: 15px;
            padding: 2rem;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .dept-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .dept-icon {
            font-size: 3.5rem;
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        .dept-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }
        .dept-count {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }
        .dept-bg-icon {
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 10rem;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
    </style>
</head>
<body>
    <?php include 'includes/header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 3rem; padding-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin-bottom: 5px;">University Departments</h2>
                <p style="color: var(--text-secondary);">Select a department to view students</p>
            </div>
            <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="dept-grid">
            <?php foreach ($departments as $name => $data): ?>
                <a href="<?php echo $target_page; ?>?department=<?php echo urlencode($name); ?>" class="dept-card" style="background-color: <?php echo $data['color']; ?>; color: <?php echo $data['text_color'] ?? 'white'; ?>;">
                    <i class="fas <?php echo $data['icon']; ?> dept-bg-icon"></i>
                    <div>
                        <i class="fas <?php echo $data['icon']; ?> dept-icon"></i>
                        <div class="dept-name"><?php echo $name; ?></div>
                    </div>
                    <div class="dept-count">
                        <?php echo $data['count']; ?> Students
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

