-- SBI AI Hackathon Chatbot - MySQL starter schema
-- Compatible with MySQL 8.0+

CREATE DATABASE IF NOT EXISTS sbi_chatbot_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE sbi_chatbot_db;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_phone (phone)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS chats (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL DEFAULT NULL,
    message TEXT NOT NULL,
    sender_type ENUM('user', 'bot') NOT NULL,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_chats_user_timestamp (user_id, `timestamp`),
    CONSTRAINT fk_chats_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

-- Also migrate databases created with the earlier, non-nullable definition.
ALTER TABLE chats
    MODIFY user_id BIGINT UNSIGNED NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS faqs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    keyword VARCHAR(100) NOT NULL,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    PRIMARY KEY (id),
    KEY idx_faqs_keyword (keyword)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

INSERT INTO faqs (keyword, question, answer)
VALUES
    (
        'digital adoption',
        'How can I get started with digital banking?',
        'Use your bank’s official website or verified mobile banking app and follow its current registration and help instructions. Never share your password, PIN, or one-time passcode in chat. Contact the bank through an official channel if you need account-specific help.'
    ),
    (
        'fund transfer',
        'How do I make a fund transfer?',
        'Use an authenticated, official banking channel and follow the transfer instructions shown there. Before authorizing a transfer, carefully verify the recipient and amount. Never share your password, PIN, or one-time passcode with anyone. For a failed or unrecognized transfer, contact the bank using an official support channel.'
    ),
    (
        'YONO app help',
        'Where can I get help with the YONO app?',
        'For YONO installation, sign-in, or troubleshooting, use the official app store listing, the bank’s official website, or its verified customer-support channel. Do not install apps from links sent by unknown contacts, and never share your password, PIN, or one-time passcode.'
    );
