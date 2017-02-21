

CREATE TABLE `ftp_queue` (
  `queue_id` bigint(20) NOT NULL,
  `ftp_site_id` int(11) DEFAULT NULL,
  `ftp_info` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ftp_site` (
  `ftp_site_id` int(11) NOT NULL,
  `server` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `ftp_path` varchar(255) DEFAULT NULL,
  `paths_to_ignore` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `ftp_queue`
  ADD PRIMARY KEY (`queue_id`);

ALTER TABLE `ftp_site`
  ADD PRIMARY KEY (`ftp_site_id`);
ALTER TABLE `ftp_queue`
  MODIFY `queue_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;
ALTER TABLE `ftp_site`
  MODIFY `ftp_site_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT;

INSERT INTO `ftp_site` (`server`, `username`, `password`, `ftp_path`, `paths_to_ignore`) VALUES
('server1', 'username', 'password', '/folder', '["\\/AdminLogs","\\/csv","\\/_vti_cnf","\\/Temp","\\/aspnet_client"]'),
