-- ============================================
-- KaziNet - Complete Database Setup
-- Run this file to set up the database
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS kazinet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kazinet;

-- Source schema
SOURCE schema.sql;

-- Source seed data
SOURCE seed.sql;
