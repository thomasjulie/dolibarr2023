-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 17 oct. 2023 à 16:21
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
-- Structure de la table `llx_creche_days_off`
--

CREATE TABLE `llx_creche_days_off` (
  `rowid` int NOT NULL,
  `entity` int NOT NULL,
  `day` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `llx_creche_days_off`
--

INSERT INTO `llx_creche_days_off` (`rowid`, `entity`, `day`) VALUES
(1, 0, '2023-10-31'),
(2, 0, '2023-11-01'),
(3, 0, '2023-12-25'),
(4, 0, '2023-12-26'),
(5, 0, '2023-12-27'),
(6, 0, '2023-12-28'),
(7, 0, '2023-12-29'),
(8, 0, '2024-01-01'),
(9, 0, '2024-04-01'),
(10, 0, '2024-05-01'),
(11, 0, '2024-05-02'),
(12, 0, '2024-05-03'),
(13, 0, '2024-05-08'),
(14, 0, '2024-05-09'),
(15, 0, '2024-05-10'),
(16, 0, '2024-05-20'),
(17, 0, '2024-08-05'),
(18, 0, '2024-08-06'),
(19, 0, '2024-08-07'),
(20, 0, '2024-08-08'),
(21, 0, '2024-08-09'),
(22, 0, '2024-08-12'),
(23, 0, '2024-08-13'),
(24, 0, '2024-08-14'),
(25, 0, '2024-08-15'),
(26, 0, '2024-08-16'),
(27, 0, '2024-08-19'),
(28, 0, '2024-08-20'),
(29, 0, '2024-08-21'),
(30, 0, '2024-08-22'),
(31, 0, '2024-08-23');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `llx_creche_days_off`
--
ALTER TABLE `llx_creche_days_off`
  ADD PRIMARY KEY (`rowid`),
  ADD KEY `idx_creche_days_off_entity` (`entity`) USING BTREE;

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `llx_creche_days_off`
--
ALTER TABLE `llx_creche_days_off`
  MODIFY `rowid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
