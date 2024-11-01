<?php
global $qt,$qver;
$qver = '1.0.2';

$qt = array(
  'w_img_radius' => '50%',
  'w_img_size' => '50%',
  's_img_radius' => '50%',
  's_img_size' => '60px',
  'margin_bottom' => '15px',
  'author_inline' => 0,
  'author_italic' => 0,
  'styling' => 0,
  'admin_dark' => 0,
  'css' => '',
  'ver' => $qver
);
//update_option("qt", $qt);

#Get option values
$qt = get_option( 'qt', $qt );

// Update if version changed
if(!isset($qt['ver']) || $qt['ver'] !== $qver){
  $qtg = get_option('qt');
  $qtu = array( //(int)$_POST[qt] //sanitize_text_field($_POST[qt])
    //Валидация данных https://codex.wordpress.org/%D0%92%D0%B0%D0%BB%D0%B8%D0%B4%D0%B0%D1%86%D0%B8%D1%8F_%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85
     'w_img_radius' => !empty($qtg['w_img_radius']) ? $qtg['w_img_radius'] : '50%',
     'w_img_size' => !empty($qtg['w_img_size']) ? $qtg['w_img_size'] : '50%',
     's_img_radius' => !empty($qtg['s_img_radius']) ? $qtg['s_img_radius'] : '50%',
     's_img_size' => !empty($qtg['s_img_size']) ? $qtg['s_img_size'] : '60px',
     'margin_bottom' => !empty($qtg['margin_bottom']) ? $qtg['margin_bottom'] : '15px',
     'author_inline' => !empty($qtg['author_inline']) ? $qtg['author_inline'] : 0,
     'author_italic' => !empty($qtg['author_italic']) ? $qtg['author_italic'] : 0,
     'styling' => !empty($qtg['styling']) ? $qtg['styling'] : 0,
     'admin_dark' => !empty($qtg['admin_dark']) ? $qtg['admin_dark'] : 0,
     'css' => !empty($qtg['css']) ? $qtg['css'] : '',
     'ver' => $qver,
  );
  update_option("qt", $qtu);
  $qt = get_option( 'qt' );
}

#Get new updated option values, and save them
if( @$_POST['action'] == 'update' ) {

 //check_admin_referer('update-options-qt');

 $qt = array( //(int)$_POST[qt] //sanitize_text_field($_POST[qt])
   //Валидация данных https://codex.wordpress.org/%D0%92%D0%B0%D0%BB%D0%B8%D0%B4%D0%B0%D1%86%D0%B8%D1%8F_%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85
     'w_img_radius' => isset($_POST['w_img_radius']) ? wp_filter_nohtml_kses($_POST['w_img_radius']) : null
    ,'w_img_size' => isset($_POST['w_img_size']) ? wp_filter_nohtml_kses($_POST['w_img_size']) : null
    ,'s_img_radius' => isset($_POST['s_img_radius']) ? wp_filter_nohtml_kses($_POST['s_img_radius']) : null
    ,'s_img_size' => isset($_POST['s_img_size']) ? wp_filter_nohtml_kses($_POST['s_img_size']) : null
    ,'margin_bottom' => isset($_POST['margin_bottom']) ? wp_filter_nohtml_kses($_POST['margin_bottom']) : null
    ,'author_inline' => isset($_POST['author_inline']) ? $_POST['author_inline'] : null
    ,'author_italic' => isset($_POST['author_italic']) ? $_POST['author_italic'] : null
    ,'admin_dark' => isset($_POST['admin_dark']) ? $_POST['admin_dark'] : null
    ,'styling' => isset($_POST['styling']) ? $_POST['styling'] : null
    ,'css' => isset($_POST['css']) ? wp_filter_post_kses($_POST['css']) : null
    ,'ver' => isset($_POST['version']) ? wp_filter_nohtml_kses($_POST['version']) : null
 );
 update_option("qt", $qt);
 echo '<div id="message" class="updated notice is-dismissible"><p><strong>'.__('Settings saved.').'</strong></p></div>'; //<script type="text/javascript">document.location.reload(true);</script>
}

//prr($qt);

if ( is_admin() ) : // Load only if we are viewing an admin page // || current_user_can("manage_options")
  if(  isset($_REQUEST['page']) && $_REQUEST['page'] == 'quotes_options'
    || isset($_REQUEST['page']) && $_REQUEST['page'] == 'quotes'
    || isset($_REQUEST['page']) && $_REQUEST['page'] == 'quotes_form'
    || isset($_REQUEST['page']) && $_REQUEST['page'] == 'yummi' ) /* Filter pages */
    add_action( 'admin_init', 'quote_register_settings' );
    function quote_register_settings() {
      global $qt;
      $url = plugin_dir_url( __FILE__ );
    	//register_setting( 'yummi_quote_options', 'qt', 'quote_validate_options' );
      if($qt['admin_dark'])
        wp_enqueue_style( 'quote-style', $url . '/css/admin_style.min.css' );
      else
        wp_enqueue_style( 'quote-style', $url . '/css/no_admin_style.min.css' );
      //wp_enqueue_style( 'quote-hint', $url . '/css/hint.min.css' );
    }

  add_action( 'admin_menu', 'yummi_quote_options' );
  function yummi_quote_options() {
  	// Add theme options page to the addmin menu
  	add_submenu_page('quotes', __('Settings'), __('Settings'), 'activate_plugins', 'quotes_options', 'yummi_quote_options_page');
  }

  // Function to generate options page
  function yummi_quote_options_page() {
  	global $wp_version, $qt;

  	$isOldWP = floatval($wp_version) < 2.5;

  	$beforeRow = $isOldWP ? "<p>" : '<tr valign="top"><th scope="row">';
  	$betweenRow = $isOldWP ? "" : '</th><td>';
  	$afterRow = $isOldWP ? "</p>" : '</td><tr>'; ?>

  	<div class="wrap">
      <div style='float:right;margin-top:13px;'> ❤ <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLHFMF373Z9GG&source=url" target="_blank"><?php _e('Donate', 'yummi_quotes')?></a> &ensp; <span style="font-size:1.3em">&starf;</span> <a href="https://wordpress.org/support/plugin/yummi-quotes/reviews/#new-post" target="_blank"><?php _e('Rate')?></a> &ensp; ❖ <a href="http://ae.yummi.club" target="_blank"><?php _e('Me', 'yummi_quotes')?></a></div>
      <?php echo "<h1>" . __('Quote', 'yummi_quotes') .' '. __( 'Settings' ) . "</h1>"; ?>

      <p>
        <?php _e('Installation codes', 'yummi_quotes') ?>:<br/>
        <pre>&lt;?php echo do_shortcode('[quotes sort=random number=1]') ?&gt;</pre>
        <small><?php _e('Put this code to your template files', 'yummi_quotes') ?>: <?php _e('Appearance') ?> &gt; <?php _e('Editor') ?></small>
      </p>
      <em>- <?php _e('or','yummi_quotes'); ?> -</em>
      <p>
        <pre>[quotes sort=random number=1] or [quotes sort=asc number=2 author="Leonardo da Vinci"] or [quotes id=1]</pre>
        <small><?php _e('Put this shortcode to your pages or push button "<i class="mce-ico mce-i-blockquote"></i>YQ" when edit, where \'sort\' can be asc, desc, random, \'number\' is number of quotes, \'author\' name of author. To show and \'id\' is ID of one quote.', 'yummi_quotes') ?></small>
      </p>

  	<form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">

    	<?php
      if(function_exists('wp_nonce_field'))
				wp_nonce_field('update-options-qt'); ?>

    	<table class="form-table"><!-- Grab a hot cup of coffee, yes we're using tables! -->

      <tr valign="top"><th scope="row"><label for="w_img_size"><?php _e('Widget Img Size', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="w_img_size" name="w_img_size" type="text" value="<?php esc_attr_e($qt['w_img_size']); ?>" />
      	</td>
    	</tr>

    	<tr valign="top"><th scope="row"><label for="w_img_radius"><?php _e('Widget Img Radius', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="w_img_radius" name="w_img_radius" type="text" value="<?php esc_attr_e($qt['w_img_radius']); ?>" />
      	</td>
    	</tr>

      <tr valign="top"><th scope="row"><label for="s_img_size"><?php _e('Shortcode Img Size', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="s_img_size" name="s_img_size" type="text" value="<?php esc_attr_e($qt['s_img_size']); ?>" />
      	</td>
    	</tr>

      <tr valign="top"><th scope="row"><label for="s_img_radius"><?php _e('Shortcode Img Radius', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="s_img_radius" name="s_img_radius" type="text" value="<?php  esc_attr_e($qt['s_img_radius']); ?>" />
      	</td>
    	</tr>

      <tr valign="top"><th scope="row"><label for="margin_bottom"><?php _e('Margin Bottom', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="margin_bottom" name="margin_bottom" type="text" value="<?php  esc_attr_e($qt['margin_bottom']); ?>" />
      	</td>
    	</tr>

      <tr valign="top"><th scope="row"><?php _e('Author Text style', 'yummi_quotes')?></th>
      	<td>
          <label for="author_inline"><input id="author_inline" name="author_inline" type="checkbox" <?php checked( (bool) $qt['author_inline'], true ); ?> /> <?php _e('Inline', 'yummi_quotes')?></label>&emsp;
           <label for="author_italic"><input id="author_italic" name="author_italic" type="checkbox" <?php checked( (bool) $qt['author_italic'], true ); ?> /> <?php _e('Italic', 'yummi_quotes')?></label>
      	</td>
    	</tr>

      <tr valign="top"><th scope="row"><label for="styling"><?php _e('Styling', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="styling" name="styling" type="checkbox" <?php checked( (bool) $qt['styling'], true ); ?> />
      	</td>
    	</tr>

    	<tr valign="top"><th scope="row"><label for="css"><?php _e('Custom Css', 'yummi_quotes')?></label></th>
      	<td>
      	   <textarea id="css" name="css" rows="5" cols="30"><?php echo stripslashes($qt['css']); ?></textarea>
      	</td>
    	</tr>

      <tr valign="top"><th scope="row"><label for="admin_dark"><?php _e('Admin Dark style', 'yummi_quotes')?></label></th>
      	<td>
      	   <input id="admin_dark" name="admin_dark" type="checkbox" <?php checked( (bool) $qt['admin_dark'], true ); ?> />
      	</td>
    	</tr>

    	</table>

      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="ver" value="<?php echo $qver ?>" />
      <input type="hidden" name="page_options" value="quotes_options" />

    	<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>

  	</form>

  	</div>

  	<?php
  }

endif;  // EndIf is_admin()
