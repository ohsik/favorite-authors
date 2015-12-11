<?php
/*
Plugin Name: Favorite Authors
Plugin URI: http://wp.ohsikpark.com/favorite-authors/
Description: Favorite authors on multi author WordPress sites for loggedin users.
Author: writegnj
Version: 1.0
Author URI: http://www.ohsikpark.com
Text Domain: favorite-authors
License: GPL2
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define plug-in path
define('FAV_AUTHORS_PATH', plugins_url() . '/favorite-authors');
define('FAV_AUTHORS_META_KEY', "favorite-authors");
/*
**  Register CSS & JS assets for plug in
    ------------------------------------------------------------------
*/
// register our form css
function fav_authors_register_assets(){
	wp_register_style( 'favorite-authors', FAV_AUTHORS_PATH . '/favorite-authors.css' );
	wp_register_style( 'themename-style', get_stylesheet_uri(), array( 'dashicons' ), '1.0', true );
    wp_register_script( 'favorite-authors-script', FAV_AUTHORS_PATH . '/favorite-authors.js', array('jquery'), '1.0', true );
}
add_action('init', 'fav_authors_register_assets');

// load our form css
function fav_authors_print_assets(){
	global $fav_authors_load_assets;
 
	if ( !$fav_authors_load_assets )
		return;
 
	wp_print_styles('favorite-authors');
	wp_print_styles('themename-style');
	wp_print_scripts('favorite-authors-script');
}
add_action('wp_footer', 'fav_authors_print_assets');

// Load and localize JS for AJAX
function fav_authors_enqueue(){
    wp_localize_script( 'favorite-authors-script', 'fav_authors_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'ajax_nonce' => wp_create_nonce('fav_authors_obj_ajax')) );
}
add_action( 'wp_enqueue_scripts', 'fav_authors_enqueue' );

// Get curren user
function fav_authors_get_user_id() {
    global $current_user;
    get_currentuserinfo();
    return $current_user->ID;
}

// Add favorite authors to current users DB
function fav_author_add_fav_author(){
    check_ajax_referer( 'fav_authors_obj_ajax', 'security' );
    $add_this_author = $_POST['clicked_author_id'];
    
    if ( current_user_can( 'edit_posts' ) ){
        $user_id = fav_authors_get_user_id();
        $author_list = get_user_meta( $user_id, FAV_AUTHORS_META_KEY, true );
        
        if( empty( $author_list ) ) {   // There was no meta_value, set an array.
            update_user_meta( $user_id, FAV_AUTHORS_META_KEY, array( $add_this_author ) );
        } else {
            $author_arr = ( is_array( $author_list ) ) ? $author_list : array( $author_list );  // Added in case current value is not an array already.
            $author_arr[] = $add_this_author;
            update_user_meta( $user_id, FAV_AUTHORS_META_KEY, $author_arr );
        }

        //update_user_meta( $user_id, FAV_AUTHORS_META_KEY, $add_this_author );
    }
     
}
add_action( 'wp_ajax_get_fav_author_id', 'fav_author_add_fav_author' );

// Remove favorite authors from current users DB
function fav_author_remove_user(){
    check_ajax_referer( 'fav_authors_obj_ajax', 'security' );
    $remove_this_author = $_POST['clicked_author_id'];
    
    if ( current_user_can( 'edit_posts' ) ){
        $user_id = fav_authors_get_user_id();
        
        $author_list = get_user_meta( $user_id, FAV_AUTHORS_META_KEY, true );
        //print_r($author_list);
        $author_saved = array_search($remove_this_author, $author_list);
        
        if( FALSE !== $author_saved ){
            // Remove $author_saved
            unset($author_list[$author_saved]);
            $author_arr = ( is_array( $author_list ) ) ? $author_list : array( $author_list );
            update_user_meta( $user_id, FAV_AUTHORS_META_KEY, $author_arr );
        }
    }
}
add_action( 'wp_ajax_remove_fav_author_id', 'fav_author_remove_user' );



// Add shorcode on php file
function fav_authors_link() {
    wp_enqueue_script('favorite-authors-script');
    wp_enqueue_style('favorite-authors');

    $fav_author_list = get_user_option( 'favorite-authors', fav_authors_get_user_id() );
    //var_dump( $fav_author_list ); 

    global $post;
    //print_r($post);
    $user_id = $post->post_author;
    
    if(in_array($user_id, $fav_author_list)){
        $str = '<span class="fav_authors rmv-fav" id="fav_author_rmove_button" data-author-id="'.$user_id.'">Unfavorite';
    }else{
        $str = '<span class="fav_authors add-fav" id="fav_author_button" data-fav-id="'.$user_id.'"><span class="dashicons dashicons-star-filled"></span> Favorite';   
    }
    
    $str .= '</span>';
    
    if ($return) { return $str; } else { echo $str; }
}

// Get favorite user list
function fav_authors_get_list(){
    $fav_author_list = get_user_option( 'favorite-authors', fav_authors_get_user_id() );
    //var_dump( $fav_author_list );
    if ($fav_author_list){
        echo '<div class="fav-authors-list">';
        foreach($fav_author_list as $fav_au ){
            $fa_au = get_userdata( $fav_au );
            $author_url = site_url( "/author/" ).$fa_au->user_login;

            echo '<li><a href="'.$author_url.'">';
            echo $fa_au->display_name;
            echo '</a> <span class="fav_authors rmv-fav" id="fav_author_rmove_button" data-author-id="'.$fa_au->ID.'">Unfavorite</span></li>';
        }
        echo '</div>';
    }else{
        echo '<h2>Add your first favorite author!</h2>';
    }
}

/*
**  Add a shortcode for front end form
    [favorite-authors-list]
    https://codex.wordpress.org/Function_Reference/add_shortcode
*/
function fav_authors_list(){
    // Load CSS & JS files
    global $fav_authors_load_assets;
    $fav_authors_load_assets = true;
    
    // Show only to logged in users
    if ( is_user_logged_in() ) {
        $output = fav_authors_get_list();
        return $output;
    }else{
        echo '<p class="login-fav_authors">Please <a href="'.wp_login_url().'" title="Login">Login</a> to continue...</p>';
    }
}
add_shortcode('favorite-authors-list', 'fav_authors_list');

// Add plug in link to setting page
function fav_authors_action_links( $links ) {
   $links[] = '<a href="http://wp.ohsikpark.com/favorite-authors/" target="_blank">Documentation</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'fav_authors_action_links' );