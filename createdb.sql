-- My Personal Budget
-- mario.piccinelli@gmail.com

-- Istruzioni per generare il database
-- 1) sqlite3 my_database.db
-- 2) .read createdb.sql
-- 3) .exit

DROP TABLE users;
CREATE TABLE users (
	id integer primary key autoincrement,
	username varchar(30),
	password varchar(30),
	userlevel integer,
	email varchar(40),
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO users VALUES (1, "mario", "cd827c33216d4e6e46bc60699f3e18a1", 0, "io@me.it", null, null);
INSERT INTO users VALUES (2, "admin", "cd827c33216d4e6e46bc60699f3e18a1", 1, "io@me.it", null, null);

DROP TABLE accounts;
CREATE TABLE accounts (
	id integer primary key autoincrement,
	description varchar(30),
	user_id integer,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO accounts (id, description, user_id) VALUES (1, "Conto Principale", 1);
INSERT INTO accounts (id, description, user_id) VALUES (2, "Conti Università", 1);
INSERT INTO accounts (id, description, user_id) VALUES (3, "conto admin", 2);

DROP TABLE categories;
CREATE TABLE categories (
	id integer primary key autoincrement,
	name varchar(20),
	description varchar(40),
	user_id integer,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO categories (id, name, description, user_id) VALUES (1, "alimentari", "roba da mangiare", 1);
INSERT INTO categories (id, name, description, user_id) VALUES (2, "scuola", "roba di scuola", 1);
INSERT INTO categories (id, name, description, user_id) VALUES (3, "lavoro", "stipendio, e cose del genere", 1);
INSERT INTO categories (id, name, description, user_id) VALUES (4, "frivolezze", "spese varie personali", 1);
INSERT INTO categories (id, name, description, user_id) VALUES (5, "admin", "descrizione di prova", 2);

DROP TABLE transactions;
CREATE TABLE transactions (
	id integer primary key autoincrement,
	description varchar(30),
	note text,
	account_id integer,
	category_id integer,
	date datetime,
	import float,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Maglietta", 1, '2010-01-31', -30, 2);
INSERT INTO transactions (description, account_id, date, import, category_id, note) 
	VALUES ("Pizza a pranzo", 1, '2010-12-01', -5.50, 1, "mmm che buona :-)");
INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Pranzo con amici", 1, '2010-12-08', -25, 1);
INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Stipendio", 1, '2010-12-31', 1000, 3);
INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Stipendio", 1, '2010-11-30', 1000, 3);
INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Stipendio", 1, '2010-10-31', 1000, 3);
INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Stipendio", 1, '2010-09-30', 1000, 3);
INSERT INTO transactions (description, account_id, date, import, category_id) 
	VALUES ("Affitto appartamento", 2, '2010-12-01', -150, 2);

	