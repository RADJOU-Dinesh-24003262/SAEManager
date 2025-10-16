-- V1.1.0
-- **************************************************************************** Définition des données


CREATE TABLE SUPERVISOR_SAE (
	amuid character varying(15), 
	name_SAE character varying(20)
);

ALTER TABLE SUPERVISOR_SAE 
ADD CONSTRAINT PK_SUPERVISOR PRIMARY KEY(amuID);

CREATE TABLE STUDENT (
	amuid character varying(15), 
	specialisation character varying(1),
	year integer,
	TD character varying(3),
	TP character varying(3)
);

ALTER TABLE STUDENT 
ADD CONSTRAINT PK_STUDENT PRIMARY KEY(amuID);


CREATE TABLE USERS (
	email character varying(30),
	last_name character varying(50),
	first_name character varying(50),
	password character varying(255),
	phone character varying(10),
	dateofbirth character varying(40),
	city character varying(30),
	amuid character varying(15) DEFAULT null,
	amuid2 character varying(15) DEFAULT null
);

ALTER TABLE USERS 
ADD CONSTRAINT PK_USER PRIMARY KEY(email);

ALTER TABLE USERS 
ADD CONSTRAINT FK1_USER FOREIGN KEY(amuid) 
REFERENCES STUDENT(amuid);

ALTER TABLE USERS 
ADD CONSTRAINT FK2_USER FOREIGN KEY(amuid2) 
REFERENCES SUPERVISOR_SAE(amuid);


CREATE SEQUENCE password_resets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE password_resets (
    id integer NOT NULL,
    user_email character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    expires_at timestamp with time zone NOT NULL,
    used boolean DEFAULT false NOT NULL,
    CONSTRAINT chk_password_resets_expiry_after_creation CHECK ((expires_at > created_at))
);

ALTER TABLE password_resets ALTER COLUMN id SET DEFAULT nextval('public.password_resets_id_seq'::regclass);

ALTER TABLE password_resets
    ADD CONSTRAINT password_resets_pkey PRIMARY KEY (id);

ALTER TABLE password_resets
    ADD CONSTRAINT uq_password_resets_token UNIQUE (token);

CREATE INDEX idx_password_resets_email ON public.password_resets USING btree (user_email);

CREATE INDEX idx_password_resets_expires ON public.password_resets USING btree (expires_at);

CREATE INDEX idx_password_resets_used ON public.password_resets USING btree (used);

CREATE INDEX idx_email_hash ON USERS USING HASH(email);

-- **************************************************************************** Insertions des données test



INSERT INTO USERS(email, last_name, first_name, password, phone, dateofbirth, city, amuID, amuID2) 
VALUES ('nathan.griguer@etu.univ-amu.fr', 'Griguer', 'Nathan', '1234', '0766199323', '10-02-2006',  'Marseille', null, null);
INSERT INTO USERS(email, last_name, first_name, password, phone, dateofbirth, city, amuID, amuID2) 
VALUES ('dinesh.radjou@etu.univ-amu.fr', 'Radjou', 'Dinesh', 'piment', '0101010101','10-02-2006', 'Marseille', null, null);




ALTER TABLE users
ADD CONSTRAINT verif_email CHECK (email ~ '^[a-z0-9.-]+@[a-z0-9.-]{2,}\.[a-z]{2,4}$');

ALTER TABLE users
ADD CONSTRAINT verif_city_last_name_first_name CHECK (    
	city ~ '^[A-Za-z]+$' AND
    last_name ~ '^[A-Za-z]+$' AND
    first_name ~ '^[A-Za-z]+$');


ALTER TABLE users
ADD CONSTRAINT verif_password CHECK (length(password) >8);

ALTER TABLE users
ADD CONSTRAINT verif_phone CHECK (phone ~ '^(04|06|07)[0-9]{8}$');

ALTER TABLE student
ADD CONSTRAINT verif_year CHECK (year IN (1,2,3));

ALTER TABLE student
ADD CONSTRAINT verif_specialisation CHECK (specialisation IN ('A','B'));

ALTER TABLE student
ADD CONSTRAINT verif_td CHECK (td IN ('TD1', 'TD2', 'TD3', 'TD4'));


ALTER TABLE student
ADD CONSTRAINT verif_tp CHECK (tp IN ('TPA', 'TPB'));