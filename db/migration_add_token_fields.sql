-- Migration: add fields for better token management
ALTER TABLE token_api
ADD COLUMN nombre VARCHAR(255) DEFAULT NULL,
ADD COLUMN descripcion TEXT DEFAULT NULL,
ADD COLUMN auth_url VARCHAR(512) DEFAULT NULL,
ADD COLUMN expires_at DATETIME DEFAULT NULL,
ADD INDEX idx_token (token(64));
