#
# Table structure for table 'tx_jbxappointmentbooking_season'
#
CREATE TABLE tx_jbxappointmentbooking_season (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	from_day int(11) DEFAULT '0' NOT NULL,
	from_month int(11) DEFAULT '0' NOT NULL,
	until_day int(11) DEFAULT '0' NOT NULL,
	until_month int(11) DEFAULT '0' NOT NULL,
	title tinytext,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_jbxappointmentbooking_slot_range'
#
CREATE TABLE tx_jbxappointmentbooking_slot_range (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	weekday int(11) DEFAULT '0' NOT NULL,
	from_hour int(11) DEFAULT '0' NOT NULL,
	from_minute int(11) DEFAULT '0' NOT NULL,
	to_hour int(11) DEFAULT '0' NOT NULL,
	to_minute int(11) DEFAULT '0' NOT NULL,
	title tinytext,
	season int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);