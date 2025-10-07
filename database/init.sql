-- Mini ORM Database Schema
-- Otomatik olarak docker-compose up ile oluşturulur

USE mini_orm;

-- Users tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    age INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories tablosu
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts tablosu
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL,
    title VARCHAR(500) NOT NULL,
    content TEXT,
    status VARCHAR(50) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments tablosu
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User-Category pivot tablosu (Many-to-Many)
CREATE TABLE IF NOT EXISTS user_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek veriler
INSERT INTO users (name, email, password, status, age) VALUES
('Ahmet Yılmaz', 'ahmet@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 25),
('Mehmet Demir', 'mehmet@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 30),
('Ayşe Kaya', 'ayse@hotmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 22),
('Fatma Şahin', 'fatma@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inactive', 28);

INSERT INTO categories (name, slug) VALUES
('Teknoloji', 'teknoloji'),
('Yazılım', 'yazilim'),
('Spor', 'spor'),
('Sanat', 'sanat');

INSERT INTO posts (user_id, category_id, title, content, status) VALUES
(1, 1, 'PHP 8.2 Yenilikleri', 'PHP 8.2 ile gelen yenilikler hakkında detaylı makale...', 'published'),
(1, 2, 'Laravel vs Symfony', 'İki popüler PHP framework karşılaştırması...', 'published'),
(2, 1, 'Docker ile Geliştirme', 'Docker kullanarak geliştirme ortamı hazırlama...', 'published'),
(3, 3, 'Futbol Taktikleri', 'Modern futbolda kullanılan taktikler...', 'draft'),
(3, 4, 'Resim Sanatı', 'Klasik resim teknikleri...', 'published');

INSERT INTO comments (user_id, post_id, content) VALUES
(2, 1, 'Çok güzel bir makale, teşekkürler!'),
(3, 1, 'PHP 8.2 gerçekten harika özelliklerle geliyor.'),
(1, 3, 'Docker kullanımı çok kolaylaştı artık.'),
(4, 5, 'Sanat konusunda daha fazla içerik bekliyoruz.');

INSERT INTO user_category (user_id, category_id) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 3),
(3, 4);

