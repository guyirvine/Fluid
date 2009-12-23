CREATE TABLE cache_tbl (
	key VARCHAR(2048) NOT NULL,
	created TIMESTAMP NOT NULL,
	data text,
	CONSTRAINT cache_pk PRIMARY KEY ( key ) );

CREATE TABLE cachedependency_tbl (
	from_key VARCHAR(2048) NOT NULL,
	to_key VARCHAR(2048) NOT NULL,
	CONSTRAINT cachedependency_pk PRIMARY KEY ( from_key, to_key ),
	CONSTRAINT  cachedependency_from_fk FOREIGN KEY ( from_key ) REFERENCES cache_tbl ( key ),
	CONSTRAINT  cachedependency_to_fk FOREIGN KEY ( to_key ) REFERENCES cache_tbl ( key ) );
