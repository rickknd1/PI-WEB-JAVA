-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2025 at 01:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `syncylinky`
--

-- --------------------------------------------------------

--
-- Table structure for table `abonnements`
--

CREATE TABLE `abonnements` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prix` double NOT NULL,
  `avantages` longtext NOT NULL COMMENT '(DC2Type:array)',
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abonnements`
--

INSERT INTO `abonnements` (`id`, `nom`, `prix`, `avantages`, `type`) VALUES
(1, 'Free', 0, 'a:2:{i:0;s:16:\"Evenement public\";i:1;s:11:\"Communautes\";}', 'Normal'),
(2, 'Premium', 10, 'a:3:{i:0;s:16:\"Evenement privee\";i:1;s:6:\"No Ads\";i:2;s:23:\"Systeme de gamification\";}', 'Premium');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `cover` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
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
-- Table structure for table `chat_rooms`
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
-- Dumping data for table `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `community_id`, `nom`, `cover`, `type`, `created_at`) VALUES
(1, 1, 'FInd Partner', '/uploads/Capture-d-ecran-2023-12-27-002902-67aa5599a537e.png', 'Public', '2025-02-10 20:38:01'),
(3, 3, 'Unreleased', '/uploads/ddd-67aeb86dee348.jpg', 'Public', '2025-02-14 04:28:45');

-- --------------------------------------------------------

--
-- Table structure for table `community`
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
-- Dumping data for table `community`
--

INSERT INTO `community` (`id`, `id_categorie_id`, `nom`, `description`, `cover`, `created_at`, `nbr_membre`) VALUES
(1, 1, 'Padel Connection', 'Only padel connection player', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTcaIgobDhF9s_plhsJ_0IY81OIaFNlkrhcWA&s', '2025-02-02 22:38:16', 8),
(2, 1, 'Street workout', 'Description Street workout', 'https://www.destination-paris-saclay.com/uploads/2024/04/street-workout-poterne-massy-jm-molina-web.jpg', '2025-02-02 22:52:34', 4),
(3, 3, 'DJ\'s', 'Afro House , Tech House , Melodic House', 'https://cdn.sanity.io/images/pge26oqu/production/6d18acf96d51efcc13a5c566f490d52066c6a9ff-1500x1001.jpg?rect=250,0,1001,1001&w=750&h=750', '2025-02-02 22:54:09', 5),
(6, 18, 'theatrie comedie', '100% comedie', '/uploads/images-67ae9f195a8a0.png', '2025-02-14 02:40:41', 2),
(7, 17, 'Enssemble tour du monde', 'we can do it', '/uploads/1345804-inline-67aea029f3b88.jpg', '2025-02-14 02:45:14', 1),
(9, 2, 'Break Dance', 'only street dancers', '/uploads/398682352dd288d87cc5cb2ce80b41fd-67b52f7f6cfb5.jpg', '2025-02-19 02:10:23', 1),
(10, 1, 'FootBall', 'faire du foot', '/uploads/nike-football-67b621951a11e.webp', '2025-02-19 19:23:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `doctrine_migration_versions`
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
-- Table structure for table `events`
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
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `id_community_id`, `nom`, `description`, `started_at`, `finish_at`, `lieu`, `type`, `cover`, `link`) VALUES
(7, 1, 'Tournois P50', '60 dt/player', '2025-02-10 17:27:00', '2025-02-11 17:27:00', 'padel connection', 'Presentiel', '/uploads/Picturee1-67aa291bef146.png', NULL),
(8, 3, 'DJ Course', 'for begginers', '2025-02-10 18:01:00', '2025-02-11 00:07:00', 'tunis', 'En Ligne', '/uploads/Capture-d-ecran-2025-01-03-145702-67aa31311df62.png', 'https:/link.com'),
(10, 3, 'Tunisiasme', 'streaming', '2025-02-14 03:56:00', '2025-02-16 03:56:00', 'gammarth', 'Presentiel', '/uploads/PROFILE-no-fill-67aeb0ee3066b.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gamifications`
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
-- Dumping data for table `gamifications`
--

INSERT INTO `gamifications` (`id`, `type_abonnement`, `nom`, `description`, `type`, `condition_gamification`) VALUES
(1, 2, '-20% Formation Symfony', '-20%', 'Reduction', 100),
(2, 1, '1 mois SyncYLinkY premium Gratuit', '1 mois SyncYLinkY premium Gratuit', 'Recompense', 200);

-- --------------------------------------------------------

--
-- Table structure for table `lieu_culturels`
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
-- Dumping data for table `lieu_culturels`
--

INSERT INTO `lieu_culturels` (`id`, `ville_id`, `nom`, `description`, `link3_d`, `cover`) VALUES
(3, 1, 'Port', 'Description Description Description', 'https://www.google.com/maps/@36.8668013,10.3529851,3a,75y,307.11h,90.24t/data=!3m8!1e1!3m6!1sCIHM0ogKEICAgID4vNTFxgE!2e10!3e11!6shttps:%2F%2Flh3.googleusercontent.com%2Fgpms-cs-s%2FAIMqDu22a2xzz4-VKtZ5XuylfdMDvRwSfxJEYknbBBXw8iP4r_6ZTijjDOFpkaRYnJidh5Ez1F', '/uploads/covers/Port-of-Sidi-Bou-Said-Tunis-Tunisia-North-Africa-67b6155fb3ab1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `lieux_id` int(11) DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membre_comunity`
--

CREATE TABLE `membre_comunity` (
  `id` int(11) NOT NULL,
  `id_user_id` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `date_adhesion` datetime NOT NULL,
  `id_comunity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membre_comunity`
--

INSERT INTO `membre_comunity` (`id`, `id_user_id`, `status`, `date_adhesion`, `id_comunity_id`) VALUES
(1, 1, 'moderator', '2025-02-19 02:10:23', 9),
(2, 2, 'moderator', '2025-02-19 19:23:17', 10),
(8, 2, 'membre', '2025-02-20 00:55:11', 2),
(9, 1, 'membre', '2025-02-20 00:55:41', 3);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
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
-- Table structure for table `user`
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
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `role`, `password`, `name`, `firstname`, `username`, `date_ob`, `gender`) VALUES
(1, 'menzah.galaxy@gmail.com', 'ROLE_ADMIN', '$2y$13$LYBMdNkPukzOMk.wU3xw7eY47H1NXuVV6KJCiFU/htdZvII4mZruS', 'limam', 'youssef', 'Limam', '2000-07-04', 'homme'),
(2, 'kosontini@gmail.com', 'ROLE_USER', '$2y$13$ef1yGBcdCcBDPxjuRmlkk.kj9BlxibnyNgsR8EMMVPpIUHmQKEt.C', 'tijani', 'kosontini', 'kson', '2001-12-13', 'autres');

-- --------------------------------------------------------

--
-- Table structure for table `user_categories`
--

CREATE TABLE `user_categories` (
  `user_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_categories`
--

INSERT INTO `user_categories` (`user_id`, `categories_id`) VALUES
(1, 1),
(1, 3),
(2, 1),
(2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ville`
--

CREATE TABLE `ville` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ville`
--

INSERT INTO `ville` (`id`, `nom`, `description`, `position`) VALUES
(1, 'Sidi bou said', 'Description Description Description Description', 'Tunis Tunis Tunis');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `nbr_visitors` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `nbr_visitors`) VALUES
(1, 80);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abonnements`
--
ALTER TABLE `abonnements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7DDCF70DFDA7B0BF` (`community_id`);

--
-- Indexes for table `community`
--
ALTER TABLE `community`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1B6040339F34925F` (`id_categorie_id`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5387574ADE66C5CD` (`id_community_id`);

--
-- Indexes for table `gamifications`
--
ALTER TABLE `gamifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DB1F936B2811BE9E` (`type_abonnement`);

--
-- Indexes for table `lieu_culturels`
--
ALTER TABLE `lieu_culturels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6AC9CE84A73F0036` (`ville_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6A2CA10CA2C806AC` (`lieux_id`);

--
-- Indexes for table `membre_comunity`
--
ALTER TABLE `membre_comunity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_88F7DAC079F37AE5` (`id_user_id`),
  ADD KEY `IDX_88F7DAC0A4C4F6C9` (`id_comunity_id`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- Indexes for table `user_categories`
--
ALTER TABLE `user_categories`
  ADD PRIMARY KEY (`user_id`,`categories_id`),
  ADD KEY `IDX_9D948084A76ED395` (`user_id`),
  ADD KEY `IDX_9D948084A21214B7` (`categories_id`);

--
-- Indexes for table `ville`
--
ALTER TABLE `ville`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abonnements`
--
ALTER TABLE `abonnements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `community`
--
ALTER TABLE `community`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `gamifications`
--
ALTER TABLE `gamifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lieu_culturels`
--
ALTER TABLE `lieu_culturels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membre_comunity`
--
ALTER TABLE `membre_comunity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ville`
--
ALTER TABLE `ville`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD CONSTRAINT `FK_7DDCF70DFDA7B0BF` FOREIGN KEY (`community_id`) REFERENCES `community` (`id`);

--
-- Constraints for table `community`
--
ALTER TABLE `community`
  ADD CONSTRAINT `FK_1B6040339F34925F` FOREIGN KEY (`id_categorie_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `FK_5387574ADE66C5CD` FOREIGN KEY (`id_community_id`) REFERENCES `community` (`id`);

--
-- Constraints for table `gamifications`
--
ALTER TABLE `gamifications`
  ADD CONSTRAINT `FK_DB1F936B2811BE9E` FOREIGN KEY (`type_abonnement`) REFERENCES `abonnements` (`id`);

--
-- Constraints for table `lieu_culturels`
--
ALTER TABLE `lieu_culturels`
  ADD CONSTRAINT `FK_6AC9CE84A73F0036` FOREIGN KEY (`ville_id`) REFERENCES `ville` (`id`);

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `FK_6A2CA10CA2C806AC` FOREIGN KEY (`lieux_id`) REFERENCES `lieu_culturels` (`id`);

--
-- Constraints for table `membre_comunity`
--
ALTER TABLE `membre_comunity`
  ADD CONSTRAINT `FK_88F7DAC079F37AE5` FOREIGN KEY (`id_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_88F7DAC0A4C4F6C9` FOREIGN KEY (`id_comunity_id`) REFERENCES `community` (`id`);

--
-- Constraints for table `user_categories`
--
ALTER TABLE `user_categories`
  ADD CONSTRAINT `FK_9D948084A21214B7` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_9D948084A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
