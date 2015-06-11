<?php
/**
 * Additional functions and filters for Twig
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Template_Twig_Extension_Default extends Twig_Extension {

	private $environment;

	/**
	 * Init runtime
	 *
	 * @access public
	 */
	public function initRuntime(Twig_Environment $environment) {
        parent::initRuntime($environment);
        $this->environment = $environment;
    }

	/**
	 * Returns a list of globals
	 *
	 * @return array
	 */
	public function getGlobals() {
		$templates = array(
			'base' => '_default/macro/base.twig',
			'form' => '_default/macro/form.twig',
		);

		$globals = array();
		foreach ($templates as $key => $template) {
			try {
				$loaded_template = $this->environment->loadTemplate($template);
				$globals[$key] = $loaded_template;
			} catch (Twig_Error_Loader $e) { }
		}

		return $globals;
	}

    /**
     * Returns a list of filters
     *
     * @return array
     */
    public function getFilters() {
        return array(
			new Twig_SimpleFilter('print_r', array($this, 'print_r_filter'), array('is_safe' => array('html'))),
			new Twig_SimpleFilter('json_decode', array($this, 'json_decode_filter'), array('is_safe' => array('html'))),
			new Twig_SimpleFilter('serialize', array($this, 'serialize_filter'), array('is_safe' => array('html'))),
			new Twig_SimpleFilter('round', array($this, 'round_filter'), array('is_safe' => array('html'))),
			new Twig_SimpleFilter('date', array($this, 'date_filter'), array('needs_environment' => true, 'is_safe' => array('html'))),
			new Twig_SimpleFilter('datetime', array($this, 'datetime_filter'), array('needs_environment' => true, 'is_safe' => array('html'))),
			new Twig_SimpleFilter('filesize', array($this, 'filesize_filter'), array('needs_environment' => true, 'is_safe' => array('html'))),
        );
	}

	/**
     * Returns a list of functions
     *
     * @return array
     */
	public function getFunctions() {
		return array(
			new Twig_SimpleFunction('strpos', array($this, 'strpos_function'), array('is_safe' => array('html'))),
			new Twig_SimpleFunction('math_add', array($this, 'math_add_function'), array('is_safe' => array('html'))),
			new Twig_SimpleFunction('math_sub', array($this, 'math_sub_function'), array('is_safe' => array('html'))),
			new Twig_SimpleFunction('math_mul', array($this, 'math_mul_function'), array('is_safe' => array('html'))),
		);
	}

	/**
	 * Filter print_r
	 *
	 * @param mixed $value
	 * @param bool $raw
	 * @return string $output
	 */
	public function print_r_filter($value, $raw = true) {
		$output = '';
		if ($raw === false) {
			$output = '<pre>';
		}

		$output .= print_r($value, true);

		if ($raw === false) {
			$output .= '</pre>';
		}

	    return $output;
	}

	/**
	 * Filter serialize
	 *
	 * @param mixed $value
	 * @return string $output
	 */
	public function serialize_filter($value) {
	    return serialize($value);
	}

	/**
	 * Filter round
	 *
	 * @param float $value
	 * @param int $decimal
	 * @return int $output
	 */
	public function round_filter($value, $decimal=0) {
		return round($value, $decimal);
	}

	/**
	 * JSON decode
	 *
	 * @param string $json
	 * @return array $array
	 */
	public function json_decode_filter($json) {
		return json_decode($json);
	}

	/**
	 * Filter date
	 *
	 * @param string $date
	 * @param string $format
	 * @return string $output
	 */
	public function date_filter(Twig_Environment $env, $date, $format = 'd/m/Y') {
		return twig_date_format_filter($env, $date, $format);
	}

	/**
	 * Filter datetime
	 *
	 * @param string $datetime
	 * @param string $format
	 * @return string $output
	 */
	public function datetime_filter(Twig_Environment $env, $datetime, $format = 'd/m/Y H:i:s') {
		return twig_date_format_filter($env, $datetime, $format);
	}

	/**
	 * Filesize filter
	 *
	 * @param int $filesize
	 * @return string $output
	 */
	public function filesize_filter(Twig_Environment $env, $filesize) {
		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'b';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Kb';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Mb';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Gb';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Tb';
		} else {
			return 'unknown';
		}
	}

	/**
	 * Function strpos
	 *
	 * @param mixed $value
	 * @param string $to_search
	 * @return mixed $output
	 */
	public function strpos_function($value, $to_search) {
	    return strpos($value, $to_search);
	}

	/**
	 * Function math add
	 *
	 * @param float $value
	 * @param float $value2
	 * @return float $sum
	 */
	public function math_add_function($value, $value2) {
		return Util::math_add($value, $value2);
	}

	/**
	 * Function math sub
	 *
	 * @param float $value
	 * @param float $value2
	 * @return float $diff
	 */
	public function math_sub_function($value, $value2) {
		return Util::math_sub($value, $value2);
	}

	/**
	 * Function math multiply
	 *
	 * @param float $value
	 * @param float $value2
	 * @return float $product
	 */
	public function math_mul_function($value, $value2) {
		return Util::math_mul($value, $value2);
	}

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName() {
        return 'Default';
    }
}