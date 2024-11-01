<?php
add_shortcode('quotes', 'quotes');
function quotes($atts){
	extract( shortcode_atts(
			array(
				'number' => '',
        'sort' => '',
				'author' => '',
				'id' => '',
			), $atts )
		);
	ob_start(); /* буферизация, для вставки в место где стоит шорткод */

  global $wpdb, $qt;

	if($qt['styling']) wp_enqueue_style( 'yummi-quotes-styling', QUOTE_URL.'/includes/css/styling.min.css', array(), '0.4' );

  $table_name = $wpdb->prefix . 'quotes'; // do not forget about tables prefix

  $per_page = isset($atts['number']) ? $atts['number'] : 1;
  $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
  $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'quote';
  $order = (isset($atts['sort']) && in_array($atts['sort'], array('asc', 'desc', 'random'))) ? $atts['sort'] : 'asc';

	$id = isset($atts['id']) ? $atts['id'] : null;

	$author_only = isset($atts['author']) ? "'".$atts['author']."'" : null;

	if($id)
		$quotes = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id", ARRAY_A);
	else
	  $quotes = ($order == 'random') ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY RAND() LIMIT %d", $per_page, $paged), ARRAY_A) : $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d", $per_page, $paged), ARRAY_A);

	if($author_only)
		$quotes = ($order == 'random') ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE author = $author_only ORDER BY RAND() LIMIT %d", $per_page, $paged), ARRAY_A) : $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE author = $author_only ORDER BY $orderby $order LIMIT %d", $per_page, $paged), ARRAY_A);

  foreach ($quotes as $key => $quote) {
    ($key & 1) ? $eo = 'odd' : $eo = 'even';
    $photo = ($quote['photo']) ? '<img src="'.$quote['photo'].'" alt="'.__('Quote', 'yummi_quotes').' '.__('by', 'yummi_quotes').' '.$quote['author'].'" />' : null;
		$photo = ($quote['photo']) ? '<img src="'.$quote['photo'].'" alt="'.__('Quote', 'yummi_quotes').' '.__('by', 'yummi_quotes').' '.$quote['author'].'" />' : null;
		$author = ($quote['author']) ? '<span class="qauthor">&mdash; '.$quote['author'].'</span>' : null;
		if($qt['author_inline'])
    	echo sprintf('<div class="yquote %s %s"><p>%s %s %s</p></div>', $quote['class'], $eo, $photo, $quote['quote'], $author);
		else
			echo sprintf('<div class="yquote %s %s"><p>%s %s</p>%s</div>', $quote['class'], $eo, $photo, $quote['quote'], $author);
  }

	$quote = ob_get_contents(); /* применить к переменной содержимое буфера */
	ob_end_clean();/* очистака буфера, для вставки в место где стоит шорткод */
	return $quote;
}
?>
