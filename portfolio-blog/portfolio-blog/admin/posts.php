<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle delete post
if(isset($_GET['delete'])) {
    $post_id = (int)$_GET['delete'];
    
    try {
        // Get post details first to delete image
        $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if($post) {
            // Delete image file if exists
            if($post['featured_image'] && file_exists('../uploads/' . $post['featured_image'])) {
                unlink('../uploads/' . $post['featured_image']);
            }
            
            // Delete post from database
            $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $success = 'Post berhasil dihapus!';
        } else {
            $error = 'Post tidak ditemukan!';
        }
    } catch(PDOException $e) {
        $error = 'Gagal menghapus post: ' . $e->getMessage();
    }
}

// Handle bulk actions
if(isset($_POST['bulk_action']) && isset($_POST['selected_posts'])) {
    $action = $_POST['bulk_action'];
    $selected_posts = $_POST['selected_posts'];
    
    try {
        if($action == 'delete') {
            $placeholders = str_repeat('?,', count($selected_posts) - 1) . '?';
            
            // Get posts to delete images
            $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id IN ($placeholders)");
            $stmt->execute($selected_posts);
            $posts = $stmt->fetchAll();
            
            // Delete image files
            foreach($posts as $post) {
                if($post['featured_image'] && file_exists('../uploads/' . $post['featured_image'])) {
                    unlink('../uploads/' . $post['featured_image']);
                }
            }
            
            // Delete posts
            $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id IN ($placeholders)");
            $stmt->execute($selected_posts);
            
            $success = count($selected_posts) . ' post berhasil dihapus!';
        } elseif($action == 'publish') {
            $placeholders = str_repeat('?,', count($selected_posts) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE blog_posts SET status = 'published' WHERE id IN ($placeholders)");
            $stmt->execute($selected_posts);
            
            $success = count($selected_posts) . ' post berhasil dipublish!';
        } elseif($action == 'draft') {
            $placeholders = str_repeat('?,', count($selected_posts) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE blog_posts SET status = 'draft' WHERE id IN ($placeholders)");
            $stmt->execute($selected_posts);
            
            $success = count($selected_posts) . ' post berhasil diubah ke draft!';
        }
    } catch(PDOException $e) {
        $error = 'Gagal melakukan bulk action: ' . $e->getMessage();
    }
}

// Pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];

if($status_filter != 'all') {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if($search) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total posts
$count_sql = "SELECT COUNT(*) FROM blog_posts $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $per_page);

// Get posts for current page
$sql = "SELECT * FROM blog_posts $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Post - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
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
        
        .post-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .bulk-actions {
            background: #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                        <a class="nav-link active" href="posts.php">
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
                            <h2 class="mb-0">Kelola Post</h2>
                            <small class="text-muted">Kelola semua artikel blog</small>
                        </div>
                        <div>
                            <a href="add_post.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tambah Post Baru
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <form method="GET" action="">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Filter Status</label>
                                    <select class="form-select" name="status" onchange="this.form.submit()">
                                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                        <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cari Post</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Cari judul atau konten..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <?php if($search || $status_filter != 'all'): ?>
                                        <a href="posts.php" class="btn btn-outline-secondary w-100">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if($posts): ?>
                        <!-- Bulk Actions -->
                        <form method="POST" action="" id="bulkForm">
                            <div class="bulk-actions">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" class="form-check-input me-2" id="selectAll">
                                            <label for="selectAll" class="form-check-label me-3">Pilih Semua</label>
                                            
                                            <select class="form-select form-select-sm me-2" name="bulk_action" style="width: auto;">
                                                <option value="">Pilih Aksi...</option>
                                                <option value="publish">Publish</option>
                                                <option value="draft">Jadikan Draft</option>
                                                <option value="delete">Hapus</option>
                                            </select>
                                            
                                            <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                    onclick="return confirm('Yakin ingin melakukan aksi ini?')">
                                                Jalankan
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Menampilkan <?php echo count($posts); ?> dari <?php echo $total_posts; ?> post
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Posts Table -->
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50">
                                                        <input type="checkbox" class="form-check-input" id="selectAllHeader">
                                                    </th>
                                                    <th width="80">Gambar</th>
                                                    <th>Judul</th>
                                                    <th width="100">Status</th>
                                                    <th width="120">Tanggal</th>
                                                    <th width="150">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($posts as $post): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="form-check-input post-checkbox" 
                                                                   name="selected_posts[]" value="<?php echo $post['id']; ?>">
                                                        </td>
                                                        <td>
                                                            <?php if($post['featured_image']): ?>
                                                                <img src="../uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                                                     class="post-thumbnail" alt="Thumbnail">
                                                            <?php else: ?>
                                                                <div class="post-thumbnail bg-secondary d-flex align-items-center justify-content-center">
                                                                    <i class="fas fa-image text-white"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($post['title']); ?></div>
                                                            <small class="text-muted">
                                                                <?php echo truncate_text(strip_tags($post['content']), 80); ?>
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
                                                            <small>
                                                                <?php echo format_date($post['created_at']); ?><br>
                                                                <span class="text-muted"><?php echo date('H:i', strtotime($post['created_at'])); ?></span>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" 
                                                                   class="btn btn-outline-primary btn-sm" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php if($post['status'] == 'published'): ?>
                                                                    <a href="../blog_detail.php?id=<?php echo $post['id']; ?>" 
                                                                       class="btn btn-outline-success btn-sm" target="_blank" title="Lihat">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a href="?delete=<?php echo $post['id']; ?>" 
                                                                   class="btn btn-outline-danger btn-sm" title="Hapus"
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
                                </div>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <nav aria-label="Posts pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($total_pages, $page + 2);
                                    
                                    for($i = $start; $i <= $end; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada post ditemukan</h5>
                                <?php if($search || $status_filter != 'all'): ?>
                                    <p class="text-muted">Coba ubah filter atau <a href="posts.php">reset pencarian</a>.</p>
                                <?php else: ?>
                                    <p class="text-muted">Mulai dengan membuat post pertama Anda!</p>
                                    <a href="add_post.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Tambah Post Baru
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('selectAllHeader').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Handle logout
        if(window.location.search.includes('logout=1')) {
            window.location.href = 'dashboard.php?logout=1';
        }
    </script>
</body>
</html>