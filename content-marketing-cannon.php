<?
/*
Plugin Name: Content Marketing Cannon
Plugin URI: http://seoroi.com/content-marketing#cannon
Description: Create Wikipedia-style comprehensive, authority articles with hardly any effort! Make better use of internal link juice, increase your linkability, and hit more longtail phrases!
Author: SEO ROI
Version: 1.0
Author URI: http://book.seoroi.com
*/

	function postsColumnsHeader($columns) {
		$columns['postID'] = 'Post ID';
		return $columns;
	}

	function postsColumnsRow($columnTitle, $postID)
	{
		if($columnTitle == 'postID'){
			echo $postID;
		}
	}

	function my_the_content_filter($content) 
	{
		global $post;

		$pattern = '/\[spid=([0-9]+)\]/i';
		preg_match_all($pattern, $content, $matches);
		$arr_ids=array();
		foreach($matches[1] as $cur)
		{
			if (strpos($cur, 'spid=')===false)
			{
				$arr_ids[]=$cur;
			}
		}
		
		$arr_table_of_contents=array();
		foreach ($arr_ids as $post_id)
		{
			$queried_post = get_post($post_id);
			$post_content=$queried_post->post_content;
			$post_title=$queried_post->post_title;
			if (strlen(trim($queried_post->post_title))==0){continue;}

			$arr_table_of_contents[]='<li style="color:#0645AD;"><a href="#subarticle_'.$post_id.'" style="text-decoration:none !important; color:#0645AD;">'.$queried_post->post_title.'</a></li>';
			$post_content=str_replace ("\n", '<br />', $post_content);
			$content=str_replace ("[spid={$post_id}]", "<a name='subarticle_{$post_id}'></a></p><p style='float: left;'><h2>{$post_title}</h2>".$post_content.'', $content);

		}
		$arr_table_of_contents=implode ('', $arr_table_of_contents);

		$content=str_replace ("[toc align=left]", '<div style="border:1px solid #AAA;; background:#F9F9F9; float:left; margin:0px 15px 0px 0px; padding:5px;"><strong style="display:block;width:100%;text-align:center;">Table of Contents</strong><ol>'.$arr_table_of_contents.'</ol></div>', $content);

		$content=str_replace ("[toc align=right]", '<div style="border:1px solid black; background:#F9F9F9; float:right; margin:0px 0px 0px 15px; padding:5px;"><strong style="display:block;width:100%;text-align:center;">Table of Contents</strong><ol>'.$arr_table_of_contents.'</ol></div>', $content);

		return  $content;
	}


	function localbuilder_get_spids($str)
	{
		$arr_ids=array();
		$pattern = '/\[spid=([0-9]+)\]/i';
		preg_match_all($pattern, $str, $matches);
		foreach($matches[1] as $cur)
		{
			if (strpos($cur, 'spid=')===false)
			{
				$arr_ids[]=$cur;
			}
		}
		return $arr_ids;
	}

	function my_save_function($post_ID, $post) 
	{
		if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {return;}

		// If we don't have any errors - validate
		// if (get_post_meta($post_ID, '_localbuilder_validation', true)=='')
		if (get_option('_localbuilder_validation')=='')
		{
				// VALIDATE POST
				// Get all spids
				$arr_ids=localbuilder_get_spids ($post->post_content);
				foreach ($arr_ids as $cur)
				{
					$inc_post=get_post($cur, ARRAY_A);
					$inc_arr=localbuilder_get_spids ($inc_post['post_content']);
					if (in_array($post_ID, $inc_arr))
					{
						// update_post_meta($post_ID, '_localbuilder_validation', "<p>ERROR: The article with Post ID {$cur} already includes this article itself, and including it would create a loop that would break both articles.</p><p>Please remove shortcode [spid={$cur}] from this article or update the Post ID.</p>");
						
						update_option ('_localbuilder_validation', "<p>ERROR: The article with Post ID {$cur} already includes this article itself, and including it would create a loop that would break both articles.</p><p>Please remove shortcode [spid={$cur}] from this article or update the Post ID.</p>");

						/*						
												$my_post = array();
												$my_post['ID'] = $post_ID;
												$my_post['post_status'] = 'draft';
												wp_update_post( $my_post );
						*/
					}

					// Validate that post ID exists
					$rel_post=array();
					$rel_post = get_post($cur) ;

					switch ($rel_post->post_status)
					{
						case 'publish': case 'pending': case 'draft': case 'auto-draft': case 'future': case 'private': case 'subarticle':
							// Post found					
						break;

						default: 
							update_option ('_localbuilder_validation', "<p>ERROR: There is no Post ID {$cur}, so I can't include an article for it. </p><p>Please remove shortcode [spid={$cur}] from this article or update the Post ID.</p>");
							/*
							$my_post = array();
							$my_post['ID'] = $post_ID;
							$my_post['post_status'] = 'pending';
							wp_update_post( $my_post );		
							*/
						break;
					}
				}
		}

		$redirect_url=get_post_meta($post_ID, '_meta_redirect_cannon_url', true );

		if ($post->post_type!='revision' && strlen($redirect_url)>0)
		{
			set_post_type($post->ID, 'subarticle');
		}

		if ($post->post_type!='revision' && strlen($redirect_url)==0)
		{
			set_post_type($post->ID, 'post');
		}
	}	
	
	function aaa() 
	{
		register_post_type( 'subarticle',
		array(
			'labels' => array(
				'name' => __( 'Sub-articles' ),
				'singular_name' => __( 'Sub-article' )
			),
		'public' => true,
		'has_archive' => true
		)
		);
	}


function myplugin_activate() {
  update_option ('_seoroi_rss', "true");
}

register_activation_hook( __FILE__, 'myplugin_activate' );

/*****************************************************************************************************************/
/*****************************************************************************************************************/
function localbuilder_admin_notice_handler() 
{
    $errors = get_option('_localbuilder_validation');
    if($errors) 
    {
        echo '<div class="error"><p>' . $errors . '</p></div>';
    }   
	update_option('_localbuilder_validation', false);
}
add_action( 'admin_notices', 'localbuilder_admin_notice_handler' );
/*****************************************************************************************************************/

/* Add a new meta box to the admin menu. */
	add_action( 'admin_menu', 'hybrid_create_meta_box' );

/* Saves the meta box data. */
	add_action( 'post_updated', 'hybrid_save_meta_data' );

function hybrid_create_meta_box() {
	global $theme_name;
	add_meta_box( 'post-meta-boxes', __('Content Marketing Cannon redirection'), 'post_meta_boxes', 'post', 'normal', 'high' );
	add_meta_box( 'subarticle-meta-boxes', __('Content Marketing Cannon redirection'), 'subarticle_meta_boxes', 'subarticle', 'normal', 'high' );
}

function hybrid_post_meta_boxes() {
	/* Array of the meta box options. */
	$meta_boxes = array('_meta_redirect_cannon_url' => array( 'name' => '_meta_redirect_cannon_url', '_meta_redirect_cannon_url' => __('Redirect this blog post to:', 'hybrid'), 'type' => 'text' ));
	return apply_filters( 'hybrid_post_meta_boxes', $meta_boxes );
}

function hybrid_subarticle_meta_boxes() {
	/* Array of the meta box options. */
	$meta_boxes = array('_meta_redirect_cannon_url' => array( 'name' => '_meta_redirect_cannon_url', '_meta_redirect_cannon_url' => __('Redirect this blog post to:', 'hybrid'), 'type' => 'text' ));
	return apply_filters( 'hybrid_subarticle_meta_boxes', $meta_boxes );
}


function post_meta_boxes() {
	global $post;
	$meta_boxes = hybrid_post_meta_boxes(); ?>

	<table class="form-table">
	<?php foreach ( $meta_boxes as $meta ) :

		$value = get_post_meta( $post->ID, $meta['name'], true );

		if ( $meta['type'] == 'text' )
			get_meta_text_input( $meta, $value );
		elseif ( $meta['type'] == 'textarea' )
			get_meta_textarea( $meta, $value );
		elseif ( $meta['type'] == 'select' )
			get_meta_select( $meta, $value );

	endforeach; ?>
	</table>
<?php
}

/**
 * Displays meta boxes on the Write subarticle panel.  Loops
 * through each meta box in the $meta_boxes variable.
 * Gets array from hybrid_subarticle_meta_boxes()
 *
 * @since 0.3
 */
function subarticle_meta_boxes() {
	global $post;
	$meta_boxes = hybrid_subarticle_meta_boxes(); ?>

	<table class="form-table">
	<?php foreach ( $meta_boxes as $meta ) :

		$value = stripslashes( get_post_meta( $post->ID, $meta['name'], true ) );

		if ( $meta['type'] == 'text' )
			get_meta_text_input( $meta, $value );
		elseif ( $meta['type'] == 'textarea' )
			get_meta_textarea( $meta, $value );
		elseif ( $meta['type'] == 'select' )
			get_meta_select( $meta, $value );

	endforeach; ?>
	</table>
<?php
}

/**
 * Outputs a text input box with arguments from the
 * parameters.  Used for both the post/subarticle meta boxes.
 *
 * @since 0.3
 * @param array $args
 * @param array string|bool $value
 */
function get_meta_text_input( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<th style="width:150px ;">
			<label for="<?php echo $name; ?>"><?php echo $_meta_redirect_cannon_url; ?></label>
		</th>
		<td>
			<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo wp_specialchars( $value, 1 ); ?>" size="30" tabindex="30" style="width: 97%;" />
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<tr>
		<td colspan=2>(Only include the authority article's slug e.g. 'my-authority-article' without the quotes)</td>
	</tr>
	<?php
}

/**
 * Outputs a select box with arguments from the
 * parameters.  Used for both the post/subarticle meta boxes.
 *
 * @since 0.3
 * @param array $args
 * @param array string|bool $value
 */
function get_meta_select( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<th style="width:10%;">
			<label for="<?php echo $name; ?>"><?php echo $_meta_redirect_cannon_url; ?></label>
		</th>
		<td>
			<select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
			<?php foreach ( $options as $option ) : ?>
				<option <?php if ( htmlentities( $value, ENT_QUOTES ) == $option ) echo ' selected="selected"'; ?>>
					<?php echo $option; ?>
				</option>
			<?php endforeach; ?>
			</select>
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}

/**
 * Outputs a textarea with arguments from the
 * parameters.  Used for both the post/subarticle meta boxes.
 *
 * @since 0.3
 * @param array $args
 * @param array string|bool $value
 */
function get_meta_textarea( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<th style="width:10%;">
			<label for="<?php echo $name; ?>"><?php echo $_meta_redirect_cannon_url; ?></label>
		</th>
		<td>
			<textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" cols="60" rows="4" tabindex="30" style="width: 97%;"><?php echo wp_specialchars( $value, 1 ); ?></textarea>
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}

function hybrid_save_meta_data( $post_id ) {
	global $post;

	if ( 'subarticle' == $_POST['post_type'] )
		$meta_boxes = array_merge( hybrid_subarticle_meta_boxes() );
	else
		$meta_boxes = array_merge( hybrid_post_meta_boxes() );

	foreach ( $meta_boxes as $meta_box ) :

		if ( !wp_verify_nonce( $_POST[$meta_box['name'] . '_noncename'], plugin_basename( __FILE__ ) ) )
			return $post_id;

		if ( 'subarticle' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		elseif ( 'post' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$data = stripslashes( $_POST[$meta_box['name']] );

		if ( get_post_meta( $post_id, $meta_box['name'] ) == '' )
			add_post_meta( $post_id, $meta_box['name'], $data, true );

		elseif ( $data != get_post_meta( $post_id, $meta_box['name'], true ) )
			update_post_meta( $post_id, $meta_box['name'], $data );

		elseif ( $data == '' )
			delete_post_meta( $post_id, $meta_box['name'], get_post_meta( $post_id, $meta_box['name'], true ) );

	endforeach;
}


/*****************************************************************************************************************/
add_action('wp_dashboard_setup', 'my_dashboard_widgets');
function my_dashboard_widgets() {
     global $wp_meta_boxes;
     unset(
          $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'],
          $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'],
          $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']
     );
	$show_seoroi_rss=get_option('_seoroi_rss');
    // add a custom dashboard widget
	if ($show_seoroi_rss=='true')
	{
	     wp_add_dashboard_widget( 'dashboard_custom_feed', 'SEOROI News', 'dashboard_custom_feed_output' ); //add new RSS feed output
	}
}
function dashboard_custom_feed_output() 
{
     echo '<div class="rss-widget">';
     wp_widget_rss_output(array(
          'url' => 'http://feeds.feedburner.com/seoroi-services',
          'title' => 'SEOROI News',
          'items' => 5,
          'show_summary' => 0,
          'show_author' => 0,
          'show_date' => 0
     ));
     echo "</div>";
}

/*****************************************************************************************************************/

add_action('template_redirect','custom_field_redirect');
function custom_field_redirect() 
{
	global $wp_query, $key, $status;

	if (is_single())
	{
		$redirect_url=get_post_meta( $wp_query->post->ID, '_meta_redirect_cannon_url', true );

		if (strlen ($redirect_url)>0)
		{
			global $wpdb;
			$p = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name like '%{$redirect_url}%'");
			if ($p)
			{
				wp_redirect(get_permalink($p), 301);
				exit();
			}
			else
			{
				wp_redirect($redirect_url, 301);
				exit();
			}				
		}
	}

}

function localbuilder_redirect_canonical_filter ($redirect, $request)
{
	$posts_array = get_posts(array('post_type' => 'subarticle', 'post_status' => 'publish'));

	if (strpos($redirect, '/subarticle/')!==false)
	{
		foreach($posts_array as $cur_post)
		{
			if ($redirect==get_permalink($cur_post->ID))
			{


				$redirect_url=get_post_meta( $cur_post->ID, '_meta_redirect_cannon_url', true );
/*				if (strlen ($redirect_url)>0)
				{
					wp_redirect($redirect_url, 301 );
					exit();
				}
*/
if (strlen ($redirect_url)>0)
		{
			global $wpdb;
			$p = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name like '%{$redirect_url}%'");
			if ($p)
			{
				wp_redirect(get_permalink($p), 301);
				exit();
			}
			else
			{
				wp_redirect($redirect_url, 301);
				exit();
			}				
		}
			}
		}
	}
	exit();
}
add_filter( 'redirect_canonical', 'localbuilder_redirect_canonical_filter', 10, 2 );

/*
	add_filter( 'pre_get_posts', 'my_get_posts' );

	function my_get_posts( $query ) {
		if ( is_home() )
			$query->set( 'post_type', array( 'post', 'page', 'album', 'movie', 'quote', 'attachment', 'subarticle' ) );

		return $query;
	}
*/
/*****************************************************************************************************************/

	// Show Post ID header and value for posts list in admin
	add_filter( 'manage_posts_columns', 'postsColumnsHeader' );
	add_filter( 'manage_posts_custom_column', 'postsColumnsRow', 10, 2 );

	// Find subarticles inclusion and rewrite content
	add_filter ('the_content', 'my_the_content_filter' );

	// Add Save Post handlers
	add_action ('post_updated', 'my_save_function', 10, 2);

	// Register custom post type
	add_action ('init', 'aaa');
	
?>
<?php
// create custom plugin settings menu
add_action('admin_menu', 'baw_create_menu');

function baw_create_menu() {

	//create new top-level menu
	add_options_page ('Content Marketing Cannon', 'Content Marketing Cannon', 'administrator', __FILE__, 'baw_settings_page');
	
	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}


function register_mysettings() {
	register_setting( 'baw-settings-group', 'new_option_name' );
	register_setting( 'baw-settings-group', 'some_other_option' );
	register_setting( 'baw-settings-group', 'option_etc' );
}

function PluginUrl() {

        //Try to use WP API if possible, introduced in WP 2.6
        if (function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));

        //Try to find manually... can't work if wp-content was renamed or is redirected
        $path = dirname(__FILE__);
        $path = str_replace("\\","/",$path);
        $path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
        return $path;
    }

function baw_settings_page() 
{
	$img_url=PluginUrl().'images';

	echo "<style>
		#cmc_support .line {float:left;width:100%; clear:both;}
		#cmc_support .line span{float:left;width:100px; padding-right:10px; display:inline-block;}
		#cmc_support .line input[type=text], #cmc_support .line textarea{width:400px;}
		#cmc_support .line.btn {margin-top:10px; text-align:right; width:510px}
		#cmc_support .line.btn2 {margin-top:20px;}
	</style>";
?>
<style>
	body	{
	margin:0 auto;
	padding:0;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#000000;
	line-height:18px;
	}
	
h1, h2	{
	font-size:18px;
	color:#004ca6;
	}
	
h3	{
	font-size:18px;
	color:#fff;
	}
	
#MainWrapper	{
	margin:0 auto;
	padding:0;
	width:1023px;
	padding-top:13px;
	}
	
#MainPadding	{
	padding:8px 13px 10px 10px;
	}
	
#ContentBg-rep	{
	background-image:url(<?=$img_url;?>/content-bg-rep.jpg);
	background-repeat:repeat-y;	
	}
	
#ContentLeft	{
	width:600px;
	}
	
#ContentRight	{
	width:384px;
	}
	
#ContentTopBg	{
	background-image:url(<?=$img_url;?>/contentright-header.jpg);
	background-repeat:no-repeat;
	height:53px;
	text-align:center;
	padding:6px 0px 0px 0px;
	}
	
#ContentRightBg-rep	{
	background-image:url(<?=$img_url;?>/contentright-bg-rep.jpg);
	background-repeat:repeat-y;
	width:371px;
	}

#ContentRightPadding	{
	padding:6px 10px 6px 10px;
	width:351px;
	}
	
#RequestBtn	{
	background-image:url(<?=$img_url;?>/make-request.jpg);
	border:0;
	cursor:pointer;
	outline:none;
	width:155px;
	height:41px;
	}
#SubscribeBtn	{
	background-image:url(<?=$img_url;?>/download-chapter.jpg);
	border:0;
	cursor:pointer;
	outline:none;
	width:229px;
	height:41px;
	}	
	
	
/****CLASS****/
.clear	{
	clear:both;
	}	
	
.fl_left	{
	float:left
	}
	
.fl_right	{
	float:right
	}
	
.textbox	{
	border:1px solid #b9b9b9;
	width:268px;
	height:27px;
	}
	
.textarea	{
	border:1px solid #b9b9b9;
	width:268px;
	height:96px;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	}

</style>
<div id="MainWrapper">
      <div id="ContentBg-rep"> <img border="0" alt="" src="<?=$img_url;?>/content-bg-top.jpg" />
        <br />
        <br />
        <div id="MainPadding">
          <div  class="wrap">
			<h2>Content Marketing Cannon Plugin Options</h2><p>by <strong>Gab Goldenberg</strong> of <strong>SEOROI.com</strong></p>
          </div>
          <div class="clear"></div>
          <br />
          <br />
          <div>
            <div id="ContentLeft" class="fl_left">
				<?
						switch ($_REQUEST['act'])
						{
							case 'update_options': 
								if ($_REQUEST['chk_seoroi_rss']==1)
								{
									update_option ('_seoroi_rss', "true");
								}
								else
								{
									update_option ('_seoroi_rss', "false");				
								}
?>
										  <h1>Want tech support or customization?</h1>
										  Fill out the form below. If need be, I (Gab Goldenberg)
										  will put you in contact with Jacob Share of <a href="http://shareselectmedia.com/">Share Select Media</a>, who programmed the plugin. Time is
										  money, so Jacob will let you know at his sole discretion
										  whether you need to pay or not.<br />
										  <br />
										  <h2>Want a link for sponsoring a plugin update / new feature?</h2>
										  You can get a link on the plugin's page on my blog, by
										  sponsoring an update to keep the plugin current with
										  WordPress, or by sponsoring a feature request.<br />
										  <br />
										  Not all feature requests will be accepted, but all are
										  read and noted. If you want to virtually guarantee the
										  feature's acceptance, pay for it.
<?
							break;

							case 'send_message': 
								$name=$_REQUEST['message_name'];
								$email=$_REQUEST['message_email'];
								$subject=$_REQUEST['message_subject'];
								$text=$_REQUEST['message_text'];

								$result=wp_mail('gab@seoroi.com', 'Content Marketing Cannon Request', "{$name}\n{$email}\n{$subject}\n{$text}");
								if ($result==false)
								{
									// Error sending message
									echo "<h2>Oops</h2><p>We weren't able to process your request for some reason. Sorry about that! It's our fault.</p><p>Would you mind trying again later please? Thanks!</p>";
								}
								else
								{
									echo "<h2>Thank You</h2><p>Thank you for contacting us! We'll reply as soon as possible.</p>";
								}
							break;
							default: 
							?>
										  <h1>Want tech support or customization?</h1>
										  Fill out the form below. If need be, I (Gab Goldenberg)
										  will put you in contact with Jacob Share of <a href="http://shareselectmedia.com/">Share Select Media</a>, who programmed the plugin. Time is
										  money, so Jacob will let you know at his sole discretion
										  whether you need to pay or not.<br />
										  <br />
										  <h2>Want a link for sponsoring a plugin update / new feature?</h2>
										  You can get a link on the plugin's page on my blog, by
										  sponsoring an update to keep the plugin current with
										  WordPress, or by sponsoring a feature request.<br />
										  <br />
										  Not all feature requests will be accepted, but all are
										  read and noted. If you want to virtually guarantee the
										  feature's acceptance, pay for it.
							<?							
						break;
						}
				?>
				<style>
					.red{
						border-color:red !important;
						background:red !important;
					}
				</style>
				<script>
					function validate()
					{
						jQuery('#message_email').removeClass('red');
						if ((jQuery('#message_email').val().indexOf(".") > 2) && (jQuery('#message_email').val().indexOf("@") > 0))
						{
						}
						else
						{
							jQuery('#message_email').addClass('red');
							jQuery('#message_email').focus();
							return false;
						}
						return true;						
					}
				</script>
              <h2>Request support, customization, or sponsor opportunities:</h2>
              <form method="post" onsubmit="return validate();">
				<input type=hidden name="act" value="send_message">
                <table width="330" cellspacing="3" cellpadding="3" border="0">
                  <tbody>
                    <tr>
                      <td width="60px" valign="top" align="left">Name:</td>
                      <td valign="middle" align="left"><input type="text" class="textbox" name="message_name" /></td>
                    </tr>
                    <tr>
                      <td valign="top" align="left">Email:</td>
                      <td valign="middle" align="left"><input type="text" class="textbox" id="message_email" name="message_email" /></td>
                    </tr>
                    <tr>
                      <td valign="top" align="left">Subject:</td>
                      <td valign="middle" align="left"><input type="text" class="textbox" name="message_subject" /></td>
                    </tr>
                    <tr>
                      <td valign="top" align="left">Message</td>
                      <td valign="middle" align="left"><textarea name="message_text" class="textarea"></textarea></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><input type="submit" value=" " id="RequestBtn" /></td>
                    </tr>
                  </tbody>
                </table>
              </form>
		<?
		echo '<form method=post>';
		echo '<input type=hidden name="act" value="update_options">';
		echo '<input type=hidden name="sub_act" value="rss">';
		$show_seoroi_rss=get_option('_seoroi_rss');
		if ($show_seoroi_rss=='true')
		{
			echo "<p>Display SEOROI News on the Dashboard <input name='chk_seoroi_rss' type=checkbox value='1' checked></p>";		
		}
		else
		{
			echo "<p>Display SEOROI News on the Dashboard <input name='chk_seoroi_rss' value='1' type=checkbox></p>";		
		}

		echo "<p><input type=submit value='Update Options' class='button-secondary action'></p>";
		echo '</form>';?>

            </div>
            <div id="ContentRight" class="fl_right">
              <div id="ContentRightBg-rep">
                <div id="ContentTopBg">
                  <h3>Want a free advanced SEO book chapter?</h3>
                </div>
                <br />
                <div id="ContentRightPadding"> - Enjoy SEO by getting
                  creative and making it a fun, stimulating game<br />
                  - Grow your business, even in highly competitive
                  markets<br />
                  - Earn respect and raises from your clients or bosses<br />
                  <br />
                  <strong>Q: What are the rules about?</strong><br />
                  <strong>A:</strong> They're principles of critical
                  thinking, which enable creativity and powerful SEO. By
                  mastering these principles, you'll be able to develop
                  your own advanced tactics. <br />
                  <br />
                  <strong>Q: How do I know the tactics are advanced, not
                    rehash?</strong><br />
                  <strong>A:</strong> Download your free chapter! The
                  chapter you get is called <strong style="font-weight: normal;">&quot;How
                    conditional CSS Boosts SEO Conversion Rates&quot; </strong>
                  <br />
                  <br />
                  <h2>Download your free chapter</h2>
                  <form action="http://www.aweber.com/scripts/addlead.pl"
                    method="post"> <input type="hidden" value="gab-seo-book"
                      name="listname" /> <input type="hidden" value="http://book.seoroi.com/thanks.html"
                      name="redirect" /> <input type="hidden" value="plugin page form"
                      name="meta_adtracking" /> <input type="hidden" value="1"
                      name="meta_message" /> <input type="hidden" value="name,email"
                      name="meta_required" /> <input type="hidden" value="1"
                      name="meta_forward_vars" />
                    <table width="330" cellspacing="3" cellpadding="3" border="0">
                      <tbody>
                        <tr>
                          <td>Name:</td>
                          <td><input type="text" class="textbox" value="" name="name" /></td>
                        </tr>
                        <tr>
                          <td>Email:</td>
                          <td><input type="text" class="textbox" value=""
                              name="email" /></td>
                        </tr>
                        <tr>
                          <td></td>
                          <td><input type="submit" value=" " id="SubscribeBtn" name="submit" /><br />
                            <div style="text-align: center;"><a target="_blank"
                                href="http://book.seoroi.com/privacy.html">Privacy Policy</a><br />
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <br />
                    <br />
                  </form>
                </div>
                <br />
                <br />
                <img border="0" alt="" src="<?=$img_url;?>/contentright-bg-bottom.jpg" />
              </div>
            </div>
          </div>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
        <img border="0" alt="0" src="<?=$img_url;?>/content-bg-bottom.jpg" /> </div>
    </div>


<?
}

?>