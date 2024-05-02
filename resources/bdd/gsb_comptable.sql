use gsb_frais;
CREATE TABLE IF NOT EXISTS comptable (
  id char(5) NOT NULL,
  nom char(30) DEFAULT NULL,
  prenom char(30)  DEFAULT NULL, 
  login char(20) DEFAULT NULL,
  mdp char(20) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO `comptable` VALUES 
('p000P','Jean','Dupond','j.dupond','okokok');

INSERT INTO `etat` (`id`, `libelle`) VALUES ('MP', 'Mise en paiement');
UPDATE `etat` SET `libelle` = 'Validée' WHERE `etat`.`id` = 'VA';
select * from comptable;



DROP TABLE IF EXISTS `fraiskm`;
CREATE TABLE IF NOT EXISTS `fraiskm` (
  `id` char(4) NOT NULL,
  `libelle` varchar(40) NOT NULL,
  `prix` decimal(3,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `fraiskm`
--

INSERT INTO `fraiskm` (`id`, `libelle`, `prix`) VALUES
('4D', 'véhicule 4CV diesel', '0.52'),
('4E', 'véhicule 4CV essence', '0.62'),
('5/6D', 'véhicule 5/6CV diesel ', '0.58'),
('5/6E', 'véhicule 5/6CV essence', '0.67');