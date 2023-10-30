<?php

if ($user->rights->creche->pointage->write) {
	$form = new Form($db);

	$now = dol_now();

	$enfants = array();
	$entity = getEntity('enfants', 0);
	$sql = "SELECT * FROM " . $db->prefix() . "creche_enfants";
	if ($entity != 1) {
		$sql .= " WHERE entity = " . $entity;
	}
	$req = $db->query($sql);
	while ($row = $db->fetch_object($req)) { 
		$enfants[] = $row;
	}

	?>
	<link href="custom/creche/css/creche.css" type="text/css" rel="stylesheet">
	<div class="children_container">
		<?php foreach ($enfants as $enfant): 
			$sql = "SELECT * FROM " . $db->prefix() . "actioncomm 
					WHERE code = 'CRECHE_POINTAGE' 
					AND datep LIKE '" .date('Y-m-d') . "%' 
					AND elementtype = 'enfants' 
					AND fk_element = " . $enfant->rowid;
			$req = $db->query($sql);
			if ($db->num_rows($req) == 0 || $db->num_rows($req) == 2) {
				$type = 'arrivee';
			} else {
				$type = 'depart';
			}

			
			if ($enfant->photo_id == null) {
				$photo = '<span class="fa fa-file" title="No photo"></span>';
			} else {
				$parts = explode('/', $enfant->photo_id);
				$file = implode('#', $parts);
				$photo = '<img src="custom/creche/viewimage.php?modulepart=creche&file=' 
				. urlencode($file) . '&entity=' . $enfant->entity . '&type=enfants" style="width: auto;height: 100px;">';
			}
			
			?>
			<div class="child">
				<div class="child__infos">
					<div class="child__infos_photo"><?= $photo ?></div>
					<div class="child__infos_name">
						<?= $enfant->prenom . ' ' . $enfant->nom ?><br />
						<a class="button" id="crechePointageBtn<?= $enfant->rowid ?>" onclick="var btn = document.getElementById('crechePointageBtn<?= $enfant->rowid ?>');btn.classList.remove('button');btn.classList.add('btn_disable');" 
							href="index.php?action=tally&type=<?= ($type == 'arrivee') ? 'arrival' : 'departure' ?>&enfantid=<?= $enfant->rowid ?>&token=<?= newToken() ?>">
							<?= ($type == 'arrivee') ? 'Arrivée' : 'Départ' ?>
						</a>
					</div>
				</div>
				<div class="child_events">
					<a class="<?= ($type == 'arrivee') ? 'btn_disable' : 'btn_repas' ?> button" 
						href="custom/creche/evenement.php?famid=<?= $enfant->fk_famille ?>&actioncode=CRECHE_FAMILLE&child=<?= $enfant->rowid ?>&label=repas&origin=enfant&token=<?= newToken() ?>">
						Repas
					</a>
					<a class="<?= ($type == 'arrivee') ? 'btn_disable' : 'btn_sieste' ?> button" 
						href="custom/creche/evenement.php?famid=<?= $enfant->fk_famille ?>&actioncode=CRECHE_FAMILLE&child=<?= $enfant->rowid ?>&label=sieste&origin=enfant&token=<?= newToken() ?>">
						Sieste
					</a>
					<a class="<?= ($type == 'arrivee') ? 'btn_disable' : 'btn_couche' ?> button" 
						href="custom/creche/evenement.php?famid=<?= $enfant->fk_famille ?>&actioncode=CRECHE_FAMILLE&child=<?= $enfant->rowid ?>&label=couche&origin=enfant&token=<?= newToken() ?>">
						Couche
					</a>
					<a class="<?= ($type == 'arrivee') ? 'btn_disable' : 'btn_autre' ?> button" 
						href="custom/creche/evenement.php?famid=<?= $enfant->fk_famille ?>&actioncode=CRECHE_FAMILLE&child=<?= $enfant->rowid ?>&label=autre&origin=enfant&token=<?= newToken() ?>">
						Autre
					</a>
				
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php
}