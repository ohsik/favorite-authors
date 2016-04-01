<?php
/*
Plugin Name: Favorite Authors
Plugin URI: http://wp.ohsikpark.com/favorite-authors/
Description: Favorite Authors allows you to add all of your favorite authors on your account. 
Author: writegnj
Version: 1.2
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
function fav_authors_get_user_id(){
    global $current_user;
    wp_get_current_user();
    return $current_user->ID;
}

// Add favorite authors to current user's usermeta
function fav_author_add_fav_author(){
    check_ajax_referer( 'fav_authors_obj_ajax', 'security' );
    $add_this_author = absint($_POST['clicked_author_id']);
    
    if($add_this_author !== (int)$add_this_author)
        return;

    if ( current_user_can( 'edit_posts' ) ){
        $user_id = absint(fav_authors_get_user_id());

        $author_list = get_user_meta( $user_id, FAV_AUTHORS_META_KEY, true );

        if( empty( $author_list ) ) { 
            update_user_meta( $user_id, FAV_AUTHORS_META_KEY, array( $add_this_author ) );
        } else {
            $author_arr = ( is_array( $author_list ) ) ? $author_list : array( $author_list );
            
            if(in_array($add_this_author, $author_list))
                return;
            
            $author_arr[] = $add_this_author;
            update_user_meta( $user_id, FAV_AUTHORS_META_KEY, $author_arr );
        }
    }
    die();
}
add_action( 'wp_ajax_get_fav_author_id', 'fav_author_add_fav_author' );

// Remove favorite authors from current user's usermeta
function fav_author_remove_user(){
    check_ajax_referer( 'fav_authors_obj_ajax', 'security' );
    
    if ($_POST['clicked_author_id']){
        $remove_this_author = absint($_POST['clicked_author_id']);
        
        if($remove_this_author !== (int)$remove_this_author)
            return;
        
        if ( current_user_can( 'edit_posts' ) ){
            $user_id = absint(fav_authors_get_user_id());

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
    die();
}
add_action( 'wp_ajax_remove_fav_author_id', 'fav_author_remove_user' );

// Add shorcode on php file
function fav_authors_link(){
    wp_enqueue_style('favorite-authors');
    
    if ( is_user_logged_in() ) {
        wp_enqueue_script('favorite-authors-script');
        wp_enqueue_style('themename-style');

        $fav_author_list = get_user_option( 'favorite-authors', fav_authors_get_user_id() );
        //var_dump( $fav_author_list ); 

        global $post;
        //print_r($post);
        $user_id = absint($post->post_author);
        
        if(fav_authors_get_user_id() == $user_id)
            return;

        if( empty( $fav_author_list ) ){
            $str = '<span class="fav_authors add-fav" id="fav_author_button" data-author-id="'.$user_id.'"><span class="dashicons dashicons-star-filled"></span> Favorite'; 
        }else{

            if(in_array($user_id, $fav_author_list)){
                $str = '<span class="fav_authors rmv-fav" id="fav_author_rmove_button" data-author-id="'.$user_id.'"><span class="dashicons dashicons-yes"></span> Favorited!';
            }else{
                $str = '<span class="fav_authors add-fav" id="fav_author_button" data-author-id="'.$user_id.'"><span class="dashicons dashicons-star-filled"></span> Favorite';   
            }

        }
        $str .= '</span>';

        echo $str;
    }else{
        echo '<p class="fa-signin"><a href="'.wp_login_url().'" title="Login">Log in to favorite this author</a></p>';
    }
}

/*
**  Pagination for favorite author list by current user
    This pagination code and get favorite user list code can be clearned up better than this...
*/
function fav_authors_pagi(){
    check_ajax_referer( 'fav_authors_obj_ajax', 'security' );
    //var_dump($_POST);
    $fav_author_list = get_user_option( 'favorite-authors', fav_authors_get_user_id() );

    $page = ! empty( $_POST['clicked_author_id'] ) ? (int) $_POST['clicked_author_id'] : 1;
    $total = count( $fav_author_list ); 
    $limit = 20; // <----------------------------------------------------- Set number of authors to show per page
    $totalPages = ceil( $total/ $limit );
    $page = max($page, 1);
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;
    if( $offset < 0 ) $offset = 0;
    
    
    if ($fav_author_list){
        $fav_author_list = array_slice( $fav_author_list, $offset, $limit );

        foreach($fav_author_list as $fav_au ){
            $fa_au = get_userdata( $fav_au );
            $author_url = esc_url(site_url( "/author/" ).$fa_au->user_login);
            
            echo '<li><a href="'.$author_url.'">';
            echo esc_html($fa_au->display_name);
            echo '</a> <span class="fav_authors rmv-fav" id="fav_author_rmove_button" data-author-id="'.$fa_au->ID.'"><span class="dashicons dashicons-yes"></span> Favorited!</span></li>';
        }
    }else{
        echo '<h2>Add favorite authors!</h2>';
    }
    die();
}
add_action( 'wp_ajax_fav_au_pagi', 'fav_authors_pagi' );

/*
**  Get favorite author list by current user
    Shortcode [favorite-authors-list] function
*/
function fav_authors_get_list(){
    $fav_author_list = get_user_option( 'favorite-authors', fav_authors_get_user_id() );
    //var_dump( $fav_author_list );

    $page = ! empty( $_POST['page'] ) ? (int) $_POST['page'] : 1;
    $total = count( $fav_author_list );  
    $limit = 20; // <----------------------------------------------------- Set number of authors to show per page
    $totalPages = ceil( $total/ $limit ); 
    $page = max($page, 1); 
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;
    if( $offset < 0 ) $offset = 0;

    if ($fav_author_list){
        $fav_author_list = array_slice( $fav_author_list, $offset, $limit );
        
        echo '<div class="fav_authors-wrap">';
            echo '<p class="fav-total">Total favorited author: '.$total.'</p>';
            echo '<div class="fav-authors-list" id="fav-authors-list">';
            foreach($fav_author_list as $fav_au ){
                $fa_au = get_userdata( $fav_au );
                $author_url = esc_url(site_url( "/?author=" ).$fa_au->ID);

                echo '<li><a href="'.$author_url.'">';
                echo esc_html($fa_au->display_name);
                echo '</a> <span class="fav_authors rmv-fav" id="fav_author_rmove_button" data-author-id="'.$fa_au->ID.'"><span class="dashicons dashicons-yes"></span> Favorited!</span></li>';
            }
            echo '</div>';
            // Pagination
            echo '<div class="fav-authors-pagination">';
 
            if( $totalPages != 0 && $total >= $limit) {
                for ($i = $page; $i <= $totalPages; $i++) {
                    if ($i == 1){
                        echo '<li id="fav_pagi" data-fav-pid="'.$i.'" class="fa_current">'.$i.'</li>';
                    }else{
                        echo '<li id="fav_pagi" data-fav-pid="'.$i.'">'.$i.'</li>';
                    }
                }
            }                   
            echo '</div>';
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
function fav_authors_action_links( $links ){
   $links[] = '<a href="http://wp.ohsikpark.com/favorite-authors/" target="_blank">Documentation</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'fav_authors_action_links' );