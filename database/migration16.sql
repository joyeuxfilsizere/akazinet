-- Migration 16: AdSense ad slot configuration + brand consistency
-- Run against the configured AkaziNet database, e.g.
--   mysql -u USER -p DBNAME < database/migration16.sql
-- Safe to run more than once.

-- Standardise the site name used in page titles and the footer.
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
    ('site_name', 'AkaziNet', 'text')
ON DUPLICATE KEY UPDATE setting_value = 'AkaziNet';

-- AdSense ad slot IDs.
-- Leave a value EMPTY to disable that slot. Until at least one slot is filled in,
-- the site falls back to Google AdSense Auto Ads (see components/ad-banner.php).
-- To fill these in: AdSense dashboard > Ad units > create a Responsive Display Ad,
-- then paste the "data-ad-slot" numeric ID here (digits only, no slashes).
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
    ('adsense_slot_header',   '', 'text'),
    ('adsense_slot_content',  '', 'text'),
    ('adsense_slot_sidebar',  '', 'text'),
    ('adsense_slot_footer',   '', 'text')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
