USE kazinet;

CREATE TABLE IF NOT EXISTS scholarship_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    organization VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    eligibility TEXT NULL,
    deadline DATE NULL,
    link_url VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
);

UPDATE scholarships SET link_url = '#', link_text = 'View Scholarships';
