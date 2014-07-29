<?php

/**
 * Template Engine for WordPress Plugins
 *
 * A class for including templating/theming in your WordPress Plugin.
 *
 * @package WordPress
 * 
 * @link http://smartmedia.no
 * @author Tor Morten Jensen / Smart Media AS
 *
 * @version 1.0.0
 */
if(!class_exists('Smart_Template_Engine')) {

	class Smart_Template_Engine {

		/**
		 * Class Version
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string $slug Plugin slug.
		 */

		public $version = '1.0.0';

		/**
		 * The slug for the plugin.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string $slug Plugin slug.
		 */

		public $slug;

		/**
		 * The files for the different post types single post types.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array $post_types Post Type Single Templates.
		 */

		public $post_types;

		/**
		 * The files for the different taxonomies and their templates.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array $taxonomies Taxonomy Templates.
		 */

		public $taxonomies;

		/**
		 * The files for the different archives and their templates.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array $archive Archive Templates.
		 */

		public $archives;

		/**
		 * Stylesheets to be loaded.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array $styles Stylesheets.
		 */

		public $styles;

		/**
		 * Plugin Directory Path.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string $directory Plugin Directory.
		 */

		public $directory;

		/**
		 * Plugin Directory URL.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string $url Plugin Directory.
		 */

		public $url;

		public function __construct($slug, $directory, $url, $post_types = array(), $archives = array(), $taxonomies = array(), $styles = array()) {

			/**
			 * Taxonomy Template.
			 *
			 * Filter the taxonomy template to replace with one of our templates.
			 *
			 * @since 1.0.0
			 *
			 * @param string $template The current tempalte.
			 * @return string The located template file if located.
			 */

			add_filter( 'taxonomy_template', array( $this, 'taxonomy_template' ) );

			/**
			 * Single Template.
			 *
			 * Filter the single template to replace with one of our templates.
			 *
			 * @since 1.0.0
			 *
			 * @param string $template The current tempalte.
			 * @return string The located template file if located.
			 */

			add_filter( 'single_template', array( $this, 'single_template' ) );

			/**
			 * Archive Template.
			 *
			 * Filter the archive template to replace with one of our templates.
			 *
			 * @since 1.0.0
			 *
			 * @param string $template The current tempalte.
			 * @return string The located template file if located.
			 */

			add_filter( 'archive_template', array( $this, 'archive_template' ) );

			/**
			 * Stylesheets.
			 *
			 * Load stylesheets from the correct location.
			 *
			 * @since 1.0.0
			 *
			 * @param string $template The current tempalte.
			 */

			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );


			/**
			 * Mandatory Variables.
			 *
			 * @since 1.0.0
			 */

			$this->slug = $slug; // the plugin slug
			$this->directory = $directory; // the directory
			$this->url = $url; // the url

			/**
			 * Optinal Variables.
			 *
			 * @since 1.0.0
			 */

			$this->post_types = $post_types; // the single templates
			$this->taxonomies = $taxonomies; // the taxonomy templates
			$this->archives = $archives; // the archive templates
			$this->styles = $styles; // the stylesheets

		}

		/**
		 * Get Themes.
		 *
		 * Locate themes availiable for the current slug.
		 *
		 * @since 1.0.0
		 *
		 * @return array An array of themes availiable.
		 */

		public function get_themes() {

			// the empty themes array
			$themes = array();

			// get themes from the plugin theme folder
			$path = $this->directory . '/themes/';

			// check that our path is a real directory
			if( is_dir( $path ) ) {

				// create and array of files and folders within the directory
				$results = scandir( $path );

				foreach ($results as $result) {

					// we do not want current and parent folders
				    if ($result === '.' or $result === '..') continue;

				    // if this is a real directory then put in our themes array
				    if ( is_dir( $path . '/' . $result) ) {
				        $themes[$result] = array( 'slug' => $result, 'path' => $path . '/' . $result );
				    }
				}
			}

			// get themes from the public theme folder
			$path = WP_CONTENT_DIR . '/'.$this->slug.'-themes/';

			// check if the public theme folder exists
			if(is_dir($path)) {

				// create and array of files and folders within the directory
				$results = scandir($path);

				foreach ($results as $result) {

					// we do not want current and parent folders
				    if ($result === '.' or $result === '..') continue;

				    // if this is a real directory then put in our themes array
				    if (is_dir($path . '/' . $result)) {
				        $themes[$result] = array('slug' => $result, 'path' => $path . '/' . $result);
				    }
				}
			}

			// return the final themes array
			return $themes;

		}

		/**
		 * Get Current Theme.
		 *
		 * Find the current theme for this slug.
		 *
		 * @since 1.0.0
		 *
		 * @return string The current theme.
		 */

		public function current_theme() {

			$option = get_option( $this->slug . '_current_theme' ) ? get_option( $this->slug . '_current_theme' ) : 'default';

			return $option;

		}

		/**
		 * Locate Template File.
		 *
		 * Searches the folders for a series of template files.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $template_names An array with template files to find.
		 * @param  string $type Optional. What kind of return you want ('dir' or 'url').
		 * @param  boolean $load Optional. Whether or not to load the template file.
		 * @param  boolean $require_once Optional. If we are to use require_once or include.
		 * @return string The located files path or URL depending on what you set in $type.
		 */

		public function locate( $template_names, $type = 'dir', $load = false, $require_once = true ) {

			// if the first parameter is not an array, break function
		    if ( !is_array($template_names) )
		        $template_names = array();

		    // empty return string
		    $located = '';

		    // get class properties
		    $directory = $this->directory; // the plugin directory
		    $url = $this->url; // the plugin url
		    $slug = $this->slug; // the current slug

		    // for backwards compatibility (previous versions of the class didn't have the theme option)
		    $this_plugin_dir = $directory . '/templates/';
		    $this_plugin_url = $url . '/templates/';

		    // the directories
		    $themedir = WP_CONTENT_DIR . '/' . $slug . '-themes/' . $this->current_theme() . '/'; // current theme in the public theme directory
		    $themeurl = WP_CONTENT_URL . '/' . $slug . '-themes/' . $this->current_theme() . '/'; // current theme in the public theme url
		    $theme_plugin_dir = $directory . '/themes/' . $this->current_theme() . '/'; // current theme in the plugin theme directory
		    $theme_plugin_url = $url . '/themes/' . $this->current_theme() . '/'; // current theme in the plugin theme url

		    // check if the current theme is located in the public directory
		    if( is_dir($themedir) ) {
		    	$theme = $themedir;
		    	$themeurl = $themeurl;
		    }
		    // if not we try in the plugin theme directory
		    elseif( !is_dir($themedir) && is_dir($theme_plugin_dir) ) {
		    	$theme = $theme_plugin_dir;
		    	$themeurl = $theme_plugin_url;
		    }
		    // this theme is imaginary, so we do not look for it in any themes
		    else {
		    	$theme = false;
		    	$themeurl = false;
		    }

		    // lets look through our templates
		    foreach ( $template_names as $template_name ) {

		    	// exit if no template name
		        if ( !$template_name )
		            continue;

		        // look for file in child theme template folder
		        if ( file_exists( get_stylesheet_directory() . '/'.$slug.'/' . $template_name ) ) {
		            $located = $type == 'dir' ? get_stylesheet_directory() . '/'.$slug.'/' . $template_name : get_stylesheet_directory_uri() . '/'.$slug.'/' . $template_name;

		            break;

		        }
		        // look for file in theme template folder
		        else if ( file_exists( get_template_directory() . '/'.$slug.'/' . $template_name ) ) {
		            $located = $type == 'dir' ? get_template_directory() . '/'.$slug.'/' . $template_name : get_template_directory_uri() . '/'.$slug.'/' . $template_name;
		            break;

		        }
		        // look for file in plugin theme folder
		        else if ( $theme && file_exists($theme . $template_name) ) {
		            $located = $type == 'dir' ? $theme . $template_name : $themeurl . $template_name;

		            break;

		        // for backwards compatibility
		        }
		        else if ( file_exists( $this_plugin_dir .  $template_name) ) {
		            $located = $type == 'dir' ? $this_plugin_dir . $template_name : $this_plugin_url . $template_name;

		            break;
		        }
		    }

		    // load the template if we found it
		    if ( $load && '' != $located && $type == 'dir')
		        load_template( $located, $require_once );

		    // return the located file
		    return $located;
		}

		/**
		 * Locate Taxonomy Template.
		 *
		 * Find the template files for our taxonomies.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $template_names The current template.
		 * @return string The theme template or the old one if nothing found.
		 */

		public function taxonomy_template($template) {

			global $wp_query;

			$taxonomies = $this->taxonomies;

			if($taxonomies) {
				foreach( $taxonomies as $tax => $files) {
					if( is_tax( $tax ) ) {

						$temp = $this->locate($files);

						if($temp)
							$template = $temp;

					}
				}
			}
			return $template;

		}

		/**
		 * Locate Archive Template.
		 *
		 * Find the template files for our archives.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $template_names The current template.
		 * @return string The theme template or the old one if nothing found.
		 */

		public function archive_template($template) {

			global $wp_query;

			$archives = $this->archives;

			if($archives) {
				foreach( $archives as $type => $files ) {

					if( is_post_type_archive( $type ) ) {

						$temp = $this->locate($files);

						if($temp)
							$template = $temp;

					}
				}
			}

			return $template;

		}

		/**
		 * Locate Single Template.
		 *
		 * Find the template files for our singles.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $template_names The current template.
		 * @return string The theme template or the old one if nothing found.
		 */

		public function single_template($template) {

			global $wp_query, $post;

			$post_types = $this->post_types;

			if($post_types) {
				foreach( $post_types as $type => $files ) {
					if( $post->post_type == $type ) {

						$temp = $this->locate($files);

						if($temp)
							$template = $temp;

					}
				}
			}

			return $template;

		}

		/**
		 * Locate Taxonomy Template.
		 *
		 * Find the template files for our taxonomies.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $template_names The current template.
		 */

		public function styles() {

			$styles = $this->styles;

			$i = 0;

			if($styles) {
				foreach($styles as $style => $files) {
					$located = $this->locate($files, 'url');
					if($located) {
						$i++;
						wp_enqueue_style( $this->slug . '-style-' . $i, $located );
					}
				}
			}

		}

	}

}
/**
 * Get a template.
 *
 * Find a template and load it, just like the WordPress get_template_part()-function.
 *
 * @since 1.0.0
 *
 * @param object $engine The instance of your template engine.
 * @param string $slug The slug of the file.
 * @param string $name Optional. The name of the file.
 * @param boolean $include Optional. If we are loading the file.
 * @param boolean $require_once Optional. If we are requiring the file.
 */

if(!function_exists('smart_get_template_part')) {
	function smart_get_template_part($engine, $slug, $name = null, $include = true, $require_once = true) {

		if(!is_object($engine))
			return;

		$templates = array();
		$name = (string) $name;
		if ( '' !== $name )
			$templates[] = "{$slug}-{$name}.php";

		$templates[] = "{$slug}.php";

		$located = $engine->locate( $templates );

		if($include)
			load_template( $located, $require_once );
		else
			return $located;

	}
}

?>
