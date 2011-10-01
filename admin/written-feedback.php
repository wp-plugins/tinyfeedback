<?php
/*
 http://codex.wordpress.org/Class_Reference/WP_List_Table
*/ 

if(!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class tinyFeedback_WrittenFeedback_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;

		// Parent construct
		parent::__construct(array(
			'singular'	=> 'comment',
			'plural'	=> 'comments',
			'ajax'		=> false
		));
	}

	function column_default($item, $column_name) {
		switch($column_name) {
			case 'add_timestamp':
				return $item[$column_name];
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

	function column_author_email($item) {
		if(isset($item['author_email']) && !empty($item['author_email'])) { // Validation and stuff
			$actions = array(
				'mail' => '<a href="mailto:' . $item['author_email'] . '">Contact</a>'
			);
			return sprintf('%1$s %2$s', $item['author_email'], $this->row_actions($actions));
		}
		return 'Anonymous';
	}

	function column_replied($item) {
		if($item['replied']) {
			return 'Replied';
		}
		return 'Not replied';
	}

	function column_message($item) {
		return $item['message'];
	}

	//function column_written($item) {

	function get_columns() {
		$columns = array(
			'cb'			=> '<input type="checkbox">',
			'author_email'	=> 'Author',
			'message'		=> 'Message',
			'add_timestamp'	=> 'Received',
			'page'			=> 'Page',
			'replied'		=> 'Replied'
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'add_timestamp'	=> array('add_timestamp', true),
			'page'			=> array('page', false),
			'replied'		=> array('replied', false)
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'replied'	=> 'Mark as replied',
			'notreplied'=> 'Mark as not replied',
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
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "tinyFeedback_textual WHERE id IN (" . $valid_ids . ")");
				}
				if('replied' === $this->current_action()) {
					$wpdb->query("UPDATE " . $wpdb->prefix . "tinyFeedback_textual SET replied=1 WHERE id IN (" . $valid_ids . ")");
				}
				if('notreplied' === $this->current_action()) {
					$wpdb->query("UPDATE " . $wpdb->prefix . "tinyFeedback_textual SET replied=0 WHERE id IN (" . $valid_ids . ")");
				}
			}
		}
	}

	function prepare_items() {
		global $wpdb;
		$per_page = 15;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		$orderby = 'add_timestamp';
		if(isset($_GET['orderby']) && in_array($_GET['orderby'], array('page', 'add_timestamp', 'replied'))) {
			$orderby = $_GET['orderby'];
		}
		$order = 'DESC';
		if(isset($_GET['order']) && in_array($_GET['order'], array('asc', 'desc'))) {
			$order = strtoupper($_GET['order']);
		}


		$data = $wpdb->get_results("SELECT textual.*, urls.url AS page FROM " . $wpdb->prefix . "tinyFeedback_textual AS textual, " . $wpdb->prefix . "tinyFeedback_URLs AS urls WHERE urls.id = textual.url_id ORDER BY " . $orderby . " " . $order, ARRAY_A);

		$current_page = $this->get_pagenum();
		$total_items = count($data);
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
	$list = new tinyFeedback_WrittenFeedback_Table();
	$list->prepare_items();
	$list->display();
}

?>

<h2>Written Feedback</h2>
<p><em>This is where you can review the feedback sent in by your visitors. Remember to see all critique in a positive light - the sender did take his time to write you his opinions.</em></p>

<form id="written-feedback" method="post">
	<input type="hidden" name="page" value="tinyFeedback">
	<input type="hidden" name="s" value="written">
<?php
	render_stats_list();
?>
</form>
