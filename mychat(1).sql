-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 08, 2025 alle 11:52
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mychat`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_pass` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_profile` varchar(255) NOT NULL,
  `user_country` text NOT NULL,
  `user_gender` text NOT NULL,
  `forgotten_answer` varchar(100) NOT NULL,
  `log_in` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_pass`, `user_email`, `user_profile`, `user_country`, `user_gender`, `forgotten_answer`, `log_in`) VALUES
(1, 'Test_name', '1234', '1234@gmail.com', 'images/pfp1.jpeg', 'au', 'f', '', 'Online'),
(2, 'User2', '12345678', 'user2@gmail.com', 'images/pfp1.jpeg', 'au', 'f', '', 'Online'),
(3, 'User3', '12345678', 'user3@gmail.com', 'images/pfp2.jpeg', 'it', 'm', '', 'Online');

-- --------------------------------------------------------

--
-- Struttura della tabella `users_chats`
--

CREATE TABLE `users_chats` (
  `msg_id` int(11) NOT NULL,
  `sender_ID` int(100) NOT NULL,
  `receiver_ID` int(100) NOT NULL,
  `msg_content` varchar(255) NOT NULL,
  `msg_status` text NOT NULL,
  `msg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `users_chats`
--

INSERT INTO `users_chats` (`msg_id`, `sender_ID`, `receiver_ID`, `msg_content`, `msg_status`, `msg_date`) VALUES
(2, 1, 2, 'so now it should be right?', 'unread', '2025-01-08 09:32:58'),
(3, 2, 1, 'are you looking at the modified date?', 'unread', '2025-01-08 09:32:01'),
(4, 1, 2, 'do you really work?', 'unread', '2025-01-08 09:38:51'),
(5, 1, 2, 'you sure?', 'unread', '2025-01-08 09:38:58'),
(6, 1, 2, 'why arent you working!!', 'unread', '2025-01-08 09:40:22'),
(7, 1, 2, 'idkk', 'unread', '2025-01-08 09:45:46'),
(8, 2, 1, 'do you now work??', 'unread', '2025-01-08 09:49:26'),
(9, 1, 2, 'maybe', 'unread', '2025-01-08 09:52:11'),
(11, 2, 1, 'it works!!!', 'unread', '2025-01-08 09:54:53'),
(12, 3, 2, 'Hi', 'unread', '2025-01-08 10:07:04'),
(13, 1, 2, 'heyyy', 'unread', '2025-01-08 10:08:00'),
(14, 2, 1, 'i need to fill up space', 'unread', '2025-01-08 10:15:02'),
(15, 2, 1, 'okkkk', 'unread', '2025-01-08 10:17:43');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_contacts`
--

CREATE TABLE `user_contacts` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indici per le tabelle `users_chats`
--
ALTER TABLE `users_chats`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `sender-chat` (`sender_ID`),
  ADD KEY `receiver-chat` (`receiver_ID`);

--
-- Indici per le tabelle `user_contacts`
--
ALTER TABLE `user_contacts`
  ADD PRIMARY KEY (`user_id`,`contact_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`contact_id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `users_chats`
--
ALTER TABLE `users_chats`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `users_chats`
--
ALTER TABLE `users_chats`
  ADD CONSTRAINT `receiver-chat` FOREIGN KEY (`receiver_ID`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sender-chat` FOREIGN KEY (`sender_ID`) REFERENCES `users` (`user_id`);

--
-- Limiti per la tabella `user_contacts`
--
ALTER TABLE `user_contacts`
  ADD CONSTRAINT `user_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_contacts_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
