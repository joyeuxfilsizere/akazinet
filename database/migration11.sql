-- Migration 11: Add banner_image column to scholarship_listings table
-- This allows scholarship listings to have a banner image

ALTER TABLE scholarship_listings ADD COLUMN banner_image VARCHAR(500) NULL AFTER organization;
