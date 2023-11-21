-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 14 nov. 2023 à 09:22
-- Version du serveur : 8.0.33
-- Version de PHP : 7.3.31-1~deb10u5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dolibarrdebian`
--

-- --------------------------------------------------------

--
-- Structure de la table `llx_creche_tx_effort`
--

CREATE TABLE `llx_creche_tx_effort` (
  `rowid` int NOT NULL,
  `nb_enfants` int NOT NULL,
  `taux` double(24,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `llx_creche_tx_effort`
--

INSERT INTO `llx_creche_tx_effort` (`rowid`, `nb_enfants`, `taux`) VALUES
(1, 1, 0.06190000),
(2, 2, 0.05160000),
(3, 3, 0.04190000),
(4, 4, 0.03100000),
(5, 5, 0.03100000),
(6, 6, 0.03100000),
(7, 7, 0.03100000);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `llx_creche_tx_effort`
--
ALTER TABLE `llx_creche_tx_effort`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `llx_creche_tx_effort`
--
ALTER TABLE `llx_creche_tx_effort`
  MODIFY `rowid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
