<?php

$pdo = new PDO('mysql:host=localhost;dbname=gsb_frais', 'root', '');
$pdo->query('SET CHARACTER SET utf8');

$requetePrepare = $pdo->prepare(
        'select visiteur.id, visiteur.mdp from visiteur'
);
$requetePrepare->execute();
$visiteurs = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
print_r($visiteurs);

foreach ($visiteurs as $visiteur) {
    $mdp = $visiteur['mdp'];
    $login = $visiteur['id'];
    $hashMdp = password_hash($mdp, PASSWORD_DEFAULT);
    $req = $pdo->prepare('UPDATE visiteur SET mdp= :hashMdp  WHERE id= :unId ');
    $req->bindParam(':unId',$login, PDO::PARAM_STR);
    $req->bindParam(':hashMdp',$hashMdp, PDO::PARAM_STR);
    $req->execute(); 
    
}