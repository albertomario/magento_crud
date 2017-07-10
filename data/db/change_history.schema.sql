DROP TABLE IF EXISTS change_history;
 
CREATE TABLE change_history (
    id int NOT NULL AUTO_INCREMENT,
    customer_id text NOT NULL,
    payload TEXT NULL,
    createdAt DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 
CREATE INDEX id ON change_history(id);