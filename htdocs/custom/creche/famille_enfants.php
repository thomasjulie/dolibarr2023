<?php
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('/creche/lib/creche_famille.lib.php');

// load module libraries
require_once __DIR__.'/class/famille.class.php';
require_once __DIR__.'/class/enfants.class.php';

// Load translation files required by the page
$langs->loadLangs(array("creche@creche", "other"));

$action     = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view'; // The action 'create'/'add', 'edit'/'update', 'view', ...

$famid = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');

// Initialize technical objects
$fam = new Famille($db);
$fam->fetch($famid);

// $object = new Enfants($db);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->hasRight('creche', 'enfants', 'read');
	$permissiontoadd = $user->hasRight('creche', 'enfants', 'write');
	$permissiontodelete = $user->hasRight('creche', 'enfants', 'delete');
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
	$permissiontodelete = 1;
}

// Security check (enable the most restrictive one)
if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) accessforbidden();
//$socid = 0; if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->module, 0, $object->table_element, $object->element, 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled("creche")) {
	accessforbidden('Module creche not enabled');
}
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */




/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("Famille")." - ".$langs->trans('Enfants');
llxHeader('', $title);

$head = famillePrepareHead($fam);
print dol_get_fiche_head($head, 'enfants', $langs->trans("Famille"), -1, $fam->picto, 0, '', '', 0, '', 1);

$sql = "SELECT * 
		FROM " . $db->prefix() . "creche_enfants 
		WHERE fk_famille = " . $famid;
$req = $db->query($sql);

dol_banner_tab($fam, 'ref', $linkback, 1, 'ref', 'libelle'); ?>

	<div class="fichecenter">
		<div class="underbanner clearboth"></div>
		<table class="centpercent notopnoleftnoright table-fiche-title">
			<tbody>
				<tr>
					<td class="nobordernopadding valignmiddle col-title"></td>
					<td class="nobordernopadding valignmiddle right col-right">
						<a class="btnTitle btnTitlePlus" 
						href="/custom/creche/enfants_card.php?action=create&token=<?= newToken() ?>&famid=<?= $fam->id ?>" 
						title="Nouveau Enfant">
							<span class="fa fa-plus-circle valignmiddle btnTitle-icon"></span>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- <hr> -->
		<table class="tagtable liste">
			<thead>
				<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre"><?= $langs->trans("Nom") ?></th>
					<th class="wrapcolumntitle liste_titre"><?= $langs->trans("Genre") ?></th>
					<th class="wrapcolumntitle liste_titre"><?= $langs->trans("Date de naissance	") ?></th>
				</tr>
			</thead>
			<tbody>
				<?php while ($res = $db->fetch_object($req)): ?>
					<tr>
						<td>
							<a href="/custom/creche/enfants_card.php?id=<?= $res->rowid ?>">
								<?= $res->prenom . ' ' . $res->nom ?>
							</a>
						</td>
						<td><?= $res->genre ?></td>
						<td><?= $res->date_naissance ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>

<?php
// End of page
llxFooter();
$db->close();