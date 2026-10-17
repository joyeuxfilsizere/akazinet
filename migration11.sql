-- Migration 11: remote jobs
--
-- Run this file against the AkaziNet database (in phpMyAdmin, select the
-- database in the left sidebar first, then use the SQL tab). Running it twice
-- is harmless apart from MySQL's "Duplicate column name 'is_remote'" error,
-- which can be ignored.
--
-- The admin panel flags a listing as remote, /pages/remote-jobs lists the
-- published ones. Until this migration has been run the site keeps working
-- without the column: the remote page shows its empty state, the admin form
-- hides the checkbox, and saving a job simply does not touch the flag.

ALTER TABLE jobs
    ADD COLUMN is_remote TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured;
