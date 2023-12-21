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
	
	$sql = "SELECT ref, CAST(RIGHT(ref, 9) AS SIGNED) AS facOrder FROM " . $db->prefix() . "facture 
			WHERE ref LIKE '" . $newNum . "%' ORDER BY facOrder DESC";
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

// Calcul pour la facture d'un contrat PAJE
function calculPaje($db, $contratId, $month)
{
	$sql = "SELECT * FROM " . $db->prefix() . "creche_contrats 
			WHERE rowid = " . $contratId;
    $req = $db->query($sql);
	$contrat = $db->fetch_object($req);
	$start = new DateTime($contrat->date_start);
	
	$days = explode(';', $contrat->days_of_week);
	$nbDayWeek = ($contrat->type == 'occasionnel') ? 1 : count($days); // Nb de jours par semaine du contrat (de 1 à 5)
	$sql = "SELECT rowid, price_ttc FROM " . $db->prefix() . "product 
	WHERE ref = 'F" . $nbDayWeek . "J'";
	$req = $db->query($sql);
	$product = $db->fetch_object($req);
	$dailyPrice = $product->price_ttc; // Prix journalier (prix service)
	
	if ($contrat->type == 'occasionnel') { // Pour contrat occasionnel on utilise le pointage
		$sql = "SELECT id, ref, datep, fk_action, code, label, fk_element, elementtype 
		FROM " . $db->prefix() . "actioncomm
		WHERE code = 'CRECHE_POINTAGE' 
		AND elementtype = 'enfants' 
		AND fk_element = " . $contrat->fk_enfants . " 
		AND label = 'arrivee' 
		AND datep LIKE '" . $month . "%' ";
		$req = $db->query($sql);
		$nbDaysTotal = $db->num_rows($req); // Nombre de jours du mois
		$monthlyPrice = $nbDaysTotal * $dailyPrice; // Prix mensuel 
		
		return array($nbDaysTotal, $monthlyPrice, $product->rowid);
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
		
		return array($nbDaysTotal / $nbMonth, $monthlyPrice, $product->rowid);
	}
}

// Calcul pour la facture d'un contrat PSU
function calculPsu($db, $contratId, $month)
{
	$sql = "SELECT * FROM " . $db->prefix() . "creche_contrats 
			WHERE rowid = " . $contratId;
    $req = $db->query($sql);
	$contrat = $db->fetch_object($req);
	
	// Enfant
	$sql = "SELECT * FROM " . $db->prefix() . "creche_enfants 
			WHERE rowid = " . $contrat->fk_enfants;
    $req = $db->query($sql);
	$enfant = $db->fetch_object($req);
	
	// Total des salaires et assimilés, nombre d'enfants
	$sql = "SELECT total_salaires, nb_enfants FROM " . $db->prefix() . "creche_famille 
			WHERE rowid = " . $enfant->fk_famille;
    $req = $db->query($sql);
	$infos_famille = $db->fetch_object($req);

	// Taux d'effort
	$sql = "SELECT taux FROM " . $db->prefix() . "creche_tx_effort 
			WHERE nb_enfants = " . $infos_famille->nb_enfants;
    $req = $db->query($sql);
	$tx_effort = $db->fetch_object($req)->taux;
	
	// Pointages
	$sql = "SELECT datep, code, label, fk_element  
		FROM " . $db->prefix() . "actioncomm
		WHERE code = 'CRECHE_POINTAGE' 
		AND elementtype = 'enfants' 
		AND fk_element = " . $contrat->fk_enfants . " 
		AND datep LIKE '" . $month . "%' ";
    $req = $db->query($sql);
	$pointages = array();
	while ($tmp = $db->fetch_object($req)) {
		$date = explode(' ', $tmp->datep);
		$pointages[$date[0]][$tmp->label] = $tmp;
	}

	$monthlyHours = 0; // Nombre d'heures mensuelle
	$minuteToHour = array( // Convertir les minutes en heures
		'15' => 0.25,
		'30' => 0.5,
		'45' => 0.75
	);
	foreach ($pointages as $day => $values) { // Calcul des heures de présences réelles 
		$arrival = new DateTimeImmutable($values['arrivee']->datep);
		$departure = new DateTimeImmutable($values['depart']->datep);
		$interval = $departure->diff($arrival);
		$diff = $interval->format('%H:%i');
		$time = (round(strtotime($diff) / 900)) * 900; // Arrondi au 1/4 heure le plus proche
		$rounded = date('h', $time) + $minuteToHour[date('i', $time)];
		$monthlyHours += $rounded;
	}

	$hourlyPrice = (floatval($infos_famille->total_salaires) / 12) * $tx_effort / 100; // Tarif horaire
	$monthlyPrice = $hourlyPrice * $monthlyHours; // Prix mensuel

	return array($monthlyHours, $monthlyPrice, $hourlyPrice); 
}

function factuAddLine($db, $rowid, $socid, $contratid, $dateFac)
{
	$sql = "SELECT entity  
	FROM " . $db->prefix() . "creche_famille WHERE fk_societe = " . $socid;
	$req = $db->query($sql);
	$entityId = $db->fetch_object($req)->entity;
	
	// Trouver le type de la crèche PAJE / PSU
	$sql = "SELECT `type`  
	FROM " . $db->prefix() . "entity_extrafields WHERE fk_object = " . $entityId;
	$req = $db->query($sql);
	$typeCreche = $db->fetch_object($req)->type;
	$date = new DateTime($dateFac);
	$date->modify('-1 month');
	
	if ($typeCreche == 'paje') {
		list($qty, $monthlyPrice, $fk_product) = calculPaje($db, $contratid, $date->format('Y-m'));
		$subPrice = $monthlyPrice / $qty;
		$qty = round($qty, 2);
		$product_type = 1;
	} else {
		list($qty, $monthlyPrice, $subPrice) = calculPsu($db, $contratid, $date->format('Y-m'));
		$fk_product = 'NULL';
		$qty = round($qty, 2);
		$product_type = 0;
	}
	
	$monthlyPrice = round($monthlyPrice, 2);
	$subPrice = round($subPrice, 2);
	$sql = "INSERT INTO " . $db->prefix() . "facturedet 
	(fk_facture, fk_product, `description`, qty, subprice, total_ht, total_ttc, product_type) 
	VALUES (" . $rowid . ", " . $fk_product . ", 'Accueil de l\'enfant à la crèche', " . $qty . ", " . $subPrice . ", " 
	. $monthlyPrice . ", " . $monthlyPrice . ", " . $product_type . ")";
	$req = $db->query($sql);
	
	// Mise à jour total facture
	$sql = "UPDATE " . $db->prefix() . "facture 
	SET total_ht = " . $monthlyPrice . ", total_ttc = " . $monthlyPrice . "
	WHERE rowid = " . $rowid; 
	$req = $db->query($sql);
}

function massCreateFac($db, $childrenIds, $object)
{
	global $user, $langs;
	
	foreach ($childrenIds as $id) {
		$sql = "SELECT fk_societe, c.rowid as contratid  
		FROM " . $db->prefix() . "creche_enfants AS e 
		INNER JOIN " . $db->prefix() . "creche_famille AS f ON f.rowid = e.fk_famille 
		INNER JOIN " . $db->prefix() . "creche_contrats AS c ON e.rowid = c.fk_enfants 
		WHERE e.rowid = " . $id;
		$req = $db->query($sql);
		$res = $db->fetch_object($req);
		$ref = getNextNumFac($db, 'facture');

		// Création facture	
		$sql = "INSERT INTO " . $db->prefix() . "facture (ref, entity, fk_soc, datec, datef, date_valid, tms, 
		fk_statut, fk_user_author, fk_user_valid, fk_cond_reglement, note_private, model_pdf, date_lim_reglement) 
		VALUES ('" . $ref . "', 1, " . $res->fk_societe .", '" . date('Y-m-d H:i:s') . "', 
		'" . date('Y-m-d') . "', '" . date('Y-m-d') . "', '" . date('Y-m-d H:i:s') . "', 1, " . $user->id . ", " 
		. $user->id . ", 9, " . $res->contratid . ", 'sponge', '" . date('Y-m-d', strtotime('+10 days')) . "')";
		$req = $db->query($sql);
		$idFac = $db->last_insert_id($db->prefix() . "facture");

		// Ajout de la line a la facture
		factuAddLine($db, $idFac, $res->fk_societe, $res->contratid, date('Y-m-d'));

		// Génération du PDF
		$permissiontoadd = $user->rights->facture->creer;
		$action = 'builddoc';
		$object->fetch($idFac);
		$upload_dir = '/usr/share/dolibarr/documents/facture';
		if (empty($hidedetails)) {
			$hidedetails = 0;
		}
		if (empty($hidedesc)) {
			$hidedesc = 0;
		}
		if (empty($hideref)) {
			$hideref = 0;
		}
		if (empty($moreparams)) {
			$moreparams = null;
		}
		
		$result = $object->generateDocument($object->model_pdf, $langs, $hidedetails, $hidedesc, $hideref, $moreparams);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		} else {
			if (empty($donotredirect)) {
				setEventMessages($langs->trans("FileGenerated"), null);
			}
		}

		// Mise à jour facture
		$pdfPath = 'facture/' . $ref . '/' . $ref . '.pdf';
		$sql = "UPDATE " . $db->prefix() . "facture 
		SET last_main_doc = '" . $pdfPath . "' 
		WHERE rowid = " . $idFac; 
		$req = $db->query($sql);
	}

	header('Location: enfants_list.php');
	exit;
}

function retardVaccin($db, $enfantId)
{
	$alert = false;
	$infosAlert = array();

	$sql = "SELECT * FROM " . $db->prefix() . "creche_enfants 
			WHERE rowid = " . $enfantId;
    $req = $db->query($sql);
	$enfant = $db->fetch_object($req);
	$dateN = new DateTime($enfant->date_naissance);

	$sql = "SELECT * FROM " . $db->prefix() . "c_crechevaccins";
	$reqVaccins = $db->query($sql);
	
	$sql = "SELECT fk_vaccins, date_1_injection, date_1_rappel, date_2_rappel 
			FROM " . $db->prefix() . "creche_vaccin 
			WHERE fk_enfants = " . $enfantId;
	$reqVaccinsEnfant = $db->query($sql);
	$vaccinsEnfant = array();
	while ($vac = $db->fetch_object($reqVaccinsEnfant)) {
		$vaccinsEnfant[$vac->fk_vaccins] = $vac;
	}
	
	// boucle sur tous les vaccins du dictionnaire
	while ($row = $db->fetch_object($reqVaccins)) {
		// l'enfant a déjà reçu au moins 1 injection pour ce vaccin
		if (array_key_exists($row->rowid, $vaccinsEnfant)) {
			// toutes les injections nécessaires pour ce vaccin on été effectué
			if (($vaccinsEnfant[$row->rowid]->date_1_injection != null || $row->injection_1 == 0) 
				&& ($vaccinsEnfant[$row->rowid]->date_1_rappel != null || $row->rappel_1 == 0) 
				&& ($vaccinsEnfant[$row->rowid]->date_2_rappel != null || $row->rappel_2 == 0)) {
				continue;
			} else {
				// si un 2ème (ou 1er) rappel pour ce vaccin est nécessaire ET il n'a pas été effectué
				if ($vaccinsEnfant[$row->rowid]->date_1_rappel == null && $row->rappel_1 != 0) {
					$newDate = clone $dateN;
					$newDate->modify('first day of +' . $row->rappel_1 . 'months');
					// la date du rappel 1 est dépassé
					if ($newDate->format('Y-m') . '-' . $dateN->format('d') < date('Y-m-d')) {
						$alert = true;
						$infosAlert[$row->label] = 'rappel_1';
					}
				} elseif ($vaccinsEnfant[$row->rowid]->date_2_rappel == null && $row->rappel_2 != 0) {
					$newDate = clone $dateN;
					$newDate->modify('first day of +' . $row->rappel_2 . 'months');
					// la date du rappel 2 est dépassé
					if ($newDate->format('Y-m') . '-' . $dateN->format('d') < date('Y-m-d')) {
						$alert = true;
						$infosAlert[$row->label] = 'rappel_2';
					}
				}
			}
		} else { // l'enfant n'a reçu aucune injection pour ce vaccin
			// la 1ère injection pour ce vaccin n'a pas été effectué
			if ($vaccinsEnfant[$row->rowid]->date_1_injection == null && $row->injection_1 != 0) {
				$newDate = clone $dateN;
				$newDate->modify('first day of +' . $row->injection_1 . 'months');
				// la date de la 1ère injection est dépassé
				if ($newDate->format('Y-m') . '-' . $dateN->format('d') < date('Y-m-d')) {
					$alert = true;
					$infosAlert[$row->label] = 'injection_1';
				}
			}
		}
	}

	return array($alert, $infosAlert);
}