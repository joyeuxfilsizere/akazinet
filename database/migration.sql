USE kazinet;

-- Add company_name column to jobs table
ALTER TABLE jobs ADD COLUMN company_name VARCHAR(150) DEFAULT NULL AFTER company_id;

-- Populate company_name from companies table for existing jobs
UPDATE jobs j JOIN companies c ON j.company_id = c.id SET j.company_name = c.name;

-- Make company_id nullable (no longer required for new jobs)
ALTER TABLE jobs MODIFY COLUMN company_id INT NULL;

-- Remove salary columns
ALTER TABLE jobs DROP COLUMN salary_min;
ALTER TABLE jobs DROP COLUMN salary_max;
