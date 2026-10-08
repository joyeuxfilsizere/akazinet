USE kazinet;
DELETE FROM settings WHERE setting_key IN ('ad_image', 'ad_url', 'ad_enabled');
