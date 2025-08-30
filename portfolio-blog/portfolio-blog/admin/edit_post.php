<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get post ID
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$post_id) {
    header('Location: posts.php');
    exit;
}

// Get the post
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post) {
    header('Location: posts.php');
    exit;
}

$success = '';
$error = '';

// Handle form submission
if($_POST) {
    $title = sanitize_input($_POST['title']);
    $content = $_POST['content']; // Don't sanitize HTML content
    $status = sanitize_input($_POST['status']);
    
    // Generate slug from title if title changed
    $slug = $post['slug'];
    if($title != $post['title']) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        
        // Check if slug already exists (exclude current post)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $post_id]);
        if($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }
    }
    
    $featured_image = $post['featured_image'];
    
    // Handle image upload
    if(isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        if(!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['featured_image']['type'];
        
        if(in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $new_image = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_image;
            
            if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                // Delete old image if exists
                if($featured_image && file_exists($upload_dir . $featured_image)) {
                    unlink($upload_dir . $featured_image);
                }
                $featured_image = $new_image;
            } else {
                $error = 'Gagal upload gambar!';
            }
        } else {
            $error = 'Format gambar tidak didukung! Gunakan JPG, PNG, GIF, atau WebP.';
        }
    }
    
    // Handle remove image
    if(isset($_POST['remove_image']) && $featured_image) {
        if(file_exists('../uploads/' . $featured_image)) {
            unlink('../uploads/' . $featured_image);
        }
        $featured_image = null;
    }
    
    // Update post if no error
    if(!$error && $title && $content) {
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, featured_image = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $slug, $content, $featured_image, $status, $post_id]);
            
            $success = 'Post berhasil diperbarui!';
            
            // Refresh post data
            $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();
        } catch(PDOException $e) {
            $error = 'Gagal memperbarui post: ' . $e->getMessage();
        }
    } elseif(!$error) {
        $error = 'Judul dan konten harus diisi!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
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
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            border-radius: 10px;
        }
        
        .header-bar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .current-image {
            max-width: 200px;
            border-radius: 10px;
            margin-bottom: 10px;
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
                            <h2 class="mb-0">Edit Post</h2>
                            <small class="text-muted">Edit artikel: <?php echo htmlspecialchars($post['title']); ?></small>
                        </div>
                        <div>
                            <a href="posts.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            <?php if($post['status'] == 'published'): ?>
                                <a href="../blog_detail.php?id=<?php echo $post['id']; ?>" target="_blank" class="btn btn-outline-success">
                                    <i class="fas fa-eye me-1"></i>Lihat Post
                                </a>
                            <?php endif; ?>
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

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">
                                                <i class="fas fa-heading me-1"></i>Judul Post *
                                            </label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   placeholder="Masukkan judul post..." 
                                                   value="<?php echo htmlspecialchars($post['title']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="content" class="form-label">
                                                <i class="fas fa-edit me-1"></i>Konten Post *
                                            </label>
                                            <textarea id="content" name="content" class="form-control" rows="20">
                                                <?php echo htmlspecialchars($post['content']); ?>
                                            </textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">
                                                <i class="fas fa-toggle-on me-1"></i>Status *
                                            </label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>
                                                    Draft
                                                </option>
                                                <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>
                                                    Published
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-image me-1"></i>Gambar Featured
                                            </label>
                                            
                                            <?php if($post['featured_image']): ?>
                                                <div class="mb-2">
                                                    <img src="../uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                                         class="current-image d-block" alt="Current Image">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                                                        <label class="form-check-label text-danger" for="remove_image">
                                                            Hapus gambar ini
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <input type="file" class="form-control" id="featured_image" name="featured_image" 
                                                   accept="image/*" onchange="previewImage(event)">
                                            <small class="form-text text-muted">
                                                Format: JPG, PNG, GIF, WebP. Max 2MB.
                                                <?php if($post['featured_image']): ?>
                                                    <br>Upload gambar baru untuk mengganti yang lama.
                                                <?php endif; ?>
                                            </small>
                                            <div id="imagePreview"></div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-save me-2"></i>Perbarui Post
                                            </button>
                                            <a href="posts.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>Batal
                                            </a>
                                        </div>

                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h6><i class="fas fa-info-circle me-1"></i>Informasi Post:</h6>
                                            <ul class="small mb-0">
                                                <li><strong>Dibuat:</strong> <?php echo format_date($post['created_at']); ?></li>
                                                <li><strong>Diperbarui:</strong> <?php echo format_date($post['updated_at']); ?></li>
                                                <li><strong>Slug:</strong> <?php echo htmlspecialchars($post['slug']); ?></li>
                                                <li><strong>Kata:</strong> <?php echo str_word_count(strip_tags($post['content'])); ?> kata</li>
                                            </ul>
                                        </div>

                                        <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded">
                                            <h6><i class="fas fa-lightbulb me-1"></i>Tips:</h6>
                                            <ul class="small mb-0">
                                                <li>Simpan sebagai draft untuk preview sebelum publish</li>
                                                <li>Gambar featured akan muncul di halaman blog</li>
                                                <li>Slug akan otomatis diperbarui jika judul diubah</li>
                                                <li>Gunakan editor untuk formatting yang lebih baik</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Admin',
            mergetags_list: [
                { value: 'First.Name', title: 'First Name' },
                { value: 'Email', title: 'Email' },
            ],
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
        });

        // Image preview function
        function previewImage(event) {
            const file = event.target.files[0];
            const previewContainer = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <div class="mt-2">
                            <small class="text-muted">Preview gambar baru:</small><br>
                            <img src="${e.target.result}" class="image-preview" alt="Preview">
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '';
            }
        }

        // Handle remove image checkbox
        document.getElementById('remove_image')?.addEventListener('change', function() {
            if(this.checked) {
                if(!confirm('Yakin ingin menghapus gambar featured ini?')) {
                    this.checked = false;
                }
            }
        });

        // Handle logout
        if(window.location.search.includes('logout=1')) {
            window.location.href = 'dashboard.php?logout=1';
        }
    </script>
</body>
</html>