USE kazinet;
INSERT INTO ad_banners (title, type, image_path, link_url, position, is_active, sort_order)
SELECT title, type, image_path, link_url, 'content', is_active, sort_order
FROM ad_banners WHERE id = 1;
