<?php
/**
 * Additional functions and filters for Twig
 *
 * @author Jochen Timmermans <jochen@tigron.be>
 */

class Template_Twig_Extension_Custom extends \Twig\Extension\AbstractExtension {

	/**
	 * Returns a list of functions
	 *
	 * @return array
	 */
	public function getFunctions() {
		return [];
	}

	/**
	 * Returns a list of filters
	 *
	 * @return array
	 */
	public function getFilters() {
		return [
			new \Twig\TwigFilter('truncate', [ $this, 'truncate' ])
		];
	}

	/**
	 * Truncate text
	 *
	 * @access public
	 * @param  string $string
	 * @param  int    $length
	 * @return string $string
	 */
	public function truncate(string $string, int $length = 180) {
		return substr($string, 0, $length);
	}

}
