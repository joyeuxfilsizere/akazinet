USE kazinet;

CREATE TABLE IF NOT EXISTS scholarships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-graduation-cap',
    icon_bg VARCHAR(50) DEFAULT 'bg-primary/10',
    icon_color VARCHAR(50) DEFAULT 'text-primary',
    link_text VARCHAR(100) DEFAULT 'Learn More',
    link_url VARCHAR(500) DEFAULT '#',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO scholarships (title, description, icon, icon_bg, icon_color, link_text, link_url, sort_order) VALUES
('University Financial Aid', 'Rwandan universities such as the University of Rwanda, Carnegie Mellon University Africa, and Africa Leadership University offer internal financial aid and merit-based scholarships.', 'fas fa-university', 'bg-blue-50', 'text-blue-500', 'Visit University of Rwanda', 'https://www.ur.ac.rw/', 1),
('NGO & Private Scholarships', 'Organizations like the Mastercard Foundation Scholars Program, We Are All The Same, and various NGOs provide scholarship funding for talented Rwandan youth.', 'fas fa-hand-holding-heart', 'bg-green-100', 'text-green-600', 'Learn More', 'https://mastercardfdn.org/all/scholars/', 2);
