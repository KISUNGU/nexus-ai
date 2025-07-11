BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS archived_document (
	id INTEGER NOT NULL, 
	document_id_str VARCHAR(100) NOT NULL, 
	document_type VARCHAR(100) NOT NULL, 
	tags VARCHAR(500), 
	archived_at DATETIME, 
	location VARCHAR(200), 
	PRIMARY KEY (id), 
	UNIQUE (document_id_str)
);
CREATE TABLE IF NOT EXISTS client (
	id INTEGER NOT NULL, 
	name VARCHAR(100) NOT NULL, 
	email VARCHAR(100), 
	company VARCHAR(100), 
	status VARCHAR(50), 
	PRIMARY KEY (id), 
	UNIQUE (name)
);
CREATE TABLE IF NOT EXISTS email (
	id INTEGER NOT NULL, 
	recipient VARCHAR(200) NOT NULL, 
	subject VARCHAR(200) NOT NULL, 
	body TEXT, 
	sent_at DATETIME, 
	status VARCHAR(50), 
	PRIMARY KEY (id)
);
CREATE TABLE IF NOT EXISTS employee (
	id INTEGER NOT NULL, 
	name VARCHAR(100) NOT NULL, 
	position VARCHAR(100), 
	department VARCHAR(100), 
	email VARCHAR(100), 
	phone VARCHAR(50), 
	hire_date VARCHAR(10), 
	PRIMARY KEY (id), 
	UNIQUE (name)
);
CREATE TABLE IF NOT EXISTS meeting (
	id INTEGER NOT NULL, 
	subject VARCHAR(200) NOT NULL, 
	date VARCHAR(10) NOT NULL, 
	time VARCHAR(5) NOT NULL, 
	participants VARCHAR(500), 
	status VARCHAR(50), 
	created_at DATETIME, 
	PRIMARY KEY (id)
);
CREATE TABLE IF NOT EXISTS project_task (
	id INTEGER NOT NULL, 
	project_name VARCHAR(100) NOT NULL, 
	task_description VARCHAR(500) NOT NULL, 
	due_date VARCHAR(10), 
	assigned_to VARCHAR(100), 
	status VARCHAR(50), 
	created_at DATETIME, 
	PRIMARY KEY (id)
);
CREATE TABLE IF NOT EXISTS sale (
	id INTEGER NOT NULL, 
	quarter VARCHAR(10) NOT NULL, 
	amount FLOAT NOT NULL, 
	product VARCHAR(100) NOT NULL, 
	date_recorded DATETIME, 
	PRIMARY KEY (id)
);
CREATE TABLE IF NOT EXISTS system_status (
	id INTEGER NOT NULL, 
	system_name VARCHAR(100) NOT NULL, 
	status VARCHAR(50) NOT NULL, 
	last_checked DATETIME, 
	PRIMARY KEY (id), 
	UNIQUE (system_name)
);
CREATE TABLE IF NOT EXISTS ticket (
	id INTEGER NOT NULL, 
	ticket_id_str VARCHAR(50) NOT NULL, 
	subject VARCHAR(200) NOT NULL, 
	status VARCHAR(50) NOT NULL, 
	assigned_to VARCHAR(100), 
	created_at DATETIME, 
	PRIMARY KEY (id), 
	UNIQUE (ticket_id_str)
);
INSERT INTO "client" ("id","name","email","company","status") VALUES (1,'Alice Dupont','alice.dupont@example.com','Tech Solutions Inc.','Active'),
 (2,'Bob Martin','bob.martin@example.com','Global Innovations','Lead');
INSERT INTO "employee" ("id","name","position","department","email","phone","hire_date") VALUES (1,'Charles Dubois','Développeur Senior','R&D','charles.dubois@example.com','555-1234','2020-01-15'),
 (2,'Émilie Bernard','Chef de Projet','Opérations','emilie.bernard@example.com','555-5678','2018-06-01'),
 (3,'Mboyo',NULL,NULL,'mboyo@123.com',NULL,NULL);
INSERT INTO "project_task" ("id","project_name","task_description","due_date","assigned_to","status","created_at") VALUES (1,'Projet Alpha','Développement de la fonctionnalité X','2025-08-01','Charles Dubois','In Progress','2025-07-11 13:55:03.305635'),
 (2,'Projet Beta','Rédaction du rapport financier','2025-07-20','Émilie Bernard','Open','2025-07-11 13:55:03.305635');
INSERT INTO "sale" ("id","quarter","amount","product","date_recorded") VALUES (1,'T1',1250000.0,'Nexus Alpha','2025-07-11 13:55:03.246364'),
 (2,'T2',1780000.0,'Nexus X200','2025-07-11 13:55:03.246364'),
 (3,'T3',1500000.0,'Nexus Beta','2025-07-11 13:55:03.246364'),
 (4,'T4',2100000.0,'Nexus Prime','2025-07-11 13:55:03.246364');
INSERT INTO "system_status" ("id","system_name","status","last_checked") VALUES (1,'CRM System','online','2025-07-11 13:55:03.284441'),
 (2,'ERP System','online','2025-07-11 13:55:03.284441'),
 (3,'HR Platform','offline','2025-07-11 13:55:03.284441');
INSERT INTO "ticket" ("id","ticket_id_str","subject","status","assigned_to","created_at") VALUES (1,'TICKET-001','Problème de connexion','Ouvert','Support Team','2025-07-11 13:55:03.274523'),
 (2,'TICKET-002','Demande de fonctionnalité','Fermé','Product Team','2025-07-11 13:55:03.274523');
COMMIT;
