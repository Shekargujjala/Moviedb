<?php
/**
 * The main template file
 */

get_header(); ?>

<main class="main-content">
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="" id="filter-form">
            <div class="filter-group">
                <label class="filter-label"><?php _e('Genre', 'moviedb-pro'); ?></label>
                <select name="genre" class="filter-select">
                    <option value=""><?php _e('All Genres', 'moviedb-pro'); ?></option>
                    <?php
                    $genres = get_terms(array(
                        'taxonomy' => 'genre',
                        'hide_empty' => false,
                    ));
                    foreach ($genres as $genre) {
                        $selected = (isset($_GET['genre']) && $_GET['genre'] == $genre->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($genre->slug) . '" ' . $selected . '>' . esc_html($genre->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label"><?php _e('Year', 'moviedb-pro'); ?></label>
                <select name="year" class="filter-select">
                    <option value=""><?php _e('All Years', 'moviedb-pro'); ?></option>
                    <?php
                    $current_year = date('Y');
                    for ($year = $current_year; $year >= 1950; $year--) {
                        $selected = (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : '';
                        echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label"><?php _e('Platform', 'moviedb-pro'); ?></label>
                <select name="platform" class="filter-select">
                    <option value=""><?php _e('All Platforms', 'moviedb-pro'); ?></option>
                    <?php
                    $platforms = get_terms(array(
                        'taxonomy' => 'streaming_platform',
                        'hide_empty' => false,
                    ));
                    foreach ($platforms as $platform) {
                        $selected = (isset($_GET['platform']) && $_GET['platform'] == $platform->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($platform->slug) . '" ' . $selected . '>' . esc_html($platform->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label"><?php _e('Type', 'moviedb-pro'); ?></label>
                <select name="post_type" class="filter-select">
                    <option value=""><?php _e('Movies & TV Shows', 'moviedb-pro'); ?></option>
                    <option value="movie" <?php echo (isset($_GET['post_type']) && $_GET['post_type'] == 'movie') ? 'selected' : ''; ?>><?php _e('Movies Only', 'moviedb-pro'); ?></option>
                    <option value="tvshow" <?php echo (isset($_GET['post_type']) && $_GET['post_type'] == 'tvshow') ? 'selected' : ''; ?>><?php _e('TV Shows Only', 'moviedb-pro'); ?></option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="search-button"><?php _e('Filter', 'moviedb-pro'); ?></button>
                <a href="<?php echo home_url(); ?>" class="search-button" style="background: #666; text-decoration: none; display: inline-block; text-align: center;"><?php _e('Reset', 'moviedb-pro'); ?></a>
            </div>
        </form>
    </div>

    <!-- Movies Grid -->
    <?php
    // Custom query for filtering
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $posts_per_page = get_option('moviedb_items_per_page', 12);
    
    $args = array(
        'post_type' => array('movie', 'tvshow'),
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'meta_query' => array(),
        'tax_query' => array(),
    );
    
    // Filter by post type
    if (isset($_GET['post_type']) && !empty($_GET['post_type'])) {
        $args['post_type'] = sanitize_text_field($_GET['post_type']);
    }
    
    // Filter by genre
    if (isset($_GET['genre']) && !empty($_GET['genre'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['genre'])
        );
    }
    
    // Filter by streaming platform
    if (isset($_GET['platform']) && !empty($_GET['platform'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'streaming_platform',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['platform'])
        );
    }
    
    // Filter by year
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $args['meta_query'][] = array(
            'key' => '_release_date',
            'value' => sanitize_text_field($_GET['year']),
            'compare' => 'LIKE'
        );
    }
    
    // Search query
    if (isset($_GET['s']) && !empty($_GET['s'])) {
        $args['s'] = sanitize_text_field($_GET['s']);
    }
    
    $movie_query = new WP_Query($args);
    
    if ($movie_query->have_posts()) : ?>
        <div class="movie-grid" id="movie-grid">
            <?php while ($movie_query->have_posts()) : $movie_query->the_post(); ?>
                <?php get_template_part('template-parts/movie-card'); ?>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-container">
            <?php
            echo paginate_links(array(
                'total' => $movie_query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'show_all' => false,
                'end_size' => 1,
                'mid_size' => 2,
                'prev_next' => true,
                'prev_text' => __('« Previous', 'moviedb-pro'),
                'next_text' => __('Next »', 'moviedb-pro'),
                'type' => 'plain',
            ));
            ?>
        </div>
        
    <?php else : ?>
        <div class="no-results">
            <h2><?php _e('No movies or TV shows found', 'moviedb-pro'); ?></h2>
            <p><?php _e('Try adjusting your search or filter criteria.', 'moviedb-pro'); ?></p>
            <a href="<?php echo home_url(); ?>" class="search-button"><?php _e('View All', 'moviedb-pro'); ?></a>
        </div>
    <?php endif; 
    
    wp_reset_postdata(); ?>
    
    <!-- Load More Button (AJAX) -->
    <?php if ($movie_query->max_num_pages > 1) : ?>
        <div class="load-more-container" style="text-align: center; margin: 2rem 0;">
            <button id="load-more-btn" class="search-button" data-page="<?php echo $paged; ?>" data-max-pages="<?php echo $movie_query->max_num_pages; ?>">
                <?php _e('Load More', 'moviedb-pro'); ?>
            </button>
        </div>
    <?php endif; ?>
    
</main>

<!-- Featured Section (if on homepage) -->
<?php if (is_home() && !isset($_GET['s']) && !isset($_GET['genre']) && !isset($_GET['year']) && !isset($_GET['platform'])) : ?>
<section class="featured-section" style="margin-top: 3rem;">
    <h2 style="color: #fff; text-align: center; margin-bottom: 2rem; font-size: 2.5rem;"><?php _e('Featured Movies', 'moviedb-pro'); ?></h2>
    <?php
    $featured_query = new WP_Query(array(
        'post_type' => array('movie', 'tvshow'),
        'posts_per_page' => 8,
        'meta_query' => array(
            array(
                'key' => '_featured',
                'value' => 'yes',
                'compare' => '='
            )
        ),
        'orderby' => 'rand'
    ));
    
    if ($featured_query->have_posts()) : ?>
        <div class="movie-grid">
            <?php while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
                <?php get_template_part('template-parts/movie-card'); ?>
            <?php endwhile; ?>
        </div>
    <?php endif; 
    wp_reset_postdata(); ?>
</section>
<?php endif; ?>

<style>
.pagination-container {
    text-align: center;
    margin: 3rem 0;
}

.pagination-container .page-numbers {
    display: inline-block;
    padding: 0.75rem 1rem;
    margin: 0 0.25rem;
    background: #1a1a1a;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    border: 1px solid #333;
    transition: all 0.3s ease;
}

.pagination-container .page-numbers:hover,
.pagination-container .page-numbers.current {
    background: #e50914;
    border-color: #e50914;
}

.no-results {
    text-align: center;
    padding: 4rem 2rem;
    color: #fff;
}

.no-results h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.no-results p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    color: #888;
}

.featured-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    padding: 3rem 0;
    border-radius: 15px;
}

.load-more-container {
    margin: 2rem 0;
}

#load-more-btn:disabled {
    background: #666;
    cursor: not-allowed;
}
</style>

<?php get_footer(); ?>