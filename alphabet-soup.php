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
		self::$supported_pts = array('post', 'page');
		add_action('save_post', array(self::instance(), 'save_post'));
		add_action('update_post', array(self::instance(), 'save_post'));
		add_action('init', array(self::instance(), '_register'));
		register_activation_hook( __FILE__, array(self::instance(), '_activate') );
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
				'query_var'	   => 'abs_letter'
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

		$term = self::sanitize_title($_POST['post_title']);

		$term_id = term_exists( $term, 'alphabet-soup' );

		// Remove all references to any old post titles
		wp_delete_object_term_relationships( $post_id, 'alphabet-soup' ); 

		wp_set_post_terms( $post_id, $term_id, 'alphabet-soup');

	}

	public static function sanitize_title($post_title)
	{
		$newtitle = strtoupper(substr($post_title, 0, 1));
		
		if( is_numeric( $newtitle ) ) {
			return 'numeric';
		} else {
			return $newtitle;
		}
	}
}

Alphabet_Soup::init();

?>