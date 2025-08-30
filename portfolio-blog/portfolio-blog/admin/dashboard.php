<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
$total_posts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
$published_posts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'");
$draft_posts = $stmt->fetchColumn();

// Get recent posts
$stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 10");
$recent_posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blog Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color), #34495e);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .action-buttons .btn {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }
        
        .header-bar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Admin Panel
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="posts.php">
                            <i class="fas fa-newspaper me-2"></i>Kelola Post
                        </a>
                        <a class="nav-link" href="add_post.php">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Post
                        </a>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Lihat Website
                        </a>
                        <a class="nav-link" href="?logout=1" onclick="return confirm('Yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-0">
                <!-- Header -->
                <div class="header-bar p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">Dashboard</h2>
                            <small class="text-muted">Selamat datang, <?php echo $_SESSION['admin_username']; ?>!</small>
                        </div>
                        <div>
                            <span class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d M Y, H:i'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-newspaper fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="h4 mb-0"><?php echo $total_posts; ?></div>
                                        <div class="text-muted small">Total Post</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-check-circle fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="h4 mb-0"><?php echo $published_posts; ?></div>
                                        <div class="text-muted small">Published</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-edit fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="h4 mb-0"><?php echo $draft_posts; ?></div>
                                        <div class="text-muted small">Draft</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-eye fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="h4 mb-0">-</div>
                                        <div class="text-muted small">Views</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-bolt me-2"></i>Quick Actions
                                    </h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="add_post.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>Tambah Post Baru
                                        </a>
                                        <a href="posts.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list me-1"></i>Kelola Semua Post
                                        </a>
                                        <a href="../blog.php" target="_blank" class="btn btn-outline-secondary">
                                            <i class="fas fa-external-link-alt me-1"></i>Lihat Blog
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Posts -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>Post Terbaru
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if($recent_posts): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Judul</th>
                                                        <th>Status</th>
                                                        <th>Tanggal</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($recent_posts as $post): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($post['title']); ?></div>
                                                                <small class="text-muted">
                                                                    <?php echo truncate_text(strip_tags($post['content']), 60); ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <?php if($post['status'] == 'published'): ?>
                                                                    <span class="badge bg-success badge-status">Published</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning badge-status">Draft</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <small><?php echo format_date($post['created_at']); ?></small>
                                                            </td>
                                                            <td>
                                                                <div class="action-buttons">
                                                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" 
                                                                       class="btn btn-outline-primary btn-sm">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <?php if($post['status'] == 'published'): ?>
                                                                        <a href="../blog_detail.php?id=<?php echo $post['id']; ?>" 
                                                                           class="btn btn-outline-success btn-sm" target="_blank">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <a href="posts.php?delete=<?php echo $post['id']; ?>" 
                                                                       class="btn btn-outline-danger btn-sm"
                                                                       onclick="return confirm('Yakin ingin menghapus post ini?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer bg-white text-center">
                                            <a href="posts.php" class="btn btn-outline-primary">
                                                Lihat Semua Post <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum ada post</h5>
                                            <p class="text-muted">Mulai dengan membuat post pertama Anda!</p>
                                            <a href="add_post.php" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i>Tambah Post
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>