
<?php

/**
 * Gestion de la connexion
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 */

use Outils\Utilitaires;

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$uc) {
    $uc = 'demandeconnexion';
}

switch ($action) {
    case 'demandeConnexion':
        include PATH_VIEWS . 'v_connexion.php';
        break;
    case 'valideConnexion':
        var_dump($uc);
        if( empty($_POST)) {
            header("Location: index.php?uc=connexion&action=demandeConnexion");
        }
        $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mdp = filter_input(INPUT_POST, 'mdp', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $user = null;

        if ($role === "visiteur") {
            $user = $pdo->getInfosVisiteur($login);
        } elseif ($role === "comptable") {
            $user = $pdo->getInfosComptable($login);
        }
       //on reapplique le hash et apres on a juste les mots de basse celle de formaulaire et celle haché si c'est le meme c'est que c'est la bonne mdp 
        //l'interet de hash en bdd on aura pas le mdp en clair ça premet de proteger les mdp en bdd car ils seront haché  le piarete il le aura pas en clair 
         $mdp = hash('sha224', $mdp);
        if (empty($user) || is_null($user) ||  $mdp != $user['mdp'])  {
            Utilitaires::ajouterErreur('Login ou mot de passe incorrect');
            include PATH_VIEWS . 'v_erreurs.php';
            include PATH_VIEWS . 'v_connexion.php';
        } else  {
            $id = $user['id'];
            $nom = $user['nom'];
            $prenom = $user['prenom'];
            Utilitaires::connecter($id, $nom, $prenom, $role);
            header('Location: index.php');
        }
        break;
    default:
        include PATH_VIEWS . 'v_connexion.php';
        break;
}
