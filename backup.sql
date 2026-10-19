CREATE TABLE IF NOT EXISTS connection_test (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    message VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO connection_test (message)
VALUES ('Koneksi PostgreSQL berhasil!');