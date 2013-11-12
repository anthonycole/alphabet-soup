<?php  
/*
Plugin Name: Alphabet Soup
*/

class Alphabet_Soup
{	
	private static $reg = array();

	public static function init()
	{
		add_action('save_post', array(self::instance(), 'save_post'));
	}

	public static function save_post( $post_id )
	{
		if ( wp_is_post_revision( $post_id ) )
			return;
	}

	public static function instance()
    {
        $cls = get_called_class();
        !isset(self::$reg[$cls]) && self::$reg[$cls] = new $cls;
        return self::$reg[$cls];
    }
}

Alphabet_Soup::init();


?>