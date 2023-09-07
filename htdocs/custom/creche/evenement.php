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
	$i--; $j--;
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

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
dol_include_once('/creche/class/famille.class.php');
dol_include_once('/creche/lib/creche_famille.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("creche@creche", "other"));

// Get parameters
$famid = GETPOST('famid', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$actioncode = GETPOST('actioncode');
$label = GETPOST('label');
$description = GETPOST('note');
$child = GETPOST('child', 'int');

// Initialize technical objects
$object = new Famille($db);
$diroutputmassaction = $conf->creche->dir_output.'/temp/massgeneration/'.$user->id;

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->creche->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->creche->famille->read;
	$permissiontoadd = $user->rights->creche->famille->write;
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
}

// Security check (enable the most restrictive one)
if (!isModEnabled("creche")) {
	accessforbidden();
}
if (!$permissiontoread) accessforbidden();


if ($action == 'add') {
    $sql = "SHOW TABLE STATUS LIKE '" . $db->prefix() . "actioncomm'";
    $req = $db->query($sql);
    $id = $db->fetch_object($req)->Auto_increment;
    
    if (isset($_FILES['attached_file'])) {
        if (!file_exists('../../../documents/agenda/' . $id)) {
            mkdir('../../../documents/agenda/' . $id, 0777, true);
        }
        $dossier = '../../../documents/agenda/' . $id . '/';
        $fichier = basename($_FILES['attached_file']['name']);
        $move = move_uploaded_file($_FILES['attached_file']['tmp_name'], $dossier . $fichier);
    }

    $selectRef = "SELECT ref FROM " . $db->prefix() . "actioncomm WHERE ref REGEXP '^[0-9]+$' ORDER BY cast(ref AS unsigned) DESC LIMIT 0,1";
    $refReq = $db->query($selectRef);
    $refLast = (int)$db->fetch_object($refReq)->ref; // dernière ref
    $refLast++; // faire +1 à la dernière ref
    
    $selectCode = "SELECT id FROM " . $db->prefix() . "c_actioncomm WHERE code = '" . $actioncode . "'";
    $codeReq = $db->query($selectCode);
    $actionCodeId = $db->fetch_object($codeReq)->id;
    
    $extraparams = '';
    $extraparamsField = '';
    if ($child != 0) {
        $extraparams = 'enfant:' . $child;
        $extraparamsField = 'extraparams';
    }
    
    $sql = "INSERT INTO " . $db->prefix() . "actioncomm (ref, datep, fk_action, code, label, note, fk_element, elementtype" . 
        ($extraparams != '' ? ", extraparams" : "") .") 
        VALUES (" . $refLast . ", '" . date('Y-m-d H:i:s') . "', " . $actionCodeId . ", '" . $actioncode . "', '" . $db->escape($label) 
        . "', '" . $db->escape($description) . "', " . $famid . ", 'famille'" . ($extraparams != '' ? ", '$extraparams'" : "") .")";
    $req = $db->query($sql);
    header('Location: famille_agenda.php?id=' . $famid);
    exit;
}


$title = $langs->trans("Famille")." - ".$langs->trans('Agenda');
$help_url = 'EN:Module_Agenda_En|DE:Modul_Terminplanung';
llxHeader('', $title, $help_url);

$form = new Form($db);
$formactions = new FormActions($db);

$sql = "SELECT rowid, prenom 
        FROM " . $db->prefix() . "creche_enfants 
        WHERE fk_famille = " . $famid;
$enfants = $db->query($sql);

dol_set_focus("#label");

if (!empty($conf->use_javascript_ajax)) {
    print "\n".'<script type="text/javascript">';
    print '$(document).ready(function () {
        function setdatefields()
        {
            if ($("#fullday:checked").val() == null) {
                $(".fulldaystarthour").removeAttr("disabled");
                $(".fulldaystartmin").removeAttr("disabled");
                $(".fulldayendhour").removeAttr("disabled");
                $(".fulldayendmin").removeAttr("disabled");
                $("#p2").removeAttr("disabled");
            } else {
                $(".fulldaystarthour").prop("disabled", true).val("00");
                $(".fulldaystartmin").prop("disabled", true).val("00");
                $(".fulldayendhour").prop("disabled", true).val("23");
                $(".fulldayendmin").prop("disabled", true).val("59");
                $("#p2").removeAttr("disabled");
            }
        }
        $("#fullday").change(function() {
            console.log("setdatefields");
            setdatefields();
        });
        
        $("#selectcomplete").change(function() {
            console.log("we change the complete status - set the doneby");
            if ($("#selectcomplete").val() == 100) {
                if ($("#doneby").val() <= 0) $("#doneby").val(\''.((int) $user->id).'\');
            }
            if ($("#selectcomplete").val() == 0) {
                $("#doneby").val(-1);
            }
        });
        
        $("#actioncode").change(function() {
            if ($("#actioncode").val() == \'AC_RDV\') $("#dateend").addClass("fieldrequired");
            else $("#dateend").removeClass("fieldrequired");
        });
        $("#aphour,#apmin").change(function() {
            if ($("#actioncode").val() == \'AC_RDV\') {
                console.log("Start date was changed, we modify end date "+(parseInt($("#aphour").val()))+" "+$("#apmin").val()+" -> "+("00" + (parseInt($("#aphour").val()) + 1)).substr(-2,2));
                $("#p2hour").val(("00" + (parseInt($("#aphour").val()) + 1)).substr(-2,2));
                $("#p2min").val($("#apmin").val());
                $("#p2day").val($("#apday").val());
                $("#p2month").val($("#apmonth").val());
                $("#p2year").val($("#apyear").val());
                $("#p2").val($("#ap").val());
            }
        });
        if ($("#actioncode").val() == \'AC_RDV\') $("#dateend").addClass("fieldrequired");
        else $("#dateend").removeClass("fieldrequired");
        setdatefields();
    })';
    print '</script>'."\n";
}

print '<form name="formaction" action="'.$_SERVER['PHP_SELF'].'?famid=' . $famid . '" method="POST" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add">';
print '<input type="hidden" name="donotclearsession" value="1">';
print '<input type="hidden" name="page_y" value="">';
// Assigned to
$listofuserid = array();
$listofcontactid = array();
$listofotherid = array();

if (empty($donotclearsession)) {
    $assignedtouser = GETPOST("assignedtouser") ? GETPOST("assignedtouser") : $user->id;
    if ($assignedtouser) {
        $listofuserid[$assignedtouser] = array('id'=>$assignedtouser, 'mandatory'=>0); // Owner first
    }
    
    $listofuserid[$assignedtouser]['transparency'] = (GETPOSTISSET('transparency') ? GETPOST('transparency', 'alpha') : 1); // 1 by default at first init
    $_SESSION['assignedtouser'] = json_encode($listofuserid);
} else {
    if (!empty($_SESSION['assignedtouser'])) {
        $listofuserid = json_decode($_SESSION['assignedtouser'], true);
    }
    $firstelem = reset($listofuserid);
    if (isset($listofuserid[$firstelem['id']])) {
        $listofuserid[$firstelem['id']]['transparency'] = (GETPOSTISSET('transparency') ? GETPOST('transparency', 'alpha') : 0); // 0 by default when refreshing
    }
}
print '<input type="hidden" name="assignedtouser" value="' . $user->id . '">';

if (empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
    print '<input type="hidden" name="actioncode" value="'.dol_getIdFromCode($db, 'AC_OTH', 'c_actioncomm').'">';
}

print load_fiche_titre($langs->trans("AddAnAction"), '', 'title_agenda');

print dol_get_fiche_head();

print '<table class="border centpercent">';

// Type of event
if (!empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
    print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Type").'</span></b></td><td>';
    $default = getDolGlobalString('AGENDA_USE_EVENT_TYPE_DEFAULT', 'AC_RDV');
    print img_picto($langs->trans("ActionType"), 'square', 'class="fawidth30 inline-block" style="color: #ddd;"');
    $selectedvalue = GETPOSTISSET("actioncode") ? GETPOST("actioncode", 'aZ09') : $default;
    print $formactions->select_type_actions($selectedvalue, "actioncode", "system", 0, -1, 0, 1);
    print '</td></tr>';
}

// Enfant
print '<tr><td class="nowrap">'.$langs->trans("Enfant concerné").'</td><td>';
print '<select name="child" id="child" class="">';
print '<option value="0"></option>';
while ($enfant = $db->fetch_object($enfants)) {
    print '<option value="' . $enfant->rowid . '">' . $enfant->prenom . '</option>';
}
print '</select>';
print '</td></tr>';

// Title
print '<tr><td'.(empty($conf->global->AGENDA_USE_EVENT_TYPE) ? ' class="fieldrequired titlefieldcreate"' : '').'>'.$langs->trans("Label").'</td><td><input type="text" id="label" name="label" class="soixantepercent" value="'.GETPOST('label').'"></td></tr>';

// Full day
print '<tr><td><span class="fieldrequired">'.$langs->trans("Date").'</span></td><td class="valignmiddle height30 small"><input type="checkbox" id="fullday" name="fullday" '.(GETPOST('fullday') ? ' checked' : '').'><label for="fullday">'.$langs->trans("EventOnFullDay").'</label>';
print '</td></tr>';

$datep = '';
$datef = '';

// Date start / Date end
print '<tr><td class="nowrap">';
print '</td><td>';
print $form->selectDate($datep, 'ap', 1, 1, 1, "action", 1, 2, 0, 'fulldaystart', '', '', '', 1, '', '', 'tzuserrel');
print ' <span class="hideonsmartphone">&nbsp; &nbsp; - &nbsp; &nbsp;</span> ';
print $form->selectDate($datef, 'p2', 1, 1, 1, "action", 1, 0, 0, 'fulldayend', '', '', '', 1, '', '', 'tzuserrel');
print '</td></tr>';

// Joindre un fichier
print '<tr><td class="nowrap">'.$langs->trans("Joindre un fichier").'</td>';
print '<td><input type="file" name="attached_file" id="attached_file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" />';
print '</td></tr>';
print '</table>';

print '<br><hr><br>';

print '<table class="border centpercent">';

// Description
print '<tr><td class="tdtop">'.$langs->trans("Description").'</td><td>';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
$doleditor = new DolEditor('note', (GETPOSTISSET('note') ? GETPOST('note', 'restricthtml') : $object->note_private), '', 120, 'dolibarr_notes', 'In', true, true, isModEnabled('fckeditor'), ROWS_4, '90%');
$doleditor->Create();
print '</td></tr>';
print '</table>';

print dol_get_fiche_end();

print $form->buttonsSaveCancel("Add");

print "</form>";

?>