<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
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
 * \file    customizeprospectcustomerfield/admin/setup.php
 * \ingroup customizeprospectcustomerfield
 * \brief   CustomizeProspectCustomerField setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/customizeprospectcustomerfield.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "customizeprospectcustomerfield@customizeprospectcustomerfield"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('customizeprospectcustomerfieldsetup', 'globalsetup'));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$rowid = GETPOST('id');


$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
	// For retrocompatibility Dolibarr < 16.0
	if (floatval(DOL_VERSION) < 16.0 && !class_exists('FormSetup')) {
		require_once __DIR__.'/../backport/v16/core/class/html.formsetup.class.php';
	} else {
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
	}
}

$formSetup = new FormSetup($db);


// HTTP HOST
$item = $formSetup->newItem('NO_PARAM_JUST_TEXT');
$item->fieldOverride = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
$item->cssClass = 'minwidth500';

$setupnotempty =+ count($formSetup->items);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);


/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if ( versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

$form = new Form($db);
$help_url = '';
$page_name = "CustomizeProspectCustomerFieldSetup";

if ($action == 'toogleStatus') { // Activate / Desactivate
	$sql = "SELECT active FROM ".MAIN_DB_PREFIX."prospectCustomerType WHERE rowid = " . $rowid;
	$req = $db->query($sql);
	$result = $db->fetch_object($req);
	if ($result->active == 1) {
		$active = 0;
	} else {
		$active = 1;
	}

	$sql = "UPDATE ".MAIN_DB_PREFIX."prospectCustomerType SET active = " . $active . " WHERE rowid = " . $rowid;
	$resql = $db->query($sql);
	if (!$resql) {
		setEventMessages($db->error(), null, 'errors');
	}
	header("Location: " . $_SERVER["PHP_SELF"]);
	exit;
} 

if (GETPOST('actionmodify') ) { // Edit
	$code = GETPOST('code');
	$label = GETPOST('label');
	if ($code != '' && $label != '') {
		$sql = "UPDATE ".MAIN_DB_PREFIX."prospectCustomerType SET code = '" . $code . "', label = '" . $label . "' WHERE rowid = " . $rowid;
		$resql = $db->query($sql);
		if (!$resql) {
			setEventMessages($db->error(), null, 'errors');
		}
		header("Location: " . $_SERVER["PHP_SELF"]);
		exit;
	}
}

llxHeader('', $langs->trans($page_name), $help_url);

if ($action == 'add') { // Add
	$code = GETPOST('code');
	$label = GETPOST('label');
	$sql = "INSERT INTO ".MAIN_DB_PREFIX."prospectCustomerType (code, label) VALUES ('" . $code . "', '" . $label . "')";
	$resql = $db->query($sql);
	if ($resql) {
		setEventMessages($langs->transnoentities("RecordSaved"), null, 'mesgs');
	} else {
		if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
			setEventMessages($langs->transnoentities("ErrorRecordAlreadyExists"), null, 'errors');
		} else {
			dol_print_error($db);
		}
	}

} 

if ($action == 'del') { // Pop-up confirm delete
	print $form->formconfirm(
		$_SERVER["PHP_SELF"].'?id=' . $rowid, 
		$langs->trans('DeleteLine'), 
		$langs->trans('ConfirmDeleteLine'), 
		'confirm_delete', 
		'', 
		0, 
		1
	);
} 
if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes') { // Delete
	$sql = "DELETE FROM ".MAIN_DB_PREFIX."prospectCustomerType WHERE rowid = " . $rowid;
	$resql = $db->query($sql);
	if (!$resql) {
		setEventMessages($db->error(), null, 'errors');
	}
	
} 

/*
 * View
 */

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = customizeprospectcustomerfieldAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "customizeprospectcustomerfield@customizeprospectcustomerfield");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("CustomizeProspectCustomerFieldSetupPage").'</span><br><br>';
?>

<form action="<?= $_SERVER["PHP_SELF"] ?>" method="POST">
	<input type="hidden" name="token" value="<?= newToken() ?>">
	<input type="hidden" name="action" value="add">
	<div class="div-table-responsive-no-min">
		<table class="noborder centpercent">
			<tbody>
				<tr class="liste_titre">
					<th class="maxwidth100">
						<span style="padding: 0px; padding-right: 3px;">Code</span>
					</th>
					<th>
						<span style="padding: 0px; padding-right: 3px;"><?= $langs->trans("Label") ?></span>
					</th>
					<th style="min-width: 26px;"></th>
					<th style="min-width: 26px;"></th>
				</tr>
				<tr class="oddeven nodrag nodrop nohover">
					<td class="">
						<input type="text" required class="flat maxwidth100" value="" name="code">
					</td>
					<td class="">
						<input type="text" required class="flat quatrevingtpercent" maxlength="128" value="" name="label">
					</td>
					<td colspan="3" class="center">
						<?php if ($action != 'edit'): ?>
							<input type="submit" class="button button-add" name="actionadd" value="<?= $langs->trans("Add") ?>">
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</form>
<br />
<form action="<?= $_SERVER["PHP_SELF"] ?>" method="POST">
	<input type="hidden" name="token" value="<?= newToken() ?>">
	<div class="div-table-responsive">
		<table class="noborder centpercent">
			<tbody>
				<tr class="liste_titre">
					<th class="wrapcolumntitle liste_titre">
						<span style="padding: 0px; padding-right: 3px;">Code</span>
					</th>
					<th class="wrapcolumntitle liste_titre_sel">
						<span style="padding: 0px; padding-right: 3px;"><?= $langs->trans("Label") ?></span>
					</th>
					<th class="wrapcolumntitle liste_titre" align="center" title="Status">
						<span style="padding: 0px; padding-right: 3px;"><?= $langs->trans("Status") ?></span>
					</th>
					<th class="wrapcolumntitle liste_titre"></th>
					<th class="wrapcolumntitle liste_titre"></th>
				</tr>

				<?php $sql = "SELECT rowid, code, label, active FROM ".MAIN_DB_PREFIX."prospectCustomerType";
				$req = $db->query($sql);
				if ($req):
					while ($result = $db->fetch_object($req)):
						?>
						<tr class="oddeven" id="rowid-5">
							<?php if ($action == 'edit' && $rowid == $result->rowid): ?>
								<td>
									<input type="text" class="flat minwidth75 maxwidth100" value="<?= $result->code ?>" name="code">
								</td>
								<td>
									<input type="text" class="flat minwidth75 maxwidth100" value="<?= $result->label ?>" name="label">
								</td>
								<td class="nowrap center" colspan="3">
									<input type="hidden" value="<?= $rowid ?>" name="id">
									<input type="submit" class="button button-edit small" name="actionmodify" value="<?= $langs->trans("Modify") ?>">
									<input type="submit" class="button button-cancel small" name="actioncancel" value="<?= $langs->trans("Cancel") ?>">
								</td>
							<?php else: ?>
								<td class="tddict"><?= $result->code ?></td>
								<td class="tddict"><?= $result->label ?></td>
								<td class="nowrap center">
									<a href="<?= $_SERVER["PHP_SELF"] . '?action=toogleStatus&id=' . $result->rowid . '&token=' . newToken() ?>">
									<?php if ($result->active == 1): ?>
										<span class="fas fa-toggle-on font-status4 size15x" title="<?= $langs->trans("Activated") ?>"></span>
									<?php else: ?>
										<span class="fas fa-toggle-off size15x" style=" color: #999;" title="<?= $langs->trans("Disabled") ?>"></span>
									<?php endif;?>
									</a>
								</td>
								<td align="center">
									<a class="editfielda" href="<?= $_SERVER["PHP_SELF"] . '?action=edit&id=' . $result->rowid . '&token=' . newToken() ?>">
									<span class="fas fa-pencil-alt" style=" color: #444;" title="<?= $langs->trans("Modify") ?>"></span>
									</a>
								</td>
								<td class="center"><a class="" href="<?= $_SERVER["PHP_SELF"] . '?action=del&id=' . $result->rowid . '&token=' . newToken() ?>">
									<span class="fas fa-trash pictodelete" title="<?= $langs->trans("Delete") ?>"></span>
									</a>
								</td>
							<?php endif;?>
						</tr>
					<?php endwhile;
				endif; ?>
			</tbody>
		</table>
	</div>
</form>

<?php
if (empty($setupnotempty)) {
	print '<br>'.$langs->trans("NothingToSetup");
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
