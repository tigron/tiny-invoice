<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Util_Picture {

	/**
	 * convert EPS to PNG
	 *
	 * @access public
	 * @param File $eps
	 * @return Picture $png
	 */
	public static function eps_to_png(File $eps) {
		$eps_path = $eps->get_path();
		$pathinfo = pathinfo($eps_path);
		$png_name = $pathinfo['filename'] . '.png';
		system('convert -colorspace rgb -resize "600>x10000" "' . $eps_path . '" "' . TMP_PATH . '/' . $png_name . '"');
		$picture = File_Store::store($png_name, file_get_contents(TMP_PATH . '/' . $png_name));
		unlink(TMP_PATH . '/' . $png_name);
		return $picture;
	}

}