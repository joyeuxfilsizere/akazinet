USE kazinet;

CREATE TABLE IF NOT EXISTS ad_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    type ENUM('adsense', 'image') NOT NULL DEFAULT 'adsense',
    ad_code TEXT NULL,
    image_path VARCHAR(255) NULL,
    link_url VARCHAR(500) NULL,
    position VARCHAR(50) NOT NULL DEFAULT 'content',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DELETE FROM settings WHERE setting_key IN ('ad_code');
