<?php
/* Copyright (C) 2023 SuperAdmin <informatique@infans.fr>
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
 * \file    creche/class/actions_creche.class.php
 * \ingroup creche
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';

/**
 * Class ActionsCreche
 */
class ActionsCreche extends CommonHookActions
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var int		Priority of hook (50 is used if value is not defined)
	 */
	public $priority;

	protected $booleanFields = array(
		'notif',
		'vaccination',
		'droit_image'
	);


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {	    // do something only for the context 'somecontext1' or 'somecontext2'
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	// Modifier affichage des formulaires de création et de modification
	public function showField($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;
		
		// echo '<pre>';var_dump('showField', $action, GETPOST('famid', 'int') > 0, $parameters);echo '</pre>';
		
		$error = 0; // Error counter
		if (in_array($parameters['currentcontext'], array('parentscard', 'enfantscard'))) { 
			if ($conf->creche->enabled) {
				if (strpos($parameters['type'], 'enum') !== false) {
					$tmp = explode('(', $parameters['type']);
					$tmp = str_replace(['"', ' ', ')'], '', $tmp[1]);
					$options = explode(',', $tmp);

					$out = '<select class="flat" name="' . $parameters['field'] . '" id="' . $parameters['field'] . '">';
					$out .= '<option value=""></option>';
					foreach ($options as $option) {
						$out .= '<option value="' . $option . '"' 
						. ($option == $parameters['value'] ? ' selected' : '') . '>' 
						. ucfirst($option) . '</option>';
					}
					$out .= '</select>';
					$out .= ajax_combobox($parameters['field']);
					
					echo $out;
					return 1;
				} elseif (in_array($parameters['field'], $this->booleanFields)) {
					$out = '<select class="flat" name="' . $parameters['field'] . '" id="' . $parameters['field'] . '">';
					$out .= '<option value="0"' . (($parameters['value'] != null && $parameters['value'] == 0) ? ' selected' : '') . '>Non</option>';
					$out .= '<option value="1"' . (($parameters['value'] != null && $parameters['value'] == 1) ? ' selected' : '') . '>Oui</option>';
					$out .= '</select>';
					$out .= ajax_combobox($parameters['field']);
					
					echo $out;
					return 1;
				} elseif ($parameters['field'] == 'fk_famille') {
					if ($action == 'create' && is_numeric(GETPOST('famid', 'int')) && GETPOST('famid', 'int') > 0) {
						$val = GETPOST('famid', 'int');
						$readonly = 'disabled';
					} else {
						$val = $parameters['value'];
						$readonly = '';
					}
					
					$out = '<select class="flat" ' . $readonly . ' name="' . $parameters['field'] . '" id="' . $parameters['field'] . '">';
					$out .= '<option value="-1"></option>';
					$sql = "SELECT rowid, libelle  
					FROM " . $this->db->prefix() . "creche_famille";
					$req = $this->db->query($sql);
					while ($option = $this->db->fetch_object($req)) {
						$out .= '<option value="' . $option->rowid . '"' 
						. ($option->rowid == $val ? ' selected' : '') . '>' 
						. ucfirst($option->libelle) . '</option>';
					}
					$out .= '</select>';
					$out .= ajax_combobox($parameters['field']);
					
					if ($action == 'create' && is_numeric(GETPOST('famid', 'int')) && GETPOST('famid', 'int') > 0) {
						$out .= '<input type="hidden" name="' . $parameters['field'] . '" id="' 
						. $parameters['field'] . '" value="' . $val . '">';
					}

					echo $out;
					return 1;
				} elseif ($parameters['field'] == 'photo_id') {
					$out = '<input type="file" name="photo_id" id="photo_id" />';
					echo $out;
					return 1;
				}
				
			}
		}
		
		
		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	// Modifier affichage des cards
	public function showFieldValue($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;
		
		// echo '<pre>';var_dump($parameters);echo '</pre>';
		
		$error = 0; // Error counter
		if (in_array($parameters['currentcontext'], array('parentscard', 'crecheparentslist', 'enfantscard'))) { 
			if ($conf->creche->enabled) {
				if (in_array($parameters['field'], $this->booleanFields)) {
					if ($parameters['value'] == 1) {
						$val = 'Oui';
					} else {
						$val = 'Non';
					}

					echo $langs->trans($val);
					return 1;
				} elseif ($parameters['field'] == 'fk_famille') {
					$sql = "SELECT rowid, libelle  
							FROM " . $this->db->prefix() . "creche_famille 
							WHERE rowid = " . $parameters['value'];
					$req = $this->db->query($sql);
					$obj = $this->db->fetch_object($req);
					
					if ($parameters['currentcontext'] == 'crecheparentslist') {
						$out = ucfirst($obj->libelle);
					} else {
						$out = '<a href="/custom/creche/famille_card.php?id=' 
						. $obj->rowid . '"><span class="fa fa-file paddingright"></span>' 
						. $obj->libelle . '</a>';
					}

					echo $out;
					return 1;
				} elseif (strpos($parameters['type'], 'enum') !== false) {
					echo ucfirst($parameters['value']);
					return 1;
				}
				
			}
		}
		
		
		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	// Upload fichier
	public function inputFile($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;
		
		$error = 0; // Error counter
		if (in_array($parameters['currentcontext'], array('enfantscard'))) { 
			if ($conf->creche->enabled) {
				if ($parameters['value'] == 'inputFileCreche') {
					$field = $parameters['field'];
					if ($object->id != null && is_numeric($object->id)) {
						$id = $object->id;
					} else {
						$sql = "SHOW TABLE STATUS LIKE '" . $this->db->prefix() . $object->table_element . "'";
						$req = $this->db->query($sql);
						$id = $this->db->fetch_object($req)->Auto_increment;
					}
					
					if (isset($_FILES[$field])) {
						if (!file_exists('../../../documents/creche/enfants/' . $id)) {
							mkdir('../../../documents/creche/enfants/' . $id, 0777, true);
						}
						$dossier = '../../../documents/creche/enfants/' . $id . '/';
						$fichier = basename($_FILES[$field]['name']);
						if(move_uploaded_file($_FILES[$field]['tmp_name'], $dossier . $fichier)) {
							$object->$field = trim($dossier, './') . '/' . $fichier;
							return 1;
						}
					}
				}
			}
		}
		
		
		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	// Afficher une image
	public function displayImg($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		// echo '<pre>';var_dump($parameters);echo '</pre>';
		
		$error = 0; // Error counter
		if (in_array($parameters['currentcontext'], array('enfantscard', 'enfantsdocument'))) { 
			if ($conf->creche->enabled) {
				if (isset($parameters['rowid'])) {
					$sql = "SELECT photo_id  
							FROM " . $this->db->prefix() . "creche_enfants 
							WHERE rowid = " . $parameters['rowid'];
					$req = $this->db->query($sql);
					$obj = $this->db->fetch_object($req);
					$parts = explode('/', $obj->photo_id);
					$file = $parts[2] . '_' . $parts[3] . '_' . $parts[4];

					$object .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref">';
					$object .= '<img src="viewimage.php?modulepart=creche&file=' 
					. urlencode($file) . '&entity=1" style="max-width: 150px;height: auto;">';
					$object .= '</div></div>';
					
					return 1;
				}
			}
		}
		
		
		if (!$error) {
			// $this->results = array('myreturn' => 999);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("CrecheMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("creche@creche");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'creche') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Creche");
			$this->results['picto'] = 'creche@creche';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->hasRight('creche', 'myobject', 'read')) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// used to make some tabs removed
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('creche@creche');
			// used when we want to add some tabs
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/creche/creche_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('CrecheTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'crecheemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		} else {
			// Bad value for $parameters['mode']
			return -1;
		}
	}

	/* Add here any other hooked methods... */
}
