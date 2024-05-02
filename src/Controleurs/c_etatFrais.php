<?php

/**
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
use Outils\Utilitaires;

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$idVisiteur = $_SESSION['idVisiteur'];
switch ($action) {
    case 'selectionnerMois':
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        // Afin de sélectionner par défaut le dernier mois dans la zone de liste
        // on demande toutes les clés, et on prend la première,
        // les mois étant triés décroissants
        $lesCles = array_keys($lesMois); //un tableau pour trier les mois 
        $moisASelectionner = $lesCles[0];
        include PATH_VIEWS . 'v_listeMois.php';
        break;
    case 'voirEtatFrais':
        $leMois = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $moisASelectionner = $leMois;
        include PATH_VIEWS . 'v_listeMois.php';
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $leMois);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $leMois);
        $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $leMois);
        $numAnnee = substr($leMois, 0, 4);
        $numMois = substr($leMois, 4, 2);
        $libEtat = $lesInfosFicheFrais['libEtat'];
        $montantValide = $lesInfosFicheFrais['montantValide'];
        $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
        $dateModif = Utilitaires::dateAnglaisVersFrancais($lesInfosFicheFrais['dateModif']);
        include PATH_VIEWS . 'v_etatFrais.php';

        break;
    case 'telechargerPDF':
    ob_start(); // Start output buffering to prevent early output
    require '../vendor/autoload.php';
  //  require '../helpers/Tcpdf.php';

    $leMois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Now that we have $leMois, define $numAnnee and $numMois
    $numAnnee = substr($leMois, 0, 4);
    $numMois = substr($leMois, 4, 2);

    // ... [fetch the data like in 'voirEtatFrais']
    $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $leMois);
    $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $leMois);
    $lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $leMois);

    $libEtat = $lesInfosFicheFrais['libEtat'];
    $montantValide = $lesInfosFicheFrais['montantValide'];
    $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
    $dateModif = Utilitaires::dateAnglaisVersFrancais($lesInfosFicheFrais['dateModif']);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetTitle('Fiche de frais - ' . $numMois . '-' . $numAnnee);

    // Add a page
    $pdf->AddPage();

    // ... [Define the actual HTML for the PDF, using the fetched data]
    $html = '
    <hr>
    <div class="panel panel-primary">
        <div class="panel-heading">Fiche de frais du mois ' . $numMois . '-' . $numAnnee . ' :</div>
        <div class="panel-body">
            <strong><u>Etat :</u></strong> ' . $libEtat . '
            depuis le ' . $dateModif . ' <br>
            <strong><u>Montant validé :</u></strong> ' . $montantValide . '
        </div>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">Éléments forfaitisés</div>
        <table class="table table-bordered table-responsive">
            <tr>';
            foreach ($lesFraisForfait as $unFraisForfait) {
                $html .= '<th>' . htmlspecialchars($unFraisForfait['libelle']) . '</th>';
            }
            $html .= '</tr><tr>';
            foreach ($lesFraisForfait as $unFraisForfait) {
                $html .= '<td class="qteForfait">' . $unFraisForfait['quantite'] . '</td>';
            }
            $html .= '</tr>
        </table>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">Descriptif des éléments hors forfait - ' . $nbJustificatifs . ' justificatifs reçus</div>
        <table class="table table-bordered table-responsive">
            <tr>
                <th class="date">Date</th>
                <th class="libelle">Libellé</th>
                <th class="montant">Montant</th>
            </tr>';
            foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                $html .= '
            <tr>
                <td>' . $unFraisHorsForfait['date'] . '</td>
                <td>' . htmlspecialchars($unFraisHorsForfait['libelle']) . '</td>
                <td>' . $unFraisHorsForfait['montant'] . '</td>
            </tr>';
            }
            $html .= '
        </table>
    </div>';

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
    exit; // Make sure this is the last line in the PDF case
    break;
}