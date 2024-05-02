<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 * 
 * 
 * //quand je veux recupere une fiche de frais par rapport le formaulire en sois y un problem  et remi sait ce que c'est avec la vue actuelle de code on verrra ça la prochaine fois 
 * 
 */
?>

<div class="row">
    <form 
    <?php //action="index.php?uc=gererFrais&action=saisirFrais"  //le fait de mettre le get  ça nous prend le lie n pas defuat et à la fin il va rajouter les proprites comme le controlleurs et l'action ç  rajoute le visiteur et le mois'?>
        method="post" role="form" action =" index.php?uc=gererFrais&action=updateSelectFicheFrais" >
        <div class="form-group">
            
            <div class="col-md-4">
                <label for="selectvisiteur" >Visiteur : </label>

                <select id="selectvisiteur" name="selectvisiteur" class="form-control">
                    <?php foreach ($lesvisiteurs as $visiteur): ?>  

                        <?php
                        $selected = "";
                        if ($_SESSION['fiche_frais']['visiteur'] == $visiteur['id']) {
                            $selected = 'selected';
                        }
                        ?>        
                        <?php echo '<option value="' . $visiteur['id'] . '"   ' . $selected . '  >' . $visiteur['nom'] . ' ' . $visiteur['prenom'] . '</option>'; ?>
                    <?php endforeach; ?>                                

                </select>

            </div>
            <div class="col-md-4">

                <label for="lstMois" >Mois : </label>
                <select id="lstMois" name="lstMois" class="form-control">
  
                    <?php
                    foreach ($lesmois as $unmois) {
                        //    var_dump($visiteur);

                        $selected = "";
                        if ($_SESSION['fiche_frais']['mois'] == $unmois['mois']) {
                            $selected = 'selected';
                        }

                        echo '<option value="' . $unmois['mois'] . '" ' . $selected . '>' . $unmois['mois'] . '</option>';
                    }
                    ?>

                </select>  
            </div>

        </div>
        <input id="ok" type="submit" value="Valider" class="btn btn-success"/> 

    </form>

</div>