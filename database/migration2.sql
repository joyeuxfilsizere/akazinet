USE kazinet;
ALTER TABLE jobs ADD COLUMN company_logo VARCHAR(255) DEFAULT NULL AFTER company_name;
