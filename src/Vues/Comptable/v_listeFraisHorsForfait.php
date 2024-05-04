@ -1,52 +0,0 @@
<div class="row">
    <div class="panel panel-info">
        <div class="panel-heading">Descriptif des éléments hors forfait</div>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th class="date">Date</th>
                    <th class="libelle">Libellé</th>  
                    <th class="montant">Montant</th>  
                    <th class="action">&nbsp;</th> 
                </tr>
            </thead>  
            <tbody>
                <?php foreach ($recupefraishorsforfait as $unFraisHorsForfait): ?>
                    <?php
                    $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                    $date = $unFraisHorsForfait['date'];
                    $montant = $unFraisHorsForfait['montant'];
                    $id = $unFraisHorsForfait['id'];
                    ?>  
                    <tr>                                                                    
                <form method="post"  action =" index.php?uc=gererFrais&action=updateSelectFicheFraishorsforfait" >
                    <td> <input type = "text" name ="madate" value = "<?php echo $date; ?>"></td>    
                    <td>  <input type = "text" name ="malibelle" value ="<?php echo $libelle; ?>">    </td>
                    <td> <input type = "text" name ="mamontant" value="<?php echo $montant; ?>"> </td>
                    <input type = "hidden" name ="id" value="<?php echo $id; ?>"> 

                    <td>
                        <button class="btn btn-success" type="submit" name="submit" value="Corriger">Corriger</button>
                        <?php ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <td>
                                <button class="btn btn-danger" type="submit" name="submit" value="Refuser">Refuser</button>
                            </td>
                       

                    </td>
                </form>
                </tr>
            <?php endforeach; ?>
            </tbody>  
        </table>

    </div>

</div>

<div class="row">

    <a href=" index.php?uc=gererFrais&action=validerfichedefrais" class="btn btn-success" type="submit">Valider</a>

</div> 
