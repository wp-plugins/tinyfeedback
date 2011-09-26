<?php
/*
 http://codex.wordpress.org/Class_Reference/WP_List_Table
*/ 

if(!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class tinyFeedback_Statistics_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;

		// Parent construct
		parent::__construct(array(
			'singular'	=> 'statistics',
			'plural'	=> 'statistics',
			'ajax'		=> false
		));
	}

	function column_default($item, $column_name) {
		switch($column_name) {
			case 'positive':
			case 'negative':
			case 'written': // Get its own function within short
				return (int)$item[$column_name];
			default:
				return print_r($item, true);
		}
	}

	function column_page($item) {
		global $wpdb;
		$site = $wpdb->get_row("SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name='siteurl'"); 
		$site = $site->option_value . $item['page'];
		$actions = array(
			'visit' => '<a href="' . $site . '">Visit page</a>' 
		);
		
		return sprintf('%1$s %2$s', $item['page'], $this->row_actions($actions));
	}

	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s">',
			'ids',
			$item['id']
		);
	}

	//function column_written($item) {

	function get_columns() {
		$columns = array(
			'cb'		=> '<input type="checkbox">',
			'page'		=> 'Page',
			'positive'	=> 'Positive',
			'negative'	=> 'Negative',
			'written'	=> 'Written'
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'page'		=> array('url', true),
			'positive'	=> array('positive', false), 
			'negative'	=> array('negative', false),
			'written'	=> array('written', false)
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'reset'		=> 'Reset counts',
			'delete'	=> 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		if(isset($_POST['ids'])) {
			$valid_ids = '';
			foreach($_POST['ids'] as $id) {
				if(is_numeric($id)) {
					$valid_ids .= $id . ',';
				}
			}
			if(strlen($valid_ids) > 1) {
				$valid_ids = substr($valid_ids, 0, -1);
				global $wpdb;

				if('delete' === $this->current_action()) {
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "tinyFeedback_URLs WHERE id IN (" . $valid_ids . ")");
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "tinyFeedback_textual WHERE url_id IN (" . $valid_ids . ")");
				}

				if('reset' === $this->current_action()) {
					$wpdb->query("UPDATE " . $wpdb->prefix . "tinyFeedback_URLs SET positive_count = 0, negative_count = 0 WHERE id IN (" . $valid_ids . ")");
				}
			}
		}
	}

	function prepare_items() {
		global $wpdb;
		$per_page = 50;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		$orderby = 'page';
		if(isset($_GET['orderby']) && in_array($_GET['orderby'], array('page', 'positive', 'negative', 'written'))) {
			$orderby = $_GET['orderby'];
		}
		$order = 'DESC';
		if(isset($_GET['order']) && in_array($_GET['order'], array('asc', 'desc'))) {
			$order = strtoupper($_GET['order']);
		}


		$data = $wpdb->get_results("
			SELECT urls.id, urls.url AS page, urls.positive_count AS positive, urls.negative_count AS negative, count(textual.url_id) AS written FROM " . $wpdb->prefix . "tinyFeedback_URLs AS urls LEFT OUTER JOIN " . $wpdb->prefix . "tinyFeedback_textual AS textual ON textual.url_id = urls.id WHERE textual.id IS NULL OR textual.url_id GROUP BY urls.id ORDER BY " . $orderby . " " . $order, ARRAY_A);

		$current_page = $this->get_pagenum();
		$total_items = count($data); /* We could make this part more efficient by simply getting count() from DB, and fetching data within that range. */
		$data = array_slice($data, (($current_page-1) * $per_page), $per_page);

		$this->items = $data;

		$this->set_pagination_args(array(
			'total_items'	=> $total_items,
			'per_page'		=> $per_page,
			'total_pages'	=> ceil($total_items/$per_page)
		));
	}
}

function render_stats_list() {
	$list = new tinyFeedback_Statistics_Table();
	$list->prepare_items();
	$list->display();
}

?>

<h2>Feedback Statistics</h2>
<p><em>Statistical overview of positive, negative and written feedback for each available page on your website.</em></p>

<form id="statistics" method="post">
	<input type="hidden" name="page" value="tinyFeedback">
<?php
	render_stats_list();
?>
</form>
