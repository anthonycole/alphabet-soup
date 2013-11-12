<?php  
/*
Plugin Name: Alphabet Soup
Description: Alphabet Soup allows you to automatically sort your posts alphanumerically on your sites frontend.
Author: Anthony Cole
Version: 0.5a
*/


class Alphabet_Soup
{	
	private static $reg = array();
	private static $supported_pts = array();

	public static function init()
	{
		$default_pts = array('songwriter', 'discography');

		$supported_pts = apply_filters('abs_post_types', $default_pts);
		
		self::$supported_pts = $supported_pts;

		add_action('save_post', array(self::instance(), 'save_post'));
		add_action('update_post', array(self::instance(), 'save_post'));

		add_action('init', array(self::instance(), '_register'));

		add_action('admin_menu', array(self::instance(), '_register_menu'));

		register_activation_hook( __FILE__, array(self::instance(), '_register') );
	}


	public static function _register()
	{
		register_taxonomy(
		'alphabet-soup',
		self::$supported_pts,
			array(
				'label' => __( 'Alphabet Soup' ),
				'rewrite' => false,
				'hierarchical' => true,
				'query_var'	   => 'abs_letter',
				'show_ui'	   => false
			)
		);
	}

	public static function _activate()
	{
		// The taxonomy hasn't registered in the activation hook yet, so we do it ourselves. Woot.
		self::_register();

		$alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','numeric');
		foreach( $alphabet as $letter )
		{			
			wp_insert_term( $letter, 'alphabet-soup');
		}
	}

	public static function instance()
    {
        $cls = get_called_class();
        !isset(self::$reg[$cls]) && self::$reg[$cls] = new $cls;
        return self::$reg[$cls];
    }


	public static function save_post( $post_id )
	{

		// This means that $_POST isn't initialised.
		if( empty($_POST) )
			return;

		// Let's make sure that these are the post types we want.
		if ( !in_array($_POST['post_type'], self::$supported_pts) )
        	return;

        // Ignore revisions.
		if ( wp_is_post_revision( $post_id ) )
			return;

		self::_set_term($post_id, $_POST['post_title']);

	}

	public static function _set_term($post_id, $post_title) {

		$new_title = strtoupper(substr($post_title, 0, 1));
		
		if( is_numeric( $newtitle ) ) {
			$term = 'numeric';
		} else {
			$term = $new_title;
		}

		$term_id = term_exists( $term, 'alphabet-soup' );

		// Remove all references to any old post titles
		wp_delete_object_term_relationships( $post_id, 'alphabet-soup' ); 

		wp_set_post_terms( $post_id, $term_id, 'alphabet-soup');
	}


	public static function _register_menu()
	{
		add_management_page('Alphabet Soup Options', 'Alphabet Soup', 'manage_options', 'ab-options', array(self::instance(), '_management_page'));
	}

	public static function _batch_update($pt)
	{
		$args = array(
			'post_type' 	 => $pt,
			'posts_per_page' => -1,
		);

		$posts = get_posts($args);

		foreach($posts as $post)
		{
			self::_set_term($post->ID, $post->post_title);
			echo '<p>' . $post->post_title . ' is now alphabetised.</p>';
		}
	}

	public static function _management_page()
	{

		?>
		<div class="wrap">
			<h2>Alphabet Soup</h2>
			<?php  
			if( $_GET['update_pts'] && wp_verify_nonce($_GET['_wpnonce'], 'ab-check') && isset($_GET['pt']) && post_type_exists($_GET['pt']) ) : 
				self::_batch_update($_GET['pt']);
			else : 
				?>
				<h3>Batch Update</h3>
				<?php foreach(self::$supported_pts as $pt ) :  ?>
					<?php $post_type = get_post_type_object($pt); ?>
					<p><a class="button" href="<?php echo wp_nonce_url(admin_url('tools.php?page=ab-options&update_pts=yes&pt=' . $pt), 'ab-check'); ?>">Add <?php echo $post_type->labels->name; ?> to the index</a></p>
				<?php endforeach; ?>
				<?php
			endif;
			?>
		</div>
		<?php
	}
}

Alphabet_Soup::init();