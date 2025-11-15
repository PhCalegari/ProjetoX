-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 10-Nov-2025 às 01:27
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
-- Estrutura da tabela `alternativa_questao`
--

DROP TABLE IF EXISTS `alternativa_questao`;
CREATE TABLE IF NOT EXISTS `alternativa_questao` (
  `Id_AltQ` int(11) NOT NULL AUTO_INCREMENT,
  `Id_Quest` int(11) NOT NULL,
  `Tipo` enum('ME','VF','LACUNA','ASSOCIACAO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `Grupo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Texto` text COLLATE utf8mb4_unicode_ci,
  `Correta` tinyint(1) DEFAULT '0',
  `Extra` json DEFAULT NULL,
  PRIMARY KEY (`Id_AltQ`),
  KEY `fk_altquest_quest` (`Id_Quest`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `alternativa_questao`
--

INSERT INTO `alternativa_questao` (`Id_AltQ`, `Id_Quest`, `Tipo`, `Grupo`, `Texto`, `Correta`, `Extra`) VALUES
(37, 20, 'VF', 'A0', 'A ambiência de ma Unidade Basica de Saúde refere-se ao espaço físico social, protissional e de elações interpessoais, entendido como lugar acolhedora e que deve proporcionar uma atençao humana para as pessoas, além de um ambiente saudável para o trabalho dos profissionais de saúde.', 1, NULL),
(38, 20, 'VF', 'A1', 'Recomenda-se uma população adscrita por Equipe de Atenção Básica (eAB) e de Saúde da Família eSF) de 3.000 a 4.500 pessoas da Atencao Basica. ocalizada dentro do seu território, garantindo os princípios e diretrizes.', 1, NULL),
(39, 20, 'VF', 'A2', 'Hà a obrigatoriedade de carga horária de 40 (quarenta) horas semanais para todos os profissionais de saúde membros das Equipes da Atenção Básica (eAB),.', 0, NULL),
(40, 20, 'VF', 'A3', 'Em áreas de grande dispersão territorial, áreas de risco e vulnerabilidade social, recomenda-se a cobertura de 100% da população com numero máximo de 750 pessoas por ACS.', 0, NULL),
(41, 21, 'VF', 'A0', 'A ambiência de ma Unidade Basica de Saúde refere-se ao espaço físico social, protissional e de elações interpessoais, entendido como lugar acolhedora e que deve proporcionar uma atençao humana para as pessoas, além de um ambiente saudável para o trabalho dos profissionais de saúde.', 1, NULL),
(42, 21, 'VF', 'A1', 'Recomenda-se uma população adscrita por Equipe de Atenção Básica (eAB) e de Saúde da Família eSF) de 3.000 a 4.500 pessoas da Atencao Basica. ocalizada dentro do seu território, garantindo os princípios e diretrizes.', 1, NULL),
(43, 21, 'VF', 'A2', 'Hà a obrigatoriedade de carga horária de 40 (quarenta) horas semanais para todos os profissionais de saúde membros das Equipes da Atenção Básica (eAB),.', 0, NULL),
(44, 21, 'VF', 'A3', 'Em áreas de grande dispersão territorial, áreas de risco e vulnerabilidade social, recomenda-se a cobertura de 100% da população com numero máximo de 750 pessoas por ACS.', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `curso`
--

DROP TABLE IF EXISTS `curso`;
CREATE TABLE IF NOT EXISTS `curso` (
  `Id_Curso` int(11) NOT NULL AUTO_INCREMENT,
  `Nome_Curso` varchar(255) NOT NULL,
  `Sigla` varchar(10) NOT NULL,
  `Qtd_Periodos` int(11) NOT NULL DEFAULT '8',
  PRIMARY KEY (`Id_Curso`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `curso`
--

INSERT INTO `curso` (`Id_Curso`, `Nome_Curso`, `Sigla`, `Qtd_Periodos`) VALUES
(14, 'Graduação em Psicologia', 'GP', 8);

-- --------------------------------------------------------

--
-- Estrutura da tabela `materias`
--

DROP TABLE IF EXISTS `materias`;
CREATE TABLE IF NOT EXISTS `materias` (
  `Id_Materia` int(11) NOT NULL AUTO_INCREMENT,
  `Nome_Materia` varchar(255) NOT NULL,
  `Id_Curso` int(11) NOT NULL,
  `Id_Periodo` int(11) NOT NULL,
  PRIMARY KEY (`Id_Materia`),
  KEY `fk_materia_periodo` (`Id_Periodo`),
  KEY `fk_materia_curso` (`Id_Curso`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `materias`
--

INSERT INTO `materias` (`Id_Materia`, `Nome_Materia`, `Id_Curso`, `Id_Periodo`) VALUES
(4, 'Integrada', 14, 43);

-- --------------------------------------------------------

--
-- Estrutura da tabela `periodo`
--

DROP TABLE IF EXISTS `periodo`;
CREATE TABLE IF NOT EXISTS `periodo` (
  `Id_Periodo` int(11) NOT NULL AUTO_INCREMENT,
  `Id_Curso` int(11) NOT NULL,
  `NumeroPeriodo` int(11) NOT NULL,
  PRIMARY KEY (`Id_Periodo`),
  KEY `fk_periodo_curso` (`Id_Curso`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `periodo`
--

INSERT INTO `periodo` (`Id_Periodo`, `Id_Curso`, `NumeroPeriodo`) VALUES
(32, 11, 8),
(31, 11, 7),
(30, 11, 6),
(29, 11, 5),
(28, 11, 4),
(27, 11, 3),
(26, 11, 2),
(25, 11, 1),
(24, 10, 8),
(23, 10, 7),
(22, 10, 6),
(21, 10, 5),
(20, 10, 4),
(19, 10, 3),
(18, 10, 2),
(17, 10, 1),
(33, 12, 1),
(34, 13, 1),
(35, 13, 2),
(36, 13, 3),
(37, 13, 4),
(38, 13, 5),
(39, 13, 6),
(40, 13, 7),
(41, 13, 8),
(42, 14, 1),
(43, 14, 2);

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
  `Endereco` varchar(255) DEFAULT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Id_Prof`),
  UNIQUE KEY `CPF` (`CPF`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `professor`
--

INSERT INTO `professor` (`Id_Prof`, `Nome`, `CPF`, `Email`, `Senha`, `Data_Cadastro`, `Aprovado`, `IsAdmin`, `Endereco`, `Telefone`) VALUES
(4, 'pedro', '12345678901', 'calegarip0@gmail.com', '$2y$10$8mhI4SGvciIQKjAyeK65Su7E4aHHOgkF1Uo2IBF8KMHGbA8B8x6pS', '2025-10-24 23:15:19', 1, 1, '', ''),
(5, 'teste', '12345678912', 'test@gmail.com', '$2y$10$p2D0o3RjelrPbf4Ud1T39e5cKa0xuFKFiJhbo9j9nBAIpVAK2E74q', '2025-10-25 00:36:35', 1, 0, NULL, NULL),
(6, 'pedro', '12412432423', 'qweqw@djsaf.com', '$2y$10$VgUSqdYiUGKi8LZA/ei7WeU/UkX5Pd4RMcJozIpQlkHPznhK/bEOi', '2025-10-25 00:55:09', 1, 0, NULL, NULL),
(7, 'pedro', '11111111111', 'pedro@ddwd.com', '$2y$10$0gKKR2iCkM89/SUA4NDbduooM2Ul3Suf5Pxgah/hYMmDwKmKct3Ka', '2025-10-25 01:31:29', 1, 0, NULL, NULL);

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

--
-- Extraindo dados da tabela `professormateria`
--

INSERT INTO `professormateria` (`Id_Prof`, `Id_Mat`) VALUES
(4, 19);

-- --------------------------------------------------------

--
-- Estrutura da tabela `professor_materia_turma`
--

DROP TABLE IF EXISTS `professor_materia_turma`;
CREATE TABLE IF NOT EXISTS `professor_materia_turma` (
  `Id_PMT` int(11) NOT NULL AUTO_INCREMENT,
  `Id_Prof` int(11) NOT NULL,
  `Id_Materia` int(11) NOT NULL,
  `Id_Turma` int(11) NOT NULL,
  PRIMARY KEY (`Id_PMT`),
  KEY `fk_pmt_prof` (`Id_Prof`),
  KEY `fk_pmt_materia` (`Id_Materia`),
  KEY `fk_pmt_turma` (`Id_Turma`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `professor_materia_turma`
--

INSERT INTO `professor_materia_turma` (`Id_PMT`, `Id_Prof`, `Id_Materia`, `Id_Turma`) VALUES
(6, 4, 4, 5),
(7, 4, 5, 6);

-- --------------------------------------------------------

--
-- Estrutura da tabela `questao`
--

DROP TABLE IF EXISTS `questao`;
CREATE TABLE IF NOT EXISTS `questao` (
  `Id_Quest` int(11) NOT NULL AUTO_INCREMENT,
  `Enunciado` text NOT NULL,
  `Tipo_Questao` enum('ME','VF','LACUNA','ASSOCIACAO') NOT NULL,
  `Nivel_Dificuldade` enum('Fácil','Médio','Difícil') DEFAULT 'Médio',
  `Qtd_Lacunas` int(11) DEFAULT '0',
  `Id_Materia` int(11) NOT NULL,
  `Id_Prof` int(11) NOT NULL,
  `Data_Criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id_Quest`),
  KEY `fk_questao_materia` (`Id_Materia`),
  KEY `fk_questao_prof` (`Id_Prof`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `questao`
--

INSERT INTO `questao` (`Id_Quest`, `Enunciado`, `Tipo_Questao`, `Nivel_Dificuldade`, `Qtd_Lacunas`, `Id_Materia`, `Id_Prof`, `Data_Criacao`) VALUES
(20, 'Questão 02: Em relação a infraestrutura, ambiência e funcionamento da Atencão Básica, de acordo ANALISE as assertivas abaixo, ASSINALANDO\r\ncom a Política Nacional de Atencão Básica (2017),\r\nV, se verdadeiras, ou F, se falsas.', 'VF', 'Difícil', 0, 4, 4, '2025-11-10 01:17:56'),
(21, 'Questão 02: Em relação a infraestrutura, ambiência e funcionamento da Atencão Básica, de acordo ANALISE as assertivas abaixo, ASSINALANDO\r\ncom a Política Nacional de Atencão Básica (2017),\r\nV, se verdadeiras, ou F, se falsas.', 'VF', 'Difícil', 0, 4, 4, '2025-11-10 01:26:28');

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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Estrutura da tabela `turma`
--

DROP TABLE IF EXISTS `turma`;
CREATE TABLE IF NOT EXISTS `turma` (
  `Id_Turma` int(11) NOT NULL AUTO_INCREMENT,
  `Nome_Turma` varchar(45) NOT NULL,
  `Id_Curso` int(11) NOT NULL,
  `Id_Periodo` int(11) NOT NULL,
  `Id_Materia` int(11) DEFAULT NULL,
  `Id_Prof` int(11) DEFAULT NULL,
  `Turno` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Id_Turma`),
  KEY `fk_turma_curso` (`Id_Curso`),
  KEY `fk_turma_periodo` (`Id_Periodo`),
  KEY `fk_turma_materia` (`Id_Materia`),
  KEY `fk_turma_prof` (`Id_Prof`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `turma`
--

INSERT INTO `turma` (`Id_Turma`, `Nome_Turma`, `Id_Curso`, `Id_Periodo`, `Id_Materia`, `Id_Prof`, `Turno`) VALUES
(5, '1º Período - Turma 01', 13, 34, 4, 4, 'Vespertino'),
(6, '2º Período - Turma 01', 14, 43, 5, 4, 'Matutino');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `vw_questoes_com_alternativas`
-- (Veja abaixo para a view atual)
--
DROP VIEW IF EXISTS `vw_questoes_com_alternativas`;
CREATE TABLE IF NOT EXISTS `vw_questoes_com_alternativas` (
`Id_Quest` int(11)
,`Enunciado` text
,`Tipo_Questao` enum('ME','VF','LACUNA','ASSOCIACAO')
,`Nivel_Dificuldade` enum('Fácil','Médio','Difícil')
,`Id_AltQ` int(11)
,`Tipo_Alternativa` enum('ME','VF','LACUNA','ASSOCIACAO')
,`Grupo` varchar(10)
,`Texto` text
,`Correta` tinyint(1)
,`Extra` json
);

-- --------------------------------------------------------

--
-- Estrutura para vista `vw_questoes_com_alternativas`
--
DROP TABLE IF EXISTS `vw_questoes_com_alternativas`;

DROP VIEW IF EXISTS `vw_questoes_com_alternativas`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_questoes_com_alternativas`  AS SELECT `q`.`Id_Quest` AS `Id_Quest`, `q`.`Enunciado` AS `Enunciado`, `q`.`Tipo_Questao` AS `Tipo_Questao`, `q`.`Nivel_Dificuldade` AS `Nivel_Dificuldade`, `aq`.`Id_AltQ` AS `Id_AltQ`, `aq`.`Tipo` AS `Tipo_Alternativa`, `aq`.`Grupo` AS `Grupo`, `aq`.`Texto` AS `Texto`, `aq`.`Correta` AS `Correta`, `aq`.`Extra` AS `Extra` FROM (`questao` `q` left join `alternativa_questao` `aq` on((`q`.`Id_Quest` = `aq`.`Id_Quest`))) ORDER BY `q`.`Id_Quest` ASC, `aq`.`Id_AltQ` ASC  ;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alternativa_questao`
--
ALTER TABLE `alternativa_questao`
  ADD CONSTRAINT `fk_altquest_quest` FOREIGN KEY (`Id_Quest`) REFERENCES `questao` (`Id_Quest`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `questao`
--
ALTER TABLE `questao`
  ADD CONSTRAINT `fk_questao_materia` FOREIGN KEY (`Id_Materia`) REFERENCES `materias` (`Id_Materia`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_questao_prof` FOREIGN KEY (`Id_Prof`) REFERENCES `professor` (`Id_Prof`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
