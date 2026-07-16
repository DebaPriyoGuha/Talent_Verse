-- TalentVerse schema update — run once on existing database
-- Adds 'tags' column to posts table for talent categories

ALTER TABLE posts
    ADD COLUMN IF NOT EXISTS tags VARCHAR(50) DEFAULT 'other';

-- Set existing posts to 'other' if tags is null
UPDATE posts SET tags = 'other' WHERE tags IS NULL OR tags = '';

-- Optional: create index for faster tag filtering
CREATE INDEX IF NOT EXISTS idx_posts_tags ON posts(tags);
