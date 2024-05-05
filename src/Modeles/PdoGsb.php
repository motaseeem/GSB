<?php

/**
 * Classe d'accès aux données.
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL - CNED <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */
/**
 * Classe d'accès aux données.
 *
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $connexion de type PDO
 * $instance qui contiendra l'unique instance de la classe
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   Release: 1.0
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

namespace Modeles;

use PDO;
use Outils\Utilitaires;

require '../config/bdd.php';

class PdoGsb {

    protected $connexion;
    private static $instance = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct() {
        $this->connexion = new PDO(DB_DSN, DB_USER, DB_PWD);
        $this->connexion->query('SET CHARACTER SET utf8');
    }

    /**
     * Méthode destructeur appelée dès qu'il n'y a plus de référence sur un
     * objet donné, ou dans n'importe quel ordre pendant la séquence d'arrêt.
     */
    public function __destruct() {
        $this->connexion = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     *
     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb(): PdoGsb {
        if (self::$instance == null) {
            self::$instance = new PdoGsb();
        }
        return self::$instance;
    }

    /**
     * Retourne les informations d'un visiteur
     *
     * @param String $login Login du visiteur
     * @param String $mdp   Mot de passe du visiteur
     *
     * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
    public function getInfosVisiteur($login): ?array {
        $requetePrepare = $this->connexion->prepare(
                'SELECT  visiteur.mdp , visiteur.id AS id, visiteur.nom AS nom, '
                . 'visiteur.prenom AS prenom '
                . 'FROM visiteur '
                . 'WHERE visiteur.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        //    $requetePrepare->bindParam(':unMdp', $mdp, PDO::PARAM_STR);
        $requetePrepare->execute();
        $user = $requetePrepare->fetch();
        
        if (empty($user)) {
            return null;
        }
        return $user;
    }

    //on a remis l'acces dierctement  la mdp hashé dans notree requte  juste pour le comparer au mdp sasisi de formulaire en gros le get mdp ça fait en 2 temps la on le fait driectement et on ecnomise le nomre de ligne et pointe de vue performance c'est mieux 
    public function getInfosComptable($login): ?array {
        $requetePrepare = $this->connexion->prepare(
                'SELECT comptable.mdp, comptable.id AS id, comptable.nom AS nom, '
                . 'comptable.prenom AS prenom '
                . 'FROM comptable '
                . 'WHERE comptable.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        //  $requetePrepare->bindParam(':unMdp', $mdp, PDO::PARAM_STR);
        $requetePrepare->execute();
        $user = $requetePrepare->fetch();

        if (empty($user)) {
            return null;
        }
        return $user;
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * hors forfait concernées par les deux arguments.
     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return tous les champs des lignes de frais hors forfait sous la forme
     * d'un tableau associatif
     */
    public function getLesFraisHorsForfait($idVisiteur, $mois): array {
        $requetePrepare = $this->connexion->prepare(
                'SELECT * FROM lignefraishorsforfait '
                . 'WHERE lignefraishorsforfait.idvisiteur = :unIdVisiteur '
                . 'AND lignefraishorsforfait.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesLignes = $requetePrepare->fetchAll();
        $nbLignes = count($lesLignes);
        for ($i = 0; $i < $nbLignes; $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = Utilitaires::dateAnglaisVersFrancais($date);
        }
        return $lesLignes;
    }

    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return le nombre entier de justificatifs
     */
    public function getNbjustificatifs($idVisiteur, $mois): int {
        $requetePrepare = $this->connexion->prepare(
                'SELECT fichefrais.nbjustificatifs as nb FROM fichefrais '
                . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
                . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['nb'];
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * au forfait concernées par les deux arguments
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return l'id, le libelle et la quantité sous la forme d'un tableau
     * associatif
     */
    public function getLesFraisForfait($idVisiteur, $mois): array {// trouver l'id visiteurs pour recupere les elemetns de fiche de frais  {
        $requetePrepare = $this->connexion->prepare(
                'SELECT fraisforfait.id as idfrais, '
                . 'fraisforfait.libelle as libelle, '
                . 'lignefraisforfait.quantite as quantite '
                . 'FROM lignefraisforfait '
                . 'INNER JOIN fraisforfait '
                . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
                . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
                . 'AND lignefraisforfait.mois = :unMois '
                . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne tous les id de la table FraisForfait
     *
     * @return un tableau associatif
     */
    public function getLesIdFrais(): array {
        $requetePrepare = $this->connexion->prepare(
                'SELECT fraisforfait.id as idfrais '
                . 'FROM fraisforfait ORDER BY fraisforfait.id'
        );
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Met à jour la table ligneFraisForfait
     * Met à jour la table ligneFraisForfait pour un visiteur et
     * un mois donné en enregistrant les nouveaux montants
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param Array  $lesFrais   tableau associatif de clé idFrais et
     *                           de valeur la quantité pour ce frais
     *
     * @return null
     */
    public function majFraisForfait($idVisiteur, $mois, $lesFrais): void {// maj ça veut dire mise à jour  {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $requetePrepare = $this->connexion->prepare(
                    'UPDATE lignefraisforfait '
                    . 'SET lignefraisforfait.quantite = :uneQte '
                    . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
                    . 'AND lignefraisforfait.mois = :unMois '
                    . 'AND lignefraisforfait.idfraisforfait = :idFrais'
            );
            $requetePrepare->bindParam(':uneQte', $qte, PDO::PARAM_INT);
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné
     *
     * @param String  $idVisiteur      ID du visiteur
     * @param String  $mois            Mois sous la forme aaaamm
     * @param Integer $nbJustificatifs Nombre de justificatifs
     *
     * @return null
     */
    public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs): void {
        $requetePrepare = $this->connexion->prepare(
                'UPDATE fichefrais '
                . 'SET nbjustificatifs = :unNbJustificatifs '
                . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
                . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(
                ':unNbJustificatifs',
                $nbJustificatifs,
                PDO::PARAM_INT
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return vrai ou faux
     */
    public function estPremierFraisMois($idVisiteur, $mois): bool {
        $boolReturn = false;
        $requetePrepare = $this->connexion->prepare(
                'SELECT fichefrais.mois FROM fichefrais '
                . 'WHERE fichefrais.mois = :unMois '
                . 'AND fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        if (!$requetePrepare->fetch()) {
            $boolReturn = true;
        }
        return $boolReturn;
    }

    /**
     * Retourne le dernier mois en cours d'un visiteur
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return le mois sous la forme aaaamm
     */
    public function dernierMoisSaisi($idVisiteur): ?string {
        $requetePrepare = $this->connexion->prepare(
                'SELECT MAX(mois) as dernierMois '
                . 'FROM fichefrais '
                . 'WHERE fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        $dernierMois = $laLigne['dernierMois'];
        return $dernierMois;
    }

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait
     * pour un visiteur et un mois donnés
     *
     * Récupère le dernier mois en cours de traitement, met à 'CL' son champs
     * idEtat, crée une nouvelle fiche de frais avec un idEtat à 'CR' et crée
     * les lignes de frais forfait de quantités nulles
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return null
     */
    public function creeNouvellesLignesFrais($idVisiteur, $mois): void {
        $dernierMois = $this->dernierMoisSaisi($idVisiteur);
        $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur, $dernierMois);
        if ($laDerniereFiche['idEtat'] == 'CR') {
            $this->majEtatFicheFrais($idVisiteur, $dernierMois, 'CL');
        }
        $requetePrepare = $this->connexion->prepare(
                'INSERT INTO fichefrais (idvisiteur,mois,nbjustificatifs,'
                . 'montantvalide,datemodif,idetat) '
                . "VALUES (:unIdVisiteur,:unMois,0,0,now(),'CR')"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesIdFrais = $this->getLesIdFrais();
        foreach ($lesIdFrais as $unIdFrais) {
            $requetePrepare = $this->connexion->prepare(
                    'INSERT INTO lignefraisforfait (idvisiteur,mois,'
                    . 'idfraisforfait,quantite) '
                    . 'VALUES(:unIdVisiteur, :unMois, :idFrais, 0)'
            );
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais['idfrais'], PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $libelle    Libellé du frais
     * @param String $date       Date du frais au format français jj//mm/aaaa
     * @param Float  $montant    Montant du frais
     *
     * @return null
     */
    public function creeNouveauFraisHorsForfait($idVisiteur, $mois, $libelle, $date, $montant): void {
        $dateFr = Utilitaires::dateFrancaisVersAnglais($date);
        $requetePrepare = $this->connexion->prepare(
                'INSERT INTO lignefraishorsforfait '
                . 'VALUES (null, :unIdVisiteur,:unMois, :unLibelle, :uneDateFr,'
                . ':unMontant) '
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unLibelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':uneDateFr', $dateFr, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMontant', $montant, PDO::PARAM_INT);
        $requetePrepare->execute();
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     *
     * @param String $idFrais ID du frais
     *
     * @return null
     */
    public function supprimerFraisHorsForfait($idFrais): void {
        $requetePrepare = $this->connexion->prepare(
                'DELETE FROM lignefraishorsforfait '
                . 'WHERE lignefraishorsforfait.id = :unIdFrais'
        );
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getLesMoisDisponibles($idVisiteur): array {
        $requetePrepare = $this->connexion->prepare(
                'SELECT fichefrais.mois AS mois FROM fichefrais '
                . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
                . 'ORDER BY fichefrais.mois desc'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesMois = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois[] = array(
                'mois' => $mois,
                'numAnnee' => $numAnnee,
                'numMois' => $numMois
            );
        }
        return $lesMois;
    }

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un
     * mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return un tableau avec des champs de jointure entre une fiche de frais
     *         et la ligne d'état
     */
    public function getLesInfosFicheFrais($idVisiteur, $mois): array {
        $requetePrepare = $this->connexion->prepare(
                'SELECT fichefrais.idetat as idEtat, '
                . 'fichefrais.datemodif as dateModif,'
                . 'fichefrais.nbjustificatifs as nbJustificatifs, '
                . 'fichefrais.montantvalide as montantValide, '
                . 'etat.libelle as libEtat '
                . 'FROM fichefrais '
                . 'INNER JOIN etat ON fichefrais.idetat = etat.id '
                . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
                . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne;
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais.
     * Modifie le champ idEtat et met la date de modif à aujourd'hui.
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $etat       Nouvel état de la fiche de frais
     *
     * @return null
     */
    //il faut modifier le calcule de montant que il faut mettre stocker pour valider
    public function majEtatFicheFrais($idVisiteur, $mois, $etat): void {
        $requetePrepare = $this->connexion->prepare(
                'UPDATE fichefrais '
                . 'SET idetat = :unEtat, datemodif = now() '
                . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
                . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unEtat', $etat, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /*
     *  fonction qui recupere tout les visiteurs 
     */

    public function getToutLesVisiteurs(): array {
        $requetePrepare = $this->connexion->prepare(
                "select * from fichefrais join visiteur on visiteur.id = fichefrais.idvisiteur where idetat='CL'"
        );
        $requetePrepare->execute();

        $resultat = $requetePrepare->fetchAll(); //fetch all ça renvoie un tableau donc le resultat donné sera stocker dans le variable $resultat 
        //var_dump($resultat);
        return $resultat;
    }

    public function getToutLesMois(): array {//parametre id visiteur , recuperer les mois pour ce visteur s  pas tout les mois 
        $requetePrepare = $this->connexion->prepare(
                "select distinct mois  from fichefrais where idetat = 'CL'" // and idvisteur=idvisteur  //ça se fait en 2etap
                // quand un mois est cloturé par le visiteurs un comptable il a acces dessus 
        );
        $requetePrepare->execute();

        $resultat = $requetePrepare->fetchAll(); //fetch all ça renvoie un tableau donc le resultat donné sera stocker dans le variable $resultat 
        //var_dump($resultat);
        return $resultat;
    }

    /**
     * cet fonction  se applique sur un :igne en particulier
     */
    public function MajFraisHorsForfait($id, $Date, $libelle, $Montant): void {
        //var_dump($Date);

        $sql = " update lignefraishorsforfait set date = :undate , montant = :unmontant , libelle = :unlibelle where id = :unid";
        $req = $this->connexion->prepare($sql);

        $req->bindParam(':undate', $Date, PDO::PARAM_STR);
        $req->bindParam(':unid', $id, PDO::PARAM_INT);
        $req->bindParam(':unlibelle', $libelle, PDO::PARAM_STR);
        $req->bindParam(':unmontant', $Montant, PDO::PARAM_INT);

        $req->execute();
    }

    public function RefusFraisHorsFrait($id): void {
        //ajoute la mot refusee dans le champ libelle 
        $sql = " UPDATE lignefraishorsforfait SET libelle = CONCAT('REFUSE ', libelle) WHERE id = :unid";
        $req = $this->connexion->prepare($sql);
        $req->bindParam(':unid', $id, PDO::PARAM_INT);

        $req->execute();
    }

    function verifierEtCreerFicheFrais($mois) {
        // Vérifier si la fiche existe
        $sqlVerif = "SELECT COUNT(*) FROM fichefrais WHERE mois = :mois";
        $stmt = $this->connexion->prepare($sqlVerif);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if (!$existe) {
            // La fiche n'existe pas, on la crée
            $sqlInsert = "INSERT INTO fichefrais (mois, ...) VALUES (:mois, ...)";
            $stmt = $this->connexion->prepare($sqlInsert);
            $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // je doit cree une methode qui recupere la fiche de  mois suv ent  pour le visiteur que je 
    // le visiteur c'est  tel donne moi le fiche de mois suivent 

    function creerLigneFraisHorsForfait($mois, $description, $montant) {
        $sql = "INSERT INTO lignefraishorsforfait (mois, libelle, montant) VALUES (:mois, :libelle, :montant)";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->bindParam(':libelle', $description, PDO::PARAM_STR);
        $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
        $stmt->execute();
    }

    function supprimerFraisHorsForfaitActuel($idFrais) {
        $sql = "DELETE FROM lignefraishorsforfait WHERE id = :id";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':id', $idFrais, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * je recuepre une fiche de frais en focntion de l' etat passeé en parametre  
     */
    function RecupFicheFrais($idetat) {
        $sql = "SELECT visiteur.nom, visiteur.prenom, fichefrais.mois, visiteur.id
            FROM fichefrais
            JOIN visiteur ON fichefrais.idvisiteur = visiteur.id
            WHERE fichefrais.idetat = :idetat";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':idetat', $idetat, PDO::PARAM_STR);
        $stmt->execute();

        // Pour récupérer et retourner les résultats
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultats;
    }

    function calculeToutFicheFrais($idvisiteur, $mois) {
        // -- la somme de tout les lignes de fiche de frais hors forfait par fiche de frais 

        $sql = "select sum(montant)    from lignefraishorsforfait  
                    where idvisiteur  = :idvisiteur and mois = :mois";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':idvisiteur', $idvisiteur, PDO::PARAM_STR);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->execute();
        $totalfichefraishorsforfait = $stmt->fetch(PDO::FETCH_ASSOC); // on le aura besoin quand on recupere plusieurs lignes des colonnes de la base et pas quand on a un seul  //
        $example = intval($totalfichefraishorsforfait['sum(montant)']);

        // -- chercher des lignes frias forfiat pour une fiche de frais en question et faire la calcule 
        //var_dump($totalfichefraishorsforfait);
        $sql = "select   sum(quantite * montant) as montantfinal   from lignefraisforfait JOIN fraisforfait on fraisforfait.id = lignefraisforfait.idfraisforfait
                    where idvisiteur  = :idvisiteur and mois = :mois and idfraisforfait  != 'KM'";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':idvisiteur', $idvisiteur, PDO::PARAM_STR);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->execute();
        $totalfraisforfait = $stmt->fetch(PDO::FETCH_ASSOC); // on le aura besoin quand on recupere plusieurs lignes des colonnes de la base et pas quand on a un seul 
        $examplee = intval($totalfraisforfait['montantfinal']);

        $sql = "select  (quantite * prix )  as finalprix  from lignefraisforfait join  visiteur on lignefraisforfait.idvisiteur = visiteur.id 
                join fraiskm on visiteur.idVehicule  = fraiskm.id 
                where idvisiteur  = :idvisiteur   and mois = :mois and idfraisforfait  = 'KM' ";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':idvisiteur', $idvisiteur, PDO::PARAM_STR);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->execute();
        $totalfraiskilometique = $stmt->fetch(PDO::FETCH_ASSOC); // on le aura besoin quand on recupere plusieurs lignes des colonnes de la base et pas quand on a un seul
        $exampleee = intval($totalfraiskilometique['finalprix']);
        //var_dump($totalfraiskilometique);
        // le but c'est de avoir le somme totale de tout la fiche de frais forfait et horsforfait 
        // -- le montant valider total n'est été pas encore existent dnas la application et pour faire le indemenité kilometiuqe fallait déja contriuie la base et faire 
        // -- ces calcules cruceille pour l'application pour apres passer à la suite 

        $montantotal = $example + $examplee + $exampleee;
        //  var_dump($montantotal);
        return $montantotal;
    }

    /*
     * dans ce requte on fait le mise à jour de la fiche de frais en utilisant tout les 3requte select precedente 
     */

    function updatetotalficheFrais($total, $idvisiteur, $mois) {
        $sql = "update  fichefrais set montantvalide = :montant WHERE idVisiteur = :idvisiteur AND mois = :mois";
        $stmt = $this->connexion->prepare($sql);

        $stmt->bindParam(':idvisiteur', $idvisiteur, PDO::PARAM_STR);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->bindParam(':montant', $total, PDO::PARAM_INT);
        $resultat = $stmt->execute();
        // var_dump($resultat);
        return $resultat;
    }

    //adidtionner tout les 3 requte et les mettre dans un update sur le montant valider 
    //focntion  calcule fiche frais 
    //
    //
    //
    //fonction  : qttite  * prix 
    //fiche drais kilmetique  * le taux 
    //ça se fait au moment ou on vlaider a fiche de frais 

    /*
      function recuptotalfraisForfait($idfraisforfait, $idvisiteur, $mois) {
      $sql = "select sum(montant * quantite) as total from fraisforfait join lignefraisforfait  on fraisforfait.id = lignefraisforfait.idfraisforfait where idvisiteur = :idvisiteur and mois = :mois
      and idfraisforfait = :idfraisforfait";
      $stmt = $this->connexion->prepare($sql);
      $stmt->bindParam(':idvisiteur', $idvisiteur, PDO::PARAM_STR);
      $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
      $stmt->bindParam(':idfraisforfait', $idfraisforfait, PDO::PARAM_INT);

      $resultat = $stmt->execute();
      return $resultat;
      }
     * 
     */

    function recuptotalfraisForfait( $idvisiteur, $mois) {
        $sql = "
        SELECT SUM(fraisforfait.montant * lignefraisforfait.quantite) AS total
        FROM fraisforfait 
        JOIN lignefraisforfait ON fraisforfait.id = lignefraisforfait.idfraisforfait
        WHERE lignefraisforfait.idvisiteur = :idvisiteur
        AND lignefraisforfait.mois = :mois
         group by  idfraisforfait
    ";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':idvisiteur', $idvisiteur, PDO::PARAM_STR);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result; // Retourner la valeur totale
    }
    
    
    public function calculeToutFrais($idVisiteur, $mois)
{
    $query = "SELECT SUM(montant * quantite) AS total
              FROM fraisforfait
              JOIN lignefraisforfait ON fraisforfait.id = lignefraisforfait.idfraisforfait
              WHERE idvisiteur = :idVisiteur AND mois = :mois AND fraisforfait.id <> 'KM'
              GROUP BY idfraisforfait";

    $stmt = $this->connexion->prepare($query);
    $stmt->bindParam(':idVisiteur', $idVisiteur);
    $stmt->bindParam(':mois', $mois);
    $stmt->execute();

        $resultats = $stmt->fetchAll(PDO::FETCH_COLUMN);


    return $resultats;
}



public function calculeFraisKilometriques($idVisiteur, $mois)
{
    $query = "SELECT (quantite * prix) AS finalprix
              FROM lignefraisforfait
              JOIN visiteur ON lignefraisforfait.idvisiteur = visiteur.id
              JOIN fraiskm ON visiteur.idVehicule = fraiskm.id
              WHERE idvisiteur = :idVisiteur AND mois = :mois AND idfraisforfait = 'KM'";

    $stmt = $this->connexion->prepare($query);
    $stmt->bindParam(':idVisiteur', $idVisiteur);
    $stmt->bindParam(':mois', $mois);
    $stmt->execute();

    $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

    return $resultat;
}
   
}
