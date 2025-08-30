<?php
session_start();
require_once 'config.php';

// Get post ID
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$post_id) {
    header('Location: blog.php');
    exit;
}

// Get the post
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? AND status = 'published'");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post) {
    header('Location: blog.php');
    exit;
}

// Get recent posts for sidebar
$stmt = $pdo->query("SELECT id, title, created_at FROM blog_posts WHERE status = 'published' AND id != $post_id ORDER BY created_at DESC LIMIT 5");
$recent_posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog muhammad ataya</title>
    <meta name="description" content="<?php echo htmlspecialchars(truncate_text(strip_tags($post['content']), 160)); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .article-header {
            padding: 100px 0 50px 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .featured-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        .article-content h1, .article-content h2, .article-content h3, 
        .article-content h4, .article-content h5, .article-content h6 {
            color: var(--primary-color);
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .article-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
        }
        
        .article-content ul, .article-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }
        
        .article-content li {
            margin-bottom: 0.5rem;
        }
        
        .article-content blockquote {
            border-left: 4px solid var(--secondary-color);
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-radius: 0 8px 8px 0;
        }
        
        .recent-posts-item {
            transition: background-color 0.3s;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .recent-posts-item:hover {
            background-color: #f8f9fa;
        }
        
        .share-buttons a {
            transition: transform 0.3s;
        }
        
        .share-buttons a:hover {
            transform: translateY(-2px);
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user-circle me-2"></i>muhammad ataya
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#portfolio">Portofolio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Kontak</a>
                    </li>
                    <?php if(isset($_SESSION['admin_logged_in'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Admin</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Article Header -->
    <section class="article-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="index.php" class="text-light">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="blog.php" class="text-light">Blog</a></li>
                            <li class="breadcrumb-item active text-light" aria-current="page">Artikel</li>
                        </ol>
                    </nav>
                    <h1 class="display-5 mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="d-flex justify-content-center align-items-center text-light">
                        <i class="fas fa-calendar me-2"></i>
                        <span class="me-4"><?php echo format_date($post['created_at']); ?></span>
                        <i class="fas fa-book-open me-2"></i>
                        <span><?php echo str_word_count(strip_tags($post['content'])); ?> kata</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Article Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <article>
                        <!-- Featured Image -->
                        <?php if($post['featured_image']): ?>
                            <div class="mb-5">
                                <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                     class="img-fluid featured-image w-100" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Article Content -->
                        <div class="article-content">
                            <?php echo $post['content']; ?>
                        </div>

                        <!-- Share Buttons -->
                        <div class="share-buttons mt-5 pt-4 border-top">
                            <h5 class="mb-3">Bagikan Artikel:</h5>
                            <div class="d-flex gap-2">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fab fa-facebook-f me-1"></i>Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                                   target="_blank" class="btn btn-info btn-sm text-white">
                                    <i class="fab fa-twitter me-1"></i>Twitter
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" class="btn btn-dark btn-sm">
                                    <i class="fab fa-linkedin-in me-1"></i>LinkedIn
                                </a>
                                <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard()">
                                    <i class="fas fa-link me-1"></i>Salin Link
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 100px;">
                        <!-- Recent Posts -->
                        <?php if($recent_posts): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Artikel Terbaru</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php foreach($recent_posts as $recent): ?>
                                        <div class="recent-posts-item">
                                            <h6 class="mb-2">
                                                <a href="blog_detail.php?id=<?php echo $recent['id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($recent['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo format_date($recent['created_at']); ?>
                                            </small>
                                        </div>
                                        <?php if($recent !== end($recent_posts)): ?>
                                            <hr class="my-2">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Newsletter Signup -->
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Newsletter</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Dapatkan artikel terbaru langsung di email Anda!</p>
                                <form>
                                    <div class="mb-3">
                                        <input type="email" class="form-control" placeholder="Email Anda" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane me-1"></i>Berlangganan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="blog.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Blog
                        </a>
                        <div class="text-end">
                            <small class="text-muted">Terakhir diperbarui: <?php echo format_date($post['updated_at']); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 muahmmad ataya. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-github fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/prism.min.js"></script>
    <script>
        function copyToClipboard() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Tersalin!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            });
        }
    </script>
</body>
</html>