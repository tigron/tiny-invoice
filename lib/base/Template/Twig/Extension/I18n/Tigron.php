<?php
/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once LIB_PATH . '/base/Template/Twig/Extension/TokenParser/Trans/Tigron.php';
require_once LIB_PATH . '/base/Template/Twig/Extension/Node/Trans/Tigron.php';

class Twig_Extensions_Extension_I18n_Tigron extends Twig_Extensions_Extension_I18n
{

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(new Twig_Extensions_TokenParser_Trans_Tigron());
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
    	$translation_filter = new Twig_SimpleFilter('trans', function (Twig_Environment $env, $string) {
			$globals = $env->getGlobals();
			$translation = $globals['env']['translation'];
			return Translation::translate($string, $translation);
    	}, array('needs_environment' => true));
        return array(
			$translation_filter
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'i18n';
    }
}
