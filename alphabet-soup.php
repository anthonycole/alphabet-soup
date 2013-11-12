<?php  
/*
Plugin Name: Alphabet Soup
*/

class Alphabet_Soup
{	
	private static $reg = array();

	private static $supported_pts = array();

	public static function init()
	{
		self::$supported_pts = array('post', 'page');
		add_action('save_post', array(self::instance(), 'save_post'));
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

		$post_title = $_POST['post_title'];

	}

	public static function sanitize_title($post_title)
	{
		$newtitle = strtoupper(substr($post_title, 0, 1));
		
		if( is_numeric( $post_title ) ) {
			return '#';
		} else {
			return $newtitle;
		}
	}

}

Alphabet_Soup::init();


?>