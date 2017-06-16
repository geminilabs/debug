<?php
/**
 * Plugin Name: debug()
 * Description: Helper functions for development
 * Version: 1.0.0
 * Author: Paul Ryley
 * Author URI: http://geminilabs.io
 */

class GL_Development
{
	/**
	 * @var GL_Development
	 */
	protected static $instance;

	/**
	 * @return static
	 */
	public static function load()
	{
		if( is_null( static::$instance )) {
			static::$instance = new static;
		}
		return static::$instance;
	}

	/**
	 * @param mixed $value ...
	 * @return string
	 */
	public function capture()
	{
		ob_start();
		call_user_func_array( [$this, 'printF'], func_get_args() );
		return ob_get_clean();
	}

	/**
	 * @param mixed $value ...
	 * @return void
	 */
	public function debug()
	{
		call_user_func_array( [$this, 'printF'], func_get_args() );
	}

	/**
	 * @return bool
	 */
	public function isDev()
	{
		return WP_ENV == 'development' || ( defined( 'DEV' ) && !!DEV );
	}

	/**
	 * @return bool
	 */
	public function isProduction()
	{
		return WP_ENV == 'production';
	}

	/**
	 * @param string $hook
	 * @return void
	 */
	public function printFiltersFor( $hook = '' )
	{
		global $wp_filter;
		if( empty( $hook ) || !isset( $wp_filter[$hook] ))return;
		$this->printF( $wp_filter[ $hook ] );
	}

	/**
	 * @param mixed $value ...
	 * @return void
	 */
	protected function printF()
	{
		$args = func_num_args();

		if( $args == 1 ) {
			printf( '<div class="print__r"><pre>%s</pre></div>',
				htmlspecialchars( print_r( func_get_arg(0), true ), ENT_QUOTES, 'UTF-8' )
			);
		}
		else if( $args > 1 ) {
			echo '<div class="print__r_group">';
			foreach( func_get_args() as $value ) {
				$this->printF( $value );
			}
			echo '</div>';
		}
	}
}

/**
 * @return void
 */
if( !function_exists( 'printF_css' )) {
	function printF_css()
	{
		$wp_styles = wp_styles()->queue;
		if( empty( $wp_styles ))return;
		ob_start(); ?>
		#wpbody .print__r {
			margin-right: 20px;
		}
		.print__r {
			position: relative;
			max-height: 600px;
			overflow-y: scroll;
			background: #1d1f21;
			border-radius: 0;
			padding: 10px;
			margin: 12px 0;
		}
		.print__r pre {
			-webkit-font-smoothing: antialiased,
			-moz-osx-font-smoothing: grayscale,
			display: block;
			font: 200 12px/1.5 "Operator Mono", Consolas, monospace;
			white-space: pre-wrap;
			color: #b5bd68; //#c5c8c6;
			background: #1d1f21;
			text-align: left;
			text-shadow: 1px 1px 0 rgba(0,0,0,0.5);
			padding: 5px;
			margin: 0;
		}
		.print__r_group {
			position: relative;
			background: #1d1f21;
			border-radius: 0;
			padding: 1px;
			margin: 12px 0;
		}
		.print__r_group .print__r {
			background: #282a2e;
			border-radius: 2px;
			padding: 0;
			margin: 9px;
		}
		.print__r_group .print__r:not(:last-child) {
			margin-bottom: 10px;
		} <?php
		$css = ob_get_clean();
		wp_add_inline_style( $wp_styles[0], $css );
	}
	add_action( 'admin_enqueue_scripts', 'printF_css', 13 );
	add_action( 'wp_enqueue_scripts', 'printF_css', 13 );
}

/**
 * @param mixed $value ...
 * @return string
 */
if( !function_exists( 'capture' )) {
	function capture() {
		$dev = GL_Development::load();
		return call_user_func_array( [$dev, 'capture'], func_get_args() );
	}
}

/**
 * @param mixed $value ...
 * @return void
 */
if( !function_exists( 'debug' )) {
	function debug() {
		$dev = GL_Development::load();
		call_user_func_array( [$dev, 'debug'], func_get_args() );
	}
}

/**
 * @param string $hook
 * @return void
 */
if( !function_exists( 'debug_hook' )) {
	function debug_hook( $hook = '' ) {
		return GL_Development::load()->printFiltersFor( $hook );
	}
}
