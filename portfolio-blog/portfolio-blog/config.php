<?php
// Database Configuration
$host = 'localhost';
$dbname = 'portfolio_blog';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Admin credentials (dalam implementasi nyata, gunakan hash password)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Ganti dengan password yang kuat

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function format_date($date) {
    return date('d M Y', strtotime($date));
}

function truncate_text($text, $length = 150) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

// Create tables if they don't exist
function create_tables($pdo) {
    // Blog posts table
    $sql_posts = "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        featured_image VARCHAR(255),
        status ENUM('draft', 'published') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Admin sessions table
    $sql_sessions = "CREATE TABLE IF NOT EXISTS admin_sessions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        session_token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    try {
        $pdo->exec($sql_posts);
        $pdo->exec($sql_sessions);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Create tables
create_tables($pdo);

// Insert sample data if tables are empty
function insert_sample_data($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $count = $stmt->fetchColumn();
    
    if($count == 0) {
        $sample_posts = [
            [
                'title' => 'Panduan Lengkap Belajar PHP untuk Pemula',
                'slug' => 'panduan-lengkap-belajar-php-untuk-pemula',
                'content' => '<p>PHP adalah salah satu bahasa pemrograman yang paling populer untuk pengembangan web. Dalam artikel ini, kita akan membahas langkah-langkah dasar untuk memulai belajar PHP.</p><h3>Apa itu PHP?</h3><p>PHP (PHP: Hypertext Preprocessor) adalah bahasa scripting server-side yang dirancang khusus untuk pengembangan web. PHP sangat mudah dipelajari dan memiliki sintaks yang sederhana.</p><h3>Keuntungan Belajar PHP:</h3><ul><li>Open source dan gratis</li><li>Mudah dipelajari</li><li>Komunitas yang besar</li><li>Banyak framework tersedia</li><li>Kompatibel dengan berbagai database</li></ul><p>Mari kita mulai perjalanan belajar PHP bersama-sama!</p>',
                'status' => 'published'
            ],
            [
                'title' => 'Tips Meningkatkan Performa Website dengan JavaScript',
                'slug' => 'tips-meningkatkan-performa-website-dengan-javascript',
                'content' => '<p>Performa website adalah salah satu faktor penting yang mempengaruhi user experience dan SEO. JavaScript memainkan peran penting dalam optimasi performa website.</p><h3>Teknik Optimasi JavaScript:</h3><ol><li><strong>Minifikasi Code</strong> - Hapus spasi dan karakter yang tidak perlu</li><li><strong>Lazy Loading</strong> - Muat konten hanya ketika diperlukan</li><li><strong>Caching</strong> - Simpan data yang sering digunakan</li><li><strong>Debouncing</strong> - Batasi frekuensi eksekusi function</li></ol><p>Dengan menerapkan teknik-teknik ini, website Anda akan lebih cepat dan responsif.</p>',
                'status' => 'published'
            ],
            [
                'title' => 'Tren Web Design 2025: Apa yang Perlu Diperhatikan',
                'slug' => 'tren-web-design-2025-apa-yang-perlu-diperhatikan',
                'content' => '<p>Dunia web design terus berkembang dengan pesat. Di tahun 2025, ada beberapa tren yang perlu diperhatikan oleh para designer dan developer.</p><h3>Tren Utama Web Design 2025:</h3><ul><li><strong>Dark Mode</strong> - Semakin populer dan user-friendly</li><li><strong>Micro-interactions</strong> - Animasi kecil yang meningkatkan UX</li><li><strong>AI Integration</strong> - Chatbot dan personalisasi konten</li><li><strong>Mobile-First Design</strong> - Prioritas utama adalah pengalaman mobile</li><li><strong>Sustainability</strong> - Design yang ramah lingkungan</li></ul><p>Pastikan untuk mengikuti tren-tren ini dalam project Anda selanjutnya!</p>',
                'status' => 'published'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, status) VALUES (?, ?, ?, ?)");
        
        foreach($sample_posts as $post) {
            $stmt->execute([$post['title'], $post['slug'], $post['content'], $post['status']]);
        }
    }
}

// Insert sample data
insert_sample_data($pdo);
?>