CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120)      NOT NULL,
    email           VARCHAR(120)      NOT NULL UNIQUE,
    password_hash   CHAR(60)          NOT NULL,
    is_active       TINYINT(1)        DEFAULT 1,
    created_at      DATETIME          DEFAULT CURRENT_TIMESTAMP,
    last_login_at   DATETIME          NULL
);

CREATE TABLE rooms (
    id          CHAR(8) PRIMARY KEY,
    owner_id    BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(120)    NOT NULL,
    description TEXT,
    status      ENUM('inactive','active','finished') DEFAULT 'inactive',
    created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,
    started_at  DATETIME NULL,
    ended_at    DATETIME NULL
);

CREATE TABLE subscriptions (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NOT NULL,
  	price_cents   INT UNSIGNED NOT NULL,
    started_at    DATE NOT NULL,
    ends_at       DATE NOT NULL
);

CREATE TABLE payments (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    amount_cents  INT UNSIGNED NOT NULL,
    method        ENUM('pix','credit_card'),
    pix_qr_id     VARCHAR(80) NULL,
    status        ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    paid_at       DATETIME NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    INDEX (status, created_at)
);