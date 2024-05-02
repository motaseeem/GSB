<?php
/**
 * Vue État de Frais
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
 * @link      https://getbootstrap.com/docs/3.3/ Documentation Bootstrap v3
 */
?>




<!--<<!-- comment -->

<div class="row">
    <form 
    <?php //action="index.php?uc=gererFrais&action=saisirFrais"  //le fait de mettre le get  ça nous prend le lie n pas defuat et à la fin il va rajouter les proprites comme le controlleurs et l'action ç  rajoute le visiteur et le mois' ?>
        method="post" role="form" action ="/index.php?uc=etatFrais&action=selectionnerFicheFrais">

        <div class="form-group">

            <div class="col-md-4">
                <label for="selectvisiteur" >Fiche Frais  : </label>

                <select id="selectvisiteurEtMois" name="selectvisiteurEtMois" class="form-control">
                    <?php foreach ($recupinfoFicheFrais as $FicheFrais): ?>  

                        <?php
                        $idFicheFrais = $FicheFrais['id'] . $FicheFrais['mois'];
                        $idFicheFraisSession = $_SESSION['suivre_fichefrais']['visiteur'] . $_SESSION['suivre_fichefrais']['mois'];
                        $selected = "";
                        if ($idFicheFrais == $idFicheFraisSession) {
                            $selected = 'selected';
                        }
                        ?>        
                        <?php echo '<option value="' . $FicheFrais['id'] . '-' . $FicheFrais['mois'] . '"   ' . $selected . '  >' . $FicheFrais['nom'] . ' ' . $FicheFrais['prenom'] . ' - ' . $FicheFrais['mois'] . '</option>'; ?>
                    <?php endforeach; ?>                                

                </select>

            </div>

            <input id="ok" type="submit" value="Valider" class="btn btn-success"/> 



    </form>



</div>


        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        