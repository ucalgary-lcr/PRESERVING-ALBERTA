<?php
// Remove wordpress junk from header
remove_action('wp_head', 'rsd_link'); // remove really simple discovery link
remove_action('wp_head', 'wp_generator'); // remove wordpress version
remove_action('wp_head', 'feed_links', 2); // remove rss feed links (make sure you add them in yourself if youre using feedblitz or an rss service)
remove_action('wp_head', 'feed_links_extra', 3); // removes all extra rss feed links
remove_action('wp_head', 'index_rel_link'); // remove link to index page
remove_action('wp_head', 'wlwmanifest_link'); // remove wlwmanifest.xml (needed to support windows live writer)
remove_action('wp_head', 'start_post_rel_link', 10, 0); // remove random post link
remove_action('wp_head', 'parent_post_rel_link', 10, 0); // remove parent post link
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // remove the next and previous post links
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );


// Remove all HTML comments from being published from plugins
function callback($buffer)
{
  $buffer = preg_replace('/<!--(.|s)*?-->/', '', $buffer);
	return $buffer;
}
function buffer_start()
{
  ob_start("callback");
}
function buffer_end()
{
  ob_end_flush();
}
add_action('get_header', 'buffer_start');
add_action('wp_footer', 'buffer_end');


// Add custom admin login logo and replace the Wordpress logo
function add_login_logo()
{
 echo '<style type="text/css">
  .login h1 a {
   width: 220px;
   height: 120px;
   background-image: url('.get_template_directory_uri().'/images/login-logo.png) !important;
   background-size: 220px 120px;
   background-repeat: no-repeat;
  }
</style>';
}
add_action('login_head', 'add_login_logo');

// Change the admin logo url destination
function add_login_url()
{
  return 'http://www.ucalgary.ca';
}
add_filter('login_headerurl', 'add_login_url');


// Change admin logo title attribute
function my_menu_notitle()
{
return 'UCalgary';
}
add_filter('login_headertitle', 'my_menu_notitle');




// Add theme support
function custom_theme_setup() {

  $headerDefaults = array(
  	'default-image'          => '',
  	'width'                  => 1200,
  	'height'                 => 0,
  	'flex-height'            => true,
  	'flex-width'             => false,
  	'uploads'                => true,
  	'random-default'         => false,
  	'header-text'            => true,
  	'default-text-color'     => '',
  	'wp-head-callback'       => '',
  	'admin-head-callback'    => '',
  	'admin-preview-callback' => '',
  );
  add_theme_support('custom-header', $headerDefaults);

  add_theme_support('title-tag');
  add_theme_support('custom-background');
  add_theme_support('post-thumbnails');
  add_theme_support('automatic-feed-links');


}
add_action('after_setup_theme', 'custom_theme_setup');


// Setup theme content width
function theme_content_width() {
	$GLOBALS['content_width'] = apply_filters('theme_content_width', 1200);
}
add_action('after_setup_theme', 'theme_content_width', 0);

// include custom jQuery
function shapeSpace_include_custom_jquery() {

	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', array(), null, true);
  wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js', array(), null, true);
  wp_enqueue_script('bootstrap-select-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js', array(), null, true);
  wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array(), null, true);
  wp_enqueue_script('fontawesome', 'https://use.fontawesome.com/a4a20cabef.js', array(), null, true);
  wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.10.0/js/lightbox.min.js', array(), null, true);

}
add_action('wp_enqueue_scripts', 'shapeSpace_include_custom_jquery');

// Add bootstrap support to the Wordpress theme
function theme_add_bootstrap()
{
    // wp_enqueue_script('jquery-2.2.4-min-js', get_template_directory_uri() . 'js/jquery-2.2.4.min.js');
    wp_enqueue_script('modernizr-js', get_template_directory_uri() . '/bootstrap/js/modernizr.js');
    wp_enqueue_script('theme-js', get_template_directory_uri() . '/bootstrap/js/theme.js');
    wp_enqueue_script('jquery-ucMenu-js', get_template_directory_uri() . '/bootstrap/js/jquery.ucMenu.js');
    wp_enqueue_script('jquery-svgInject-js', get_template_directory_uri() . '/bootstrap/js/jquery.svgInject.js');
    wp_enqueue_script('mustache-min-js', get_template_directory_uri() . '/bootstrap/js/mustache.min.js');
    wp_enqueue_script('tabcordion-js', get_template_directory_uri() . '/bootstrap/js/tabcordion.min.js');
    wp_enqueue_script('jquery-matchHeight-js', get_template_directory_uri() . '/bootstrap/js/jquery.matchHeight.js');
    wp_enqueue_script('owl-carousel-js', get_template_directory_uri() . '/bootstrap/js/owl.carousel.js');
    //wp_enqueue_script('lightbox-js', get_template_directory_uri() . '/bootstrap/js/lightbox.js');

    //wp_enqueue_style('bootstrap-css', get_template_directory_uri() . '/bootstrap/css/bootstrap.min.css');
    //wp_enqueue_style('bootstrap-select-css', get_template_directory_uri() . '/bootstrap/css/bootstrapSelect.min.css');
    //wp_enqueue_style('jquery-ui-css', get_template_directory_uri() . '/bootstrap/css/jquery-ui.min.css');
    //wp_enqueue_style( 'lightbox-css', get_template_directory_uri() . '/bootstrap/css/lightbox.css' );
    wp_enqueue_style( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css' );
    wp_enqueue_style( 'bootstrap-select-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css' );
    wp_enqueue_style( 'jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.10.0/css/lightbox.min.css' );

    wp_enqueue_style( 'UCStyles-css', get_template_directory_uri() . '/bootstrap/css/UCStyles.css' );
	  wp_enqueue_style( 'style-css', get_template_directory_uri() . '/style.css' );
}
add_action('wp_enqueue_scripts', 'theme_add_bootstrap');


// Register Custom Navigation Walker
require_once('bootstrap/wp_bootstrap_navwalker.php');


// Declare primary menu
register_nav_menus( array(
  'primary' => __('Primary Menu', 'bootstrapbasic'),
));


// Sidebar setup (Also known as sidebar widgets area)
function theme_sidebars()
{
  register_sidebar(array(
  'name' => 'Main Sidebar',
  'id' => 'sidebar-main',
  'before_widget' => '<div class="widget">',
  'after_widget' => '</div>',
  'before_title' => '<h2 class="upper2 title3"><span>',
  'after_title' => '</span></h2>',
  ));
}
add_action('widgets_init', 'theme_sidebars');




// Post navigation with page numbers
function theme_numeric_posts_nav()
{
  if(is_singular())
    return;
    global $wp_query;


  /** Stop execution if there's only 1 page */
  if($wp_query->max_num_pages <= 1)
      return;

  $paged = get_query_var('paged') ? absint(get_query_var('paged')): 1;
  $max = intval($wp_query->max_num_pages);


  /** Add current page to the array */
  if($paged >= 1)
    $links[] = $paged;


  /** Add the pages around the current page to the array */
  if($paged >= 3) {
    $links[] = $paged - 1;
    $links[] = $paged - 2;
  }

  if(($paged + 2) <= $max){
      $links[] = $paged + 2;
      $links[] = $paged + 1;
  }

  echo '<nav><ul class="pagination">' . "\n";

  /** Previous Post Link */
  if(get_previous_posts_link())
    printf( '<li>%s</li>' . "\n", get_previous_posts_link());


  /** Link to first page, plus ellipses if necessary */
  if(! in_array( 1, $links)){
      $class = 1 == $paged ? ' class="active"' : '';
      printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link(1)), '1');

      if (! in_array(2, $links))
        echo '<li></li>';
      }


  /** Link to current page, plus 2 pages in either direction if necessary */
  sort($links);

  foreach((array) $links as $link){
      $class = $paged == $link ? ' class="active"' : '';
      printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link)), $link);
  }


  /** Link to last page, plus ellipses if necessary */
  if (! in_array( $max, $links)){

    if(! in_array($max - 1, $links))
      echo '<li></li>' . "\n";
      $class = $paged == $max ? ' class="active"' : '';
      printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($max)), $max);
    }


  /** Next Post Link */
  if (get_next_posts_link())
    printf('<li>%s</li>' . "\n", get_next_posts_link());
    echo '</ul></nav>' . "\n";
}


// Style Calendar Widget
add_filter( 'get_calendar' , 'aus_calendar' , 2 ) ;
function aus_calendar( $markup ) {
	$markup = str_replace( '<table id="wp-calendar"' , '<table id="wp-calendar" class="table table-stripped"' , $markup ) ;
	return $markup;
}


// Add meta data to posts
function posted_on() {
	printf( __('<p><span class="glyphicon glyphicon-calendar"></span> Posted On: <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a></span></p>', ''),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentyeleven' ), get_the_author() ) ),
		get_the_author(),
		(get_comments_number()==1?(get_comments_number().' comment'):(get_comments_number().' comments')),
		get_comments_link()
	);
}












// Check for static widgets in widget-ready areas
//function is_sidebar_active( $index ){
//    global $wp_registered_sidebars;
//    $widgetcolums = wp_get_sidebars_widgets();
//    if ( $widgetcolums[$index] )
//    return true;
//    return false;
//} // end is_sidebar_active
?>
