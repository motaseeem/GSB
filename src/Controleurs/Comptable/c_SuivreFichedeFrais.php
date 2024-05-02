<?php

/** on veut que quand on appuie sur le bouton ça recupere deja les element forfait et hors forfait..commont faire ?  
 * Gestion de l'affichage des frais
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
/*
  use Outils\Utilitaires;
  $action = $_GET['action'];

  $recupefrais = $pdo->getLesFraisForfait($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);
  $recupefraishorsforfait = $pdo->getLesFraisHorsForfait($_SESSION['fiche_frais']['visiteur'], $_SESSION['fiche_frais']['mois']);

  //recupereation de tout les fiche de frais qui ont un statut valider
  $recupinfoFicheFrais = $pdo->RecupFicheFrais('VA');
  //$action = $_GET['action'];
  //var_dump($recupinfoFicheFrais);

  include PATH_VIEWS . 'Comptable/v_SuivreFichedeFrais.php';

  switch ($action) {

  case 'SelectFicheFrais':

  $_SESSION['fiche_frais'] = [//$ session c'est l'armoire de casier  et a'linterieur un autre casier fiche frias et dedans je mis mini caiser  : visiteur , mois /// il permet de recupere les valeurs peu import ou je suis
  'Fiche Frais' => $_POST['selectvisiteurEtMois'],// les elments que je doit recupere à partir d'un id fiche frais commont porceder à les afficher
  ];


  //$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $idVisiteur = $_SESSION['idVisiteur'];


 */

use Outils\Utilitaires;

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

switch ($action) {


    case 'selectionnerFicheFrais':

        $visiteurEtmois = $_POST['selectvisiteurEtMois'];
        $data = explode('-', $visiteurEtmois);

        $_SESSION['suivre_fichefrais'] = [
            'visiteur' => $data[0],
            'mois' => $data[1],
        ];
        header("Location: index.php?uc=etatFrais&action=suivrefrais"); // lien il va valider le saisir le frais  qui dirigre vers la page gerer frais

        break;
    case 'mettreAjourEtatFrais':
        $visiteurId = $_SESSION['suivre_fichefrais']['visiteur']; // ou récupérer de $_POST si vous passez ces valeurs via le formulaire
        $mois = $_SESSION['suivre_fichefrais']['mois']; // idem, selon votre implémentation
        // Appel à la méthode de mise à jour
        $majEtatFicheFrais = $pdo->majEtatFicheFrais($visiteurId, $mois, 'MP');

        unset($_SESSION['suivre_fichefrais']); //je vide ma session dans le but car il st plus affihcer dnas le menu deroulnate la fiche de frais donc l''afficage ne va pas se faire 
        Utilitaires::ajouterMessage('La fiche de frais a été mise en paiement avec succès.');

        // header("Location: index.php?uc=etatFrais&action=suivrefrais");
        break;

    case 'telechargerPDF':
    ob_start(); // Start output buffering to prevent early output
    require '../vendor/autoload.php';

    if (!empty($_SESSION['suivre_fichefrais'])) {
        $visiteur = $_SESSION['suivre_fichefrais']['visiteur'];
        $mois = $_SESSION['suivre_fichefrais']['mois'];

        $leMois = $mois;
        $numAnnee = substr($leMois, 0, 4);
        $numMois = substr($leMois, 4, 2);

        // Récupérer les données nécessaires pour la génération du PDF
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteur, $leMois);
        $lesFraisForfait = $pdo->getLesFraisForfait($visiteur, $leMois);
        $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($visiteur, $leMois);

        $libEtat = $lesInfosFicheFrais['libEtat'];
        $montantValide = $lesInfosFicheFrais['montantValide'];
        $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
        $dateModif = Utilitaires::dateAnglaisVersFrancais($lesInfosFicheFrais['dateModif']);

        // Calculer le total des frais forfaitaires
        $calculetotalFrais = $pdo->calculeToutFrais($visiteur, $mois);
        $totalFraisForfaitaires = is_array($calculetotalFrais) ? array_sum($calculetotalFrais) : $calculetotalFrais;

        // Calculer le total des frais kilométriques
        $calculkilometre = $pdo->calculeFraisKilometriques($visiteur, $mois);
        $totalFraisKilometriques = is_array($calculkilometre) ? implode(', ', $calculkilometre) : $calculkilometre;

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetTitle('Fiche de frais - ' . $numMois . '-' . $numAnnee);

        // Add a page
        $pdf->AddPage();

        // Generate the HTML content for the PDF
        $html = '
        <table>
            <tr>
                <td>Visiteur</td>
                <td>' . $visiteur . '</td>
                <td>Sophie GRANDPRÉ</td>
            </tr>
            <tr>
                <td>Mois</td>
                <td>' . $mois . '</td>
                <td></td>
            </tr>
        </table>

        <h3>Frais Forfaitaires</h3>
        <table>
            <thead>
                <tr>
                    <th>Type de frais</th>
                    <th>Quantité</th>
                    <th>Montant unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($lesFraisForfait as $unFraisForfait) {
            if (isset($unFraisForfait['montant'])) {
                $html .= '
                <tr>
                    <td>' . htmlspecialchars($unFraisForfait['libelle']) . '</td>
                    <td>' . $unFraisForfait['quantite'] . '</td>
                    <td>' . number_format($unFraisForfait['montant'], 2) . '</td>
                    <td>' . number_format($unFraisForfait['quantite'] * $unFraisForfait['montant'], 2) . '</td>
                </tr>';
            }
        }

        $html .= '
            </tbody>
        </table>

        <h3>Autres Frais</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
            $html .= '
            <tr>
                <td>' . $unFraisHorsForfait['date'] . '</td>
                <td>' . htmlspecialchars($unFraisHorsForfait['libelle']) . '</td>
                <td>' . number_format($unFraisHorsForfait['montant'], 2) . '</td>
            </tr>';
        }

        $html .= '
            </tbody>
        </table>

        <p>
            <strong>TOTAL ' . $mois . ' : </strong>' . number_format($totalFraisForfaitaires + (float)$totalFraisKilometriques, 2) . '
        </p>';

        // Print text using writeHTML()
        $pdf->writeHTML($html, true, false, true, false, '');

        // Clean the output buffer and send headers for the PDF
        ob_end_clean();

        // Close and output PDF document
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="REMBOURSEMENT_FRAIS_' . $numMois . '-' . $numAnnee . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdf->Output('', 'S');
        exit;
    } else {
        // Gérer le cas où la fiche de frais n'est pas sélectionnée
        echo "Aucune fiche de frais sélectionnée.";
    }
    break;
}



//$visiteur = $_SESSION['suivre_fichefrais']['visiteur'];
//$mois = $_SESSION['suivre_fichefrais']['mois'];
//$lesFraisForfait = $pdo->getLesFraisForfait($visiteur, $mois);


//recupereation de tout les fiche de frais qui ont un statut valider
$recupinfoFicheFrais = $pdo->RecupFicheFrais('VA');

include PATH_VIEWS . 'Comptable/v_MessageSucess.php';

include PATH_VIEWS . 'Comptable/v_SuivreFichedeFrais.php';

/*
 * si on selectionner une fiche  de frais on affiche la fiche de frais  
 */

if (!empty($_SESSION['suivre_fichefrais'])) {
    //  var_dump($_SESSION['suivre_fichefrais']['visiteur']);
    $visiteur = $_SESSION['suivre_fichefrais']['visiteur'];
    $mois = $_SESSION['suivre_fichefrais']['mois'];

    $pdo->calculeToutFicheFrais($visiteur, $mois);

    $leMois = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lesMois = $pdo->getLesMoisDisponibles($visiteur);
    $moisASelectionner = $leMois;
    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteur, $mois);
    $lesFraisForfait = $pdo->getLesFraisForfait($visiteur, $mois);
    $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($visiteur, $mois);
    $numAnnee = substr($mois, 0, 4);
    $numMois = substr($mois, 4, 2);
    $libEtat = $lesInfosFicheFrais['libEtat'];
    $montantValide = $lesInfosFicheFrais['montantValide'];
    $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
    $dateModif = Utilitaires::dateAnglaisVersFrancais($lesInfosFicheFrais['dateModif']);
    include PATH_VIEWS . 'v_etatFrais.php';
    include PATH_VIEWS . 'Comptable/v_MiseEnPaiement.php';
}
