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

use Illuminate\Support\Arr;

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

function getNbDay($db, $start, $end, $dayNum, $entity)
{
	$daysOfWeek = array(
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
	);
	$dayName = $daysOfWeek[$dayNum];
	
	$period = new DatePeriod(
		new DateTime($start),
		DateInterval::createFromDateString('next ' . $dayName),
		(new DateTime($end))->modify('+1 day') // ajouter 1 jour à la date de fin car elle est exclue par défaut dans "datePeriod"
	);
	$nb = 0;
	foreach ($period as $value) {
		if ($value->format("N") == $dayNum) {
			$sql = "SELECT day FROM " . $db->prefix() . "creche_days_off 
			WHERE entity IN (0," . $entity . ") AND day = '" . $value->format('Y-m-d') . "'";
			$req = $db->query($sql);
			$closed = $db->num_rows($req);
			
			if ($closed == 0) {
				$nb++;
			}
		}	
	}
	return $nb;
}

function getNbMonth($db, $start, $end)
{	
	$period = new DatePeriod(
		new DateTime($start . '-01'),
		DateInterval::createFromDateString('1 month'),
		new DateTime($end)
	);
	$nb = 0;
	foreach ($period as $value) {
		$nb++;	
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

function getNextNumFac($db, $type = 'facture')
{
	if ($type == 'facture') {
		$newNum = 'ICFAC-';
	} else {
		$newNum = 'ICAVO-';
	}
	
	$sql = "SELECT num_fac, CAST(RIGHT(num_fac, 9) AS SIGNED) AS facOrder FROM " . $db->prefix() . "creche_factures 
			WHERE num_fac LIKE '" . $newNum . "%' ORDER BY facOrder DESC";
    $req = $db->query($sql);
    $lastNum = $db->fetch_object($req)->facOrder;

	$year = substr($lastNum, 0, 2);
	$month = substr($lastNum, 2, 2);
	if (date('y') != $year || date('m') != $month) {
		$newNum .= date('ym') . '00001';
	} else {
		$newNum .= $lastNum + 1;
	}
	
	return $newNum;
}

function calculPaje($db, $contratId, $month)
{
	$sql = "SELECT * FROM " . $db->prefix() . "creche_contrats 
			WHERE rowid = " . $contratId;
    $req = $db->query($sql);
	$contrat = $db->fetch_object($req);
	$start = new DateTime($contrat->date_start);
	
	$days = explode(';', $contrat->days_of_week);
	$nbDayWeek = ($contrat->type == 'occasionnel') ? 1 : count($days); // Nb de jours par semaine du contrat (de 1 à 5)
	$sql = "SELECT price_ttc FROM " . $db->prefix() . "product 
	WHERE ref = 'F" . $nbDayWeek . "J'";
	$req = $db->query($sql);
	$dailyPrice = $db->fetch_object($req)->price_ttc; // Prix journalier (prix service)
	
	if ($contrat->type == 'occasionnel') {
		$sql = "SELECT id, ref, datep, fk_action, code, label, fk_element, elementtype 
		FROM " . $db->prefix() . "actioncomm
		WHERE code = 'CRECHE_POINTAGE' 
		AND elementtype = 'enfants' 
		AND fk_element = " . $contrat->fk_enfants . " 
		AND label = 'arrivee' 
		AND datep LIKE '" . $month . "%' ";
		$req = $db->query($sql);
		$nbDaysTotal = $db->num_rows($req);
		$monthlyPrice = $nbDaysTotal * $dailyPrice; // Prix mensuel 
		
		return array($nbDaysTotal, $monthlyPrice);
	} else {
		$nbDays = array();
		$nbDaysTotal = 0; // Nb de jours annuel
		foreach ($days as $num) {
			$nbDays[$num] = getNbDay($db, $contrat->date_start, $contrat->date_end, $num, $contrat->entity);
			$nbDaysTotal += $nbDays[$num];
		}

		$nbMonth = getNbMonth($db, $start->format('Y-m'), $contrat->date_end); // Nb de mois sur le contrat (de 1 à 12)

		$annualPrice = $nbDaysTotal * $dailyPrice; // Prix annuel
		$monthlyPrice = $annualPrice / $nbMonth; // Prix mensuel (lissé)
		
		return array($nbDaysTotal / $nbMonth, $monthlyPrice);
	}

	
}