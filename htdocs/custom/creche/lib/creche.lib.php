<?php
/* Copyright (C) 2023 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    creche/lib/creche.lib.php
 * \ingroup creche
 * \brief   Library files with common functions for Creche
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function crecheAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("creche@creche");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/creche/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/creche/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/creche/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@creche:/creche/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@creche:/creche/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'creche@creche');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'creche@creche', 'remove');

	return $head;
}

/**
 * Controle e-mail
 */
function validateEmail($mail)
{
	return filter_var($mail, FILTER_VALIDATE_EMAIL);
}

/**
 * Controle téléphone protable
 */
function validateMobilePhone($phone)
{
	return preg_match("#^(\+33|0)[67][0-9]{8}$#", $phone);
}

/**
 * Controle code postal
 */
function validatePostalCode($postalCode)
{
	return preg_match("#^[0-9]{5}$#",$postalCode)	;
}

/**
 * Get the path for the documents of an object
 * 
 * @param int $objectType type of object (famille, parents, enfants, ...)
 * @param int $objectId ID of the object
 * @return 	string
 */
function getOutPutDir($objectType, $objectId)
{
	global $dolibarr_main_data_root;
	return $dolibarr_main_data_root . '/creche/' . strtolower($objectType) . '/' . $objectId;
}

function getNbDay($start, $end, $dayName)
{
	$daysOfWeek = array(
		'monday' => 1,
		'tuesday' => 2,
		'wednesday' => 3,
		'thursday' => 4,
		'friday' => 5,
	);
	
	$period = new DatePeriod(
		new DateTime($start),
		DateInterval::createFromDateString('next ' . $dayName),
		(new DateTime($end))->modify('+1 day') // ajouter 1 jour à la date de fin car elle est exclue par défaut dans "datePeriod"
	);
	$nb = 0;
	foreach ($period as $value) {
		// echo '<pre>';var_dump($value->format('Y-m-d l'));echo '</pre>';
		if ($value->format("N") == $daysOfWeek[$dayName]) {
			$nb++;
		}	
	}
	return $nb;
}

function getRefEvenement($db)
{
	$selectRef = "SELECT ref FROM " . $db->prefix() . "actioncomm WHERE ref REGEXP '^[0-9]+$' ORDER BY cast(ref AS unsigned) DESC LIMIT 0,1";
    $refReq = $db->query($selectRef);
    $refLast = (int)$db->fetch_object($refReq)->ref; // dernière ref
    $refLast++; // faire +1 à la dernière ref
	return $refLast;
}

function getActionCodeId ($db, $actioncode)
{
	$selectCode = "SELECT id FROM " . $db->prefix() . "c_actioncomm WHERE code = '" . $actioncode . "'";
    $codeReq = $db->query($selectCode);
    $actionCodeId = $db->fetch_object($codeReq)->id;
	return $actionCodeId;
}