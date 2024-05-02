<?php
/**
 * Vue Erreurs
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




<!-- Ici, j'ai ajouté une condition isset pour vérifier si $_REQUEST['sucess']
est défini avant de l'utiliser dans la boucle foreach. Cela empêchera l'erreur "Undefined array key" si $_REQUEST['sucess'] n'est pas défini. -->


    <?php
    if 
        (isset($_REQUEST['success'])) { // Assurez-vous d'utiliser le bon nom de clé
           ?> <div class="alert alert-success" role="alert">  <?php
        foreach ($_REQUEST['success'] as $success) {
            echo '<p>' . htmlspecialchars($success) . '</p>';
            
        }
         ?>
           </div> <?php

    }
    ?>

    
    