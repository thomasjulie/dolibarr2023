-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 22 nov. 2023 à 16:19
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
-- Structure de la table `llx_creche_infos_medicale`
--

CREATE TABLE `llx_creche_infos_medicale` (
  `fk_enfants` int NOT NULL,
  `poids` int DEFAULT NULL,
  `date_poids` date DEFAULT NULL,
  `medecin_traitant` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tel_medecin` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `allergies` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `llx_creche_infos_medicale`
--
ALTER TABLE `llx_creche_infos_medicale`
  ADD PRIMARY KEY (`fk_enfants`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
