<?php
/*
Plugin Name: Yummi Quotes
Description: Add and manage Quotes, shortcode and widget supported
Plugin URI: https://wordpress.org/plugins/yummi-quotes/
Author URI: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url
Author: Alex Egorov
License: GPLv2 or later (license.txt)
Version: 1.0.5
Text Domain: yummi_quotes
*/


/**
 * $yummi_quotes_db_version - holds current database version
 * and used on plugin update to sync database tables
 */
global $wpdb, $yummi_quotes_db_version, $qthis, $table_name;
$yummi_quotes_db_version = '0.1'; // version changed from 0.1 to 0.2
$table_name = $wpdb->prefix . 'quotes'; // do not forget about tables prefix

//define('QUOTE_FILE', __FILE__);
define('QUOTE_PATH', plugin_dir_path(__FILE__));
define('QUOTE_URL', untrailingslashit(plugins_url('', __FILE__)));
//define('QUOTE_REFERENCE', 'Yummi Quote');

/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */
register_activation_hook(__FILE__, 'yummi_quotes_install');
function yummi_quotes_install()
{
    global $wpdb, $yummi_quotes_db_version, $table_name;

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      quote TEXT NOT NULL,
      author VARCHAR(100) NULL,
      photo VARCHAR(150) NULL,
      class VARCHAR(25) NULL,
      PRIMARY KEY  (id)
    ) DEFAULT CHARACTER SET utf8;";
    //category int(11) NULL,

    /*
    Величины в столбцах VARCHAR представляют собой строки переменной длины. Так же как и для столбцов CHAR, можно задать столбец VARCHAR любой длины между 1 и 255. Однако, в противоположность CHAR, при хранении величин типа VARCHAR используется только то количество символов, которое необходимо, плюс один байт для записи длины. Хранимые величины пробелами не дополняются, наоборот, концевые пробелы при хранении удаляются.

    Type         |         Bytes | English words | Multi-byte words
    -----------+---------------+---------------+-----------------
    TINYTEXT     |           255 |           ±44 |              ±23
    TEXT         |        65,535 |       ±11,000 |           ±5,900
    MEDIUMTEXT   |    16,777,215 |    ±2,800,000 |       ±1,500,000
    LONGTEXT     | 4,294,967,295 |  ±740,000,000 |     ±380,000,000
    */

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('yummi_quotes_db_version', $yummi_quotes_db_version);

    /**
     * [OPTIONAL] Example of updating to 0.1 version
     *
     * If you develop new version of plugin
     * just increment $yummi_quotes_db_version variable
     * and add following block of code
     *
     * must be repeated for each new version
     * in version 0.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('yummi_quotes_db_version');
    if ($installed_ver != $yummi_quotes_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          quote TEXT NOT NULL,
          author VARCHAR(100) NULL,
          photo VARCHAR(150) NULL,
          class VARCHAR(25) NULL,
          PRIMARY KEY  (id)
        ) DEFAULT CHARACTER SET utf8;";
        //category int(11) NULL,

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('yummi_quotes_db_version', $yummi_quotes_db_version);
    }


}

/**
 * register_activation_hook implementation
 *
 * [OPTIONAL]
 * additional implementation of register_activation_hook
 * to insert some dummy data
 */
register_activation_hook(__FILE__, 'yummi_quotes_install_data');
function yummi_quotes_install_data()
{
    global $wpdb, $yummi_quotes_db_version, $table_name;

    /*
    $existing_entry = $wpdb->get_row("SELECT * FROM $table_name WHERE level = $level AND moves = $moves", OBJECT);
    if($existing_entry){
        //the entry exists, so update it
        $new_count = $existing_entry->count + 1;
        $wpdb->update($table_name, array('level' => $level,'moves' => $moves,'count' => $new_count), array('id' => $existing_entry->id));
    } else {
        //the entry does not exist, so insert is as new
        $wpdb->insert($table_name, array('level' => $level, 'moves' => $moves,'count' => 1), array('%d', '%d', '%d'));
    }
    */

    $existing_entry = $wpdb->get_row("SELECT * FROM $table_name", OBJECT);
    if($existing_entry) { /* Check if table is empty */
      //Table does not empty
    }else{
      //Table is empty

      //$installed_ver = get_option('yummi_quotes_db_version');
      //if ($installed_ver != $yummi_quotes_db_version) {

        $wpdb->insert(
          $table_name,
          array(
              'quote' => __('A man can live and be healthy without killing animals for food; therefore, if he eats meat, he participates in taking animal life merely for the sake of his appetite. And to act so is immoral.', 'yummi_quotes'),
              'author' => __('Leo Tolstoy', 'yummi_quotes'),
              'photo' => '',
              //'category' => '',
              'class' => ''
          )
        );
        $wpdb->insert(
          $table_name,
          array(
              'quote' => __('I have from an early age abjured the use of meat, and the time will come when men such as I will look upon the murder of animals as they now look upon the murder of men.', 'yummi_quotes'),
              'author' => __('Leonardo da Vinci', 'yummi_quotes'),
              'photo' => '',
              //'category' => '',
              'class' => ''
          )
        );
      //};
    }
}

/**
 * Trick to update plugin database, see docs
 */
add_action('plugins_loaded', 'yummi_quotes_update_db_check');
function yummi_quotes_update_db_check()
{
    global $yummi_quotes_db_version;
    if (get_site_option('yummi_quotes_db_version') != $yummi_quotes_db_version) {
        yummi_quotes_install();
    }
}

/**
 * PART 2. Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * yummi_quotes_List_Table class that will display our custom table
 * records in nice table
 */
class yummi_quotes_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => __('Quote'),
            'plural' => __('Quotes', 'yummi_quotes'),
        ));

    }

    function init() {
      global $qthis;
      $qthis = new stdClass();
      $qthis->path = dirname( __FILE__ );
      $qthis->name = basename( $qthis->path );
      $qthis->url = plugins_url( "/{$qthis->name}/" );
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_author($item) /* column_author author - название колонки в которой выделить текст */
    {
        return '<em>' . $item['author'] . '</em>';
    }

    /**
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_photo($item) /* column_photo photo - название колонки в которой выделить текст */
    {
        return '<img src="' . $item['photo'] . '"/>';
    }
    /**
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_qid($item) /* column_id id - название колонки в которой выделить текст */
    {
        return $item['id'];
    }
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_quote($item) /* column_quote  quote - название колонки в которую добавить опци редактирвоания и удаления */
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=quotes_form&id=%s">%s</a>', $item['id'], __('Edit')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete Permanently')),
        );

        return sprintf('%s %s',
            $item['quote'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'quote' => __('Quote'),
            'author' => __('Author'),
            'photo' => __('Photo','yummi_quotes'),
            'class' => __('CSS class', 'yummi_quotes'),
            'qid' => __('ID', 'yummi_quotes'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'quote' => array('quote', true),
            'author' => array('author', false),
            'photo' => array('photo', false),
            'class' => array('class', false),
            'qid' => array('qid', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
          'delete' => __('Delete Permanently')
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb, $table_name;

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb, $table_name;

        $per_page = 25; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'quote';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
    static function yummi_plugins() {
      global $qthis;

      if(!is_admin() || !current_user_can("manage_options"))
        die( _e('You do not have sufficient permissions to access this page.', 'yummi_quotes') );
      if(!function_exists('yummi_plugins'))
        include_once( $qthis->path . '/includes/yummi-plugins.php' );
    }
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */

/**
 * admin_menu hook implementation, will add pages to list persons and to add new one
 */

add_action('admin_menu', 'yummi_quotes_admin_menu');
function yummi_quotes_admin_menu()
{
  if( empty( $GLOBALS['admin_page_hooks']['yummi']) )
    $main_page = add_menu_page( 'yummi', 'Yummi '.__('Plugins'), 'manage_options', 'yummi', array($qthis->path, 'yummi_plugins'), before_title_URL.'/includes/img/dashicons-yummi.png' );

  /*add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );*/
  //add_submenu_page( 'yummi','yummi_quotes', 'Yummi Quotes', 'manage_options', 'yummi_quotes', array(&$qthis->path, 'admin_page') );

  /* add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position ) */
  add_menu_page( 'Yummi '.__('Quotes', 'yummi_quotes'), 'Yummi '.__('Quotes', 'yummi_quotes'), 'activate_plugins', 'quotes', 'yummi_quotes_page_handler', 'dashicons-format-quote');
  add_submenu_page('quotes', __('Quotes', 'yummi_quotes'), __('Quotes', 'yummi_quotes'), 'activate_plugins', 'quotes', 'yummi_quotes_page_handler');
  // add new will be described in next part
  add_submenu_page('quotes', __('Add'), __('Add'), 'activate_plugins', 'quotes_form', 'yummi_quotes_form_page_handler');
}

add_filter('plugin_action_links', 'yummi_quotes_plugin_action_links', 10, 2);
function yummi_quotes_plugin_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) { // check to make sure we are on the correct plugin
			//$settings_link = '<a href="https://yummi.club/" target="_blank">' . __('Demo', 'yummi_quotes') . '</a> | ';
			$settings_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url" target="_blank">❤ ' . __('Donate', 'yummi_quotes') . '</a> | ';
      $settings_link .= '<a href="admin.php?page=quotes">' . __('Settings') . '</a>'; // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page

        array_unshift($links, $settings_link); // add the link to the list
    }
    return $links;
}

add_action('admin_init', 'yummi_quotes_options_redirect');
function yummi_quotes_options_redirect() {
    add_option('name', 'value');
    if (get_option('yummi_quotes_options_redirect', false)) {
        delete_option('yummi_quotes_options_redirect');
        if(!isset($_GET['activate-multi']))
        {
            wp_redirect("admin.php?page=quotes");
        }
    }
    register_setting( 'quotes_theme_options', 'quotes_options', 'quotes_validate_options' );
}

/* Красивая функция вывода масивов */
if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; }}

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function yummi_quotes_page_handler()
{
    global $wpdb;

    $table = new yummi_quotes_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Quotes deleted: %d', 'yummi_quotes'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Quotes', 'yummi_quotes')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=quotes_form');?>"><span class="dashicons dashicons-plus"></span> <?php _e('Add')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="quotes-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selequotesd
 */

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function yummi_quotes_form_page_handler()
{
    global $wpdb, $table_name;

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'quote' => '',
        'author' => '',
        'photo' => '',
        'class' => null,
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = yummi_quotes_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Quote was successfully saved.', 'yummi_quotes');
                } else {
                    $notice = __('There was an error while saving quote', 'yummi_quotes');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Quote was successfully updated', 'yummi_quotes');
                } else {
                    $notice = __('There was an error while updating quote OR quote was not changed', 'yummi_quotes');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'yummi_quotes');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('quotes_form_meta_box', 'Quote data', 'yummi_quotes_form_meta_box_handler', 'Quote', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Quote')?>
      <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=quotes');?>"><?php _e('Back to Quotes list', 'yummi_quotes')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('Quote', 'normal', $item); ?>
                    <?php //=prr($_REQUEST);
                    $_REQUEST['nonce'] = !empty($_REQUEST['nonce']) ? $_REQUEST['nonce'] : null;
                    ?>
                    <input type="submit" value="<?php echo( $_REQUEST['nonce'] || $_REQUEST['id'] ) ? __('Update') : __('Add') ;?>" id="submit" class="button-primary" name="submit">
                    <?php echo( $_REQUEST['nonce'] || $_REQUEST['id'] ) ? sprintf('<a href="?page=quotes_form" class="button-secondary">%s</a>', __('Add New Quote', 'yummi_quotes')) : null ;?>
                    <?php //echo ( $_REQUEST['id'] ) ? '<a href="'. get_admin_url(get_current_blog_id(), "admin.php?page=quotes_form").'">'.__('Add New Quote', 'yummi_quotes').'</a>' : null ;?>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function yummi_quotes_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="quote"><?php _e('Quote')?></label>
        </th>
        <td>
            <textarea id="quote" name="quote" style="width: 95%" value="<?php echo esc_attr($item['quote'])?>" rows="4" cols="50" class="code" placeholder="<?php _e('Quote text', 'yummi_quotes')?>" required><?php echo esc_attr($item['quote'])?></textarea>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="author"><?php _e('Author')?></label>
        </th>
        <td>
            <input id="author" name="author" type="text" style="width: 95%" value="<?php echo esc_attr($item['author'])?>"
                   size="50" class="code" placeholder="<?php _e('Author')?>">
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="photo"><?php _e('Photo','yummi_quotes')?></label>
        </th>
        <td>
            <img class="photography wlis" style="max-height: 150px;" src="<?php echo $item['photo']; ?>"/><br/>

            <input id="photo" name="photo" type="text" style="width: 95%" value="<?php echo esc_attr($item['photo'])?>" class="code" placeholder="<?php _e('Url', 'yummi_quotes')?>">
            <button class="upload_photo button" style=""><span class="dashicons dashicons-admin-media"></span> <?php _e('Upload/Add image', 'yummi_quotes'); ?></button>
            <button class="remove_photo button"><?php _e('Remove image', 'yummi_quotes'); ?></button>
            <?php wp_enqueue_media(); ?>
            <script type="text/javascript">
              jQuery(document).ready(function($) {

                var wordpress_ver = "<?php echo get_bloginfo("version"); ?>", upload_btn;
                $(".upload_photo").click(function(event) {

                  upload_btn = $(this);

                  var frame;
                  //console.log( upload_btn.parent().children() );
                  var img = upload_btn.parent().children();
                  var input = upload_btn.parent().children().next();
                  if (wordpress_ver >= "3.5") {
                    event.preventDefault();
                    if (frame) {
                      frame.open();
                      return;
                    }
                    frame = wp.media({
    						      title: '<?php _e('Select or Upload Author Photography', 'yummi_quotes'); ?>',
    						      // button: {text: 'Use this media'},
    						      multiple: false  // Set to true to allow multiple files to be selected
    						    });
                    frame.on( "select", function() {
                      // Grab the selected attachment.
                      var attachment = frame.state().get("selection").first();
                      frame.close();
                      if (img.hasClass("wlis")) {
                        img.attr("src", attachment.attributes.url);
                        input.val(attachment.attributes.url);
                        if( input.value !== '' && bg.style.display == 'block' )
                          bg.style.display = 'none';
                        else
                          bg.style.display = 'block';
                      }
                      else
                        $("#photo").val(attachment.attributes.url);
                    });
                    frame.open();
                  }
                  else {
                    tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
                    return false;
                  }
                });

                $(".remove_photo").click(function() {
                  $(".photography").attr("src", "");
                  $("#photo").val("");
                  return false;
                });

                if( wordpress_ver < "3.5" ) {
                  window.send_to_editor = function(html) {
                    imgurl = $("img",html).attr("src");
                    var bg = upload_btn.parent().children('#bg-<?php echo $widget->id ?>')[0];
                    var img = upload_btn.parent().children();
                    var input = upload_btn.parent().children().next();

                    if( img.hasClass("wlis") ) {
                      img.attr("src", imgurl);
                      input.val(imgurl);
                      if( input.value !== '' && bg.style.display == 'block' )
                        bg.style.display = 'none';
                      else
                        bg.style.display = 'block';
                    }else
                      $("#photo").val(imgurl);
                    tb_remove();
                  }
                }

              });
            </script>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="class"><?php _e('CSS class', 'yummi_quotes')?></label>
        </th>
        <td>
            <input id="class" name="class" type="text" style="width: 95%" value="<?php echo esc_attr($item['class'])?>"
                   size="50" class="code" placeholder="<?php _e('CSS class', 'yummi_quotes')?>">
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function yummi_quotes_validate_person($item)
{
    $messages = array();

    if (empty($item['quote'])) $messages[] = __('Quote is required', 'yummi_quotes');
    //if (empty($item['author']) $messages[] = __('Author is required', 'yummi_quotes');
    //if (empty($item['class'])) $messages[] = __('Css class is required', 'yummi_quotes');
    //if(!empty($item['class']) && !absint(intval($item['class'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['class']) && !preg_match('/[0-9]+/', $item['class'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == yummi_quotes
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
add_action('init', 'yummi_quotes_languages');
function yummi_quotes_languages()
{
  load_plugin_textdomain('yummi_quotes', false, dirname(plugin_basename(__FILE__)));
}

require QUOTE_PATH . '/includes/admin.php';
include_once 'includes/widget.php';
include_once 'includes/shortcode.php';


add_action('wp_print_styles', 'quote_css');
function quote_css() {
  global $qt;

  $author_italic = $qt["author_italic"] ? 'font-style:italic' : null;

  $author_inline = $qt["author_inline"] ? null : 'display: block;';

  //prr($qt);
  echo '<style>
          .yquote { display: block; clear: both; margin-bottom:'.$qt["margin_bottom"].' }
          .yquote img { float: left; width: '.$qt['s_img_size'].'; margin: 0 10px 10px 0; border-radius: '.$qt['s_img_radius'].'; }
          .yquote span.qauthor { '.$author_inline.' text-align: right; '.$author_italic.' }
          .wquote { display: block; clear: both; }
          .wquote div { text-align:center; }
          .wquote img { dispaly: block; width: '.$qt['w_img_size'].'; border-radius: '.$qt['w_img_radius'].'; }
          '.$qt['css'].'
        </style>';
}

function yq_add_mce_button() {
  if ( !current_user_can( 'edit_posts' ) &&  !current_user_can( 'edit_pages' ) ) {
    return;
  }
  if ( 'true' == get_user_option( 'rich_editing' ) ) { // check if WYSIWYG is enabled
    add_filter( 'mce_external_plugins', 'yq_add_tinymce_plugin' );
    add_filter( 'mce_buttons', 'yq_register_mce_button' );
  }
}
add_action('admin_head', 'yq_add_mce_button');
function yq_register_mce_button( $buttons ) {
  array_push( $buttons, 'yq_mce_button' );
  return $buttons;
}
function yq_add_tinymce_plugin( $plugin_array ) {
  $plugin_array['yq_mce_button'] = QUOTE_URL.'/includes/js/mce-button.js';
  return $plugin_array;
}
?>
