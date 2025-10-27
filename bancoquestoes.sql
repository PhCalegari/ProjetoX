-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 26-Out-2025 às 01:26
-- Versão do servidor: 5.7.40
-- versão do PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bancoquestoes`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `alternativa`
--

DROP TABLE IF EXISTS `alternativa`;
CREATE TABLE IF NOT EXISTS `alternativa` (
  `Id_Alt` int(11) NOT NULL AUTO_INCREMENT,
  `Id_Quest` int(11) NOT NULL,
  `Texto_Alternativa` varchar(255) NOT NULL,
  `Resp_Cor_Alt` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`Id_Alt`),
  KEY `Id_Quest` (`Id_Quest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `associacao`
--

DROP TABLE IF EXISTS `associacao`;
CREATE TABLE IF NOT EXISTS `associacao` (
  `Id_Ass` int(11) NOT NULL AUTO_INCREMENT,
  `Id_Quest` int(11) NOT NULL,
  `Item_Ori` varchar(255) NOT NULL,
  `Item_Dest` varchar(255) NOT NULL,
  `Num_Ori` int(11) DEFAULT NULL,
  `Num_Dest` int(11) DEFAULT NULL,
  PRIMARY KEY (`Id_Ass`),
  KEY `Id_Quest` (`Id_Quest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lacuna`
--

DROP TABLE IF EXISTS `lacuna`;
CREATE TABLE IF NOT EXISTS `lacuna` (
  `Id_Lac` int(11) NOT NULL AUTO_INCREMENT,
  `Id_Quest` int(11) NOT NULL,
  `Posicao` int(11) NOT NULL,
  `Num_Lac` int(11) DEFAULT NULL,
  `Resp_Cor_Lac` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Id_Lac`),
  KEY `Id_Quest` (`Id_Quest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `materias`
--

DROP TABLE IF EXISTS `materias`;
CREATE TABLE IF NOT EXISTS `materias` (
  `Id_Mat` int(11) NOT NULL AUTO_INCREMENT,
  `Nome_Materia` varchar(100) NOT NULL,
  PRIMARY KEY (`Id_Mat`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `professor`
--

DROP TABLE IF EXISTS `professor`;
CREATE TABLE IF NOT EXISTS `professor` (
  `Id_Prof` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) NOT NULL,
  `CPF` char(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Senha` varchar(255) NOT NULL,
  `Data_Cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `Aprovado` tinyint(1) DEFAULT '0',
  `IsAdmin` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`Id_Prof`),
  UNIQUE KEY `CPF` (`CPF`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `professor`
--

INSERT INTO `professor` (`Id_Prof`, `Nome`, `CPF`, `Email`, `Senha`, `Data_Cadastro`, `Aprovado`, `IsAdmin`) VALUES
(4, 'pedro', '12345678901', 'calegarip0@gmail.com', '$2y$10$VaEcfcDck5BVqnMi4lGKSO8BxfPVwtZRNyVIdZMoBaPsh4u9lwQL6', '2025-10-24 23:15:19', 1, 1),
(5, 'teste', '12345678912', 'test@gmail.com', '$2y$10$p2D0o3RjelrPbf4Ud1T39e5cKa0xuFKFiJhbo9j9nBAIpVAK2E74q', '2025-10-25 00:36:35', 1, 0),
(6, 'pedro', '12412432423', 'qweqw@djsaf.com', '$2y$10$VgUSqdYiUGKi8LZA/ei7WeU/UkX5Pd4RMcJozIpQlkHPznhK/bEOi', '2025-10-25 00:55:09', 2, 0),
(7, 'pedro', '11111111111', 'pedro@ddwd.com', '$2y$10$0gKKR2iCkM89/SUA4NDbduooM2Ul3Suf5Pxgah/hYMmDwKmKct3Ka', '2025-10-25 01:31:29', 2, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `professormateria`
--

DROP TABLE IF EXISTS `professormateria`;
CREATE TABLE IF NOT EXISTS `professormateria` (
  `Id_Prof` int(11) NOT NULL,
  `Id_Mat` int(11) NOT NULL,
  PRIMARY KEY (`Id_Prof`,`Id_Mat`),
  KEY `Id_Mat` (`Id_Mat`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `questao`
--

DROP TABLE IF EXISTS `questao`;
CREATE TABLE IF NOT EXISTS `questao` (
  `Id_Quest` int(11) NOT NULL AUTO_INCREMENT,
  `Enunciado` text NOT NULL,
  `Tipo_Questao` varchar(50) NOT NULL,
  `Nivel_Dificuldade` varchar(30) DEFAULT NULL,
  `Qtd_Lacunas` int(11) DEFAULT '0',
  `Id_Mat` int(11) NOT NULL,
  `Id_Prof` int(11) NOT NULL,
  PRIMARY KEY (`Id_Quest`),
  KEY `Id_Mat` (`Id_Mat`),
  KEY `Id_Prof` (`Id_Prof`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `recuperacaosenha`
--

DROP TABLE IF EXISTS `recuperacaosenha`;
CREATE TABLE IF NOT EXISTS `recuperacaosenha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiracao` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `recuperacaosenha`
--

INSERT INTO `recuperacaosenha` (`id`, `email`, `token`, `expiracao`) VALUES
(1, 'cleis.calegari@gmail.com', '4464022655186a36809000a833064579', '2025-10-25 03:12:37'),
(2, 'calegarip0@gmail.com', 'edbef5d7f217251b378e3177af4f0a74', '2025-10-25 03:15:47'),
(3, 'calegarip0@gmail.com', 'a74a0feb36c143c191be0a01d1615932', '2025-10-25 03:26:38'),
(5, 'calegarip0@gmail.com', '6b89ff03c3c554f3bab17ba20f9cf82e', '2025-10-25 03:32:15'),
(10, 'cleis.calegari@gmail.com', 'a59c356f7cc94f1c3da41d097bb50cc1', '2025-10-25 05:27:05'),
(7, 'calegarip0@gmail.com', '0f64cc6a73a4382c7f971711bb74d9cf', '2025-10-25 04:56:29'),
(8, 'calegarip0@gmail.com', '4e62e94045f727b71994806f586853dc', '2025-10-25 04:56:31'),
(9, 'calegarip0@gmail.com', '4c4cfd0ad78e5b36f520ecbe11d1b0d1', '2025-10-25 04:56:53'),
(11, 'test@gmail.com', '64272e9075929be4ab4f912b9cfac643', '2025-10-25 05:27:25'),
(12, 'calegarip0@gmail.com', 'dec3ab9bc066e86abbba1b066a40ad75', '2025-10-25 05:29:43'),
(13, 'luana.wggg@gmail.com', '68614be5d71c402dcf2995b4f0b4ce90', '2025-10-26 02:22:33');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
