-- MySQL dump 10.13  Distrib 5.5.32, for Linux (i686)
--
-- Host: localhost    Database: invoice
-- ------------------------------------------------------
-- Server version	5.5.32-31.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `ISON` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `ISO2` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `ISO3` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `vat` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `european` tinyint(4) NOT NULL,
  `european_continent` int(1) NOT NULL,
  `old_id` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ISO2` (`ISO2`),
  KEY `old_id` (`old_id`)
) ENGINE=MyISAM AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country`
--

LOCK TABLES `country` WRITE;
/*!40000 ALTER TABLE `country` DISABLE KEYS */;
INSERT INTO `country` VALUES (1,'Burundi','108','BI','BDI','BI',0,0,257),(2,'Comoros','174','KM','COM','KM',0,0,269),(3,'Djibouti','262','DJ','DJI','DJ',0,0,253),(4,'Eritrea','232','ER','ERI','ER',0,0,291),(5,'Ethiopia','231','ET','ETH','ET',0,0,251),(6,'Kenya','404','KE','KEN','KE',0,0,254),(7,'Madagascar','450','MG','MDG','MG',0,0,261),(8,'Malawi','454','MW','MWI','MW',0,0,265),(9,'Mauritius','480','MU','MUS','MU',0,0,230),(10,'Mayotte','175','YT','MYT','YT',0,0,0),(11,'Mozambique','508','MZ','MOZ','MZ',0,0,258),(12,'Réunion','638','RE','REU','RE',0,0,262),(13,'Rwanda','646','RW','RWA','RW',0,0,250),(14,'Seychelles','690','SC','SYC','SC',0,0,248),(15,'Somalia','706','SO','SOM','SO',0,0,252),(16,'Tanzania, United Republic of','834','TZ','TZA','TZ',0,0,255),(17,'Uganda','800','UG','UGA','UG',0,0,256),(18,'Zambia','894','ZM','ZMB','ZM',0,0,260),(19,'Zimbabwe','716','ZW','ZWE','ZW',0,0,263),(20,'Angola','24','AO','AGO','AO',0,0,244),(21,'Cameroon','120','CM','CMR','CM',0,0,237),(22,'Central African Republic','140','CF','CAF','CF',0,0,236),(23,'Chad','148','TD','TCD','TD',0,0,235),(24,'Congo','178','CG','COG','CG',0,0,242),(25,'Congo, Democratic Republic of the','180','CD','COD','CD',0,0,243),(26,'Equatorial Guinea','226','GQ','GNQ','GQ',0,0,240),(27,'Gabon','266','GA','GAB','GA',0,0,241),(28,'Sao Tome and Principe','678','ST','STP','ST',0,0,239),(29,'Algeria','12','DZ','DZA','DZ',0,0,213),(30,'Egypt','818','EG','EGY','EG',0,0,20),(31,'Libya','434','LY','LBY','LY',0,0,218),(32,'Morocco','504','MA','MAR','MA',0,0,212),(33,'Sudan','736','SD','SDN','SD',0,0,249),(34,'Tunisia','788','TN','TUN','TN',0,0,216),(35,'Western Sahara','732','EH','ESH','EH',0,0,0),(36,'Botswana','72','BW','BWA','BW',0,0,267),(37,'Lesotho','426','LS','LSO','LS',0,0,266),(38,'Namibia','516','NA','NAM','NA',0,0,264),(39,'South Africa','710','ZA','ZAF','ZA',0,0,27),(40,'Swaziland','748','SZ','SWZ','SZ',0,0,268),(41,'Benin','204','BJ','BEN','BJ',0,0,229),(42,'Burkina Faso','854','BF','BFA','BF',0,0,226),(43,'Cape Verde','132','CV','CPV','CV',0,0,238),(44,'Cote d\'Ivoire','384','CI','CIV','CI',0,0,225),(45,'Gambia','270','GM','GMB','GM',0,0,220),(46,'Ghana','288','GH','GHA','GH',0,0,233),(47,'Guinea','324','GN','GIN','GN',0,0,224),(48,'Guinea-Bissau','624','GW','GNB','GW',0,0,245),(49,'Liberia','430','LR','LBR','LR',0,0,231),(50,'Mali','466','ML','MLI','ML',0,0,223),(51,'Mauritania','478','MR','MRT','MR',0,0,222),(52,'Niger','562','NE','NER','NE',0,0,227),(53,'Nigeria','566','NG','NGA','NG',0,0,234),(54,'Saint Helena','654','SH','SHN','SH',0,0,290),(55,'Senegal','686','SN','SEN','SN',0,0,221),(56,'Sierra Leone','694','SL','SLE','SL',0,0,232),(57,'Togo','768','TG','TGO','TG',0,0,228),(58,'Anguilla','660','AI','AIA','AI',0,0,1264),(59,'Antigua and Barbuda','28','AG','ATG','AG',0,0,1268),(60,'Aruba','533','AW','ABW','AW',0,0,297),(61,'Bahamas','44','BS','BHS','BS',0,0,1242),(62,'Barbados','52','BB','BRB','BB',0,0,1246),(63,'British Virgin Islands','92','VG','VGB','VG',0,0,1284),(64,'Cayman Islands','136','KY','CYM','KY',0,0,1345),(65,'Cuba','192','CU','CUB','CU',0,0,53),(66,'Dominica','212','DM','DMA','DM',0,0,1767),(67,'Dominican Republic','214','DO','DOM','DO',0,0,1809),(68,'Grenada','308','GD','GRD','GD',0,0,1473),(69,'Guadeloupe','312','GP','GLP','GP',0,0,590),(70,'Haiti','332','HT','HTI','HT',0,0,509),(71,'Jamaica','388','JM','JAM','JM',0,0,1876),(72,'Martinique','474','MQ','MTQ','MQ',0,0,596),(73,'Montserrat','500','MS','MSR','MS',0,0,1664),(74,'Netherlands Antilles','530','AN','ANT','AN',0,0,599),(75,'Puerto Rico','630','PR','PRI','PR',0,0,1787),(76,'Saint Kitts and Nevis','659','KN','KNA','KN',0,0,1869),(77,'Saint Lucia','662','LC','LCA','LC',0,0,1758),(78,'Saint Vincent and the Grenadines','670','VC','VCT','VC',0,0,1784),(79,'Trinidad and Tobago','780','TT','TTO','TT',0,0,1868),(80,'Turks and Caicos Islands','796','TC','TCA','TC',0,0,1649),(81,'United States Virgin Islands','850','VI','VIR','VI',0,0,0),(82,'Belize','84','BZ','BLZ','BZ',0,0,501),(83,'Costa Rica','188','CR','CRI','CR',0,0,506),(84,'El Salvador','222','SV','SLV','SV',0,0,503),(85,'Guatemala','320','GT','GTM','GT',0,0,502),(86,'Honduras','340','HN','HND','HN',0,0,504),(87,'Mexico','484','MX','MEX','MX',0,0,52),(88,'Nicaragua','558','NI','NIC','NI',0,0,505),(89,'Panama','591','PA','PAN','PA',0,0,507),(90,'Argentina','32','AR','ARG','AR',0,0,54),(91,'Bolivia','68','BO','BOL','BO',0,0,591),(92,'Brazil','76','BR','BRA','BR',0,0,55),(93,'Chile','152','CL','CHL','CL',0,0,56),(94,'Colombia','170','CO','COL','CO',0,0,57),(95,'Ecuador','218','EC','ECU','EC',0,0,593),(96,'Falkland Islands (Malvinas)','238','FK','FLK','FK',0,0,500),(97,'French Guiana','254','GF','GUF','GF',0,0,594),(98,'Guyana','328','GY','GUY','GY',0,0,592),(99,'Paraguay','600','PY','PRY','PY',0,0,595),(100,'Peru','604','PE','PER','PE',0,0,51),(101,'Suriname','740','SR','SUR','SR',0,0,597),(102,'Uruguay','858','UY','URY','UY',0,0,598),(103,'Venezuela','862','VE','VEN','VE',0,0,58),(104,'Bermuda','60','BM','BMU','BM',0,0,1441),(105,'Canada','124','CA','CAN','CA',0,0,5400),(106,'Greenland','304','GL','GRL','GL',0,0,299),(107,'Saint Pierre and Miquelon','666','PM','SPM','PM',0,0,508),(108,'United States','840','US','USA','',0,0,1),(109,'China','156','CN','CHN','CN',0,0,86),(110,'China - Hong Kong Special Administrative Region','344','HK','HKG','HK',0,0,852),(111,'China - Macao Special Administrative Region','446','MO','MAC','MO',0,0,853),(112,'Japan','392','JP','JPN','JP',0,0,81),(113,'Korea, Democratic People\'s Republic of','408','KP','PRK','KP',0,0,850),(114,'Korea, Republic of','410','KR','KOR','KR',0,0,82),(115,'Mongolia','496','MN','MNG','MN',0,0,976),(116,'Taiwan','158','TW','TWN','TW',0,0,886),(117,'Afghanistan','4','AF','AFG','AF',0,0,93),(118,'Bangladesh','50','BD','BGD','BD',0,0,880),(119,'Bhutan','64','BT','BTN','BT',0,0,975),(120,'India','356','IN','IND','IN',0,0,91),(121,'Iran, Islamic Republic of','364','IR','IRN','IR',0,0,98),(122,'Kazakhstan','398','KZ','KAZ','KZ',0,0,8),(123,'Kyrgyzstan','417','KG','KGZ','KG',0,0,996),(124,'Maldives','462','MV','MDV','MV',0,0,960),(125,'Nepal','524','NP','NPL','NP',0,0,977),(126,'Pakistan','586','PK','PAK','PK',0,0,92),(127,'Sri Lanka','144','LK','LKA','LK',0,0,94),(128,'Tajikistan','762','TJ','TJK','TJ',0,0,992),(129,'Turkmenistan','795','TM','TKM','TM',0,0,993),(130,'Uzbekistan','860','UZ','UZB','UZ',0,0,998),(131,'Brunei Darussalam','96','BN','BRN','BN',0,0,673),(132,'Cambodia','116','KH','KHM','KH',0,0,855),(133,'Indonesia','360','ID','IDN','ID',0,0,62),(134,'Lao People\'s Democratic Republic','418','LA','LAO','LA',0,0,856),(135,'Malaysia','458','MY','MYS','MY',0,0,60),(136,'Myanmar','104','MM','MMR','MM',0,0,95),(137,'Philippines','608','PH','PHL','PH',0,0,63),(138,'Singapore','702','SG','SGP','SG',0,0,65),(139,'Thailand','764','TH','THA','TH',0,0,66),(140,'Timor-Leste','626','TL','TLS','TL',0,0,0),(141,'Viet Nam','704','VN','VNM','VN',0,0,84),(142,'Armenia','51','AM','ARM','AM',0,0,374),(143,'Azerbaijan','31','AZ','AZE','AZ',0,0,994),(144,'Bahrain','48','BH','BHR','BH',0,0,973),(145,'Cyprus','196','CY','CYP','CY',1,1,357),(146,'Georgia','268','GE','GEO','GE',0,0,995),(147,'Iraq','368','IQ','IRQ','IQ',0,0,964),(148,'Israel','376','IL','ISR','IL',0,0,972),(149,'Jordan','400','JO','JOR','JO',0,0,962),(150,'Kuwait','414','KW','KWT','KW',0,0,965),(151,'Lebanon','422','LB','LBN','LB',0,0,961),(152,'Palestinian Territory, Occupied','275','PS','PSE','PS',0,0,970),(153,'Oman','512','OM','OMN','OM',0,0,968),(154,'Qatar','634','QA','QAT','QA',0,0,974),(155,'Saudi Arabia','682','SA','SAU','SA',0,0,966),(156,'Syria','760','SY','SYR','SY',0,0,963),(157,'Turkey','792','TR','TUR','TR',0,0,90),(158,'United Arab Emirates','784','AE','ARE','AE',0,0,971),(159,'Yemen','887','YE','YEM','YE',0,0,967),(160,'Belarus','112','BY','BLR','BY',0,0,375),(161,'Bulgaria','100','BG','BGR','BG',1,1,359),(162,'Czech Republic','203','CZ','CZE','CZ',1,1,420),(163,'Hungary','348','HU','HUN','HU',1,1,36),(164,'Moldova, Republic of','498','MD','MDA','MD',0,0,373),(165,'Poland','616','PL','POL','PL',1,1,48),(166,'Romania','642','RO','ROU','RO',1,1,40),(167,'Russian Federation','643','RU','RUS','RU',0,0,7),(168,'Slovakia','703','SK','SVK','SK',1,1,421),(169,'Ukraine','804','UA','UKR','UA',0,0,380),(170,'Aland Islands','248','AX','ALA','AX',0,0,0),(171,'Denmark','208','DK','DNK','DK',1,1,45),(172,'Estonia','233','EE','EST','EE',1,1,372),(173,'Faeroe Islands','234','FO','FRO','FO',0,0,298),(174,'Finland','246','FI','FIN','FI',1,1,358),(175,'Guernsey','831','GG','GGY','GG',0,0,0),(176,'Iceland','352','IS','ISL','IS',0,0,354),(177,'Ireland','372','IE','IRL','IE',1,1,353),(178,'Jersey','832','JE','JEY','JE',0,0,0),(179,'Latvia','428','LV','LVA','LV',1,1,371),(180,'Lithuania','440','LT','LTU','LT',1,1,370),(181,'Man, Isle of','833','IM','IMN','IM',0,0,0),(182,'Norway','578','NO','NOR','NO',0,1,47),(183,'Svalbard and Jan Mayen Islands','744','SJ','SJM','SJ',0,0,0),(184,'Sweden','752','SE','SWE','SE',1,1,46),(185,'United Kingdom','826','GB','GBR','GB',1,1,44),(186,'Albania','8','AL','ALB','AL',0,0,355),(187,'Andorra','20','AD','AND','AD',0,0,376),(188,'Bosnia and Herzegovina','70','BA','BIH','BA',0,0,387),(189,'Croatia','191','HR','HRV','HR',0,0,385),(190,'Gibraltar','292','GI','GIB','GI',0,0,350),(191,'Greece','300','GR','GRC','EL',1,1,30),(192,'Holy See (Vatican City State)','336','VA','VAT','VA',0,0,0),(193,'Italy','380','IT','ITA','IT',1,1,39),(194,'Macedonia','807','MK','MKD','MK',0,0,389),(195,'Malta','470','MT','MLT','MT',1,1,356),(196,'Montenegro','499','ME','MNE','ME',0,0,5401),(197,'Portugal','620','PT','PRT','PT',1,1,351),(198,'San Marino','674','SM','SMR','SM',0,0,378),(199,'Serbia','688','RS','SRB','RS',0,0,381),(200,'Slovenia','705','SI','SVN','SI',1,1,386),(245,'Canary Islands','724','ES','ESP','ES',0,1,0),(202,'Austria','40','AT','AUT','AT',1,1,43),(203,'Belgium','56','BE','BEL','BE',1,1,32),(204,'France','250','FR','FRA','FR',1,1,33),(205,'Germany','276','DE','DEU','DE',1,1,49),(206,'Liechtenstein','438','LI','LIE','LI',0,0,423),(207,'Luxembourg','442','LU','LUX','LU',1,1,352),(208,'Monaco','492','MC','MCO','MC',0,0,377),(209,'Netherlands','528','NL','NLD','NL',1,1,31),(210,'Switzerland','756','CH','CHE','CH',0,1,41),(211,'Australia','36','AU','AUS','AU',0,0,61),(212,'Christmas Island','162','CX','CXR','CX',0,0,0),(213,'Cocos (keeling) Islands','166','CC','CCK','CC',0,0,0),(214,'New Zealand','554','NZ','NZL','NZ',0,0,64),(215,'Norfolk Island','574','NF','NFK','NF',0,0,672),(216,'Fiji','242','FJ','FJI','FJ',0,0,679),(217,'New Caledonia','540','NC','NCL','NC',0,0,687),(218,'Papua New Guinea','598','PG','PNG','PG',0,0,675),(219,'Solomon Islands','90','SB','SLB','SB',0,0,677),(220,'Vanuatu','548','VU','VUT','VU',0,0,678),(221,'Guam','316','GU','GUM','GU',0,0,671),(222,'Kiribati','296','KI','KIR','KI',0,0,686),(223,'Marshall Islands','584','MH','MHL','MH',0,0,692),(224,'Micronesia, Federated States of','583','FM','FSM','FM',0,0,691),(225,'Nauru','520','NR','NRU','NR',0,0,674),(226,'Northern Mariana Islands','580','MP','MNP','MP',0,0,1670),(227,'Palau','585','PW','PLW','PW',0,0,680),(228,'American Samoa','16','AS','ASM','AS',0,0,684),(229,'Cook Islands','184','CK','COK','CK',0,0,682),(230,'French Polynesia','258','PF','PYF','PF',0,0,689),(231,'Niue','570','NU','NIU','NU',0,0,683),(232,'Pitcairn','612','PN','PCN','PN',0,0,0),(233,'Samoa','882','WS','WSM','WS',0,0,685),(234,'Tokelau','772','TK','TKL','TK',0,0,0),(235,'Tonga','776','TO','TON','TO',0,0,676),(236,'Tuvalu','798','TV','TUV','TV',0,0,688),(237,'Wallis and Futuna Islands','876','WF','WLF','WF',0,0,681),(238,'Antarctica','10','AQ','ATA','AQ',0,0,0),(239,'Bouvet Island','74','BV','BVT','BV',0,0,0),(240,'British Indian Ocean Territory','86','IO','IOT','IO',0,0,0),(241,'French Southern Territories','260','TF','ATF','TF',0,0,0),(242,'Heard Island and McDonald Islands','334','HM','HMD','HM',0,0,0),(243,'South Georgia and the South Sandwich Islands','239','GS','SGS','GS',0,0,0),(244,'United States Minor Outlying Islands','581','UM','UMI','UM',0,0,0),(201,'Spain','724','ES','ESP','ES',1,1,34);
/*!40000 ALTER TABLE `country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(64) CHARACTER SET utf8 NOT NULL,
  `lastname` varchar(64) CHARACTER SET utf8 NOT NULL,
  `company` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `phone` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `mobile` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `email` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `street` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `housenumber` varchar(32) CHARACTER SET utf8 NOT NULL,
  `city` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `zipcode` varchar(10) CHARACTER SET utf8 NOT NULL,
  `state` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT '0',
  `vat` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `unique_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mime_type` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file`
--

LOCK TABLES `file` WRITE;
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
/*!40000 ALTER TABLE `file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `customer_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `invoice_contact_id` int(11) NOT NULL,
  `price_excl` decimal(10,2) NOT NULL,
  `price_incl` decimal(10,2) NOT NULL,
  `paid` tinyint(4) NOT NULL,
  `expiration_date` datetime NOT NULL,
  `send_reminder_mail` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice`
--

LOCK TABLES `invoice` WRITE;
/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_contact`
--

DROP TABLE IF EXISTS `invoice_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(64) CHARACTER SET utf8 NOT NULL,
  `lastname` varchar(64) CHARACTER SET utf8 NOT NULL,
  `company` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `phone` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `fax` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `mobile` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `street` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `nr` varchar(32) CHARACTER SET utf8 NOT NULL,
  `city` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `zipcode` varchar(10) CHARACTER SET utf8 NOT NULL,
  `state` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT '0',
  `vat` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `export_id` int(11) NOT NULL,
  `vat_bound` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `export_id` (`export_id`),
  KEY `country_id` (`country_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_contact`
--

LOCK TABLES `invoice_contact` WRITE;
/*!40000 ALTER TABLE `invoice_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_item`
--

DROP TABLE IF EXISTS `invoice_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_item`
--

LOCK TABLES `invoice_item` WRITE;
/*!40000 ALTER TABLE `invoice_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name_local` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name_short` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name_ogone` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `name_short` (`name_short`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language`
--

LOCK TABLES `language` WRITE;
/*!40000 ALTER TABLE `language` DISABLE KEYS */;
INSERT INTO `language` VALUES (1,'English','English','en','en_US'),(2,'French','Français','fr','fr_FR'),(3,'Dutch','Nederlands','nl','nl_NL');
/*!40000 ALTER TABLE `language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `classname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(11) NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `classname` (`classname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `picture`
--

DROP TABLE IF EXISTS `picture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `picture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `picture`
--

LOCK TABLES `picture` WRITE;
/*!40000 ALTER TABLE `picture` DISABLE KEYS */;
/*!40000 ALTER TABLE `picture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transfer`
--

DROP TABLE IF EXISTS `transfer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `type` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer`
--

LOCK TABLES `transfer` WRITE;
/*!40000 ALTER TABLE `transfer` DISABLE KEYS */;
/*!40000 ALTER TABLE `transfer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL,
  `date_of_birth` date NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Demo','User','user@example.com','user','12dea96fec20593566ab75692c9949596833adc9',0,'0000-00-00',0,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-07-23 15:54:33
