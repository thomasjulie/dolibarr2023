-- Copyright (C) 2023 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_creche_parents(
	-- BEGIN MODULEBUILDER FIELDS
	rowid int AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_famille int NOT NULL, 
	entity int DEFAULT 1 NOT NULL, 
	civilite enum("m", "mme") NOT NULL, 
	nom varchar(150) NOT NULL, 
	prenom varchar(150) NOT NULL, 
	adresse varchar(250) NOT NULL, 
	code_postal varchar(5) NOT NULL, 
	ville varchar(250) NOT NULL, 
	tel_portable varchar(20) NOT NULL, 
	mail varchar(150) NOT NULL, 
	login varchar(100) NOT NULL, 
	mdp varchar(150) NOT NULL, 
	notif tinyint(1), 
	mode_notif enum("mail","sms"), 
	fk_user_create integer NOT NULL, 
	date_create datetime NOT NULL, 
	fk_user_update integer NOT NULL, 
	date_update datetime NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
