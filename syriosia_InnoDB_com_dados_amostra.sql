-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 23-Out-2025 às 16:12
-- Versão do servidor: 10.6.11-MariaDB
-- versão do PHP: 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `syrios`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_aluno`
--

DROP TABLE IF EXISTS `syrios_aluno`;
CREATE TABLE IF NOT EXISTS `syrios_aluno` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `matricula` varchar(10) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `nome_a` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_aluno_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=565 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_aluno`
--

INSERT INTO `syrios_aluno` (`id`, `matricula`, `school_id`, `nome_a`, `created_at`, `updated_at`) VALUES
(7, '2229287', 5, 'ANTONIO CARLOS NASCIMENTO EUFRASIO', '2025-10-15 01:26:41', NULL),
(8, '3730158', 5, 'ANTONIO DANIEL VIANA BATISTA NETO', '2025-10-15 01:26:41', NULL),
(9, '2242118', 5, 'ANTONIO GABRIEL ARAUJO DE FREITAS', '2025-10-15 01:26:41', NULL),
(10, '3610065', 5, 'ANTONIO IGOR DA SILVA XAVIER', '2025-10-15 01:26:41', NULL),
(11, '2926910', 5, 'ANTONIO LEVI DA SILVA PEREIRA', '2025-10-15 01:26:41', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_diretor_turma`
--

DROP TABLE IF EXISTS `syrios_diretor_turma`;
CREATE TABLE IF NOT EXISTS `syrios_diretor_turma` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `professor_id` bigint(20) UNSIGNED NOT NULL,
  `turma_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `ano_letivo` int(11) NOT NULL DEFAULT 2025,
  `vigente` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_diretor_turma` (`professor_id`,`turma_id`),
  KEY `syrios_diretor_turma_turma_id_foreign` (`turma_id`),
  KEY `syrios_diretor_turma_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_diretor_turma`
--

INSERT INTO `syrios_diretor_turma` (`id`, `professor_id`, `turma_id`, `school_id`, `ano_letivo`, `vigente`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, 2025, 1, '2025-10-21 13:53:32', '2025-10-21 13:53:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_disciplina`
--

DROP TABLE IF EXISTS `syrios_disciplina`;
CREATE TABLE IF NOT EXISTS `syrios_disciplina` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `abr` varchar(10) NOT NULL,
  `descr_d` varchar(100) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_disciplina_escola_abr` (`school_id`,`abr`),
  KEY `syrios_disciplina_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_disciplina`
--

INSERT INTO `syrios_disciplina` (`id`, `abr`, `descr_d`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 'AMA', 'APROFUNDAMENTO EM MATEMATICA', 5, '2025-10-15 01:41:59', '2025-10-15 01:41:59'),
(2, 'APO', 'APROFUNDAMENTO EM LINGUA PORTUGUESA', 5, '2025-10-15 01:41:59', '2025-10-15 01:41:59'),
(3, 'ART', 'ARTE', 5, '2025-10-15 01:41:59', '2025-10-15 01:41:59'),
(4, 'BIO', 'BIOLOGIA', 5, '2025-10-15 01:41:59', '2025-10-15 01:41:59'),
(5, 'CID', 'FORMAÇÃO PARA CIDADANIA E DESENV. DE COMP. SOCIOEM...', 5, '2025-10-15 01:41:59', '2025-10-15 01:41:59');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_enturmacao`
--

DROP TABLE IF EXISTS `syrios_enturmacao`;
CREATE TABLE IF NOT EXISTS `syrios_enturmacao` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `ano_letivo` int(11) NOT NULL DEFAULT 2025,
  `vigente` tinyint(1) NOT NULL DEFAULT 1,
  `aluno_id` bigint(20) UNSIGNED NOT NULL,
  `turma_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enturmacao_unica` (`aluno_id`,`turma_id`,`ano_letivo`),
  KEY `syrios_enturmacao_aluno_id_foreign` (`aluno_id`),
  KEY `syrios_enturmacao_turma_id_foreign` (`turma_id`),
  KEY `syrios_enturmacao_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=547 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_enturmacao`
--

INSERT INTO `syrios_enturmacao` (`id`, `school_id`, `ano_letivo`, `vigente`, `aluno_id`, `turma_id`, `created_at`, `updated_at`) VALUES
(7, 5, 2025, 1, 7, 4, '2025-10-15 02:53:42', '2025-10-15 02:53:42'),
(8, 5, 2025, 1, 8, 1, '2025-10-15 02:53:42', '2025-10-15 02:53:42'),
(9, 5, 2025, 1, 9, 1, '2025-10-15 02:53:42', '2025-10-15 02:53:42'),
(10, 5, 2025, 1, 10, 1, '2025-10-15 02:53:42', '2025-10-15 02:53:42'),
(11, 5, 2025, 1, 11, 1, '2025-10-15 02:53:42', '2025-10-15 02:53:42'),
(12, 5, 2025, 1, 12, 1, '2025-10-15 02:53:42', '2025-10-15 02:53:42');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_escola`
--

DROP TABLE IF EXISTS `syrios_escola`;
CREATE TABLE IF NOT EXISTS `syrios_escola` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inep` varchar(20) DEFAULT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `nome_e` varchar(150) NOT NULL,
  `frase_efeito` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `secretaria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_master` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `syrios_escola_inep_unique` (`inep`),
  UNIQUE KEY `syrios_escola_cnpj_unique` (`cnpj`),
  KEY `syrios_escola_secretaria_id_foreign` (`secretaria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_escola`
--

INSERT INTO `syrios_escola` (`id`, `inep`, `cnpj`, `nome_e`, `frase_efeito`, `logo_path`, `cidade`, `estado`, `endereco`, `telefone`, `secretaria_id`, `is_master`, `created_at`, `updated_at`) VALUES
(1, '00000001', NULL, 'Secretaria do Administrador Master', 'Trasnparência e Objetividade', 'logos/syrios.png', 'Capital', 'CE', NULL, NULL, NULL, 1, '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(4, NULL, NULL, 'SEDUC - SECRETARIA DA EDUCAÇÃO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-10-15 03:07:19', '2025-10-15 03:07:19'),
(5, NULL, NULL, 'EEMTI Dep. Ubiratan Diniz de Aguiar', 'Construindo Conhecimentos, Fortalecendo Valores', 'logos/ubiratan.png', NULL, NULL, NULL, NULL, 4, 0, '2025-10-15 03:08:30', '2025-10-15 03:08:30'),
(6, NULL, NULL, 'EEFTI Fernando Cavalcante Mota', 'Frase de Efeito Fernando Mota', 'logos/fmota.png', NULL, NULL, NULL, NULL, 4, 0, '2025-10-15 15:35:40', '2025-10-15 15:36:39');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_modelo_motivo`
--

DROP TABLE IF EXISTS `syrios_modelo_motivo`;
CREATE TABLE IF NOT EXISTS `syrios_modelo_motivo` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_modelomotivo_escola` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_modelo_motivo`
--

INSERT INTO `syrios_modelo_motivo` (`id`, `school_id`, `descricao`, `categoria`, `created_at`, `updated_at`) VALUES
(1, 5, 'Uso de celular em sala', 'Comportamento', '2025-10-20 10:40:32', '2025-10-20 10:40:32'),
(2, 5, 'Desrespeito ao professor', 'Disciplina', '2025-10-20 10:40:32', '2025-10-20 10:40:32'),
(3, 5, 'Atraso frequente', 'Pontualidade', '2025-10-20 10:40:32', '2025-10-20 10:40:32'),
(4, 5, 'Conversas paralelas durante a exposição do conteúdo', 'Comportamento', '2025-10-20 10:40:32', '2025-10-22 10:36:56'),
(5, 5, 'Agressão verbal ou física', 'Grave', '2025-10-20 10:40:32', '2025-10-20 10:40:32'),
(6, 5, 'Fuga do ambiente escolar', 'Grave', '2025-10-20 10:40:32', '2025-10-20 10:40:32'),
(7, 5, 'Brincadeiras durante a exposição do conteúdo', 'Comportamento', '2025-10-22 10:37:42', '2025-10-22 10:37:42');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_notificacao`
--

DROP TABLE IF EXISTS `syrios_notificacao`;
CREATE TABLE IF NOT EXISTS `syrios_notificacao` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `reg_id` varchar(200) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_notificacao_usuario_id_foreign` (`usuario_id`),
  KEY `syrios_notificacao_school_id_foreign` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_ocorrencia`
--

DROP TABLE IF EXISTS `syrios_ocorrencia`;
CREATE TABLE IF NOT EXISTS `syrios_ocorrencia` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `ano_letivo` int(11) NOT NULL DEFAULT 2025,
  `vigente` tinyint(1) NOT NULL DEFAULT 1,
  `aluno_id` bigint(20) UNSIGNED NOT NULL,
  `professor_id` bigint(20) UNSIGNED NOT NULL,
  `oferta_id` bigint(20) UNSIGNED DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `local` varchar(100) DEFAULT NULL,
  `atitude` varchar(100) DEFAULT NULL,
  `outra_atitude` varchar(150) DEFAULT NULL,
  `comportamento` varchar(100) DEFAULT NULL,
  `sugestao` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `nivel_gravidade` tinyint(4) NOT NULL DEFAULT 1,
  `sync` tinyint(4) NOT NULL DEFAULT 1,
  `recebido_em` timestamp NULL DEFAULT NULL,
  `encaminhamentos` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_ocorrencia_school_id_aluno_id_index` (`school_id`,`aluno_id`),
  KEY `syrios_ocorrencia_professor_id_oferta_id_index` (`professor_id`,`oferta_id`),
  KEY `fk_ocorrencia_aluno` (`aluno_id`),
  KEY `fk_ocorrencia_oferta` (`oferta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_ocorrencia`
--

INSERT INTO `syrios_ocorrencia` (`id`, `school_id`, `ano_letivo`, `vigente`, `aluno_id`, `professor_id`, `oferta_id`, `descricao`, `local`, `atitude`, `outra_atitude`, `comportamento`, `sugestao`, `status`, `nivel_gravidade`, `sync`, `recebido_em`, `encaminhamentos`, `created_at`, `updated_at`) VALUES
(1, 5, 2025, 1, 397, 1, 28, NULL, 'Sala de aula', 'Advertência', NULL, '1ª vez', NULL, 1, 1, 1, NULL, NULL, '2025-10-20 10:42:48', '2025-10-20 10:42:48'),
(2, 5, 2025, 1, 485, 1, 28, NULL, 'Sala de aula', 'Advertência', NULL, '1ª vez', NULL, 1, 1, 1, NULL, NULL, '2025-10-20 10:42:48', '2025-10-20 10:42:48'),
(3, 5, 2025, 1, 397, 1, 28, NULL, 'Sala de aula', 'Advertência', NULL, '1ª vez', NULL, 1, 1, 1, NULL, NULL, '2025-10-20 14:29:04', '2025-10-20 14:29:04'),
(4, 5, 2025, 1, 485, 1, 28, NULL, 'Sala de aula', 'Advertência', NULL, '1ª vez', NULL, 1, 1, 1, NULL, NULL, '2025-10-20 14:29:04', '2025-10-20 14:29:04'),
(5, 5, 2025, 1, 486, 1, 28, NULL, 'Sala de aula', 'Advertência', NULL, '1ª vez', NULL, 1, 1, 1, NULL, NULL, '2025-10-20 14:29:04', '2025-10-20 14:29:04');
-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_ocorrencia_motivo`
--

DROP TABLE IF EXISTS `syrios_ocorrencia_motivo`;
CREATE TABLE IF NOT EXISTS `syrios_ocorrencia_motivo` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `modelo_motivo_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ocmotivo_ocorrencia` (`ocorrencia_id`),
  KEY `fk_ocmotivo_modelo` (`modelo_motivo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_ocorrencia_motivo`
--

INSERT INTO `syrios_ocorrencia_motivo` (`id`, `ocorrencia_id`, `modelo_motivo_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-10-20 10:42:48', '2025-10-20 10:42:48'),
(2, 1, 4, '2025-10-20 10:42:48', '2025-10-20 10:42:48'),
(3, 1, 2, '2025-10-20 10:42:48', '2025-10-20 10:42:48'),
(4, 1, 5, '2025-10-20 10:42:48', '2025-10-20 10:42:48'),
(5, 1, 6, '2025-10-20 10:42:48', '2025-10-20 10:42:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_oferta`
--

DROP TABLE IF EXISTS `syrios_oferta`;
CREATE TABLE IF NOT EXISTS `syrios_oferta` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `ano_letivo` int(11) NOT NULL DEFAULT 2025,
  `vigente` tinyint(1) NOT NULL DEFAULT 1,
  `turma_id` bigint(20) UNSIGNED NOT NULL,
  `disciplina_id` bigint(20) UNSIGNED NOT NULL,
  `professor_id` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_oferta_school_id_foreign` (`school_id`),
  KEY `syrios_oferta_turma_id_foreign` (`turma_id`),
  KEY `syrios_oferta_disciplina_id_foreign` (`disciplina_id`),
  KEY `syrios_oferta_professor_id_foreign` (`professor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=381 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_oferta`
--

INSERT INTO `syrios_oferta` (`id`, `school_id`, `ano_letivo`, `vigente`, `turma_id`, `disciplina_id`, `professor_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 2025, 1, 1, 42, 1, 1, '2025-10-15 02:38:43', '2025-10-15 02:38:43'),
(2, 5, 2025, 1, 2, 42, 1, 1, '2025-10-15 02:38:43', '2025-10-15 02:38:43'),
(3, 5, 2025, 1, 3, 42, 1, 1, '2025-10-15 02:38:43', '2025-10-15 02:38:43'),
(4, 5, 2025, 1, 4, 42, 1, 1, '2025-10-15 02:38:43', '2025-10-15 02:38:43'),
(5, 5, 2025, 1, 6, 42, 1, 1, '2025-10-15 02:38:43', '2025-10-15 02:38:43');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_professor`
--

DROP TABLE IF EXISTS `syrios_professor`;
CREATE TABLE IF NOT EXISTS `syrios_professor` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_professor_usuario_id_foreign` (`usuario_id`),
  KEY `syrios_professor_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_professor`
--

INSERT INTO `syrios_professor` (`id`, `usuario_id`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 1, 5, '2025-10-15 01:33:08', '2025-10-15 01:33:08'),
(2, 24, 5, '2025-10-15 01:33:08', '2025-10-15 01:33:08'),
(3, 27, 5, '2025-10-15 01:33:08', '2025-10-15 01:33:08'),
(4, 28, 5, '2025-10-15 01:33:08', '2025-10-15 01:33:08'),
(5, 47, 5, '2025-10-15 01:33:08', '2025-10-15 01:33:08');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_regimento`
--

DROP TABLE IF EXISTS `syrios_regimento`;
CREATE TABLE IF NOT EXISTS `syrios_regimento` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(191) NOT NULL DEFAULT 'Regimento Escolar',
  `arquivo` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_regimento_school_id_index` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_regimento`
--

INSERT INTO `syrios_regimento` (`id`, `school_id`, `titulo`, `arquivo`, `created_at`, `updated_at`) VALUES
(1, 5, 'Regimento Escolar - 2025', 'regimentos/ICJW7Y1NcyENWO09vkJBlsrJmkC2ZWiCEsd4ZGcS.pdf', '2025-10-21 23:26:21', '2025-10-21 23:26:21');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_regstatus`
--

DROP TABLE IF EXISTS `syrios_regstatus`;
CREATE TABLE IF NOT EXISTS `syrios_regstatus` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `descr_s` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_role`
--

DROP TABLE IF EXISTS `syrios_role`;
CREATE TABLE IF NOT EXISTS `syrios_role` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `syrios_role_role_name_unique` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_role`
--

INSERT INTO `syrios_role` (`id`, `role_name`, `created_at`, `updated_at`) VALUES
(1, 'master', '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(2, 'secretaria', '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(3, 'escola', '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(4, 'professor', '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(5, 'admin', '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(6, 'pais', '2025-10-13 06:03:10', '2025-10-13 06:03:10'),
(7, 'gestor', '2025-10-13 06:03:10', '2025-10-13 06:03:10');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_sessao`
--

DROP TABLE IF EXISTS `syrios_sessao`;
CREATE TABLE IF NOT EXISTS `syrios_sessao` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_sessao_usuario_id_foreign` (`usuario_id`),
  KEY `syrios_sessao_school_id_foreign` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_turma`
--

DROP TABLE IF EXISTS `syrios_turma`;
CREATE TABLE IF NOT EXISTS `syrios_turma` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `serie_turma` varchar(20) NOT NULL,
  `turno` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_turma_identificacao` (`school_id`,`serie_turma`,`turno`),
  KEY `syrios_turma_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_turma`
--

INSERT INTO `syrios_turma` (`id`, `school_id`, `serie_turma`, `turno`, `created_at`, `updated_at`) VALUES
(1, 5, '1A', 'Integral', '2025-10-15 01:52:36', '2025-10-15 01:52:36'),
(2, 5, '1B', 'Integral', '2025-10-15 01:52:36', '2025-10-15 01:52:36'),
(3, 5, '1C', 'Integral', '2025-10-15 01:52:36', '2025-10-15 01:52:36'),
(4, 5, '1D', 'Integral', '2025-10-15 01:52:36', '2025-10-15 01:52:36'),
(5, 5, '1N', 'Noite', '2025-10-15 01:52:36', '2025-10-15 01:52:36');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_usuario`
--

DROP TABLE IF EXISTS `syrios_usuario`;
CREATE TABLE IF NOT EXISTS `syrios_usuario` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `nome_u` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_super_master` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuario_cpf_escola` (`cpf`,`school_id`),
  KEY `syrios_usuario_school_id_foreign` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_usuario`
--

INSERT INTO `syrios_usuario` (`id`, `school_id`, `cpf`, `senha_hash`, `nome_u`, `status`, `is_super_master`, `created_at`, `updated_at`) VALUES
(1, 1, 'master', '$2a$12$6dgE5prY.fbzUu2j7CgfhuucnkILGXPmGZznHOlIpwp.PCtv/11Je', 'DAVID DOS SANTOS DA COSTA', 1, 1, '2025-10-15 01:18:46', NULL),
(2, 5, '00rozangela', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ROZANGELA LEAL PIRES', 1, 0, '2025-10-15 01:18:46', NULL),
(3, 5, '000000marta', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MARTA DE LIMA BRILHANTE', 1, 0, '2025-10-15 01:18:46', NULL),
(4, 5, '00000cleane', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CLEANE DE AGUIAR DE SOUSA', 0, 0, '2025-10-15 01:18:46', NULL),
(5, 5, '0000eutalia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EUTALIA PINHEIRO GOMES', 1, 0, '2025-10-15 01:18:46', NULL);
-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_usuario_role`
--

DROP TABLE IF EXISTS `syrios_usuario_role`;
CREATE TABLE IF NOT EXISTS `syrios_usuario_role` (
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usuario_id`,`role_id`,`school_id`),
  KEY `syrios_usuario_role_role_id_foreign` (`role_id`),
  KEY `syrios_usuario_role_school_id_foreign` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `syrios_usuario_role`
--

INSERT INTO `syrios_usuario_role` (`usuario_id`, `role_id`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-10-15 01:40:02', '2025-10-15 01:40:02'),
(1, 2, 4, '2025-10-15 12:05:18', '2025-10-15 12:05:18'),
(1, 3, 5, '2025-10-15 03:13:01', '2025-10-15 03:13:01'),
(1, 3, 6, '2025-10-15 15:37:49', '2025-10-15 15:37:49'),
(1, 4, 5, '2025-10-15 03:03:38', '2025-10-15 03:03:38'),
(1, 4, 6, '2025-10-20 22:16:32', '2025-10-20 22:16:32'),
(2, 4, 5, '2025-10-15 01:40:02', '2025-10-15 01:40:02'),
(3, 4, 5, '2025-10-15 01:40:02', '2025-10-15 01:40:02');

-- --------------------------------------------------------

--
-- Estrutura da tabela `syrios_visao_aluno`
--

DROP TABLE IF EXISTS `syrios_visao_aluno`;
CREATE TABLE IF NOT EXISTS `syrios_visao_aluno` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `aluno_id` bigint(20) UNSIGNED NOT NULL,
  `dat_ult_visao` datetime DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `syrios_visao_aluno_aluno_id_foreign` (`aluno_id`),
  KEY `syrios_visao_aluno_school_id_foreign` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `syrios_aluno`
--
ALTER TABLE `syrios_aluno`
  ADD CONSTRAINT `fk_aluno_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_diretor_turma`
--
ALTER TABLE `syrios_diretor_turma`
  ADD CONSTRAINT `fk_diretor_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_diretor_professor` FOREIGN KEY (`professor_id`) REFERENCES `syrios_professor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_diretor_turma` FOREIGN KEY (`turma_id`) REFERENCES `syrios_turma` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_disciplina`
--
ALTER TABLE `syrios_disciplina`
  ADD CONSTRAINT `fk_disciplina_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_enturmacao`
--
ALTER TABLE `syrios_enturmacao`
  ADD CONSTRAINT `fk_enturmacao_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `syrios_aluno` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enturmacao_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enturmacao_turma` FOREIGN KEY (`turma_id`) REFERENCES `syrios_turma` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_escola`
--
ALTER TABLE `syrios_escola`
  ADD CONSTRAINT `fk_escola_secretaria` FOREIGN KEY (`secretaria_id`) REFERENCES `syrios_escola` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_modelo_motivo`
--
ALTER TABLE `syrios_modelo_motivo`
  ADD CONSTRAINT `fk_modelomotivo_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_notificacao`
--
ALTER TABLE `syrios_notificacao`
  ADD CONSTRAINT `fk_notificacao_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `syrios_usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_ocorrencia`
--
ALTER TABLE `syrios_ocorrencia`
  ADD CONSTRAINT `fk_ocorrencia_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `syrios_aluno` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_oferta` FOREIGN KEY (`oferta_id`) REFERENCES `syrios_oferta` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_professor` FOREIGN KEY (`professor_id`) REFERENCES `syrios_professor` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_ocorrencia_motivo`
--
ALTER TABLE `syrios_ocorrencia_motivo`
  ADD CONSTRAINT `fk_ocmotivo_modelo` FOREIGN KEY (`modelo_motivo_id`) REFERENCES `syrios_modelo_motivo` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocmotivo_ocorrencia` FOREIGN KEY (`ocorrencia_id`) REFERENCES `syrios_ocorrencia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_oferta`
--
ALTER TABLE `syrios_oferta`
  ADD CONSTRAINT `fk_oferta_disciplina` FOREIGN KEY (`disciplina_id`) REFERENCES `syrios_disciplina` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oferta_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oferta_professor` FOREIGN KEY (`professor_id`) REFERENCES `syrios_professor` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oferta_turma` FOREIGN KEY (`turma_id`) REFERENCES `syrios_turma` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_professor`
--
ALTER TABLE `syrios_professor`
  ADD CONSTRAINT `fk_professor_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_professor_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `syrios_usuario` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_regimento`
--
ALTER TABLE `syrios_regimento`
  ADD CONSTRAINT `fk_regimento_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_sessao`
--
ALTER TABLE `syrios_sessao`
  ADD CONSTRAINT `fk_sessao_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sessao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `syrios_usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_turma`
--
ALTER TABLE `syrios_turma`
  ADD CONSTRAINT `fk_turma_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_usuario`
--
ALTER TABLE `syrios_usuario`
  ADD CONSTRAINT `fk_usuario_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_usuario_role`
--
ALTER TABLE `syrios_usuario_role`
  ADD CONSTRAINT `fk_urole_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_urole_role` FOREIGN KEY (`role_id`) REFERENCES `syrios_role` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_urole_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `syrios_usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `syrios_visao_aluno`
--
ALTER TABLE `syrios_visao_aluno`
  ADD CONSTRAINT `fk_visao_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `syrios_aluno` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_visao_escola` FOREIGN KEY (`school_id`) REFERENCES `syrios_escola` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
