BEGIN TRANSACTION;

CREATE TABLE users (
	id integer primary key autoincrement,
	username varchar(30),
	password varchar(30),
	userlevel integer,
	email varchar(40),
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO "users" VALUES(1,'mario','cd827c33216d4e6e46bc60699f3e18a1',1,'io@me.it',NULL,NULL);
INSERT INTO "users" VALUES(2,'admin','cd827c33216d4e6e46bc60699f3e18a1',1,'io@me.it',NULL,NULL);

CREATE TABLE accounts (
	id integer primary key autoincrement,
	description varchar(30),
	user_id integer,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO "accounts" VALUES(1,'Conto Principale',1,NULL,NULL);
INSERT INTO "accounts" VALUES(2,'Conti Università',1,NULL,NULL);
INSERT INTO "accounts" VALUES(3,'conto admin',2,NULL,NULL);

CREATE TABLE categories (
	id integer primary key autoincrement,
	name varchar(20),
	description varchar(40),
	user_id integer,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO "categories" VALUES(1,'alimentari','roba da mangiare',1,NULL,NULL);
INSERT INTO "categories" VALUES(2,'scuola','roba di scuola',1,NULL,NULL);
INSERT INTO "categories" VALUES(3,'lavoro','stipendio, e cose del genere',1,NULL,NULL);
INSERT INTO "categories" VALUES(4,'frivolezze','spese varie personali',1,NULL,NULL);
INSERT INTO "categories" VALUES(5,'admin','descrizione di prova',2,NULL,NULL);

CREATE TABLE transactions (
	id integer primary key autoincrement,
	description varchar(30),
	note text,
	account_id integer,
	category_id integer,
	date datetime,
	import float,
	auto int,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO "transactions" VALUES(1,'Maglietta',NULL,1,2,'2010-01-31 00:00:00 CET',-30.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(2,'Pizza a pranzo','mmm che buona :-)',1,1,'2010-12-01 00:00:00 CET',-5.5,0,NULL,NULL);
INSERT INTO "transactions" VALUES(3,'Pranzo con amici',NULL,1,1,'2010-12-08 00:00:00 CET',-25.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(4,'Stipendio',NULL,1,3,'2010-12-31 00:00:00 CET',1000.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(5,'Stipendio',NULL,1,3,'2010-11-30 00:00:00 CET',1000.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(6,'Stipendio',NULL,1,3,'2010-10-31 00:00:00 CET',1000.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(7,'Stipendio',NULL,1,3,'2010-09-30 00:00:00 CET',1000.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(8,'Affitto appartamento',NULL,2,2,'2010-12-01 00:00:00 CET',-150.0,0,NULL,NULL);
INSERT INTO "transactions" VALUES(9,'chiusura mese',NULL,1,0,'2009-12-31 00:00:00 CET',0.0,1,'2010-12-31 11:19:33','2010-12-31 11:19:33');
INSERT INTO "transactions" VALUES(10,'chiusura mese',NULL,1,0,'2010-01-31 00:00:00 CET',-30.0,1,'2010-12-31 11:21:01','2010-12-31 11:21:01');
INSERT INTO "transactions" VALUES(11,'chiusura mese',NULL,1,0,'2010-02-28 00:00:00 CET',-30.0,1,'2010-12-31 11:21:03','2010-12-31 11:21:03');
INSERT INTO "transactions" VALUES(12,'chiusura mese',NULL,1,0,'2010-03-31 00:00:00 CEST',-30.0,1,'2010-12-31 11:21:05','2010-12-31 11:21:05');
INSERT INTO "transactions" VALUES(13,'chiusura mese',NULL,1,0,'2010-04-30 00:00:00 CEST',-30.0,1,'2010-12-31 11:21:07','2010-12-31 11:21:07');
INSERT INTO "transactions" VALUES(14,'chiusura mese',NULL,1,0,'2010-05-31 00:00:00 CEST',-30.0,1,'2010-12-31 11:21:09','2010-12-31 11:21:09');
INSERT INTO "transactions" VALUES(15,'chiusura mese',NULL,1,0,'2010-06-30 00:00:00 CEST',-30.0,1,'2010-12-31 11:21:11','2010-12-31 11:21:11');
INSERT INTO "transactions" VALUES(16,'chiusura mese',NULL,1,0,'2010-07-31 00:00:00 CEST',-30.0,1,'2010-12-31 11:21:13','2010-12-31 11:21:13');
INSERT INTO "transactions" VALUES(17,'chiusura mese',NULL,1,0,'2010-08-31 00:00:00 CEST',-30.0,1,'2010-12-31 11:21:16','2010-12-31 11:21:16');
INSERT INTO "transactions" VALUES(18,'chiusura mese',NULL,1,0,'2010-09-30 00:00:00 CEST',970.0,1,'2010-12-31 11:21:18','2010-12-31 11:21:18');
INSERT INTO "transactions" VALUES(19,'chiusura mese',NULL,1,0,'2010-10-31 00:00:00 CEST',1970.0,1,'2010-12-31 11:21:20','2010-12-31 11:21:20');
INSERT INTO "transactions" VALUES(20,'chiusura mese',NULL,1,0,'2010-11-30 00:00:00 CET',2970.0,1,'2010-12-31 11:21:23','2010-12-31 11:21:23');
INSERT INTO "transactions" VALUES(21,'chiusura mese',NULL,1,0,'2010-12-31 00:00:00 CET',3939.5,1,'2010-12-31 11:21:25','2010-12-31 11:21:25');
INSERT INTO "transactions" VALUES(22,'chiusura mese',NULL,2,0,'2009-12-31 00:00:00 CET',0.0,1,'2010-12-31 11:24:32','2010-12-31 11:24:32');
INSERT INTO "transactions" VALUES(23,'chiusura mese',NULL,2,0,'2010-01-31 00:00:00 CET',0.0,1,'2010-12-31 11:24:35','2010-12-31 11:24:35');
INSERT INTO "transactions" VALUES(24,'chiusura mese',NULL,2,0,'2010-02-28 00:00:00 CET',0.0,1,'2010-12-31 11:24:37','2010-12-31 11:24:37');
INSERT INTO "transactions" VALUES(25,'chiusura mese',NULL,2,0,'2010-03-31 00:00:00 CEST',0.0,1,'2010-12-31 11:24:39','2010-12-31 11:24:39');
INSERT INTO "transactions" VALUES(26,'chiusura mese',NULL,2,0,'2010-04-30 00:00:00 CEST',0.0,1,'2010-12-31 11:24:41','2010-12-31 11:24:41');
INSERT INTO "transactions" VALUES(27,'chiusura mese',NULL,2,0,'2010-05-31 00:00:00 CEST',0.0,1,'2010-12-31 11:24:44','2010-12-31 11:24:44');
INSERT INTO "transactions" VALUES(28,'chiusura mese',NULL,2,0,'2010-06-30 00:00:00 CEST',0.0,1,'2010-12-31 11:24:46','2010-12-31 11:24:46');
INSERT INTO "transactions" VALUES(29,'chiusura mese',NULL,2,0,'2010-07-31 00:00:00 CEST',0.0,1,'2010-12-31 11:24:48','2010-12-31 11:24:48');
INSERT INTO "transactions" VALUES(30,'chiusura mese',NULL,2,0,'2010-08-31 00:00:00 CEST',0.0,1,'2010-12-31 11:24:51','2010-12-31 11:24:51');
INSERT INTO "transactions" VALUES(31,'chiusura mese',NULL,2,0,'2010-09-30 00:00:00 CEST',0.0,1,'2010-12-31 11:24:54','2010-12-31 11:24:54');
INSERT INTO "transactions" VALUES(32,'chiusura mese',NULL,2,0,'2010-10-31 00:00:00 CEST',0.0,1,'2010-12-31 11:24:56','2010-12-31 11:24:56');
INSERT INTO "transactions" VALUES(33,'chiusura mese',NULL,2,0,'2010-11-30 00:00:00 CET',0.0,1,'2010-12-31 11:24:59','2010-12-31 11:24:59');
INSERT INTO "transactions" VALUES(34,'chiusura mese',NULL,2,0,'2010-12-31 00:00:00 CET',-150.0,1,'2010-12-31 11:25:02','2010-12-31 11:25:02');

CREATE TABLE tags (
	id integer primary key autoincrement,
	name varchar(30),
	user_id integer,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO "tags" VALUES(1,'natale',1,NULL,NULL);
INSERT INTO "tags" VALUES(2,'ufficio',1,NULL,NULL);
INSERT INTO "tags" VALUES(3,'bollette',1,NULL,NULL);
INSERT INTO "tags" VALUES(4,'pulizie',1,NULL,NULL);

CREATE TABLE transactiontags (
	id integer primary key autoincrement,
	transaction_id integer,
	tag_id integer,
	created_at varchar(30),
	updated_at varchar(30)
);

INSERT INTO "transactiontags" VALUES(1,2,2,NULL,NULL);
INSERT INTO "transactiontags" VALUES(2,3,2,NULL,NULL);

DELETE FROM sqlite_sequence;

INSERT INTO "sqlite_sequence" VALUES('users',2);
INSERT INTO "sqlite_sequence" VALUES('accounts',3);
INSERT INTO "sqlite_sequence" VALUES('categories',5);
INSERT INTO "sqlite_sequence" VALUES('transactions',34);
INSERT INTO "sqlite_sequence" VALUES('tags',4);
INSERT INTO "sqlite_sequence" VALUES('transactiontags',2);

COMMIT;
