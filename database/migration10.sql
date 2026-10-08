USE kazinet;

-- Add application_url column to jobs table for direct apply links
ALTER TABLE jobs ADD COLUMN application_url VARCHAR(500) DEFAULT NULL AFTER how_to_apply;

-- Add application_email column to jobs table for email-based applications
ALTER TABLE jobs ADD COLUMN application_email VARCHAR(150) DEFAULT NULL AFTER application_url;

-- Add WhatsApp group link to settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('whatsapp_group_link', '', 'url')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
