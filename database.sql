CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    twitch_id VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    bot_username VARCHAR(255) DEFAULT NULL,
    oauth_token VARCHAR(255) DEFAULT NULL,
    channel_name VARCHAR(255) NOT NULL,
    advent_command VARCHAR(255) DEFAULT '!advent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE advent_calendars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    door_number INT NOT NULL,
    is_open BOOLEAN DEFAULT FALSE,
    prize VARCHAR(255) DEFAULT NULL,
    giveaway_duration INT DEFAULT 300,
    winner_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
