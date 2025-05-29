-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 01:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `voting_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Photo` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `Username`, `Password`, `Photo`) VALUES
(1, 'church', '$2y$10$S/xVi0iRd3e6ZNrOcRS0m.nrTcp82EBtjbz5AQv6dUGs/bCUDr7Ke', '6838332635b53_ad.png');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `CandidateID` int(11) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `PositionID` int(11) DEFAULT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`CandidateID`, `FirstName`, `LastName`, `Photo`, `PositionID`, `Status`) VALUES
(1, 'Joseph', 'Keen', '68375e7b06d16_img2.png', 1, 'active'),
(2, 'Alex', 'Maina', '1747917637_373c43ccb441dbc2.png', 3, 'active'),
(3, 'Faith', 'Amunga', '68375ed086bd3_img3.webp', 1, 'active'),
(4, 'Jane', 'Hall', '1748466174_9d1e59289e2a104a.jpg', 6, 'active'),
(5, 'Antony', 'Mbuvi', '1748466632_7d98376fa15f8b08.jpg', 3, 'active'),
(6, 'Lisa', 'Ngugi', '1748467131_1efd0a402feb3014.jpg', 8, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `election_cycles`
--

CREATE TABLE `election_cycles` (
  `CycleID` int(11) NOT NULL,
  `ElectionName` varchar(100) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `IsActive` tinyint(1) DEFAULT 0,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `election_title`
--

CREATE TABLE `election_title` (
  `ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_title`
--

INSERT INTO `election_title` (`ID`, `Title`, `Status`) VALUES
(1, 'Election 2025', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `PositionID` int(11) NOT NULL,
  `PositionName` varchar(100) DEFAULT NULL,
  `Description` text NOT NULL,
  `Status` enum('active','inactive') DEFAULT 'active',
  `VotesAllowed` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`PositionID`, `PositionName`, `Description`, `Status`, `VotesAllowed`) VALUES
(1, 'Treasurer', 'Handles financial administrations of the church', 'active', 1),
(2, 'Secretary', 'Maintains church records, minutes of meetings', 'active', 1),
(3, 'Administrator', 'Manages church operations, day-to-day administrative tasks.', 'active', 1),
(4, 'Senior Pastor', 'Primary spiritual leader and preacher.', 'active', 1),
(5, 'Deacon', 'Serves practical needs, assists with communion', 'active', 1),
(6, 'Youth Director', 'Disciples teens through Bible studies and events.', 'active', 1),
(7, 'Missions Director', 'Leads local and global outreach efforts, organizes mission trips,', 'active', 1),
(8, 'Facilities Manager', 'Oversees building maintenance, repairs, and event setup/cleanup.', 'active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `VoterID` int(11) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`VoterID`, `FirstName`, `LastName`, `Email`, `Password`, `Photo`, `Status`) VALUES
(1, 'Michael', 'Townley', 'michael@gmail.com', '$2y$10$F20C2.KG.8Djrw4CuMHh8uV3k.aEgu7HaAVT5d/w5lT4aIj5uVYtK', 'voter_6829c7d9622fb3.49343790.jpg', 'active'),
(2, 'Brian', 'Mwai', 'brian@gmail.com', '$2y$10$vuhtz/clPgI6RJPhgPSaMeaDqtq68F.rLEWZgzF0fHugp7enkNyrm', 'br.webp', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `VoteID` int(11) NOT NULL,
  `VoterID` int(11) NOT NULL,
  `PositionID` int(11) NOT NULL,
  `CandidateID` int(11) NOT NULL,
  `VoteDate` datetime DEFAULT current_timestamp(),
  `CycleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`VoteID`, `VoterID`, `PositionID`, `CandidateID`, `VoteDate`, `CycleID`) VALUES
(1, 1, 1, 1, '2025-05-23 19:55:24', NULL),
(2, 1, 3, 2, '2025-05-23 19:55:24', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`CandidateID`),
  ADD KEY `PositionID` (`PositionID`);

--
-- Indexes for table `election_cycles`
--
ALTER TABLE `election_cycles`
  ADD PRIMARY KEY (`CycleID`);

--
-- Indexes for table `election_title`
--
ALTER TABLE `election_title`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`PositionID`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`VoterID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`VoteID`),
  ADD KEY `VoterID` (`VoterID`),
  ADD KEY `CandidateID` (`CandidateID`),
  ADD KEY `CycleID` (`CycleID`),
  ADD KEY `PositionID` (`PositionID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `CandidateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `election_cycles`
--
ALTER TABLE `election_cycles`
  MODIFY `CycleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `election_title`
--
ALTER TABLE `election_title`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `PositionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `VoterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `VoteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`PositionID`) REFERENCES `positions` (`PositionID`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`VoterID`) REFERENCES `voters` (`VoterID`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`CandidateID`) REFERENCES `candidates` (`CandidateID`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`CycleID`) REFERENCES `election_cycles` (`CycleID`),
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`PositionID`) REFERENCES `positions` (`PositionID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
