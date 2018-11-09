<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20181025_115250_Invoice_vat extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query('
			DROP TABLE IF EXISTS `vat_rate`;
		');

		$db->query('
			CREATE TABLE `vat_rate` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		');

		$db->query("
			INSERT INTO `vat_rate` (`id`, `name`) VALUES
			(1,	'Standard'),
			(2,	'Reduced A'),
			(3,	'Reduced B'),
			(4,	'Super-reduced'),
			(5,	'Parking');
		");

		$db->query("
			DROP TABLE IF EXISTS `vat_rate_country`;
		");

		$db->query("
			CREATE TABLE `vat_rate_country` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `vat_rate_id` int(11) NOT NULL,
			  `country_id` int(11) NOT NULL,
			  `vat` decimal(10,2) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");

		$db->query("
			INSERT INTO `vat_rate_country` (`id`, `vat_rate_id`, `country_id`, `vat`) VALUES
			(1,	1,	203,	21.00),
			(2,	2,	203,	6.00),
			(3,	3,	203,	12.00),
			(4,	5,	203,	12.00),
			(5,	1,	161,	20.00),
			(6,	2,	161,	9.00),
			(7,	1,	162,	21.00),
			(8,	2,	162,	10.00),
			(9,	3,	162,	15.00),
			(10,	1,	171,	25.00),
			(11,	1,	205,	19.00),
			(12,	2,	205,	7.00),
			(13,	1,	172,	20.00),
			(14,	2,	172,	9.00),
			(15,	1,	177,	23.00),
			(16,	2,	177,	9.00),
			(17,	3,	177,	13.50),
			(18,	4,	177,	4.80),
			(19,	5,	177,	13.50),
			(20,	1,	191,	24.00),
			(21,	2,	191,	6.00),
			(22,	3,	191,	13.00),
			(23,	1,	201,	21.00),
			(24,	2,	201,	10.00),
			(25,	4,	201,	4.00),
			(26,	1,	204,	20.00),
			(27,	2,	204,	5.50),
			(28,	3,	204,	10.00),
			(29,	4,	204,	2.10),
			(30,	1,	189,	25.00),
			(31,	2,	189,	5.00),
			(32,	3,	189,	13.00),
			(33,	1,	193,	22.00),
			(34,	2,	193,	5.00),
			(35,	3,	193,	10.00),
			(36,	4,	193,	4.00),
			(37,	1,	145,	19.00),
			(38,	2,	145,	5.00),
			(39,	3,	145,	9.00),
			(40,	1,	179,	21.00),
			(41,	2,	179,	12.00),
			(42,	1,	180,	21.00),
			(43,	2,	180,	5.00),
			(44,	3,	180,	9.00),
			(45,	1,	207,	17.00),
			(46,	2,	207,	8.00),
			(47,	4,	207,	3.00),
			(48,	5,	207,	14.00),
			(49,	1,	163,	27.00),
			(50,	2,	163,	5.00),
			(51,	3,	163,	18.00),
			(52,	1,	195,	18.00),
			(53,	2,	195,	5.00),
			(54,	3,	195,	7.00),
			(55,	1,	209,	21.00),
			(56,	2,	209,	6.00),
			(57,	1,	202,	20.00),
			(58,	2,	202,	10.00),
			(59,	3,	202,	13.00),
			(60,	5,	202,	13.00),
			(61,	1,	165,	23.00),
			(62,	2,	165,	5.00),
			(63,	3,	165,	8.00),
			(64,	1,	197,	23.00),
			(65,	2,	197,	6.00),
			(66,	3,	197,	13.00),
			(67,	5,	197,	13.00),
			(68,	1,	166,	19.00),
			(69,	2,	166,	5.00),
			(70,	3,	166,	9.00),
			(71,	1,	200,	22.00),
			(72,	2,	200,	9.50),
			(73,	1,	168,	20.00),
			(74,	2,	168,	10.00),
			(75,	1,	174,	24.00),
			(76,	2,	174,	10.00),
			(77,	3,	174,	14.00),
			(78,	1,	184,	25.00),
			(79,	2,	184,	6.00),
			(80,	3,	184,	12.00),
			(81,	1,	185,	20.00),
			(82,	2,	185,	5.00);
		");

		$db->query('
			CREATE TABLE `invoice_vat` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `invoice_id` int(11) NOT NULL,
			  `vat_rate_id` int(11) NOT NULL,
			  `rate` decimal(10,2) NOT NULL,
			  `base` decimal(10,2) NOT NULL,
			  `vat` decimal(10,2) NOT NULL,
			  FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`),
			  FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rate` (`id`)
			);
		');

		$db->query('
			CREATE TABLE `creditnote_vat` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `creditnote_id` int(11) NOT NULL,
			  `vat_rate_id` int(11) NOT NULL,
			  `rate` decimal(10,2) NOT NULL,
			  `base` decimal(10,2) NOT NULL,
			  `vat` decimal(10,2) NOT NULL,
			  PRIMARY KEY (`id`),
			  FOREIGN KEY (`creditnote_id`) REFERENCES `creditnote` (`id`),
			  FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rate` (`id`)
			);
		');

		$db->query("
			ALTER TABLE `invoice`
			ADD `vat_mode` enum('line','group') NOT NULL DEFAULT 'group' AFTER `price_incl`;
		");

		$db->query("
			ALTER TABLE `creditnote`
			ADD `vat_mode` enum('line','group') NOT NULL DEFAULT 'group' AFTER `price_incl`;
		");

		$db->query("
			ALTER TABLE `invoice_item`
			ADD `vat_rate_id` int(11) NULL AFTER `product_type_id`,
			CHANGE `price` `price_excl` decimal(10,2) NOT NULL AFTER `qty`,
			ADD `price_incl` decimal(10,2) NOT NULL AFTER `price_excl`,
			CHANGE `vat` `vat_rate_value` decimal(10,2) NOT NULL AFTER `price_incl`,
			ADD FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rate` (`id`);
		");

		$db->query("
			ALTER TABLE `creditnote_item`
			ADD `vat_rate_id` int(11) NOT NULL AFTER `product_type_id`,
			CHANGE `price` `price_excl` decimal(10,2) NOT NULL AFTER `qty`,
			ADD `price_incl` decimal(10,2) NOT NULL AFTER `price_excl`,
			CHANGE `vat` `vat_rate_value` decimal(10,2) NOT NULL AFTER `price_incl`,
			ADD FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rate` (`id`);
		");

		// We're assuming none of the VAT rates have changed, ever
		$invoice_items = Invoice_Item::get_all();
		foreach ($invoice_items as $invoice_item) {
			if ($invoice_item->vat_rate_value > 0) {
				$vat_rate_ids = $db->get_column('SELECT vat_rate_id FROM vat_rate_country WHERE country_id = ? AND vat = ?', [$invoice_item->invoice->customer_contact->country_id, $invoice_item->vat_rate_value]);

				$vat_rate_id = $vat_rate_ids[0];

				$vat_rate = Vat_Rate::get_by_id($vat_rate_id);

				$invoice_item->vat_rate = $vat_rate;
				$invoice_item->save();
			}
		}

		$creditnote_items = Creditnote_Item::get_all();
		foreach ($creditnote_items as $creditnote_item) {
			if ($creditnote_item->vat_rate_value > 0) {
				$vat_rate_ids = $db->get_column('SELECT vat_rate_id FROM vat_rate_country WHERE country_id = ? AND vat = ?', [$creditnote_item->creditnote->customer_contact->country_id, $creditnote_item->vat_rate_value]);

				$vat_rate_id = $vat_rate_ids[0];

				$vat_rate = Vat_Rate::get_by_id($vat_rate_id);

				$creditnote_item->vat_rate = $vat_rate;
				$creditnote_item->save();
			}
		}

		$invoices = Invoice::get_all();
		foreach ($invoices as $invoice) {
			$invoice->generate_invoice_vat();
		}

		$creditnotes = Creditnote::get_all();
		foreach ($creditnotes as $creditnote) {
			$creditnote->generate_creditnote_vat();
		}
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
