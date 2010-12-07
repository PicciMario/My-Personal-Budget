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

INSERT INTO users VALUES (1, "mario", "cd827c33216d4e6e46bc60699f3e18a1", 0, "mario.piccinelli@gmail.com", null, null);
INSERT INTO users VALUES (2, "admin", "cd827c33216d4e6e46bc60699f3e18a1", 1, "mario.piccinelli@gmail.com", null, null);

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

DROP TABLE transactions;

CREATE TABLE transactions (
	id integer primary key autoincrement,
	description varchar(30),
	account_id integer,
	import float,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO transactions (description, account_id, import) VALUES ("Maglietta", 1, -30);
INSERT INTO transactions (description, account_id, import) VALUES ("Pizza a pranzo", 1, -5.50);
INSERT INTO transactions (description, account_id, import) VALUES ("Stipendio", 1, 1100);
INSERT INTO transactions (description, account_id, import) VALUES ("transazione di prova sul conto 2", 2, -1.30);

	