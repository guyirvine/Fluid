CREATE SEQUENCE queue_seq;
CREATE SEQUENCE outbox_seq;
CREATE SEQUENCE inbox_seq;


CREATE TABLE queue_tbl ( 
	id BIGINT NOT NULL,
	name VARCHAR(2048) NOT NULL,
	CONSTRAINT queue_pk PRIMARY KEY ( id ),
	CONSTRAINT queue_name_unq UNIQUE (name) );


CREATE TABLE outbox_tbl (
	id BIGINT NOT NULL,
	destination VARCHAR(2048) NOT NULL,
	data VARCHAR(4096) NOT NULL,
	created TIMESTAMP NOT NULL,
	CONSTRAINT outbox_pk PRIMARY KEY ( id ) );


CREATE TABLE inbox_tbl (
	id BIGINT NOT NULL,
	queue_id BIGINT NOT NULL,
	data VARCHAR(4096) NOT NULL,
	created TIMESTAMP NOT NULL,
	CONSTRAINT inbox_pk PRIMARY KEY ( id ),
	CONSTRAINT inbox_queue_fk FOREIGN KEY ( queue_id ) REFERENCES queue_tbl ( id ) );

