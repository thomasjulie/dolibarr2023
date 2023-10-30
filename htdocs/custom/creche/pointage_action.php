<?php

if ($action == 'tally') {
	require_once DOL_DOCUMENT_ROOT.'/custom/creche/lib/creche.lib.php';
	if (GETPOST('type') == 'arrival') {
		$type = 'arrivee';
	} else {
		$type = 'depart';
	}
	$refLast = getRefEvenement($db);
    $actionCodeId = getActionCodeId($db, 'CRECHE_POINTAGE');
    
    $sql = "INSERT INTO " . $db->prefix() . "actioncomm (ref, datep, fk_action, code, label, fk_element, elementtype) 
        VALUES (" . $refLast . ", '" . date('Y-m-d H:i:s') . "', " . $actionCodeId . ", 'CRECHE_POINTAGE', '" . $type 
        . "', " . GETPOST('enfantid') . ", 'enfants')";
    $req = $db->query($sql);

	setEventMessages('Pointage effectué', null);

	sleep(1);

	header('Location: index.php');
	exit;
}