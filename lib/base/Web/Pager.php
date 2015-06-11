<?php
/**
 * Handles paginating of query results
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

class Web_Pager {
	/**
	 * Classname
	 *
	 * @access private
	 * @var string $classname
	 */
	private $classname;

	/**
	 * Options
	 *
	 * @access private
	 * @var array $options
	 */
	private $options = [
		'conditions' => [],
		'direction' => 'asc',
		'page' => 1,
		'jump_to' => false,
		'joins' => [],
		'limit' => null
	];

	/**
	 * Items
	 *
	 * @access public
	 * @var array $items
	 */
	public $items = [];

	/**
	 * Item count
	 *
	 * @access public
	 * @var int $item_count
	 */
	public $item_count = 0;

	/**
	 * Interval
	 *
	 * @access private
	 * @var int $interval
	 */
	private $interval = 5;

	/**
	 * Link: a html string with pager links
	 *
	 * @access public
	 * @var string $link
	 */
	public $links;

	/**
	 * Constructor
	 *
	 * @access private
	 * @param $code
	 */
	public function __construct($classname = null) {
		if ($classname === null) {
			throw new Exception('You must provide a classname');
		}
		$this->classname = $classname;
		if (file_exists(LIB_PATH . '/' . ucfirst($this->classname) . '.php')) {
			require_once LIB_PATH . '/' . ucfirst($this->classname) . '.php';
		}
	}

	/**
	 * Set sort field
	 *
	 * @access public
	 * @param string $sort
	 */
	public function set_sort($sort) {
		$this->options['sort'] = $sort;
	}

	/**
	 * Set direction
	 *
	 * @access public
	 * @param string $direction
	 */
	public function set_direction($direction = 'asc') {
		$this->options['direction'] = $direction;
	}

	/**
	 * Set sort_permissions
	 *
	 * @access public
	 * @param array $sort_permissions
	 */
	public function set_sort_permissions($sort_permissions) {
		$this->options['sort_permissions'] = $sort_permissions;
	}

	/**
	 * Set page
	 *
	 * @access public
	 * @param int $page
	 */
	public function set_page($page) {
		$this->options['page'] = $page;
	}

	/**
	 * Set limit
	 *
	 * @access public
	 * @param int $limit
	 */
	public function set_limit($limit) {
		$this->options['limit'] = $limit;
	}

	/**
	 * Set condition
	 *
	 * @access public
	 * @param string $field
	 * @param string $comparison (optional)
	 * @param string $value
	 */
	public function set_condition() {
		$params = func_get_args();
		$conditions = $this->options['conditions'];

		$field = array_shift($params);

		if (count($params) == 1) {
			$conditions[$field] = array('=', array_shift($params));
		} else {
			$conditions[$field] = array( array_shift($params), array_shift($params));
		}

		$this->options['conditions'] = $conditions;
	}

	/**
	 * Add join
	 *
	 * @access public
	 * @param string $table
	 * @param string $table_with
	 */
	public function add_join() {
		$params = func_get_args();
		$this->options['joins'][] = $params;
	}

	/**
	 * Activate 'Jump to page'
	 *
	 * @access public
	 * @param bool $jump_to
	 */
	public function set_jump_to($jump_to) {
		$this->options['jump_to'] = $jump_to;
	}

	/**
	 * Set a search
	 *
	 * @access public
	 * @param string $search
	 */
	public function set_search($search) {
		$this->options['conditions']['%search%'] = $search;
	}

	/**
	 * Get search
	 *
	 * @access public
	 * @return string $search
	 */
	public function get_search() {
		if (isset($this->options['conditions']['%search%'])) {
			return $this->options['conditions']['%search%'];
		} else {
			return '';
		}
	}

	/**
	 * Clear conditions
	 *
	 * @access public
	 */
	public function clear_conditions() {
		unset($this->options['conditions']);
		$this->options['conditions'] = array();
	}

	/**
	 * Clear condition
	 *
	 * @access public
	 * @param string $key
	 */
	public function clear_condition($key) {
		unset($this->options['conditions'][$key]);
	}

	/**
	 * Get conditions
	 *
	 * @return array $conditions
	 */
	public function get_conditions() {
		return $this->options['conditions'];
	}

	/**
	 * Create the header cells of the paged table
	 *
	 * @param string $header Name of the header
	 * @param string $field_name Name of the database field that is represented here
	 * @return string $output
	 * @access public
	 */
	public function create_header($header, $field_name) {
		if ($this->options['sort'] == $field_name) {
			if ($this->options['direction'] == 'asc') {
				$direction = 'desc';
			} else {
				$direction = 'asc';
			}
		} else {
			$direction = 'asc';
		}
		$hash = $this->create_options_hash($this->options['conditions'], $this->options['page'], $field_name, $direction, $this->options['joins']);

		$output = '<a href="' . $_SERVER['REDIRECT_URL'] . '?q=' . $hash . '">';
		$output .= $header . ' ';

		if ($this->options['sort'] == $field_name) {
			if ($direction == 'desc') {
				$output .= '<span class="glyphicon glyphicon-chevron-up"></span>';
			} else {
				$output .= '<span class="glyphicon glyphicon-chevron-down"></span>';
			}
		}
		$output .= '</a>';

		return $output;
	}

	/**
	 * Paginate the results
	 *
	 * @access private
	 */
	public function page($all = false) {
		$qry_str = $_SERVER['QUERY_STRING'];
		parse_str($qry_str, $qry_str_parts);
		unset($qry_str_parts['p']);
		unset($qry_str_parts['q']);
		$request_uri = base64_encode($_SERVER['REDIRECT_URL'] . '?' . implode('&', $qry_str_parts));

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			if (!isset($_GET['q']) AND isset($_SESSION[Application::get()->name]['pager'][$request_uri]) AND Application::Get()->config->sticky_pager) {
				$this->options = array_merge($this->options, $this->get_options_from_hash($_SESSION[Application::get()->name]['pager'][$request_uri]));
			} elseif (isset($_GET['q'])) {
				$this->options = array_merge($this->options, $this->get_options_from_hash($_GET['q']));
			}
		}

		if (!isset($this->options['sort']) ) {
			if (isset($this->options['sort_permissions']) and count($this->options['sort_permissions']) > 0) {
				$this->options['sort'] = key($this->options['sort_permissions']);
			} else {
				$this->options['sort'] = 1;
			}
		}

		if (isset($_GET['p'])) {
			$this->set_page($_GET['p']);
		}

		/**
		 * We now have to rewrite the sort according to the permissions
		 */
		if (!isset($this->options['sort_permissions'][$this->options['sort']])) {
			throw new Exception('Sorting not allowed for field ' . $this->options['sort']);
		}
		$sort = $this->options['sort_permissions'][$this->options['sort']];

		$this->options['all'] = $all;

		$params = array(
			$sort,
			$this->options['direction'],
			$this->options['page'],
			$this->options['limit'],
			$this->options['conditions'],
			$this->options['all'],
			$this->options['joins']
		);

		$this->items = call_user_func_array(array($this->classname, 'get_paged'), $params);
		$this->item_count = call_user_func_array(array($this->classname, 'count'), array($this->options['conditions'], $this->options['joins']));
		$this->generate_links();

		$hash = $this->create_options_hash($this->options['conditions'], $this->options['page'], $this->options['sort'], $this->options['direction'], $this->options['joins']);
		$_SESSION[Application::Get()->name]['pager'][$request_uri] = $hash;
	}

	/**
	 * Get the pager options from a hash
	 *
	 * @access private
	 * @param string $hash
	 * @return array $options
	 */
	private function get_options_from_hash($hash) {
		return unserialize( base64_decode($hash) );
	}

	/**
	 * Create options hash
	 *
	 * @access private
	 * @param array $conditions
	 * @param int $page
	 * @param int $sort
	 * @param array $joins
	 * @param string $direction
	 */
	private function create_options_hash($conditions, $page, $sort, $direction, $joins) {
		$options = [
			'conditions' => $conditions,
			'page' => $page,
			'sort' => $sort,
			'direction' => $direction,
			'joins' => $joins,
		];

		return urlencode(base64_encode(serialize($options)));
	}

	/**
	 * Create page link
	 *
	 * @access private
	 * @param int $page
	 * @param string $url
	 * @param bool $active
	 */
	private function create_page_link($page, $url, $active = false) {
		$link = '<li';
		$class = [];
		if ($active) {
			$class[] = 'active';
		}
		if (is_numeric($page) AND $page < 10) {
			$class[] = 'single';
		}
		if (count($class) > 0) {
			$link.= ' class="' . implode(' ', $class) . '"';
		}
		$link .= '>';
		$link .= '<a href="' . $url . '">' . $page . '</a>';
		return $link;
	}

	/**
	 * Generate the necessary links to navigate the paged result
	 *
	 * @access private
	 */
	private function generate_links() {
		$config = Config::Get();
		$items_per_page = $config->items_per_page;
		if ($items_per_page == 0) {
			$pages = 0;
		} else {
			$pages = ceil($this->item_count / $items_per_page);
		}

		// Don't make links if there is only one page
		if ($pages == 1) {
			$this->links = '';
			return;
		}

		$str_links = '';
		$links = [];
		if ($this->options['page'] > 1) {
			$links[] = '-1';
		}

		for ($i = 1; $i <= $pages; $i++) {
			$print = false;

			// Display the first two pages
			if ($i < 2) {
				$print = true;
			}

			// Display the two pages before and after the current one
			if ($i >= $this->options['page']-2 AND $i <= $this->options['page']+2) {
				$print = true;
			}

			// Make sure at least 9 pages are printed all the time
			if (($this->options['page'] < 5 AND $i <= 7) OR ($this->options['page'] > $pages-5 AND $i >= $pages-6)) {
				$print = true;
			}

			// Display the last two pages
			if ($i > $pages-1) {
				$print = true;
			}

			if ($print === true) {
				if (end($links) > 0 AND end($links)+1 != $i) {
					$links[] = '...';
				}

				$links[] = $i;
				$previous_print = $i;
			}
		}

		if ($this->options['page'] < $pages) {
			$links[] = '+1';
		}

		foreach ($links as $key => $link) {
			if ($link === '-1') {
				$number = $this->options['page']-1;
				$text = '&laquo;';
				$active = false;
			} elseif ($link === '+1') {
				$number = $this->options['page']+1;
				$text = '&raquo;';
				$active = false;
			} elseif ($link == $this->options['page']) {
				$number = $link;
				$text = $link;
				$active = true;
			} elseif ($link == '...') {
				continue;
			} elseif (is_numeric($link)) {
				$number = $link;
				$text = $link;
				$active = false;
			}

			if ($text == '&raquo;' AND $this->options['jump_to']) {
				$str_links .= '<li><span class="jump-to-page"><input type="text" size="4" placeholder="#" id="jump-to-page-' . str_replace('_', '-', strtolower($this->classname)) . '"></span></li>';
			}

			$hash = $this->create_options_hash($this->options['conditions'], $number, $this->options['sort'], $this->options['direction'], $this->options['joins']);

			$qry_str = $url = '';
			if (isset($_SERVER['QUERY_STRING'])) {
				$qry_str = $_SERVER['QUERY_STRING'];
			}
			parse_str($qry_str, $qry_str_parts);
			$qry_str_parts['q'] = $hash;
			if (isset($qry_str_parts['p'])) {
				unset($qry_str_parts['p']);
			}
			if (isset($_SERVER['REDIRECT_URL'])) {
				$url = $_SERVER['REDIRECT_URL'];
			}
			$url .= '?' . http_build_query($qry_str_parts);
			$str_links .= $this->create_page_link($text, $url, $active);
			if ($key+1 == count($links) AND $text != '&raquo;' AND $this->options['jump_to']) {
				$str_links .= '<li><span class="jump-to-page"><input type="text" size="4" placeholder="#" id="jump-to-page-' . str_replace('_', '-', strtolower($this->classname)) . '"></span></li>';
			}
		}

		$content = '<ul class="pagination pagination-centered" id="pager-' . str_replace('_', '-', strtolower($this->classname)) . '">' . $str_links . '</ul>';
		$this->links = $content;
	}
}
