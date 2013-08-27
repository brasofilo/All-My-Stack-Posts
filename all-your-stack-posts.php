<?php
/* Plugin Name: All Your Stack Posts
 * Description: Get all Questions or Answers from a given user in a given Stack site. 
 * Plugin URI: https://github.com/brasofilo/All-Your-Stack-Posts
 * Version:     1.1
 * Author:      Rodolfo Buaiz
 * Author URI:  http://stackexchange.com/users/1211516?tab=accounts
 * License: GPLv2 or later
 */

/*
All Stack Posts
Copyright (C) 2013  Rodolfo Buaiz

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
 
add_action(
	'plugins_loaded',
	array ( B5F_SE_MyQA::get_instance(), 'plugin_setup' )
);
register_activation_hook( 
		__FILE__, 
		array( 'B5F_SE_MyQA', 'register_project_template' ) 
);
register_deactivation_hook( 
		__FILE__, 
		array( 'B5F_SE_MyQA', 'deregister_project_template' ) 
);

class B5F_SE_MyQA
{
	protected static $instance = NULL;
	public $plugin_url = NULL;
	public $plugin_path = NULL;
	public $metabox;
	
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	public function plugin_setup()
	{
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );
		
		include_once('includes/metabox.php');
		$this->metabox = new B5F_SE_Metabox( $this->plugin_path, $this->plugin_url );
		
		add_action( 'init', array( $this, 'github_updater' ) );
	}
	
	public function __construct() {}
	
	public function github_updater()
	{
		include_once 'includes/updater.php';

		define( 'WP_GITHUB_FORCE_UPDATE', true );

		if ( !is_admin() )
			return;

		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'all-your-stack-posts',
			'api_url' => 'https://api.github.com/repos/brasofilo/All-Your-Stack-Posts',
			'raw_url' => 'https://raw.github.com/brasofilo/All-Your-Stack-Posts/master',
			'github_url' => 'https://github.com/brasofilo/All-Your-Stack-Posts',
			'zip_url' => 'https://github.com/brasofilo/All-Your-Stack-Posts/archive/master.zip',
			'sslverify' => true,
			'requires' => '3.0',
			'tested' => '3.6',
			'readme' => 'README.md',
			'access_token' => 'adb4be07dbaf1bd808f1e62a9681020dd381c0e1',
		);

		new WP_GitHub_Updater( $config );

	}
	
	public static function register_project_template()
	{ 
		// Get source and destination for copying from the plugin to the theme directory
		$destination = self::get_template_destination();
		$source = self::get_template_source();

		// Copy the template file from the plugin to the destination
		self::copy_page_template( $source, $destination );
	}
	
	public static function deregister_project_template()
	{
		// Get the path to the theme
		$template_path = get_stylesheet_directory() . '/template-stackapp.php';
		
		// If the template file is in the theme path, delete it.
		if( file_exists( $template_path ) )
			unlink( $template_path );
	}
	
	/**
	 * From page-template-plugin
	 * @return string The destination to the plugin directory relative to the currently active theme
	 */
	private static function get_template_destination() 
	{
		return get_template_directory() . '/template-stackapp.php';
	} 

	/**
	 * From page-template-plugin
	 * @return string The path to the template file relative to the plugin.
	 */
	private static function get_template_source() 
	{
		return dirname( __FILE__ ) . '/includes/template-stackapp.php';
	} 
	
	/**
	 * From page-template-plugin
	 * @param type $source
	 * @param type $destination
	 */
	private static function copy_page_template( $source, $destination )	
	{
		// Check if template already exists. If so don't copy it; otherwise, copy if
		if( ! file_exists( $destination ) ) 
		{
			// Create an empty version of the file
			touch( $destination );
			
			// Read the source file starting from the beginning of the file
			if( null != ( $handle = @fopen( $source, 'r' ) ) ) 
			{
				// Read the contents of the file into a string. Read up to the length of the source file
				if( null != ( $content = fread( $handle, filesize( $source ) ) ) ) 
				{
					// Relinquish the resource
					fclose( $handle );
				} 
			} 
						
			// Now open the file for reading and writing
			if( null != ( $handle = @fopen( $destination, 'r+' ) ) ) 
			{
				// Attempt to write the contents of the string
				if( null != fwrite( $handle, $content, strlen( $content ) ) ) 
				{
					// Relinquish the resource
					fclose( $handle );
				} 
			} 
		} 

	} 	
}

