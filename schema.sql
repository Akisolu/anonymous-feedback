-- 1. MAIN TABLES

CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS states (
    state_id SERIAL PRIMARY KEY,
    name VARCHAR(16) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS feedbacks (
    feedback_id SERIAL PRIMARY KEY,
    message TEXT NOT NULL,
    state_id INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TRACEABILITY TABLE

CREATE TABLE IF NOT EXISTS feedback_records (
    feedback_record_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    feedback_id INT NOT NULL,
    old_state_id INT NOT NULL,
    new_state_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. FOREIGN KEYS

-- RELATIONSHIP FEEDBACK -> STATE
ALTER TABLE feedbacks
    ADD FOREIGN KEY (state_id) 
    REFERENCES states(state_id);

-- RELATIONSHIP FEEDBACK_RECORD -> USER
ALTER TABLE feedback_records 
    ADD FOREIGN KEY (user_id) 
    REFERENCES users(user_id)
    ON DELETE CASCADE;

-- RELATIONSHIP FEEDBACK_RECORD -> FEEDBACK
ALTER TABLE feedback_records
    ADD FOREIGN KEY (feedback_id) 
    REFERENCES feedbacks(feedback_id)
    ON DELETE CASCADE;

-- RELATIONSHIP FEEDBACK_RECORDS -> STATE (NEW AND OLD)
-- OLD STATE
ALTER TABLE feedback_records
    ADD FOREIGN KEY (old_state_id) 
    REFERENCES states(state_id);

-- NEW STATE
ALTER TABLE feedback_records
    ADD FOREIGN KEY (new_state_id) 
    REFERENCES states(state_id);

-- 4. TRIGGER

CREATE OR REPLACE FUNCTION feedback_state_change() 
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_user_id INT;
BEGIN 
    IF OLD.state_id <> NEW.state_id THEN

        -- Try to get the configured user_id from the PHP app.
        v_user_id := COALESCE(
            NULLIF(current_setting('app.current_user_id', true), ''), 
            '1'
        )::INT;

        INSERT INTO feedback_records (user_id, feedback_id, old_state_id, new_state_id)
            VALUES (v_user_id, NEW.feedback_id, OLD.state_id, NEW.state_id);
    END IF;
    RETURN NEW;
END;
$$;

CREATE OR REPLACE TRIGGER trg_audit_feedback_state
AFTER UPDATE ON feedbacks
FOR EACH ROW
EXECUTE FUNCTION feedback_state_change();

INSERT INTO states (name) 
VALUES 
    ('unread'),
    ('read'),
    ('archived'),
    ('deleted')
ON CONFLICT (id_state) DO NOTHING;