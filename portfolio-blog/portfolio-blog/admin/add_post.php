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

// Handle form submission
if($_POST) {
    $title = sanitize_input($_POST['title']);
    $content = $_POST['content']; // Don't sanitize HTML content
    $status = sanitize_input($_POST['status']);
    
    // Generate slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = trim($slug, '-');
    
    // Check if slug already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
    $stmt->execute([$slug]);
    if($stmt->fetchColumn() > 0) {
        $slug .= '-' . time();
    }
    
    $featured_image = null;
    
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
            $featured_image = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $featured_image;
            
            if(!move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                $error = 'Gagal upload gambar!';
                $featured_image = null;
            }
        } else {
            $error = 'Format gambar tidak didukung! Gunakan JPG, PNG, GIF, atau WebP.';
        }
    }
    
    // Insert post if no error
    if(!$error && $title && $content) {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, featured_image, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $featured_image, $status]);
            
            $success = 'Post berhasil ditambahkan!';
            
            // Clear form
            $_POST = [];
        } catch(PDOException $e) {
            $error = 'Gagal menyimpan post: ' . $e->getMessage();
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
    <title>Tambah Post Baru - Admin</title>
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
                        <a class="nav-link" href="posts.php">
                            <i class="fas fa-newspaper me-2"></i>Kelola Post
                        </a>
                        <a class="nav-link active" href="add_post.php">
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
                            <h2 class="mb-0">Tambah Post Baru</h2>
                            <small class="text-muted">Buat artikel blog baru</small>
                        </div>
                        <div>
                            <a href="posts.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
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
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="content" class="form-label">
                                                <i class="fas fa-edit me-1"></i>Konten Post *
                                            </label>
                                            <textarea id="content" name="content" class="form-control" rows="20">
                                                <?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?>
                                            </textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">
                                                <i class="fas fa-toggle-on me-1"></i>Status *
                                            </label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>
                                                    Draft
                                                </option>
                                                <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>
                                                    Published
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="featured_image" class="form-label">
                                                <i class="fas fa-image me-1"></i>Gambar Featured
                                            </label>
                                            <input type="file" class="form-control" id="featured_image" name="featured_image" 
                                                   accept="image/*" onchange="previewImage(event)">
                                            <small class="form-text text-muted">
                                                Format: JPG, PNG, GIF, WebP. Max 2MB.
                                            </small>
                                            <div id="imagePreview"></div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-save me-2"></i>Simpan Post
                                            </button>
                                            <a href="posts.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>Batal
                                            </a>
                                        </div>

                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h6><i class="fas fa-lightbulb me-1"></i>Tips:</h6>
                                            <ul class="small mb-0">
                                                <li>Gunakan judul yang menarik dan SEO friendly</li>
                                                <li>Gambar featured akan ditampilkan di halaman blog</li>
                                                <li>Gunakan editor untuk formatting konten</li>
                                                <li>Simpan sebagai draft dulu untuk review</li>
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
                        <img src="${e.target.result}" class="image-preview" alt="Preview">
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '';
            }
        }

        // Handle logout
        if(window.location.search.includes('logout=1')) {
            window.location.href = 'dashboard.php?logout=1';
        }
    </script>
</body>
</html>