<?php

/**
 * Gestion des frais
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

/**
 * creation de menu dérolant : la permier truc qui va etre afficher 
 */
use Outils\Utilitaires;

$lesvisiteurs = $pdo->getToutLesVisiteurs(); 
$lesmois = $pdo->getToutLesMois();
$action = $_GET['action'];

switch ($action) {

    case 'updateSelectFicheFrais':
      
        $_SESSION['fiche_frais'] = [
            
            'visiteur' => $_POST['selectvisiteur'],
            'mois' => $_POST['lstMois'] // on les recuepres des formualires 
        ];
        
        
         header("Location: index.php?uc=gererFrais&action=saisirFrais"); 
        
        break;
    case 'mettreAjourFicheFrais':

        $pdo->majFraisForfait($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois'], $_POST['lesFrais']); //faire appelle a la fonction 

        header("Location: index.php?uc=gererFrais&action=saisirFrais");
        break;
    case 'updateSelectFicheFraishorsforfait':

        if ($_POST['submit'] == 'Corriger') {          
            $date = DateTime::createFromFormat('d/m/Y', $_POST['madate']); //creation objet datetime aveccune vrai date qui recupere depuis le formualire 
            $formattedDate = $date->format('Y-m-d'); // on passe de mon objet datetime à nouevelle objet //ca passe de format fr à from american 

            $pdo->MajFraisHorsForfait($_POST['id'], $formattedDate, $_POST['malibelle'], $_POST['mamontant']); //il faut remplacer avec le dolllar post    MajFraisHorsForfait($id, $Date, $libelle, $Montant)            
        } else if ($_POST['submit'] == 'Refuser') {
            
            $MoisSuivent = Utilitaires::donnerMoisSuivant($_SESSION['fiche_frais']['mois']); //static il est lié à la classe on a pas besoin de cree une objet pour la utiliser 
            if ($pdo->estPremierFraisMois($_SESSION['fiche_frais']['visiteur'], $MoisSuivent)) {// on va ici si le fiche de frais ne exist pas si il exist pas on rajoute ce qui il faut ffaire et c'est de la crreéé
                $pdo->creeNouvellesLignesFrais($_SESSION['fiche_frais']['visiteur'], $MoisSuivent); //
            }
            $date = DateTime::createFromFormat('d/m/Y', $_POST['madate']); //creation objet datetime aveccune vrai date qui recupere depuis le formualire 
            $formattedDate = $date->format('Y-m-d'); // on passe de mon objet datetime à nouevelle objet //ca passe de format fr à from american 
            $pdo->creeNouveauFraisHorsForfait(
                    $_SESSION['fiche_frais']['visiteur'],
                    $MoisSuivent,
                    substr('Refuser ' . $_POST['malibelle'], 0, 99), //l'espace important a la fin de mt refuser pour pas colé les chaines de caractere 
                    $_POST['madate'],
                    $_POST['mamontant']
            );

            $pdo->supprimerFraisHorsForfait($_POST['id']);

         
        }


        //  * Teste si un visiteur possède une fiche de frais pour le mois passé en argument



        header("Location: index.php?uc=gererFrais&action=saisirFrais");
        break;
    case 'validerfichedefrais':   //action faire en paramretre pour valider la fiche l'action qui fait ça
        $calculemontanttotalfichefrais = $pdo->calculeToutFicheFrais($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);
        $updatelefichefdefrais = $pdo->updatetotalficheFrais($calculemontanttotalfichefrais, $_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);
        $validerfrais = $pdo->majEtatFicheFrais($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois'], 'VA'); //on acrit VA on sait qque on la mettre valider donc on la mis uniqment comme ça sans besoin de faire d'autres modif il suiffet juste de la ecrire comme ça 
        Utilitaires::ajouterMessage('La fiche de frais a été validée avec succès.');

        unset($_SESSION['fiche_frais']);

        header("Location: index.php?uc=gererFrais&action=saisirFrais");

        break;
}


require PATH_VIEWS . 'Comptable/v_menu_derolante.php'; //si tu mis le variable apres le require il serait pas et i le affiche pas 
// Vérifiez si la session 'fiche_frais' contient des données.
if (!empty($_SESSION['fiche_frais'])) {
    // Récupérez les frais forfaitisés et hors forfait du visiteur pour le mois spécifié.
    $recupefrais = $pdo->getLesFraisForfait($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);
    
    $recupefraishorsforfait = $pdo->getLesFraisHorsForfait($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);

    // Vérifiez si des frais ont été récupérés.
    if (!empty($recupefrais)) {

        // Si des frais ont été récupérés, incluez les vues pour les afficher.
        require PATH_VIEWS . 'Comptable/v_listeFraisForfait.php';
        require PATH_VIEWS . 'Comptable/v_listeFraisHorsForfait.php';
    } else {
        // Si aucun frais n'a été récupéré, définissez un message d'erreur.
        //$_SESSION['message'] = "pas de fiche de frais pour ce visiteur ce mois";    
        Utilitaires::ajouterErreur('pas de fiche de frais pour ce visiteur ce mois');
        include PATH_VIEWS . 'Comptable/v_erreurs_1.php';
    }
}

// Incluez ensuite la vue qui affiche le message d'erreur si nécessaire.
/*
  if (isset($_SESSION['message'])) {
  echo $_SESSION['message'];
  unset($_SESSION['message']); // Effacez le message après l'affichage.
  }
 * 
 */
//pour les supression de frais hors forfait qui le comptable juge que ils sont non utile ou pas professionelle faut cree en 1er le bouton 
//supprimer que quand il declanche  il modifie le champe de libelle conerné  en rahoutant le mot refusé ?, en la ra rajoutant dans la bdd 
include PATH_VIEWS . 'Comptable/v_MessageSucess.php';




//le bouton vailde dnasc le fiche de frais àça me amene qualque part dans dans le sitch case à l'endroit prescis 