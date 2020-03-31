<?php
if ( ! defined( 'JOBS_F_URL' ) ) {
	include_once __DIR__ . "/404.php";
	exit();
}

if ( ! function_exists( 'dbDelta' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
}

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$b               = '$b';
$U               = '$U';
$sql             = "CREATE TABLE {$this->Tables['categories']} (
			  id mediumint(6) NOT NULL AUTO_INCREMENT,
			  category varchar(100) NOT NULL UNIQUE,
			  is_active tinyint(1) DEFAULT 1 NOT NULL,
			  primary key (id)
			) {$charset_collate};
			
			CREATE TABLE {$this->Tables['job_ads']} (
			  id int(6) NOT NULL AUTO_INCREMENT,
			  job_category_id mediumint(6) NOT NULL,
			  title varchar(250) NOT NULL,
			  description text NULL,
			  is_active tinyint(1) DEFAULT 1 NOT NULL,
			  expiry_date date NULL,
			  added_time datetime NULL,
			  location varchar(150) NULL,
			  job_type varchar(100) DEFAULT 'Full Time',
			  salary_type varchar(50) DEFAULT 'Monthly',
			  salary varchar(15) DEFAULT '',
			  cc_email varchar(50) NULL,
			  currency_code varchar(100) NULL,
			  views int(6) NULL DEFAULT '0',
			  last_update_time datetime NULL,
			  deleted tinyint(1) NOT NULL DEFAULT '0',
			  deleted_by int(11) NOT NULL DEFAULT '0',
			  deleted_time datetime NULL,
			  primary key (id)
			) {$charset_collate};
			
			
			CREATE TABLE {$this->Tables['job_applications']} (
			  id int(6) NOT NULL AUTO_INCREMENT,
			  ad_id int(6) NOT NULL DEFAULT '0',
			  job_category_name varchar(250) NULL,
			  job_title varchar(250) NOT NULL,
			  applicant_name varchar(250) NOT NULL,
			  applicant_contact varchar(50) NOT NULL,
			  applicant_email varchar(250) NOT NULL,
			  cv_file varchar(250) NULL,
			  applicant_message text NULL,
			  received_time datetime NULL,
			  received_ip varchar(50) NULL,
			  read_by_admin int(11) NULL DEFAULT 0,
			  read_by_admin_time datetime NULL,
			  read_by_admin_ip varchar(100) NULL,
			  status tinyint(1) NULL DEFAULT 0,
			  deleted tinyint(1) NOT NULL DEFAULT '0',
			  deleted_by int(11) NOT NULL DEFAULT '0',
			  deleted_time datetime NULL,
			  primary key (id)
			) {$charset_collate};

			CREATE TABLE {$this->Tables['currencies']} (
			  id int(3) NOT NULL AUTO_INCREMENT,
			  name varchar(100) NOT NULL,
			  code varchar(100) NOT NULL,
			  symbol varchar(100) null,
			  primary key (id)
			) {$charset_collate};			
			";

dbDelta( $sql );

$sql = "REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (1, 'Leke', 'ALL', 'Lek');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (2, 'Dollars', 'USD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (3, 'Afghanis', 'AFN', '؋');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (4, 'Pesos', 'ARS', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (5, 'Guilders', 'AWG', 'ƒ');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (6, 'Dollars', 'AUD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (7, 'New Manats', 'AZN', 'ман');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (8, 'Dollars', 'BSD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (9, 'Dollars', 'BBD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (10, 'Rubles', 'BYR', 'p.');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (11, 'Euro', 'EUR', '€');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (12, 'Dollars', 'BZD', 'BZ$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (13, 'Dollars', 'BMD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (14, 'Bolivianos', 'BOB', '$b');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (15, 'Convertible Marka', 'BAM', 'KM');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (16, 'Pula', 'BWP', 'P');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (17, 'Leva', 'BGN', 'лв');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (18, 'Reais', 'BRL', 'R$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (19, 'Pounds', 'GBP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (20, 'Dollars', 'BND', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (21, 'Riels', 'KHR', '៛');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (22, 'Dollars', 'CAD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (23, 'Dollars', 'KYD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (24, 'Pesos', 'CLP', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (25, 'Yuan Renminbi', 'CNY', '¥');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (26, 'Pesos', 'COP', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (27, 'Colón', 'CRC', '₡');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (28, 'Kuna', 'HRK', 'kn');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (29, 'Pesos', 'CUP', '₱');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (30, 'Koruny', 'CZK', 'Kč');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (31, 'Kroner', 'DKK', 'kr');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (32, 'Pesos', 'DOP ', 'RD$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (33, 'Dollars', 'XCD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (34, 'Pounds', 'EGP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (35, 'Colones', 'SVC', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (36, 'Pounds', 'FKP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (37, 'Dollars', 'FJD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (38, 'Cedis', 'GHC', '¢');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (39, 'Pounds', 'GIP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (40, 'Quetzales', 'GTQ', 'Q');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (41, 'Pounds', 'GGP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (42, 'Dollars', 'GYD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (43, 'Lempiras', 'HNL', 'L');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (44, 'Dollars', 'HKD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (45, 'Forint', 'HUF', 'Ft');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (46, 'Kronur', 'ISK', 'kr');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (47, 'Rupees', 'INR', 'Rp');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (48, 'Rupiahs', 'IDR', 'Rp');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (49, 'Rials', 'IRR', '﷼');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (50, 'Pounds', 'IMP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (51, 'New Shekels', 'ILS', '₪');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (52, 'Dollars', 'JMD', 'J$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (53, 'Yen', 'JPY', '¥');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (54, 'Pounds', 'JEP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (55, 'Tenge', 'KZT', 'лв');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (56, 'Won', 'KPW', '₩');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (57, 'Won', 'KRW', '₩');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (58, 'Soms', 'KGS', 'лв');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (59, 'Kips', 'LAK', '₭');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (60, 'Lati', 'LVL', 'Ls');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (61, 'Pounds', 'LBP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (62, 'Dollars', 'LRD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (63, 'Switzerland Francs', 'CHF', 'CHF');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (64, 'Litai', 'LTL', 'Lt');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (65, 'Denars', 'MKD', 'ден');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (66, 'Ringgits', 'MYR', 'RM');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (67, 'Rupees', 'MUR', '₨');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (68, 'Pesos', 'MXN', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (69, 'Tugriks', 'MNT', '₮');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (70, 'Meticais', 'MZN', 'MT');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (71, 'Dollars', 'NAD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (72, 'Rupees', 'NPR', '₨');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (73, 'Guilders', 'ANG', 'ƒ');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (74, 'Dollars', 'NZD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (75, 'Cordobas', 'NIO', 'C$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (76, 'Nairas', 'NGN', '₦');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (77, 'Krone', 'NOK', 'kr');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (78, 'Rials', 'OMR', '﷼');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (79, 'Rupees', 'PKR', '₨');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (80, 'Balboa', 'PAB', 'B/.');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (81, 'Guarani', 'PYG', 'Gs');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (82, 'Nuevos Soles', 'PEN', 'S/.');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (83, 'Pesos', 'PHP', 'Php');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (84, 'Zlotych', 'PLN', 'zł');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (85, 'Rials', 'QAR', '﷼');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (86, 'New Lei', 'RON', 'lei');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (87, 'Rubles', 'RUB', 'руб');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (88, 'Pounds', 'SHP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (89, 'Riyals', 'SAR', '﷼');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (90, 'Dinars', 'RSD', 'Дин.');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (91, 'Rupees', 'SCR', '₨');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (92, 'Dollars', 'SGD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (93, 'Dollars', 'SBD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (94, 'Shillings', 'SOS', 'S');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (95, 'Rand', 'ZAR', 'R');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (96, 'Rupees', 'LKR', '₨');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (97, 'Kronor', 'SEK', 'kr');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (98, 'Dollars', 'SRD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (99, 'Pounds', 'SYP', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (100, 'New Dollars', 'TWD', 'NT$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (101, 'Baht', 'THB', '฿');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (102, 'Dollars', 'TTD', 'TT$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (103, 'Lira', 'TRY', 'TL');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (104, 'Liras', 'TRL', '£');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (105, 'Dollars', 'TVD', '$');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (106, 'Hryvnia', 'UAH', '₴');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (107, 'Pesos', 'UYU', '$U');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (108, 'Sums', 'UZS', 'лв');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (109, 'Bolivares Fuertes', 'VEF', 'Bs');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (110, 'Dong', 'VND', '₫');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (111, 'Rials', 'YER', '﷼');
			REPLACE INTO {$this->Tables['currencies']} (id, name, code, symbol) VALUES (112, 'Zimbabwe Dollars', 'ZWD', 'Z$')";

$sql = explode( ";", $sql );
foreach ( $sql as $Q ) {
	$this->DB2->rawQuery( $Q );
}

$this->set_option( 'db_version', self::CurrentDBVersion );