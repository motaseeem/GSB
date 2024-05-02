  <?php


if ($estConnecte) {
	include PATH_VIEWS . 'Comptable/v_accueil.php';
} else {
    include PATH_VIEWS . 'v_connexion.php';
}
