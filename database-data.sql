DROP TABLE IF EXISTS users, posts, comments, superusers;

CREATE TABLE users (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    status BOOLEAN NOT NULL DEFAULT 1
);

CREATE TABLE posts (
    post_id SERIAL PRIMARY KEY,
    user_id VARCHAR(50) REFERENCES users(username),
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
    comment_id SERIAL PRIMARY KEY,
    post_id INT REFERENCES posts(post_id),
    user_id VARCHAR(50) REFERENCES users(username),
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE superusers (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(100) NOT NULL
);

INSERT INTO users (username, password, name, mobile_number, email, status) VALUES
('admin', MD5('MyPa$$w0rd'), 'Admin User', '1234567890', 'admin@gmail.com', 1),
('user1', MD5('passwordForUser1'), 'User One', '9876543210', 'user1@example.com', 1),
('user2', MD5('passwordForUser2'), 'User Two', '5551234567', 'user2@example.com', 1),
('user3', MD5('passwordForUser3'), 'User Three', '9998887777', 'user3@example.com', 1);


INSERT INTO posts (user_id, title, content)
VALUES
('admin', 'First Post', 'This is the content of the first post.'),
('admin', 'Second Post', 'This is the content of the second post.'),
('admin', 'Third Post', 'This is the content of the third post.');

INSERT INTO comments (post_id, user_id, comment_text) VALUES
(1, 'admin', 'This is a comment on the first post.'),
(1, 'admin', 'Another insightful comment on the first post.'),
(2, 'admin', 'This is a comment on the second post. Great job!'),
(2, 'admin', 'Engaging content in the second post, loved it.'),
(3, 'admin', 'This is a comment on the third post.'),
(3, 'admin', 'Really enjoyed the third post, looking forward to more.');

INSERT INTO superusers (username, password)
VALUES 
    ('superuser1', MD5('Superuser@1')),
    ('superuser2', MD5('Superuser@2')),
    ('superuser3', MD5('Superuser@3'));

