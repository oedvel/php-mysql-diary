/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`diary` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `diary`;

/*Table structure for table `diary_auth_tokens` */

CREATE TABLE `diary_auth_tokens` (
  `fld_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fld_selector` char(12) DEFAULT NULL,
  `fld_token` char(64) DEFAULT NULL,
  `fld_userid` int(11) NOT NULL,
  `fld_expiration_date` datetime DEFAULT NULL,
  `fld_creation_date` datetime DEFAULT NULL,
  `fld_tag` char(3) NOT NULL,
  PRIMARY KEY (`fld_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `diary_categories` */

CREATE TABLE `diary_categories` (
  `fld_id` int(11) NOT NULL AUTO_INCREMENT,
  `fld_cat` varchar(255) DEFAULT NULL,
  `fld_creation_date` datetime DEFAULT NULL,
  `fld_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`fld_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `diary_categories` */

insert  into `diary_categories`(`fld_id`,`fld_cat`,`fld_creation_date`,`fld_update_date`) values 
(1,'Everyday','2024-01-31 08:03:39',NULL);

/*Table structure for table `diary_days` */

CREATE TABLE `diary_days` (
  `fld_id` int(11) NOT NULL AUTO_INCREMENT,
  `fld_cat_id` int(11) DEFAULT NULL,
  `fld_content` longtext,
  `fld_date` date NOT NULL,
  `fld_creation_date` datetime DEFAULT NULL,
  `fld_update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`fld_id`),
  KEY `CATEGORY_ID` (`fld_cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `diary_users` */

CREATE TABLE `diary_users` (
  `fld_id` int(11) NOT NULL AUTO_INCREMENT,
  `fld_username` varchar(255) DEFAULT NULL,
  `fld_pwd` varchar(255) DEFAULT NULL,
  `fld_role` varchar(255) DEFAULT NULL,
  `fld_creation_date` datetime DEFAULT NULL,
  `fld_update_date` datetime DEFAULT NULL,
  `fld_password_reset_date` datetime DEFAULT NULL,
  PRIMARY KEY (`fld_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
