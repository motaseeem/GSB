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

$lesvisiteurs = $pdo->getToutLesVisiteurs(); //     la fonction qui va venire lancé des requte sql pour mettre à jour les valeurs 
$lesmois = $pdo->getToutLesMois();
$action = $_GET['action'];
//var_dump($recupefrais);
//var_dump($lesvisiteurs);
// $_get = id visiteurs , mois 
//var_dump($_GET);
//var_dump($_POST['lesFrais']);
// var_dump($_POST);

switch ($action) {

    case 'updateSelectFicheFrais':
        //qlqchose = $pdo->getLesInfosFicheFrais($_POST['selectvisiteur'], $_POST['lstMois']);
       // var_dump($qlqchose);

     //   if ($qlqchose['idEtat'] == 'CL') {


            //c'est un tableau  , 
            //commont ajouter q. q chose en session     //fiche frais l'ettiquette qui est la valeur dans la session 
            
            //le date time c'est lui qui confrtis 
            //la cree sur la valeur de lois fiche frais 
       // } else {
       //     echo 'ça marche pas fils';
     //   }
        $_SESSION['fiche_frais'] = [//$ session c'est l'armoire de casier  et a'linterieur un autre casier fiche frias et dedans je mis mini caiser  : visiteur , mois /// il permet de recupere les valeurs peu import ou je suis 

            'visiteur' => $_POST['selectvisiteur'],
            'mois' => $_POST['lstMois'] // on les recuepres des formualires 
        ];
         header("Location: index.php?uc=gererFrais&action=saisirFrais"); // lien il va valider le saisir le frais  qui dirigre vers la page gerer frais
        //l'header ça permet de rajouter des infroamtions pour le client donne aux client implementé au code 
        // c'est information que le serveur 
        break;
    case 'mettreAjourFicheFrais':
        //dans ma nouvelle swtich case je doit mettre ajour les elements rempli dans les champs et les mettre à jour dans la bdd

        $pdo->majFraisForfait($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois'], $_POST['lesFrais']); //faire appelle a la fonction 

        header("Location: index.php?uc=gererFrais&action=saisirFrais"); // lien il va valider le saisir le frais  qui dirigre vers la page gerer frais
        break;
    case 'updateSelectFicheFraishorsforfait':

        if ($_POST['submit'] == 'Corriger') {            //si le submit == corgier tu fait telle action sinon tu fait tel action 
            //tu fait le parti de code qui coresspond à la correction 
            $date = DateTime::createFromFormat('d/m/Y', $_POST['madate']); //creation objet datetime aveccune vrai date qui recupere depuis le formualire 
            $formattedDate = $date->format('Y-m-d'); // on passe de mon objet datetime à nouevelle objet //ca passe de format fr à from american 

            $pdo->MajFraisHorsForfait($_POST['id'], $formattedDate, $_POST['malibelle'], $_POST['mamontant']); //il faut remplacer avec le dolllar post    MajFraisHorsForfait($id, $Date, $libelle, $Montant)            
            /// on a cree un variable qui se base sur ce qui dans le ligne 52 on l'a mis à la place de notre variable $_post[madate']
        } else if ($_POST['submit'] == 'Refuser') {
            //if (isset($_POST['id'])) {
            //  $pdo->RefusFraisHorsFrait($_POST['id']);
            //plusierlle 
            //}//faire appelle a la fonction qui recupere le mois suivent de mois qu on lui ia donné   
            $MoisSuivent = Utilitaires::donnerMoisSuivant($_SESSION['fiche_frais']['mois']); //static il est lié à la classe on a pas besoin de cree une objet pour la utiliser 
            if ($pdo->estPremierFraisMois($_SESSION['fiche_frais']['visiteur'], $MoisSuivent)) {// on va ici si le fiche de frais ne exist pas si il exist pas on rajoute ce qui il faut ffaire et c'est de la crreéé
                $pdo->creeNouvellesLignesFrais($_SESSION['fiche_frais']['visiteur'], $MoisSuivent); //
            }
            $date = DateTime::createFromFormat('d/m/Y', $_POST['madate']); //creation objet datetime aveccune vrai date qui recupere depuis le formualire 
            $formattedDate = $date->format('Y-m-d'); // on passe de mon objet datetime à nouevelle objet //ca passe de format fr à from american 
            // on a utilksé dollarr parce que on a recupere des elements depuis le formualire quand on clique sur le bouton supprimere ça nous donne tout les info en lien de frais hors forfait tout la rest on peux la recuepre depuis dollar 
            $pdo->creeNouveauFraisHorsForfait(
                    $_SESSION['fiche_frais']['visiteur'],
                    $MoisSuivent,
                    substr('Refuser ' . $_POST['malibelle'], 0, 99), //l'espace important a la fin de mt refuser pour pas colé les chaines de caractere 
                    $_POST['madate'],
                    $_POST['mamontant']
            );

            $pdo->supprimerFraisHorsForfait($_POST['id']);

            //
            //
            //etpa1 :pour la fiche de mois actuelle  allez recupere le mois d'apres faut  baser sur la date ou on est actuellemment dnas le vrai vie 
            //etap2 : il faudrait verifé si i ly a fiche de frais pour le mois suivent existe  , dans le cas ou le fiche ne existe pas on  va la cree si il existe on recupere c'est tout rien à faire 
            //etpa3 : creation nouvelle ligne frais hors forfait qui a ete pour la fiche de frais de mois suivent  , 
            //etpa4 : supprimer le frais hors forfait pour le mois actuelle parce que ce sera deplacer dans le mois suivent 
        }


        //  * Teste si un visiteur possède une fiche de frais pour le mois passé en argument



        header("Location: index.php?uc=gererFrais&action=saisirFrais");
        break;
    case 'validerfichedefrais':   //action faire en paramretre pour valider la fiche l'action qui fait ça
        $calculemontanttotalfichefrais = $pdo->calculeToutFicheFrais($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);
        //var_dump($calculemontanttotalfichefrais);
        $updatelefichefdefrais = $pdo->updatetotalficheFrais($calculemontanttotalfichefrais, $_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);
        //   var_dump($updatelefichefdefrais);
        $validerfrais = $pdo->majEtatFicheFrais($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois'], 'VA'); //on acrit VA on sait qque on la mettre valider donc on la mis uniqment comme ça sans besoin de faire d'autres modif il suiffet juste de la ecrire comme ça 
        Utilitaires::ajouterMessage('La fiche de frais a été validée avec succès.');

        unset($_SESSION['fiche_frais']);

        header("Location: index.php?uc=gererFrais&action=saisirFrais");

        break;
}

//var_dump($_POST['submit']);

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
//var_dump($_POST(RefusFraisHorsFrait('libelle')));
include PATH_VIEWS . 'Comptable/v_MessageSucess.php';

// var_dump($pdo->MajFraisHorsForfait(1 ,'2023-03-06' ,' Taxiii' , 245.01));



//le bouton vailde dnasc le fiche de frais àça me amene qualque part dans dans le sitch case à l'endroit prescis 