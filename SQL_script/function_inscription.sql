CREATE OR REPLACE FUNCTION register_student(
    n_email character varying(30), 
    n_lname character varying(50), 
    n_fname character varying(50), 
    n_password character varying(255),
    n_phone character varying(10), 
    n_dateofbirth date,
    n_city character varying(30), 
    n_amuid character varying(15), 
    n_specialisation character varying(1), 
    n_year integer,
    n_td character varying(3), 
    n_tp character varying(3)
) RETURNS TABLE(
    user_id character varying(30),
    success BOOLEAN
) AS $$
DECLARE
    duplicates_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO duplicates_count
    FROM USERS
    WHERE email = n_email;
    
    IF duplicates_count > 0 THEN
        RETURN QUERY SELECT NULL::character varying(30), FALSE; 
        RETURN;
    END IF;
    
    INSERT INTO STUDENT(amuID, specialisation, year, TD, TP) 
    VALUES (n_amuid, n_specialisation, n_year, n_td, n_tp);
    
    INSERT INTO USERS (email, last_name, first_name, password, phone, dateofbirth, city, amuid, amuid2)     
    VALUES (n_email, n_lname, n_fname, n_password, n_phone, n_dateofbirth, n_city, n_amuid, NULL);
    
    RETURN QUERY SELECT n_email, TRUE;
    RETURN; 
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION register_teacher(
    n_email character varying(30), 
    n_lname character varying(50), 
    n_fname character varying(50), 
    n_password character varying(255),
    n_phone character varying(10), 
    n_dateofbirth date,
    n_city character varying(30), 
    n_amuid character varying(15)
) RETURNS TABLE(
    user_id character varying(30),
    success BOOLEAN
) AS $$
DECLARE
    duplicates_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO duplicates_count
    FROM USERS
    WHERE email = n_email;
    
    IF duplicates_count > 0 THEN
        RETURN QUERY SELECT NULL::character varying(30), FALSE; 
        RETURN;
    END IF;
    
    INSERT INTO SUPERVISOR_SAE(amuID, name_SAE)
    VALUES (n_amuid, NULL);
    
    INSERT INTO USERS (email, last_name, first_name, password, phone, dateofbirth, city, amuid, amuid2)     
    VALUES (n_email, n_lname, n_fname, n_password, n_phone, n_dateofbirth, n_city, NULL, n_amuid);
    
    RETURN QUERY SELECT n_email, TRUE;
    RETURN; 
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION register_user(
    n_email character varying(30), 
    n_lname character varying(50), 
    n_fname character varying(50), 
    n_password character varying(255),
    n_phone character varying(10),
    n_dateofbirth date,
    n_city character varying(30) 
) RETURNS TABLE(
    user_id character varying(30),
    success BOOLEAN
) AS $$
DECLARE
    duplicates_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO duplicates_count
    FROM USERS
    WHERE email = n_email;
    
    IF duplicates_count > 0 THEN
        RETURN QUERY SELECT NULL::character varying(30), FALSE; 
        RETURN;
    END IF;
    
    INSERT INTO USERS (email, last_name, first_name, password, phone, dateofbirth, city, amuid, amuid2)     
    VALUES (n_email, n_lname, n_fname, n_password, n_phone, n_dateofbirth, n_city, NULL, NULL);
    
    RETURN QUERY SELECT n_email, TRUE;
    RETURN; 
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE VIEW users_email AS
SELECT email FROM USERS;

CREATE OR REPLACE FUNCTION connection(
    n_email character varying(30)
) RETURNS TABLE(
    user_id character varying(30),
    pwd character varying(255),
    success BOOLEAN
) AS $$
DECLARE 
    true_pwd character varying(255);
BEGIN
    IF n_email IN (SELECT email FROM users_email) THEN
        SELECT password INTO true_pwd
        FROM USERS
        WHERE email = n_email;
        
        RETURN QUERY SELECT n_email, true_pwd, TRUE;
        RETURN;
    ELSE
        RETURN QUERY SELECT NULL::character varying(30), NULL::character varying(255), FALSE;
        RETURN;
    END IF;
END;
$$ LANGUAGE plpgsql;