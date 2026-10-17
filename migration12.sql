-- Migration 12: correct the miscased Marketing category name
--
-- Run this file against the AkaziNet database (in phpMyAdmin, select the
-- database in the left sidebar first, then use the SQL tab). It is safe to
-- run more than once.
--
-- The live row for the Marketing category carries the misspelled name
-- 'Adminstration', which the category filter and the /jobs?category=marketing
-- heading both render. The slug is what links and canonicals use, so only the
-- display name needs correcting.

UPDATE categories
SET name = 'Marketing'
WHERE slug = 'marketing'
  AND name <> 'Marketing';
