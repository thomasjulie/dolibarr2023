-- Copyright (C) 2023 SuperAdmin <informatique@infans.fr>
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


CREATE TABLE llx_creche_contrats(
	-- BEGIN MODULEBUILDER FIELDS
	rowid int AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_enfants int NOT NULL, 
	entity int DEFAULT 1 NOT NULL, 
	type varchar(100) NOT NULL, 
	date_start date NOT NULL, 
	date_end date NOT NULL, 
	nb_day int, 
	days_of_week varchar(100), 
	hours_of_day varchar(250) NOT NULL, 
	date_created datetime NOT NULL, 
	date_signed datetime, 
	file_path varchar(250)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
