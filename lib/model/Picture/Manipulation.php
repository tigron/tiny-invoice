<?php
/**
 * Picture_Manipulation class
 *
 * Manipulates pictures
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Picture_Manipulation {

	/**
	 * Contains image resource
	 *
	 * @access private
	 * @var Resource
	 */
	private $image = '';

	/**
	 * Contains resized image resource
	 *
	 * @access private
	 * @var Resource
	 */
	private $image_resized = '';

	/**
	 * Contains mime_type
	 *
	 * @access private
	 * @var string
	 */
	private $mime_type = '';

	/**
	 * Contains width
	 *
	 * @access private
	 * @var int
	 */
	private $width;

	/**
	 * Contains height
	 *
	 * @access private
	 * @var int
	 */
	private $height;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($file) {
		$this->load($file);
	}

	/**
	 * Load image
	 *
	 * @access private
	 * @param Picture $picture
	 */
	private function load(Picture $picture) {
		$this->width = $picture->width;
		$this->height = $picture->height;
		$this->mime_type = $picture->mime_type;
		$this->image = $this->open($picture->get_path());
	}

	/**
	 * Open image
	 *
	 * @access private
	 * @return Resource $img
	 */
	private function open($path) {
		switch ($this->mime_type) {
			case 'image/jpeg':
				$img = imagecreatefromjpeg($path);
				break;
			case 'image/gif':
				$img = imagecreatefromgif($path);
				break;
			case 'image/png':
				$img = imagecreatefrompng($path);
				break;
			default:
				$img = false;
		}

		return $img;
	}

	/**
	 * Resize image
	 *
	 * @access public
	 * @param int $new_width (px)
	 * @param int $new_height (px)
	 * @proportional bool $proportional
	 */
	public function output($destination = null, $quality = 100) {

		switch ($this->mime_type) {
			case 'image/jpeg':
				imagejpeg($this->image_resized, $destination, $quality);
				break;
			case 'image/gif':
				imagegif($this->image_resized, $destination);
				break;
			case 'image/png':
				$scale_quality = round(($quality/100) * 9);
				$invert_scale_quality = 9 - $scale_quality;
				imagepng($this->image_resized, $destination, $invert_scale_quality);
			default:
				break;
		}

		imagedestroy($this->image_resized);
	}

	/**
	 * Calculate output dimensions
	 *
	 * @access private
	 * @param int $width (px)
	 * @param int $height (px)
	 * @param int $new_width (px)
	 * @param int $new_height (px)
	 * @return array $output_dimensions
	 */
	private function get_output_dimensions($new_width = null, $new_height = null, $mode) {
		switch ($mode) {
			case 'exact':
				$output_width = $new_width;
				$output_height = $new_height;
				break;
			case 'auto':
				list($output_width, $output_height) = $this->calculate_size($new_width, $new_height);
				break;
			case 'crop':
				list($output_width, $output_height) = $this->calculate_crop_size($new_width, $new_height);
				break;
		}

		return array($output_width, $output_height);
	}

	/**
	 * Calculate output dimensions
	 *
	 * @access private
	 * @param int $new_width
	 * @param int $new_height
	 * @return array $output_dimensions
	 */
	private function calculate_size($new_width, $new_height) {
		$old_aspect_ratio = $this->width / $this->height;

		if (is_null($new_width)) {
			$new_width = round($new_height * $old_aspect_ratio);
		} elseif (is_null($new_height)){
			$new_height = round($new_width / $old_aspect_ratio);
		}

		$new_aspect_ratio = $new_width / $new_height;

		if ($new_width > $this->width AND $new_height > $this->height) {
			$output_width = $this->width;
			$output_height = $this->height;
		} elseif ($new_aspect_ratio == $old_aspect_ratio) {
			$output_width = $new_width;
			$output_height = $new_height;
		} elseif ($new_aspect_ratio < $old_aspect_ratio) {
			$output_height = round($new_width / $old_aspect_ratio);
			$output_width = $new_width;
		} elseif ($new_aspect_ratio > $old_aspect_ratio) {
			$output_width = round($new_height * $old_aspect_ratio);
			$output_height = $new_height;
		}

		return array($output_width, $output_height);
	}

	/**
	 * Calculate output dimensions for cropping
	 *
	 * @access private
	 * @param int $new_width
	 * @param int $new_height
	 * @return array $output_dimensions
	 */
	private function calculate_crop_size($new_width, $new_height) {
		$height_ratio = $this->height / $new_height;
		$width_ratio = $this->width / $new_width;

		if ($height_ratio < $width_ratio) {
			$output_ratio = $height_ratio;
		} else {
			$output_ratio = $width_ratio;
		}

		$output_height = $this->height / $output_ratio;
		$output_width = $this->width / $output_ratio;

		return array($output_width, $output_height);
	}

	/**
	 * Get dimensions
	 *
	 * @access public
	 * @param mixed $path
	 * @return array $dimensions
	 */
	public function get_dimensions($path = null) {
		if (!is_null($path)) {
			$this->path = $path;
		}

		return getimagesize($this->path);
	}

	/**
	 * Get mime types
	 *
	 * @access public
	 * @param mixed $path
	 * @return string $mime_type
	 */
	public function get_mime_type($path = null) {
		if (!is_null($path)) {
			$this->path = $path;
		}

		return Util::file_mime_type($this->path);
	}

	/**
	 * Resize image
	 *
	 * @access public
	 * @param int $new_width (px)
	 * @param int $new_height (px)
	 * @proportional bool $proportional
	 */
	public function resize($new_width = null, $new_height = null, $mode = 'auto') {
		if (is_null($new_width) AND is_null($new_height)) {
			throw new Exception('specifiy output dimensions');
		}

		list($output_width, $output_height) = $this->get_output_dimensions($new_width, $new_height, $mode);

		$this->image_resized = imagecreatetruecolor($output_width, $output_height);

		if ($this->mime_type == 'image/gif' OR $this->mime_type == 'image/png') {
			$transparent_index = imagecolortransparent($this->image);
			if ($transparent_index >= 0) { // GIF
				imagepalettecopy($this->image, $this->image_resized);
				imagefill($this->image_resized, 0, 0, $transparent_index);
				imagecolortransparent($this->image_resized, $transparent_index);
				imagetruecolortopalette($this->image_resized, true, 256);
			} else { // PNG
				imagealphablending($this->image_resized, false);
				imagesavealpha($this->image_resized,true);
				$transparent = imagecolorallocatealpha($this->image_resized, 255, 255, 255, 127);
				imagefilledrectangle($this->image_resized, 0, 0, $output_width, $output_height, $transparent);
			}
		}

		imagecopyresampled($this->image_resized, $this->image, 0, 0, 0, 0, $output_width, $output_height, $this->width, $this->height);

		if ($mode == 'crop') {
			$this->crop($output_width, $output_height, $new_width, $new_height);
		}
	}

	/**
	 * Crop image
	 *
	 * @access private
	 * @param int $output_width (px)
	 * @param int $output_height (px)
	 * @param int $new_width (px)
	 * @param int $new_height (px)
	 */
	private function crop($output_width, $output_height, $new_width, $new_height) {
		$offset_x = ($output_width / 2) - ($new_width / 2);
		$offset_y = ($output_height / 2) - ($new_height / 2);

		$crop = $this->image_resized;
		$this->image_resized = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($this->image_resized, $crop , 0, 0, $offset_x, $offset_y, $output_width, $output_height , $output_width, $output_height);
	}
}
