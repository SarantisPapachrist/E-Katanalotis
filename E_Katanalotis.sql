Drop database if exists E_Katanalotis;
Create database E_Katanalotis;
USE E_Katanalotis;

Create Table Users(
 PersonID int NOT NULL AUTO_INCREMENT,
 Username varchar(100) NOT NULL,
 Pass varchar(100) NOT NULL,
 Email varchar(100) NOT NULL,
 First_Name varchar(100) NOT NULL,
 Last_Name varchar(100) NOT NULL,
 score FLOAT DEFAULT 0,
 total_score FLOAT DEFAULT 0,
 tokens FLOAT DEFAULT 0,
 total_tokens FLOAT DEFAULT 0,
 adminstrator enum('admin','user') DEFAULT 'user',
 Index idx_username (Username),
 Primary Key(PersonID)     
)ENGINE=InnoDB;

CREATE TABLE Products (
 Product_ID INT NOT NULL AUTO_INCREMENT,
 Product_name VARCHAR(150) NOT NULL,
 Category VARCHAR(250) NOT NULL,
 SubCategory VARCHAR(250) NOT NULL,
 INDEX idx_product_name (Product_name),
 PRIMARY KEY (Product_ID)
)ENGINE=InnoDB;

CREATE TABLE Prices (
 Price FLOAT(10),
 Product_name VARCHAR(150) NOT NULL,
 price_date DATE, 
 PRIMARY KEY (Product_name, price_date),
 INDEX idx_product_name (Product_name),
 CONSTRAINT prod_name FOREIGN KEY (Product_name) REFERENCES Products(Product_name) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=InnoDB;

Create Table Offers(
 offer_id INT NOT NULL AUTO_INCREMENT,
 Category VARCHAR(255),
 SubCategory VARCHAR(255),
 Price FLOAT(20),
 Shop_id VARCHAR(255),
 shop_name VARCHAR(400),
 Likes INT,
 Dislikes INT,
 Apothema BOOLEAN,
 Product VARCHAR(500) NOT NULL,
 Pusername VARCHAR(100) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(offer_id),
 CONSTRAINT fk_product FOREIGN KEY (Product) REFERENCES Products(Product_name) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_pusr FOREIGN KEY (Pusername) REFERENCES User(Username) ON DELETE RESTRICT ON UPDATE CASCADE	
)ENGINE=InnoDB;

Create Table LikesDislikes(
 offer_id INT NOT NULL,
 person_id INT NOT NULL,
 Click1 enum("like", "dislike"),
 PRIMARY KEY(offer_id, person_id),
 CONSTRAINT fk_offer_id FOREIGN KEY (offer_id) REFERENCES Offers(offer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
 CONSTRAINT fk_person_id FOREIGN KEY (person_id) REFERENCES User(PersonID) ON DELETE RESTRICT ON UPDATE CASCADE
)ENGINE=InnoDB;

DROP TABLE IF EXISTS Sunolika_Tokens;
Create Table Sunolika_Tokens(
  sunolika_tokens BIGINT DEFAULT 0
)ENGINE=InnoDB;
INSERT INTO Sunolika_Tokens (sunolika_tokens) VALUES (0);

INSERT INTO Users(Username, Pass, Email, First_Name, Last_Name) Values
('lefteris', 123, 'lefteris@mail.com', 'Lefteris', 'Amitsis'),
('sarantis', 456, 'sarantis@mail.com', 'Sarantis', 'Papachristofilou');

DROP EVENT IF EXISTS Zero_Score;
CREATE EVENT Zero_Score
ON SCHEDULE EVERY 1 MONTH
STARTS TIMESTAMPADD(MONTH, 1, CURRENT_TIMESTAMP)
DO
UPDATE User SET score = 0;

DROP EVENT IF EXISTS Zero_Tokens;
CREATE EVENT Zero_Tokens
ON SCHEDULE EVERY 1 MONTH
STARTS TIMESTAMPADD(MONTH, 1, CURRENT_TIMESTAMP)
DO
UPDATE User SET tokens = 0;

DROP EVENT IF EXISTS Expired_Offers;
DELIMITER //
CREATE EVENT Expired_Offers
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DECLARE latest_price DECIMAL(10, 2);
    DECLARE avg_price DECIMAL(10, 2);
    
    SELECT Price INTO latest_price
    FROM Prices
    ORDER BY price_date DESC
    LIMIT 1;

    SELECT AVG(Price) INTO avg_price
    FROM Prices
    WHERE price_date >= YEARWEEK(DATE_SUB(NOW(), INTERVAL 1 WEEK)); 

    DELETE FROM Offers
    WHERE created_at <= TIMESTAMPADD(WEEK, -1, CURRENT_TIMESTAMP)
        AND (Price > latest_price * 0.8 AND Price > avg_price);
END//
DELIMITER ;

DROP EVENT IF EXISTS Ypologise_Tokens;
DROP EVENT IF EXISTS Moirase_Tokens;


DELIMITER //
CREATE EVENT Ypologise_Tokens
ON SCHEDULE EVERY 1 MONTH
STARTS TIMESTAMP(CONCAT(YEAR(CURRENT_TIMESTAMP), '-', MONTH(CURRENT_TIMESTAMP), '-01'), '00:00:00')
DO BEGIN
    DECLARE Total_Tokens INT;
    SELECT COUNT(*)*100 INTO Total_Tokens FROM User WHERE adminstrator = 'user';

    UPDATE Sunolika_Tokens SET sunolika_tokens = Total_Tokens;
END;
//
DELIMITER ;

DELIMITER //
CREATE EVENT Moirase_Tokens
ON SCHEDULE EVERY 1 MONTH
STARTS TIMESTAMP(LAST_DAY(CURRENT_DATE), '23:59:59') 
DO BEGIN
    DECLARE sunolo_tokens INT;
    DECLARE sunoliko_score INT;

    SELECT sunolika_tokens INTO sunolo_tokens FROM Sunolika_Tokens;
    SELECT SUM(score) INTO sunoliko_score FROM User;
	
    UPDATE User SET tokens = ROUND((score / sunoliko_score) * (sunolo_tokens * 0.8)),
    total_tokens = total_tokens + ROUND((score / sunoliko_score) * (sunolo_tokens * 0.8))
    WHERE adminstrator = 'user';
END;
//
DELIMITER ;