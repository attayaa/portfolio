<?php
session_start();
require_once 'config.php';

// Ambil blog posts terbaru
$stmt = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 6");
$recent_posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio - MUHAMAD ATAYA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 3rem;
            font-weight: bold;
        }
        
        .skill-item {
            transition: transform 0.3s;
        }
        
        .skill-item:hover {
            transform: translateY(-5px);
        }
        
        .blog-card {
            transition: transform 0.3s;
            height: 100%;
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
        }
        
        .blog-image {
            height: 200px;
            object-fit: cover;
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
            <a class="navbar-brand" href="#home">
                <i class="fas fa-user-circle me-2"></i>MUHAMAD ATAYA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#portfolio">Portofolio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#blog">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Semua Blog</a>
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

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 mb-4">Halo, Saya MUHAMAD ATAYA</h1>
                    <p class="lead mb-4">Full Stack Developer & UI/UX Designer dengan passion untuk menciptakan solusi digital yang inovatif.</p>
                    <a href="#portfolio" class="btn btn-light btn-lg me-3">Lihat Karya</a>
                    <a href="#contact" class="btn btn-outline-light btn-lg">Hubungi Saya</a>
                </div>
                <div class="col-lg-6">
                    <img src="../Foto/foto surken.jpg" 
                         alt="Profile" class="img-fluid rounded-circle shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="text-center section-title">Tentang Saya</h2>
            <div class="row">
                <div class="col-lg-6">
                    <p class="lead">Saya adalah seorang developer dengan pengalaman 2+ tahun dalam pengembangan aplikasi web dan mobile. Saya memiliki keahlian dalam berbagai teknologi modern dan selalu eager untuk belajar hal-hal baru.</p>
                    <p>Saya percaya bahwa teknologi harus dapat memecahkan masalah nyata dan memberikan value yang nyata bagi pengguna. Itulah mengapa saya selalu fokus pada user experience dan clean code.</p>
                </div>
                <div class="col-lg-6">
                    <h4>Keahlian Saya:</h4>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="skill-item p-3 bg-light rounded text-center">
                                <i class="fab fa-js-square fa-3x text-warning mb-2"></i>
                                <h6>JavaScript</h6>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="skill-item p-3 bg-light rounded text-center">
                                <i class="fab fa-php fa-3x text-primary mb-2"></i>
                                <h6>PHP</h6>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="skill-item p-3 bg-light rounded text-center">
                                <i class="fab fa-react fa-3x text-info mb-2"></i>
                                <h6>React</h6>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="skill-item p-3 bg-light rounded text-center">
                                <i class="fas fa-database fa-3x text-success mb-2"></i>
                                <h6>Database</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center section-title">Portofolio</h2>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 shadow">
                        <img src="../Foto/todolist.png" 
                             class="card-img-top" alt="E-Commerce Website">
                        <div class="card-body">
                            <h5 class="card-title">TODOLIST  Website</h5>
                            <p class="card-text">Website TODOLIST lengkap dengan sistem deadline.</p>
                            <div class="mb-2">
                                <span class="badge bg-primary">PHP</span>
                                <span class="badge bg-success">MySQL</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>Demo
                            </a>
                            <a href="#" class="btn btn-outline-dark btn-sm">
                                <i class="fab fa-github me-1"></i>GitHub
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 shadow">
                        <img src="../Foto/warung.png" 
                             class="card-img-top" alt="Task Management App">
                        <div class="card-body">
                            <h5 class="card-title">kantin website</h5>
                            <p class="card-text">website kantin dengan fonnte untuk struk pemesanan.</p>
                            <div class="mb-2">
                                <span class="badge bg-info">php_check_syntax</span>
                                <span class="badge bg-dark">fonnte</span>
                                
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>Demo
                            </a>
                            <a href="#" class="btn btn-outline-dark btn-sm">
                                <i class="fab fa-github me-1"></i>GitHub
                            </a>
                        </div>
                    </div>
                </div>
                
                        </div>
                        <div class="card-footer">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>Demo
                            </a>
                            <a href="#" class="btn btn-outline-dark btn-sm">
                                <i class="fab fa-behance me-1"></i>Behance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section id="blog" class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="section-title mb-0">Blog Terbaru</h2>
                <a href="blog.php" class="btn btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="row">
                <?php if($recent_posts): ?>
                    <?php foreach($recent_posts as $post): ?>
                        <div class="col-lg-4 mb-4">
                            <div class="card blog-card shadow">
                                <?php if($post['featured_image']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                         class="card-img-top blog-image" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/400x200/6c757d/ffffff?text=No+Image" 
                                         class="card-img-top blog-image" alt="No Image">
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                                        </small>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                    <p class="card-text"><?php echo substr(strip_tags($post['content']), 0, 120) . '...'; ?></p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="blog_detail.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                        Baca Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="lead text-muted">Belum ada artikel blog.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center section-title">Hubungi Saya</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-4">
                                <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                <h5>Email</h5>
                                <p>muhammad.attaya03@email.com</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-4">
                                <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                                <h5>Telepon</h5>
                                <p>+62 087761510759</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="p-4">
                                <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                                <h5>Lokasi</h5>
                                <p>Tnagerang, Indonesia</p>
                            </div>
                        </div>
                    </div>
                    <form class="mt-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" placeholder="Nama Anda" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control" placeholder="Email Anda" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Subjek" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" placeholder="Pesan Anda" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Kirim Pesan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 muhamaad ataya. All rights reserved.</p>
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
    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>