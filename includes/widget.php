<?php /*
	Quotes Widget

	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html

	Copyright: (c) 2016 Alex "EviLex" Iegorov - http://

		@package Quotes
		@version 0.1
*/

class Quotes extends WP_Widget {

/*  Constructor
/* ------------------------------------ */
	function quotes() {
		parent::__construct( false, __('Quotes', 'yummi_quotes'), array('description' => 'Display quotes', 'classname' => 'widget_quotes') );
	}

/*  Widget
/* ------------------------------------ */
	public function widget($args, $instance) {
		extract( $args );
		$instance['title']?NULL:$instance['title']='';
		$title = apply_filters('widget_title',$instance['title']);
		$output = $before_widget."\n";
		if($title)
			$output .= $before_title.$title.$after_title;

    global $wpdb;

    $table_name = $wpdb->prefix . 'quotes'; // do not forget about tables prefix

    $per_page = ($instance['quotes_num']) ? $instance['quotes_num'] : 1 ;
    $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'quote';
    $order = ($instance['quotes_orderby'] && in_array($instance['quotes_orderby'], array('asc', 'desc', 'rand'))) ? $instance['quotes_orderby'] : 'asc';
		$type = ($instance['quotes_type'] && in_array($instance['quotes_type'], array('all','quote','author'))) ? $instance['quotes_type'] : 'quote';
		$id = ($instance['quotes_id'] && in_array($instance['quotes_id'], array('all'))) ? 'all' : $instance['quotes_id'];
		$author = ($instance['quotes_author'] && in_array($instance['quotes_author'], array('all'))) ? 'all' : $instance['quotes_author'];

		//prr('$type:'.$type.' $id:'.$id.' $author:'.$author);

		if($type == 'quote' && $id != 'all')
			$quotes = ($order == 'rand') ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id = $id ORDER BY RAND() LIMIT %d", $per_page), ARRAY_A) : $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id = $id ORDER BY $orderby $order LIMIT %d", $per_page), ARRAY_A);
		elseif($type == 'author' && $author != 'all')
			$quotes = ($order == 'rand') ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE author = '$author' ORDER BY RAND() LIMIT %d", $per_page), ARRAY_A) : $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE author = '$author' ORDER BY $orderby $order LIMIT %d", $per_page), ARRAY_A);
		else
	    $quotes = ($order == 'rand') ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY RAND() LIMIT %d", $per_page), ARRAY_A) : $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d", $per_page), ARRAY_A);

		ob_start();

    foreach ($quotes as $key => $quote) {
      ($key & 1) ? $eo = 'odd' : $eo = 'even';
      $photo = ($quote['photo'] && $instance['quotes_photo'] && $instance['quotes_photo'] == 1) ? '<div><img src="'.$quote['photo'].'" alt="'.__('Quote').' '.__('by', 'yummi_quotes').' '.$quote['author'].'" /></div><br/>' : null;
			$author_italic = !empty($instance['quotes_author_italic']) ? 'font-style:italic' : null;

			if(!empty($instance['quotes_author_inline']))
				$qauthor = ($quote['author']) ? '<span class="wauthor" style="'.$author_italic.'">&mdash; '.$quote['author'].'</span>' : null;
			else
				$qauthor = ($quote['author']) ? '<span class="wauthor" style="float:right;'.$author_italic.'"><br/>&mdash; '.$quote['author'].'</span>' : null;
      echo sprintf('<div class="wquote %s %s">%s %s %s</div>', $quote['class'], $eo, $photo, $quote['quote'], $qauthor);
    }

		$output .= ob_get_clean();
		$output .= $after_widget."\n";
		echo $output;
	}

/*  Widget update
/* ------------------------------------ */
	public function update($new,$old) {
		$instance = $old;
		$instance['title'] = strip_tags($new['title']);
		$instance['quotes_photo'] = $new['quotes_photo']?1:0;
		//$instance['quotes_category'] = $new['quotes_category']?1:0;
		$instance['quotes_num'] = $new['quotes_num'];
		$instance['quotes_id'] = $new['quotes_id'];
		$instance['quotes_type'] = $new['quotes_type']?$new['quotes_type']:'quote';
		$instance['quotes_author_inline'] = $new['quotes_author_inline'];
		$instance['quotes_author_italic'] = $new['quotes_author_italic'];
		$instance['quotes_author'] = $new['quotes_author'];
		$instance['quotes_orderby'] = $new['quotes_orderby'];
		return $instance;
	}

/*  Widget form
/* ------------------------------------ */
	public function form($instance) {
		global $wpdb;
		// Default widget settings
		$defaults = array(
			'title'						=> '',
			'quotes_photo'		=> 1,
			//'quotes_category'	=> 1,
			'quotes_num'      => 1,
			'quotes_type'      => 'quote',
			'quotes_orderby' 	=> 'rand',
			'quotes_id'				=> 'all',
			'quotes_author_inline'		=> 0,
			'quotes_author_italic'		=> 0,
			'quotes_author'		=> 'all',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
?>

	<div class="quotes-options-posts">
		<?//prr($instance)?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:')?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
		</p>

		<p>
			<label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("quotes_num"); ?>"><?php _e('Show'); ?>:</label>
			<input style="width:20%;" id="<?php echo $this->get_field_id("quotes_num"); ?>" name="<?php echo $this->get_field_name("quotes_num"); ?>" type="number" value="<?php echo absint($instance["quotes_num"]); ?>" min="1" />
		</p>
		<!--<p>
			<label style="width: 100%; display: inline-block;" for="<.?php echo $this->get_field_id("posts_cat_id"); ?>">Category:</label>
			<.?php wp_dropdown_categories( array( 'name' => $this->get_field_name("posts_cat_id"), 'selected' => $instance["posts_cat_id"], 'show_option_all' => 'All', 'show_count' => true ) ); ?>
		</p>-->
		<p>
			<label style="width: 100%; display: inline-block;" for="<?php echo $this->get_field_id("quotes_orderby"); ?>"><?php _e('Order'); ?>:</label>
			<select style="width: 100%;" id="<?php echo $this->get_field_id("quotes_orderby"); ?>" name="<?php echo $this->get_field_name("quotes_orderby"); ?>">
				<option value="rand"<?php selected( $instance["quotes_orderby"], "rand" ); ?>><?php _e('Random'); ?></option>
			  <option value="asc"<?php selected( $instance["quotes_orderby"], "asc" ); ?>>ASC</option>
			  <option value="desc"<?php selected( $instance["quotes_orderby"], "desc" ); ?>>DESC</option>
			</select>
		</p>
		<p>
			<label><input type="radio" name="<?php echo $this->get_field_name("quotes_type"); ?>" value="quote"<?php checked( $instance["quotes_type"], "quote" ); ?>><?php _e('Quote'); ?></label>&emsp;
			<label><input type="radio" name="<?php echo $this->get_field_name("quotes_type"); ?>" value="author"<?php checked( $instance["quotes_type"], "author" ); ?>><?php _e('Author'); ?></label>
		</p>
		<p>
			<label style="width: 100%; display: inline-block;" for="<?php echo $this->get_field_id("quotes_id"); ?>"><?php _e('Quote'); ?>:</label>
			<select style="width: 100%;" id="<?php echo $this->get_field_id("quotes_id"); ?>" name="<?php echo $this->get_field_name("quotes_id"); ?>">
				<option value="all"<?php selected( $instance["quotes_id"], "all" ); ?>><?php _e('All'); ?></option>
				<?php
				$quotes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}quotes", OBJECT);
				foreach ($quotes as $key => $quote) {
					echo '<option value="'.$quote->id.'"'.selected( $instance["quotes_id"], $quote->id ).'>'.$quote->author.' "'.mb_strimwidth($quote->quote, 0, 80, '...').'"</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label style="width: 100%; display: inline-block;" for="<?php echo $this->get_field_id("quotes_author"); ?>"><?php _e('Author'); ?>:</label>
			<select style="width: 100%;" id="<?php echo $this->get_field_id("quotes_author"); ?>" name="<?php echo $this->get_field_name("quotes_author"); ?>">
				<option value="all"<?php selected( $instance["quotes_author"], "all" ); ?>><?php _e('All'); ?></option>
				<?php
				$quotes_authors = $wpdb->get_results("SELECT DISTINCT(author) FROM {$wpdb->prefix}quotes", OBJECT);
				foreach ($quotes as $key => $quote) {
					echo '<option value="'.$quote->author.'"'.selected( $instance["quotes_author"], $quote->author ).'>'.$quote->author.'</option>';
				} ?>
			</select>
		</p>

    <p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('quotes_photo'); ?>" name="<?php echo $this->get_field_name('quotes_photo'); ?>" <?php checked( (bool) $instance["quotes_photo"], true ); ?>>
			<label for="<?php echo $this->get_field_id('quotes_photo'); ?>"><?php echo __('Show').' '.__('Author\'s Photography', 'yummi_quotes'); ?></label>
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('quotes_author_inline'); ?>" name="<?php echo $this->get_field_name('quotes_author_inline'); ?>" <?php checked( (bool) $instance["quotes_author_inline"], true ); ?>>
			<label for="<?php echo $this->get_field_id('quotes_author_inline'); ?>"><?php _e('Author Inline', 'yummi_quotes'); ?></label>
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('quotes_author_italic'); ?>" name="<?php echo $this->get_field_name('quotes_author_italic'); ?>" <?php checked( (bool) $instance["quotes_author_italic"], true ); ?>>
			<label for="<?php echo $this->get_field_id('quotes_author_italic'); ?>"><?php _e('Author Italic', 'yummi_quotes'); ?></label>
		</p>

		<hr>

	</div>
<?php

}

}

/*  Register widget
/* ------------------------------------ */
if ( ! function_exists( 'quotes_register_widget' ) ) {

	function quotes_register_widget() {
		register_widget( 'quotes' );
	}

}
add_action( 'widgets_init', 'quotes_register_widget' );
