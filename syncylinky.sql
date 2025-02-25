-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 26 fév. 2025 à 00:56
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `syncylinky`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnements`
--

CREATE TABLE `abonnements` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prix` double NOT NULL,
  `avantages` longtext NOT NULL COMMENT '(DC2Type:array)',
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `abonnements`
--

INSERT INTO `abonnements` (`id`, `nom`, `prix`, `avantages`, `type`) VALUES
(1, 'Free', 0, 'a:2:{i:0;s:16:\"Evenement public\";i:1;s:11:\"Communautes\";}', 'Normal'),
(2, 'Premium', 10, 'a:3:{i:0;s:16:\"Evenement privee\";i:1;s:6:\"No Ads\";i:2;s:23:\"Systeme de gamification\";}', 'Premium');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `cover` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`, `cover`, `date_creation`) VALUES
(1, 'Sports', 'Le sport est une activité physique qui implique généralement des compétitions, des règles spécifiques et un esprit d\'équipe ou individuel. Il vise à améliorer la condition physique, la coordination et la santé tout en favorisant le dépassement de soi. Les sports peuvent être pratiqués à différents niveaux, du loisir au professionnel, et incluent des disciplines variées comme le football, le basketball, la natation, le tennis, etc.', 'https://img.freepik.com/photos-gratuite/outils-sport_53876-138077.jpg', '2025-02-02 19:59:12'),
(2, 'Dance', 'La danse est un art du mouvement qui exprime des émotions, des idées ou des histoires à travers des gestes et des enchaînements corporels, souvent synchronisés avec de la musique. Elle peut être pratiquée comme une discipline artistique, un loisir ou un rituel culturel. Il existe de nombreux styles de danse, tels que la danse classique (ballet), la danse contemporaine, le hip-hop, la salsa, le tango ou encore les danses traditionnelles. La danse allie créativité, expression personnelle et technique, tout en étant une forme de communication universelle.', 'https://www.kennedy-center.org/globalassets/whats-on/genre/dance/2024-2025/alvin-ailey/2425_dance_eventimage_alvinailey2.jpg?width=768&quality=70', '2025-02-02 19:59:37'),
(3, 'Musique', 'La musique est un art qui consiste à organiser sons et silences pour créer des mélodies, des rythmes et des harmonies. Elle peut être vocale, instrumentale ou électronique, et se décline en une multitude de genres (classique, pop, rock, jazz, hip-hop, etc.). La musique joue un rôle culturel, émotionnel et social, permettant d\'exprimer des émotions, de raconter des histoires ou de rassembler des communautés. Elle est universelle et traverse les époques et les cultures.', 'https://www.apprentus.fr/blog/wp-content/uploads/sites/4/2022/01/shutterstock_681809980.jpg', '2025-02-02 20:00:10'),
(13, 'Farm', 'farmers', '/uploads/1200x680-paysan-67a8e7da11150.webp', '2025-02-09 18:37:22'),
(17, 'Travel', 'Traveling', '/uploads/full-shot-travel-concept-with-landmarks-scaled-67ae911342638.jpg', '2025-02-14 01:40:51'),
(18, 'Theatre', 'art', '/uploads/theatre-structure-1-67ae913ac1521.webp', '2025-02-14 01:41:30');

-- --------------------------------------------------------

--
-- Structure de la table `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `community_id`, `nom`, `cover`, `type`, `created_at`) VALUES
(1, 1, 'FInd Partner', '/uploads/Capture-d-ecran-2023-12-27-002902-67aa5599a537e.png', 'Public', '2025-02-10 20:38:01'),
(3, 3, 'Unreleased', '/uploads/ddd-67aeb86dee348.jpg', 'Public', '2025-02-14 04:28:45'),
(6, 14, 'Lady\'s Only', '/uploads/360-F-358288750-W5ObJ9CvKAsjNUv4OlxhMa6QuPUWed20-67bceeac2fdc0.jpg', 'Private', '2025-02-24 23:11:56');

-- --------------------------------------------------------

--
-- Structure de la table `comment`
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `community`
--

CREATE TABLE `community` (
  `id` int(11) NOT NULL,
  `id_categorie_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `nbr_membre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `community`
--

INSERT INTO `community` (`id`, `id_categorie_id`, `nom`, `description`, `cover`, `created_at`, `nbr_membre`) VALUES
(1, 1, 'Padel Connection', 'Only padel connection player', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTcaIgobDhF9s_plhsJ_0IY81OIaFNlkrhcWA&s', '2025-02-02 22:38:16', 1),
(3, 3, 'DJ\'s', 'Afro House , Tech House , Melodic House', 'https://cdn.sanity.io/images/pge26oqu/production/6d18acf96d51efcc13a5c566f490d52066c6a9ff-1500x1001.jpg?rect=250,0,1001,1001&w=750&h=750', '2025-02-02 22:54:09', 1),
(6, 18, 'theatrie comedie', '100% comedie', '/uploads/images-67ae9f195a8a0.png', '2025-02-14 02:40:41', 0),
(7, 17, 'Enssemble tour du monde', 'we can do it', '/uploads/1345804-inline-67aea029f3b88.jpg', '2025-02-14 02:45:14', 1),
(10, 1, 'FootBall', 'faire du football', '/uploads/2774679-travailler-dans-le-football-610x370-67b88de58b17c.jpg', '2025-02-19 19:23:17', 1),
(13, 1, 'Street workout', 'only warriors', '/uploads/street-workout-poterne-massy-jm-molina-web-67b8868f9a665.jpg', '2025-02-21 14:58:39', 2),
(14, 2, 'Oriental', 'Dance Oriental', '/uploads/danse-orientale-67b8cc9fad79e.jpg', '2025-02-21 19:57:35', 3);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250202185010', '2025-02-02 19:50:19', 2020),
('DoctrineMigrations\\Version20250209220950', '2025-02-09 23:09:59', 1210),
('DoctrineMigrations\\Version20250209224544', '2025-02-09 23:45:51', 154),
('DoctrineMigrations\\Version20250209231735', '2025-02-10 00:17:39', 154),
('DoctrineMigrations\\Version20250210182527', '2025-02-10 19:25:47', 1341),
('DoctrineMigrations\\Version20250211120928', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `id_community_id` int(11) DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `started_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `finish_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `lieu` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `acces` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `events`
--

INSERT INTO `events` (`id`, `id_community_id`, `nom`, `description`, `started_at`, `finish_at`, `lieu`, `type`, `cover`, `link`, `acces`) VALUES
(7, 1, 'Tournois P50', '60 dt/player', '2025-02-10 17:27:00', '2025-02-11 17:27:00', 'padel connection', 'Presentiel', '/uploads/images-67b878a5afcbb.jpg', NULL, 'Public'),
(8, 3, 'DJ Course', 'for begginers', '2025-02-10 18:01:00', '2025-02-11 00:07:00', 'tunis', 'En Ligne', '/uploads/Capture-d-ecran-2025-01-03-145702-67aa31311df62.png', 'https:/link.com', 'Public'),
(10, 3, 'Tunisiasme', 'streaming', '2025-02-14 03:56:00', '2025-02-16 03:56:00', 'gammarth', 'Presentiel', '/uploads/PROFILE-no-fill-67aeb0ee3066b.jpg', NULL, 'Public'),
(13, 14, 'Formation With M.olga', 'Lady\'s only', '2025-02-25 22:40:00', '2025-02-27 22:40:00', 'Aqua Viva', 'Presentiel', '/uploads/imagdes-67bce74f208b8.jpg', NULL, 'Private'),
(14, 14, 'Workshop Mixte', 'girls and boys', '2025-02-26 00:35:00', '2025-02-27 19:40:00', 'Dance Lab', 'Presentiel', '/uploads/couple-danseur-oriental-homme-femme-mixte-male-67bcf43a6b9ca.jpg', NULL, 'Public');

-- --------------------------------------------------------

--
-- Structure de la table `gamifications`
--

CREATE TABLE `gamifications` (
  `id` int(11) NOT NULL,
  `type_abonnement` int(11) DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `type` varchar(255) NOT NULL,
  `condition_gamification` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `gamifications`
--

INSERT INTO `gamifications` (`id`, `type_abonnement`, `nom`, `description`, `type`, `condition_gamification`) VALUES
(1, 2, '-20% Formation Symfony', '-20%', 'Reduction', 100),
(2, 1, '1 mois SyncYLinkY premium Gratuit', '1 mois SyncYLinkY premium Gratuit', 'Recompense', 200);

-- --------------------------------------------------------

--
-- Structure de la table `inscription_abonnement`
--

CREATE TABLE `inscription_abonnement` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `abonnement_id` int(11) DEFAULT NULL,
  `subscribed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expired_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `mode_paiement` varchar(255) NOT NULL,
  `renouvellement_auto` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inscription_abonnement`
--

INSERT INTO `inscription_abonnement` (`id`, `user_id`, `abonnement_id`, `subscribed_at`, `expired_at`, `mode_paiement`, `renouvellement_auto`) VALUES
(2, 1, 2, '2025-02-26 00:22:11', '2025-03-26 00:22:11', 'Flouci', 0);

-- --------------------------------------------------------

--
-- Structure de la table `lieu_culturels`
--

CREATE TABLE `lieu_culturels` (
  `id` int(11) NOT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `link3_d` varchar(255) NOT NULL,
  `cover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `lieu_culturels`
--

INSERT INTO `lieu_culturels` (`id`, `ville_id`, `nom`, `description`, `link3_d`, `cover`) VALUES
(3, 1, 'Port', 'Description Description Description', 'https://www.google.com/maps/@36.8668013,10.3529851,3a,75y,307.11h,90.24t/data=!3m8!1e1!3m6!1sCIHM0ogKEICAgID4vNTFxgE!2e10!3e11!6shttps:%2F%2Flh3.googleusercontent.com%2Fgpms-cs-s%2FAIMqDu22a2xzz4-VKtZ5XuylfdMDvRwSfxJEYknbBBXw8iP4r_6ZTijjDOFpkaRYnJidh5Ez1F', '/uploads/covers/Port-of-Sidi-Bou-Said-Tunis-Tunisia-North-Africa-67b6155fb3ab1.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `lieux_id` int(11) DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `membre_comunity`
--

CREATE TABLE `membre_comunity` (
  `id` int(11) NOT NULL,
  `id_user_id` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `date_adhesion` datetime NOT NULL,
  `community_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `membre_comunity`
--

INSERT INTO `membre_comunity` (`id`, `id_user_id`, `status`, `date_adhesion`, `community_id`) VALUES
(2, 2, 'owner', '2025-02-19 19:23:17', 10),
(9, 1, 'membre', '2025-02-20 00:55:41', 3),
(12, 2, 'membre', '2025-02-21 14:59:11', 13),
(13, 1, 'membre', '2025-02-21 16:07:01', 1),
(14, 1, 'membre', '2025-02-21 16:07:09', 13),
(17, 1, 'owner', '2025-02-21 19:57:35', 14),
(18, 2, 'moderator', '2025-02-21 19:57:47', 14),
(20, 4, 'membre', '2025-02-21 20:01:00', 14),
(26, 1, 'membre', '2025-02-21 23:09:32', 7);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` varchar(255) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `update_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reaction`
--

CREATE TABLE `reaction` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `share`
--

CREATE TABLE `share` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `shared_from_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `create_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `role` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `date_ob` date NOT NULL,
  `gender` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `role`, `password`, `name`, `firstname`, `username`, `date_ob`, `gender`) VALUES
(1, 'menzah.galaxy@gmail.com', 'ROLE_ADMIN', '$2y$13$LYBMdNkPukzOMk.wU3xw7eY47H1NXuVV6KJCiFU/htdZvII4mZruS', 'limam', 'youssef', 'Limam', '2000-07-04', 'homme'),
(2, 'kosontini@gmail.com', 'ROLE_USER', '$2y$13$ef1yGBcdCcBDPxjuRmlkk.kj9BlxibnyNgsR8EMMVPpIUHmQKEt.C', 'tijani', 'kosontini', 'kson', '2001-12-13', 'autres'),
(4, 'user1@syncylinky.tn', 'ROLE_USER', '$2y$13$WkzyxrNw5U7Xy3Y4G8fameJHKv9H9u9VJEC7Q29LIhRu51iMAr8BS', 'user', '1', 'User 1', '1957-03-17', 'autres');

-- --------------------------------------------------------

--
-- Structure de la table `user_categories`
--

CREATE TABLE `user_categories` (
  `user_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_categories`
--

INSERT INTO `user_categories` (`user_id`, `categories_id`) VALUES
(1, 1),
(1, 3),
(2, 1),
(2, 3),
(4, 2),
(4, 18);

-- --------------------------------------------------------

--
-- Structure de la table `ville`
--

CREATE TABLE `ville` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ville`
--

INSERT INTO `ville` (`id`, `nom`, `description`, `position`) VALUES
(1, 'Sidi bou said', 'Description Description Description Description', 'Tunis Tunis Tunis');

-- --------------------------------------------------------

--
-- Structure de la table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `nbr_visitors` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `visitors`
--

INSERT INTO `visitors` (`id`, `nbr_visitors`) VALUES
(1, 125);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abonnements`
--
ALTER TABLE `abonnements`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7DDCF70DFDA7B0BF` (`community_id`);

--
-- Index pour la table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9474526C4B89032C` (`post_id`),
  ADD KEY `IDX_9474526CA76ED395` (`user_id`);

--
-- Index pour la table `community`
--
ALTER TABLE `community`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1B6040339F34925F` (`id_categorie_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5387574ADE66C5CD` (`id_community_id`);

--
-- Index pour la table `gamifications`
--
ALTER TABLE `gamifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DB1F936B2811BE9E` (`type_abonnement`);

--
-- Index pour la table `inscription_abonnement`
--
ALTER TABLE `inscription_abonnement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_26D8E52FA76ED395` (`user_id`),
  ADD UNIQUE KEY `UNIQ_26D8E52FF1D74413` (`abonnement_id`);

--
-- Index pour la table `lieu_culturels`
--
ALTER TABLE `lieu_culturels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6AC9CE84A73F0036` (`ville_id`);

--
-- Index pour la table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6A2CA10CA2C806AC` (`lieux_id`);

--
-- Index pour la table `membre_comunity`
--
ALTER TABLE `membre_comunity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_88F7DAC079F37AE5` (`id_user_id`),
  ADD KEY `IDX_88F7DAC0FDA7B0BF` (`community_id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5A8A6C8DA76ED395` (`user_id`);

--
-- Index pour la table `reaction`
--
ALTER TABLE `reaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A4D707F74B89032C` (`post_id`),
  ADD KEY `IDX_A4D707F7F8697D13` (`comment_id`),
  ADD KEY `IDX_A4D707F7A76ED395` (`user_id`);

--
-- Index pour la table `share`
--
ALTER TABLE `share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EF069D5A4B89032C` (`post_id`),
  ADD KEY `IDX_EF069D5A5919D5BC` (`shared_from_id`),
  ADD KEY `IDX_EF069D5AA76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- Index pour la table `user_categories`
--
ALTER TABLE `user_categories`
  ADD PRIMARY KEY (`user_id`,`categories_id`),
  ADD KEY `IDX_9D948084A76ED395` (`user_id`),
  ADD KEY `IDX_9D948084A21214B7` (`categories_id`);

--
-- Index pour la table `ville`
--
ALTER TABLE `ville`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `abonnements`
--
ALTER TABLE `abonnements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `community`
--
ALTER TABLE `community`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `gamifications`
--
ALTER TABLE `gamifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `inscription_abonnement`
--
ALTER TABLE `inscription_abonnement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `lieu_culturels`
--
ALTER TABLE `lieu_culturels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `membre_comunity`
--
ALTER TABLE `membre_comunity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reaction`
--
ALTER TABLE `reaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `share`
--
ALTER TABLE `share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `ville`
--
ALTER TABLE `ville`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD CONSTRAINT `FK_7DDCF70DFDA7B0BF` FOREIGN KEY (`community_id`) REFERENCES `community` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `FK_9474526C4B89032C` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`),
  ADD CONSTRAINT `FK_9474526CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `community`
--
ALTER TABLE `community`
  ADD CONSTRAINT `FK_1B6040339F34925F` FOREIGN KEY (`id_categorie_id`) REFERENCES `categories` (`id`);

--
-- Contraintes pour la table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `FK_5387574ADE66C5CD` FOREIGN KEY (`id_community_id`) REFERENCES `community` (`id`);

--
-- Contraintes pour la table `gamifications`
--
ALTER TABLE `gamifications`
  ADD CONSTRAINT `FK_DB1F936B2811BE9E` FOREIGN KEY (`type_abonnement`) REFERENCES `abonnements` (`id`);

--
-- Contraintes pour la table `inscription_abonnement`
--
ALTER TABLE `inscription_abonnement`
  ADD CONSTRAINT `FK_26D8E52FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_26D8E52FF1D74413` FOREIGN KEY (`abonnement_id`) REFERENCES `abonnements` (`id`);

--
-- Contraintes pour la table `lieu_culturels`
--
ALTER TABLE `lieu_culturels`
  ADD CONSTRAINT `FK_6AC9CE84A73F0036` FOREIGN KEY (`ville_id`) REFERENCES `ville` (`id`);

--
-- Contraintes pour la table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `FK_6A2CA10CA2C806AC` FOREIGN KEY (`lieux_id`) REFERENCES `lieu_culturels` (`id`);

--
-- Contraintes pour la table `membre_comunity`
--
ALTER TABLE `membre_comunity`
  ADD CONSTRAINT `FK_88F7DAC079F37AE5` FOREIGN KEY (`id_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_88F7DAC0FDA7B0BF` FOREIGN KEY (`community_id`) REFERENCES `community` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `FK_5A8A6C8DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `reaction`
--
ALTER TABLE `reaction`
  ADD CONSTRAINT `FK_A4D707F74B89032C` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`),
  ADD CONSTRAINT `FK_A4D707F7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_A4D707F7F8697D13` FOREIGN KEY (`comment_id`) REFERENCES `comment` (`id`);

--
-- Contraintes pour la table `share`
--
ALTER TABLE `share`
  ADD CONSTRAINT `FK_EF069D5A4B89032C` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`),
  ADD CONSTRAINT `FK_EF069D5A5919D5BC` FOREIGN KEY (`shared_from_id`) REFERENCES `share` (`id`),
  ADD CONSTRAINT `FK_EF069D5AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `user_categories`
--
ALTER TABLE `user_categories`
  ADD CONSTRAINT `FK_9D948084A21214B7` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_9D948084A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
