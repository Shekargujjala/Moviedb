<?php
/**
 * MovieDB Pro Theme Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Theme Setup
function moviedb_theme_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list'));
    add_theme_support('custom-logo');
    
    // Set post thumbnail size
    set_post_thumbnail_size(300, 450, true);
    add_image_size('movie-poster', 300, 450, true);
    add_image_size('movie-backdrop', 1200, 675, true);
    add_image_size('cast-photo', 150, 150, true);
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'moviedb-pro'),
        'footer' => __('Footer Menu', 'moviedb-pro'),
    ));
}
add_action('after_setup_theme', 'moviedb_theme_setup');

// Enqueue styles and scripts
function moviedb_enqueue_assets() {
    // Styles
    wp_enqueue_style('moviedb-style', get_stylesheet_uri(), array(), '1.0.0');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap', array(), null);
    
    // Scripts
    wp_enqueue_script('moviedb-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('moviedb-ajax', get_template_directory_uri() . '/js/ajax.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('moviedb-ajax', 'moviedb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('moviedb_nonce'),
        'tmdb_api_key' => get_option('moviedb_tmdb_api_key', ''),
    ));
}
add_action('wp_enqueue_scripts', 'moviedb_enqueue_assets');

// Register Custom Post Types
function moviedb_register_post_types() {
    // Movies Post Type
    register_post_type('movie', array(
        'labels' => array(
            'name' => __('Movies', 'moviedb-pro'),
            'singular_name' => __('Movie', 'moviedb-pro'),
            'add_new' => __('Add New Movie', 'moviedb-pro'),
            'add_new_item' => __('Add New Movie', 'moviedb-pro'),
            'edit_item' => __('Edit Movie', 'moviedb-pro'),
            'new_item' => __('New Movie', 'moviedb-pro'),
            'view_item' => __('View Movie', 'moviedb-pro'),
            'search_items' => __('Search Movies', 'moviedb-pro'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-video-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'rewrite' => array('slug' => 'movies'),
        'show_in_rest' => true,
    ));
    
    // TV Shows Post Type
    register_post_type('tvshow', array(
        'labels' => array(
            'name' => __('TV Shows', 'moviedb-pro'),
            'singular_name' => __('TV Show', 'moviedb-pro'),
            'add_new' => __('Add New TV Show', 'moviedb-pro'),
            'add_new_item' => __('Add New TV Show', 'moviedb-pro'),
            'edit_item' => __('Edit TV Show', 'moviedb-pro'),
            'new_item' => __('New TV Show', 'moviedb-pro'),
            'view_item' => __('View TV Show', 'moviedb-pro'),
            'search_items' => __('Search TV Shows', 'moviedb-pro'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-format-video',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'rewrite' => array('slug' => 'tv-shows'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'moviedb_register_post_types');

// Register Custom Taxonomies
function moviedb_register_taxonomies() {
    // Genres
    register_taxonomy('genre', array('movie', 'tvshow'), array(
        'labels' => array(
            'name' => __('Genres', 'moviedb-pro'),
            'singular_name' => __('Genre', 'moviedb-pro'),
            'add_new_item' => __('Add New Genre', 'moviedb-pro'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'rewrite' => array('slug' => 'genre'),
        'show_in_rest' => true,
    ));
    
    // Streaming Platforms
    register_taxonomy('streaming_platform', array('movie', 'tvshow'), array(
        'labels' => array(
            'name' => __('Streaming Platforms', 'moviedb-pro'),
            'singular_name' => __('Streaming Platform', 'moviedb-pro'),
            'add_new_item' => __('Add New Platform', 'moviedb-pro'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'rewrite' => array('slug' => 'platform'),
        'show_in_rest' => true,
    ));
    
    // Tags
    register_taxonomy('movie_tag', array('movie', 'tvshow'), array(
        'labels' => array(
            'name' => __('Movie Tags', 'moviedb-pro'),
            'singular_name' => __('Movie Tag', 'moviedb-pro'),
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_tagcloud' => true,
        'rewrite' => array('slug' => 'tag'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'moviedb_register_taxonomies');

// Add Custom Meta Fields
function moviedb_add_meta_boxes() {
    add_meta_box(
        'movie_details',
        __('Movie Details', 'moviedb-pro'),
        'moviedb_movie_details_callback',
        array('movie', 'tvshow'),
        'normal',
        'high'
    );
    
    add_meta_box(
        'tmdb_import',
        __('TMDB Import', 'moviedb-pro'),
        'moviedb_tmdb_import_callback',
        array('movie', 'tvshow'),
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'moviedb_add_meta_boxes');

// Movie Details Meta Box Callback
function moviedb_movie_details_callback($post) {
    wp_nonce_field('moviedb_movie_details_nonce', 'moviedb_movie_details_nonce');
    
    // Get existing values
    $release_date = get_post_meta($post->ID, '_release_date', true);
    $runtime = get_post_meta($post->ID, '_runtime', true);
    $imdb_rating = get_post_meta($post->ID, '_imdb_rating', true);
    $tmdb_rating = get_post_meta($post->ID, '_tmdb_rating', true);
    $trailer_url = get_post_meta($post->ID, '_trailer_url', true);
    $backdrop_url = get_post_meta($post->ID, '_backdrop_url', true);
    $tmdb_id = get_post_meta($post->ID, '_tmdb_id', true);
    $cast_crew = get_post_meta($post->ID, '_cast_crew', true);
    $streaming_info = get_post_meta($post->ID, '_streaming_info', true);
    $subtitle_links = get_post_meta($post->ID, '_subtitle_links', true);
    $download_links = get_post_meta($post->ID, '_download_links', true);
    $image_gallery = get_post_meta($post->ID, '_image_gallery', true);
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="release_date"><?php _e('Release Date', 'moviedb-pro'); ?></label></th>
            <td><input type="date" id="release_date" name="release_date" value="<?php echo esc_attr($release_date); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="runtime"><?php _e('Runtime (minutes)', 'moviedb-pro'); ?></label></th>
            <td><input type="number" id="runtime" name="runtime" value="<?php echo esc_attr($runtime); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="imdb_rating"><?php _e('IMDB Rating', 'moviedb-pro'); ?></label></th>
            <td><input type="number" id="imdb_rating" name="imdb_rating" value="<?php echo esc_attr($imdb_rating); ?>" step="0.1" min="0" max="10" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="tmdb_rating"><?php _e('TMDB Rating', 'moviedb-pro'); ?></label></th>
            <td><input type="number" id="tmdb_rating" name="tmdb_rating" value="<?php echo esc_attr($tmdb_rating); ?>" step="0.1" min="0" max="10" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="trailer_url"><?php _e('Trailer URL', 'moviedb-pro'); ?></label></th>
            <td><input type="url" id="trailer_url" name="trailer_url" value="<?php echo esc_attr($trailer_url); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="backdrop_url"><?php _e('Backdrop Image URL', 'moviedb-pro'); ?></label></th>
            <td><input type="url" id="backdrop_url" name="backdrop_url" value="<?php echo esc_attr($backdrop_url); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="tmdb_id"><?php _e('TMDB ID', 'moviedb-pro'); ?></label></th>
            <td><input type="number" id="tmdb_id" name="tmdb_id" value="<?php echo esc_attr($tmdb_id); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="cast_crew"><?php _e('Cast & Crew (JSON)', 'moviedb-pro'); ?></label></th>
            <td><textarea id="cast_crew" name="cast_crew" rows="5" class="large-text"><?php echo esc_textarea($cast_crew); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="streaming_info"><?php _e('Streaming Info (JSON)', 'moviedb-pro'); ?></label></th>
            <td><textarea id="streaming_info" name="streaming_info" rows="3" class="large-text"><?php echo esc_textarea($streaming_info); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="subtitle_links"><?php _e('Subtitle Links (JSON)', 'moviedb-pro'); ?></label></th>
            <td><textarea id="subtitle_links" name="subtitle_links" rows="3" class="large-text"><?php echo esc_textarea($subtitle_links); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="download_links"><?php _e('Download Links (JSON)', 'moviedb-pro'); ?></label></th>
            <td><textarea id="download_links" name="download_links" rows="3" class="large-text"><?php echo esc_textarea($download_links); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="image_gallery"><?php _e('Image Gallery URLs (JSON)', 'moviedb-pro'); ?></label></th>
            <td><textarea id="image_gallery" name="image_gallery" rows="3" class="large-text"><?php echo esc_textarea($image_gallery); ?></textarea></td>
        </tr>
    </table>
    
    <p><strong><?php _e('Note:', 'moviedb-pro'); ?></strong> <?php _e('JSON fields should contain valid JSON data for complex information like cast, crew, and streaming platforms.', 'moviedb-pro'); ?></p>
    <?php
}

// TMDB Import Meta Box
function moviedb_tmdb_import_callback($post) {
    ?>
    <div id="tmdb-import-section">
        <p>
            <label for="tmdb-search"><?php _e('Search TMDB:', 'moviedb-pro'); ?></label>
            <input type="text" id="tmdb-search" placeholder="<?php _e('Enter movie/TV show title', 'moviedb-pro'); ?>" class="widefat" />
        </p>
        <p>
            <button type="button" id="search-tmdb" class="button"><?php _e('Search', 'moviedb-pro'); ?></button>
            <button type="button" id="import-tmdb" class="button button-primary" disabled><?php _e('Import Selected', 'moviedb-pro'); ?></button>
        </p>
        <div id="tmdb-results"></div>
    </div>
    <?php
}

// Save Custom Meta Fields
function moviedb_save_movie_details($post_id) {
    if (!isset($_POST['moviedb_movie_details_nonce']) || !wp_verify_nonce($_POST['moviedb_movie_details_nonce'], 'moviedb_movie_details_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array(
        'release_date', 'runtime', 'imdb_rating', 'tmdb_rating', 'trailer_url',
        'backdrop_url', 'tmdb_id', 'cast_crew', 'streaming_info', 
        'subtitle_links', 'download_links', 'image_gallery'
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'moviedb_save_movie_details');

// Add Admin Menu
function moviedb_admin_menu() {
    add_menu_page(
        __('MovieDB Settings', 'moviedb-pro'),
        __('MovieDB Pro', 'moviedb-pro'),
        'manage_options',
        'moviedb-settings',
        'moviedb_settings_page',
        'dashicons-admin-settings',
        30
    );
}
add_action('admin_menu', 'moviedb_admin_menu');

// Settings Page
function moviedb_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('moviedb_tmdb_api_key', sanitize_text_field($_POST['tmdb_api_key']));
        update_option('moviedb_items_per_page', intval($_POST['items_per_page']));
        echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'moviedb-pro') . '</p></div>';
    }
    
    $api_key = get_option('moviedb_tmdb_api_key', '');
    $items_per_page = get_option('moviedb_items_per_page', 12);
    ?>
    <div class="wrap">
        <h1><?php _e('MovieDB Pro Settings', 'moviedb-pro'); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('TMDB API Key', 'moviedb-pro'); ?></th>
                    <td>
                        <input type="text" name="tmdb_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                        <p class="description"><?php _e('Get your API key from', 'moviedb-pro'); ?> <a href="https://www.themoviedb.org/settings/api" target="_blank">TMDB</a></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Items Per Page', 'moviedb-pro'); ?></th>
                    <td>
                        <input type="number" name="items_per_page" value="<?php echo esc_attr($items_per_page); ?>" min="1" max="50" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// AJAX Functions for TMDB Integration
function moviedb_search_tmdb() {
    check_ajax_referer('moviedb_nonce', 'nonce');
    
    $query = sanitize_text_field($_POST['query']);
    $type = sanitize_text_field($_POST['type']); // 'movie' or 'tv'
    $api_key = get_option('moviedb_tmdb_api_key');
    
    if (empty($api_key)) {
        wp_die(json_encode(array('error' => 'TMDB API key not configured')));
    }
    
    $url = "https://api.themoviedb.org/3/search/{$type}?api_key={$api_key}&query=" . urlencode($query);
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        wp_die(json_encode(array('error' => 'Failed to connect to TMDB')));
    }
    
    $body = wp_remote_retrieve_body($response);
    wp_die($body);
}
add_action('wp_ajax_moviedb_search_tmdb', 'moviedb_search_tmdb');

function moviedb_import_tmdb() {
    check_ajax_referer('moviedb_nonce', 'nonce');
    
    $tmdb_id = intval($_POST['tmdb_id']);
    $type = sanitize_text_field($_POST['type']);
    $api_key = get_option('moviedb_tmdb_api_key');
    
    if (empty($api_key)) {
        wp_die(json_encode(array('error' => 'TMDB API key not configured')));
    }
    
    // Get movie/TV show details
    $url = "https://api.themoviedb.org/3/{$type}/{$tmdb_id}?api_key={$api_key}&append_to_response=credits,videos,images";
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        wp_die(json_encode(array('error' => 'Failed to connect to TMDB')));
    }
    
    $data = json_decode(wp_remote_retrieve_body($response), true);
    
    // Create post
    $post_data = array(
        'post_title' => $data['title'] ?? $data['name'],
        'post_content' => $data['overview'],
        'post_status' => 'draft',
        'post_type' => ($type == 'movie') ? 'movie' : 'tvshow',
        'meta_input' => array(
            '_tmdb_id' => $tmdb_id,
            '_release_date' => $data['release_date'] ?? $data['first_air_date'],
            '_runtime' => $data['runtime'] ?? $data['episode_run_time'][0] ?? '',
            '_tmdb_rating' => $data['vote_average'],
            '_backdrop_url' => 'https://image.tmdb.org/t/p/w1280' . $data['backdrop_path'],
            '_cast_crew' => json_encode($data['credits']),
            '_image_gallery' => json_encode($data['images']),
        )
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id) {
        // Set featured image
        if (!empty($data['poster_path'])) {
            $poster_url = 'https://image.tmdb.org/t/p/w500' . $data['poster_path'];
            moviedb_set_featured_image($post_id, $poster_url);
        }
        
        // Set genres
        if (!empty($data['genres'])) {
            $genre_ids = array();
            foreach ($data['genres'] as $genre) {
                $term = get_term_by('name', $genre['name'], 'genre');
                if (!$term) {
                    $term = wp_insert_term($genre['name'], 'genre');
                    $genre_ids[] = $term['term_id'];
                } else {
                    $genre_ids[] = $term->term_id;
                }
            }
            wp_set_object_terms($post_id, $genre_ids, 'genre');
        }
        
        // Set trailer URL
        if (!empty($data['videos']['results'])) {
            foreach ($data['videos']['results'] as $video) {
                if ($video['type'] == 'Trailer' && $video['site'] == 'YouTube') {
                    update_post_meta($post_id, '_trailer_url', 'https://www.youtube.com/watch?v=' . $video['key']);
                    break;
                }
            }
        }
        
        wp_die(json_encode(array('success' => true, 'post_id' => $post_id)));
    } else {
        wp_die(json_encode(array('error' => 'Failed to create post')));
    }
}
add_action('wp_ajax_moviedb_import_tmdb', 'moviedb_import_tmdb');

// Helper function to set featured image from URL
function moviedb_set_featured_image($post_id, $image_url) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    
    file_put_contents($file, $image_data);
    
    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    
    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    set_post_thumbnail($post_id, $attach_id);
}

// Watchlist functionality
function moviedb_add_to_watchlist() {
    if (!is_user_logged_in()) {
        wp_die(json_encode(array('error' => 'Please login to add to watchlist')));
    }
    
    $post_id = intval($_POST['post_id']);
    $user_id = get_current_user_id();
    
    $watchlist = get_user_meta($user_id, 'movie_watchlist', true);
    if (!is_array($watchlist)) {
        $watchlist = array();
    }
    
    if (!in_array($post_id, $watchlist)) {
        $watchlist[] = $post_id;
        update_user_meta($user_id, 'movie_watchlist', $watchlist);
        wp_die(json_encode(array('success' => true, 'action' => 'added')));
    } else {
        $watchlist = array_diff($watchlist, array($post_id));
        update_user_meta($user_id, 'movie_watchlist', $watchlist);
        wp_die(json_encode(array('success' => true, 'action' => 'removed')));
    }
}
add_action('wp_ajax_moviedb_add_to_watchlist', 'moviedb_add_to_watchlist');

// Custom query for filtering
function moviedb_filter_posts($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_home() || is_archive()) {
            if (isset($_GET['genre']) && !empty($_GET['genre'])) {
                $query->set('tax_query', array(
                    array(
                        'taxonomy' => 'genre',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['genre'])
                    )
                ));
            }
            
            if (isset($_GET['year']) && !empty($_GET['year'])) {
                $query->set('meta_query', array(
                    array(
                        'key' => '_release_date',
                        'value' => sanitize_text_field($_GET['year']),
                        'compare' => 'LIKE'
                    )
                ));
            }
            
            if (isset($_GET['platform']) && !empty($_GET['platform'])) {
                $query->set('tax_query', array(
                    array(
                        'taxonomy' => 'streaming_platform',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['platform'])
                    )
                ));
            }
        }
    }
}
add_action('pre_get_posts', 'moviedb_filter_posts');

// Helper functions
function moviedb_get_movie_rating($post_id) {
    $imdb_rating = get_post_meta($post_id, '_imdb_rating', true);
    $tmdb_rating = get_post_meta($post_id, '_tmdb_rating', true);
    
    if ($imdb_rating) {
        return $imdb_rating . '/10';
    } elseif ($tmdb_rating) {
        return $tmdb_rating . '/10';
    }
    
    return 'N/A';
}

function moviedb_get_runtime($post_id) {
    $runtime = get_post_meta($post_id, '_runtime', true);
    if ($runtime) {
        $hours = floor($runtime / 60);
        $minutes = $runtime % 60;
        return $hours . 'h ' . $minutes . 'm';
    }
    return 'N/A';
}

function moviedb_get_release_year($post_id) {
    $release_date = get_post_meta($post_id, '_release_date', true);
    if ($release_date) {
        return date('Y', strtotime($release_date));
    }
    return 'N/A';
}

function moviedb_get_streaming_platforms($post_id) {
    $platforms = get_the_terms($post_id, 'streaming_platform');
    if ($platforms && !is_wp_error($platforms)) {
        return $platforms;
    }
    return array();
}

function moviedb_is_in_watchlist($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $watchlist = get_user_meta($user_id, 'movie_watchlist', true);
    return is_array($watchlist) && in_array($post_id, $watchlist);
}

// Shortcodes
function moviedb_featured_movies_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6,
        'type' => 'movie',
        'orderby' => 'date',
        'order' => 'DESC'
    ), $atts);
    
    $query = new WP_Query(array(
        'post_type' => $atts['type'],
        'posts_per_page' => intval($atts['limit']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'meta_key' => '_featured',
        'meta_value' => 'yes'
    ));
    
    ob_start();
    if ($query->have_posts()) {
        echo '<div class="movie-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/movie-card');
        }
        echo '</div>';
        wp_reset_postdata();
    }
    return ob_get_clean();
}
add_shortcode('featured_movies', 'moviedb_featured_movies_shortcode');

// Widget Areas
function moviedb_widgets_init() {
    register_sidebar(array(
        'name' => __('Sidebar', 'moviedb-pro'),
        'id' => 'sidebar-1',
        'description' => __('Add widgets here.', 'moviedb-pro'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer', 'moviedb-pro'),
        'id' => 'footer-1',
        'description' => __('Footer widget area.', 'moviedb-pro'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'moviedb_widgets_init');