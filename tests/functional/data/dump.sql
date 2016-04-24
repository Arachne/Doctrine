CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL UNIQUE, PRIMARY KEY(id), FOREIGN KEY (article_id) REFERENCES article (id));

INSERT INTO article (id, name) VALUES (1, "Lorem Ipsum");
INSERT INTO page (id, article_id) VALUES (1, 1);