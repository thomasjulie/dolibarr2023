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


CREATE TABLE llx_creche_factures(
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
	fk_contrat int NOT NULL,
	entity INTEGER DEFAULT 1 NOT NULL,
	num_fac varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
	type enum('facture','avoir') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'facture',
	total_ht double(24,8) DEFAULT '0.00000000',
	total_tva double(24,8) DEFAULT '0.00000000',
	total_ttc double(24,8) DEFAULT '0.00000000',
	date_creation datetime NOT NULL,
	date_facturation datetime NOT NULL,
	date_lim_reglement date NOT NULL,
	status int NOT NULL DEFAULT '0',
	paye tinyint(1) NOT NULL DEFAULT '0',
	fk_user_create int NOT NULL,
	fk_user_update int NOT NULL,
	date_update datetime NOT NULL,
	fk_facture_source int DEFAULT NULL,
	fk_cond_reglement int NOT NULL DEFAULT '1',
	fk_mode_reglement int DEFAULT NULL
) ENGINE=innodb;
