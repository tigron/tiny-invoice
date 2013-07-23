<?php
/**
 * Handles paginating of query results
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
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
	private $options = array();

	/**
	 * Items
	 *
	 * @access public
	 * @var array $items
	 */
	public $items = array();

	/**
	 * Interval
	 *
	 * @access private
	 * @var int $interval
	 */
	private $interval = 5;

	/**
	 * Constructor
	 *
	 * @access private
	 * @param $code
	 */
	public function __construct($classname = null, $sort = 'id', $direction = 'ASC', $page = 1, $extra_conditions = array(), $all = false) {
		if ($classname === null) {
			throw new Exception('You must provide a classname');
		}
		$this->classname = $classname;
		if (file_exists(LIB_PATH . '/' . ucfirst($this->classname) . '.php')) {
			require_once LIB_PATH . '/' . ucfirst($this->classname) . '.php';
		}
		if (isset($_GET['sort'])) {
			$this->options['sort'] = $_GET['sort'];
		} else {
			$this->options['sort'] = $sort;
		}

		if (isset($_GET['direction'])) {
			$this->options['direction'] = $_GET['direction'];
		} else {
			$this->options['direction'] = $direction;
		}

		if (isset($_GET['page'])) {
			$this->options['page'] = $_GET['page'];
		} else {
			$this->options['page'] = $page;
		}

		$this->options['all'] = $all;

		if (isset($_GET['extra_conditions']) and count($extra_conditions) == 0) {
			$this->options['extra_conditions'] = unserialize(base64_decode(urldecode($_GET['extra_conditions'])));
		} else {
			$this->options['extra_conditions'] = $extra_conditions;
		}
		$this->page();
	}

	/**
	 * Get search
	 *
	 * @access public
	 * @return string $search
	 */
	public function get_search() {
		if (isset($this->options['extra_conditions']['%search%'])) {
			return $this->options['extra_conditions']['%search%'];
		} else {
			return '';
		}
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
		if ($this->options['direction'] == 'ASC') {
			$direction = 'DESC';
		} else {
			$direction = 'ASC';
		}

		if (isset($_GET['search'])) {
			$search = '&search=' . $_GET['search'];
		} else {
			$search = '';
		}

		return '<a href="' . $_SERVER['REDIRECT_URL'] . '?page=' . $this->options['page'] . $search . '&sort=' . $field_name .'&direction=' . $direction .'&extra_conditions=' . base64_encode(serialize($this->options['extra_conditions'])) . '">' . $header . '</a>';
		$output = '<a href="">' . $header . '</a>';

		return $output;
	}

	/**
	 * Paginate the results
	 *
	 * @access private
	 */
	private function page() {
		$this->items = call_user_func_array(array($this->classname, 'get_paged'), array($this->options['sort'], $this->options['direction'], $this->options['page'], $this->options['extra_conditions'], $this->options['all']));
		$this->generate_links();
	}

	/**
	 * Generate the necessary links to navigate the paged result
	 *
	 * @access private
	 */
	private function generate_links() {
		$count = call_user_func_array(array($this->classname, 'count'), array($this->options['extra_conditions']));
		$config = Config::Get();
		$items_per_page = $config->items_per_page;
		if ($items_per_page == 0) {
			$pages = 0;
		} else {
			$pages = ceil($count / $items_per_page);
		}
		// Don't make links if there is only one page
		if ($pages == 1) {
			$this->links = '';
			return;
		}

		$first_page_link = $this->options['page'] - $this->interval;
		if ($first_page_link < 0) {
			$to_end = $first_page_link * (-1);
		} else {
			$to_end = 0;
		}

		if (isset($_GET['search'])) {
			$search = '&search=' . $_GET['search'];
		} else {
			$search = '';
		}

		if ($first_page_link < 1) {
			$first_page_link = 1;
		}
		if ($this->options['page'] >= $pages) {
			$last_page_link = $pages;
		} else {
			$last_page_link = $this->options['page'] + $this->interval;
		}
		$last_page_link += $to_end;
		if ($last_page_link >= $pages) {
			$last_page_link = $pages;
		}

		$links = '';
		for ($i = $first_page_link; $i<=$last_page_link; $i++) {
			if ($i == $this->options['page']) {
				$links .= '<li class="active"><a href="#">' . $i . '</a></li>' ;
			} else {
				$links .= '<li><a href="' . $_SERVER['REDIRECT_URL'] . '?page=' . $i . '&sort=' . $this->options['sort'] . $search . '&direction=' . $this->options['direction'] . '&extra_conditions=' . urlencode(base64_encode(serialize($this->options['extra_conditions']))) . '">' . $i . '</a></li>';
			}
		}
		if ($first_page_link > 1) {
			$links = '<li><a href="' . $_SERVER['REDIRECT_URL'] . '?page=1&sort=' . $this->options['sort'] . $search . '&direction=' . $this->options['direction'] . '&extra_conditions=' . urlencode(base64_encode(serialize($this->options['extra_conditions']))) . '">[1]</a></li>' . $links;
		}
		if ($last_page_link < $pages) {
			$links .= '&nbsp;&nbsp;&nbsp;' . '<a href="' . $_SERVER['REDIRECT_URL'] . '?page=' . $pages . '&sort=' . $this->options['sort'] . $search . '&direction=' . $this->options['direction'] . '&extra_conditions=' . urlencode(base64_encode(serialize($this->options['extra_conditions']))) . '">[' . $pages . ']</a>';
		}

		$this->links = '<div class="pagination pagination-centered"><ul>' . $links . '</ul></div>';
	}
}
