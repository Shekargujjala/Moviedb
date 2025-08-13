<?php
/**
 * The template for displaying single TV show posts
 */

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<!-- TV Show Hero Section -->
<div class="movie-hero tvshow-hero" style="background-image: url('<?php echo esc_url(get_post_meta(get_the_ID(), '_backdrop_url', true)); ?>');">
    <div class="movie-hero-content">
        <div class="movie-hero-info">
            <h1 class="movie-title"><?php the_title(); ?></h1>
            <div class="tvshow-meta">
                <span class="release-year"><?php echo moviedb_get_release_year(get_the_ID()); ?></span>
                <span class="season-info">
                    <?php 
                    $seasons = get_post_meta(get_the_ID(), '_seasons', true);
                    $episodes = get_post_meta(get_the_ID(), '_episodes', true);
                    if ($seasons) {
                        echo $seasons . ' ' . ($seasons == 1 ? __('Season', 'moviedb-pro') : __('Seasons', 'moviedb-pro'));
                    }
                    if ($episodes) {
                        echo ' • ' . $episodes . ' ' . __('Episodes', 'moviedb-pro');
                    }
                    ?>
                </span>
                <span class="rating">
                    <i class="fas fa-star"></i> <?php echo moviedb_get_movie_rating(get_the_ID()); ?>
                </span>
                <span class="status">
                    <?php 
                    $status = get_post_meta(get_the_ID(), '_status', true);
                    if ($status) {
                        echo '<span class="status-badge status-' . sanitize_html_class(strtolower($status)) . '">' . esc_html($status) . '</span>';
                    }
                    ?>
                </span>
            </div>
            <div class="movie-genres">
                <?php
                $genres = get_the_terms(get_the_ID(), 'genre');
                if ($genres && !is_wp_error($genres)) {
                    foreach ($genres as $genre) {
                        echo '<span class="genre-tag">' . esc_html($genre->name) . '</span>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<main class="main-content">
    <div class="movie-details-grid">
        
        <!-- TV Show Poster -->
        <div class="movie-poster-section">
            <?php if (has_post_thumbnail()) : ?>
                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'movie-poster'); ?>" 
                     alt="<?php the_title_attribute(); ?>" 
                     class="movie-poster-large">
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="movie-actions">
                <?php if (is_user_logged_in()) : ?>
                    <button class="watchlist-button <?php echo moviedb_is_in_watchlist(get_the_ID()) ? 'added' : ''; ?>" 
                            data-post-id="<?php echo get_the_ID(); ?>">
                        <i class="fas fa-heart"></i>
                        <span><?php echo moviedb_is_in_watchlist(get_the_ID()) ? __('Remove from Watchlist', 'moviedb-pro') : __('Add to Watchlist', 'moviedb-pro'); ?></span>
                    </button>
                <?php endif; ?>
                
                <?php 
                $trailer_url = get_post_meta(get_the_ID(), '_trailer_url', true);
                if ($trailer_url) : ?>
                    <a href="<?php echo esc_url($trailer_url); ?>" class="trailer-button" target="_blank">
                        <i class="fas fa-play"></i> <?php _e('Watch Trailer', 'moviedb-pro'); ?>
                    </a>
                <?php endif; ?>
                
                <button class="share-button" onclick="shareMovie()">
                    <i class="fas fa-share-alt"></i> <?php _e('Share', 'moviedb-pro'); ?>
                </button>
            </div>
            
            <!-- Streaming Platforms -->
            <?php 
            $platforms = moviedb_get_streaming_platforms(get_the_ID());
            if ($platforms) : ?>
                <div class="streaming-section">
                    <h3><?php _e('Available On', 'moviedb-pro'); ?></h3>
                    <div class="streaming-platforms">
                        <?php foreach ($platforms as $platform) : ?>
                            <div class="platform-item">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/platforms/<?php echo $platform->slug; ?>.png" 
                                     alt="<?php echo esc_attr($platform->name); ?>" 
                                     class="platform-logo">
                                <span><?php echo esc_html($platform->name); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- TV Show Stats -->
            <div class="tvshow-stats">
                <h3><?php _e('Show Stats', 'moviedb-pro'); ?></h3>
                <div class="stats-grid">
                    <?php 
                    $first_air_date = get_post_meta(get_the_ID(), '_first_air_date', true);
                    $last_air_date = get_post_meta(get_the_ID(), '_last_air_date', true);
                    $networks = get_post_meta(get_the_ID(), '_networks', true);
                    $episode_runtime = get_post_meta(get_the_ID(), '_episode_runtime', true);
                    ?>
                    
                    <?php if ($first_air_date) : ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php _e('First Aired', 'moviedb-pro'); ?></span>
                            <span class="stat-value"><?php echo date('M j, Y', strtotime($first_air_date)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($last_air_date) : ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php _e('Last Aired', 'moviedb-pro'); ?></span>
                            <span class="stat-value"><?php echo date('M j, Y', strtotime($last_air_date)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($episode_runtime) : ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php _e('Episode Runtime', 'moviedb-pro'); ?></span>
                            <span class="stat-value"><?php echo $episode_runtime; ?> min</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($networks) : 
                        $networks_data = json_decode($networks, true);
                        if ($networks_data && is_array($networks_data)) : ?>
                            <div class="stat-item">
                                <span class="stat-label"><?php _e('Network', 'moviedb-pro'); ?></span>
                                <span class="stat-value">
                                    <?php 
                                    $network_names = array();
                                    foreach ($networks_data as $network) {
                                        $network_names[] = esc_html($network['name']);
                                    }
                                    echo implode(', ', $network_names);
                                    ?>
                                </span>
                            </div>
                        <?php endif;
                    endif; ?>
                </div>
            </div>
        </div>
        
        <!-- TV Show Details -->
        <div class="movie-info-section">
            
            <!-- Synopsis -->
            <div class="movie-synopsis">
                <h2><?php _e('Synopsis', 'moviedb-pro'); ?></h2>
                <div class="synopsis-content">
                    <?php the_content(); ?>
                </div>
            </div>
            
            <!-- Season Information -->
            <?php 
            $seasons_data = get_post_meta(get_the_ID(), '_seasons_data', true);
            if ($seasons_data) {
                $seasons_info = json_decode($seasons_data, true);
                if ($seasons_info && is_array($seasons_info)) : ?>
                    <div class="seasons-section">
                        <h3><?php _e('Seasons', 'moviedb-pro'); ?></h3>
                        <div class="seasons-grid">
                            <?php foreach ($seasons_info as $season) : ?>
                                <div class="season-card">
                                    <?php if (isset($season['poster_path']) && $season['poster_path']) : ?>
                                        <img src="https://image.tmdb.org/t/p/w300<?php echo $season['poster_path']; ?>" 
                                             alt="<?php echo esc_attr($season['name']); ?>" 
                                             class="season-poster">
                                    <?php endif; ?>
                                    <div class="season-info">
                                        <h4><?php echo esc_html($season['name']); ?></h4>
                                        <?php if (isset($season['episode_count'])) : ?>
                                            <p class="episode-count"><?php echo $season['episode_count']; ?> <?php _e('Episodes', 'moviedb-pro'); ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($season['air_date'])) : ?>
                                            <p class="air-date"><?php echo date('Y', strtotime($season['air_date'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($season['overview']) && $season['overview']) : ?>
                                            <p class="season-overview"><?php echo wp_trim_words($season['overview'], 20); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            } ?>
            
            <!-- TV Show Details Table -->
            <div class="movie-details-table">
                <h3><?php _e('Details', 'moviedb-pro'); ?></h3>
                <table class="details-table">
                    <tr>
                        <td><strong><?php _e('First Air Date', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $first_air = get_post_meta(get_the_ID(), '_first_air_date', true);
                            echo $first_air ? date('F j, Y', strtotime($first_air)) : 'N/A';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Status', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $status = get_post_meta(get_the_ID(), '_status', true);
                            echo $status ? esc_html($status) : 'N/A';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Total Seasons', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $seasons = get_post_meta(get_the_ID(), '_seasons', true);
                            echo $seasons ? $seasons : 'N/A';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Total Episodes', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $episodes = get_post_meta(get_the_ID(), '_episodes', true);
                            echo $episodes ? $episodes : 'N/A';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('TMDB Rating', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $tmdb_rating = get_post_meta(get_the_ID(), '_tmdb_rating', true);
                            if ($tmdb_rating) {
                                echo '<span class="rating-value">' . $tmdb_rating . '/10</span>';
                                echo '<div class="rating-stars">';
                                for ($i = 1; $i <= 5; $i++) {
                                    $star_rating = ($tmdb_rating / 2);
                                    if ($i <= $star_rating) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i - 0.5 <= $star_rating) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                echo '</div>';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Genres', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php
                            $genres = get_the_terms(get_the_ID(), 'genre');
                            if ($genres && !is_wp_error($genres)) {
                                $genre_names = array();
                                foreach ($genres as $genre) {
                                    $genre_names[] = '<a href="' . get_term_link($genre) . '">' . esc_html($genre->name) . '</a>';
                                }
                                echo implode(', ', $genre_names);
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('TMDB ID', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $tmdb_id = get_post_meta(get_the_ID(), '_tmdb_id', true);
                            if ($tmdb_id) {
                                echo '<a href="https://www.themoviedb.org/tv/' . $tmdb_id . '" target="_blank">' . $tmdb_id . '</a>';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Cast & Crew Section -->
            <?php 
            $cast_crew = get_post_meta(get_the_ID(), '_cast_crew', true);
            if ($cast_crew) {
                $cast_data = json_decode($cast_crew, true);
                if ($cast_data && isset($cast_data['cast'])) : ?>
                    <div class="cast-crew-section">
                        <h3><?php _e('Cast', 'moviedb-pro'); ?></h3>
                        <div class="cast-grid">
                            <?php 
                            $cast_limit = 12;
                            $cast_count = 0;
                            foreach ($cast_data['cast'] as $actor) : 
                                if ($cast_count >= $cast_limit) break;
                                ?>
                                <div class="cast-member">
                                    <div class="cast-photo">
                                        <?php if ($actor['profile_path']) : ?>
                                            <img src="https://image.tmdb.org/t/p/w185<?php echo $actor['profile_path']; ?>" 
                                                 alt="<?php echo esc_attr($actor['name']); ?>"
                                                 loading="lazy">
                                        <?php else : ?>
                                            <div class="no-photo">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cast-info">
                                        <h4 class="actor-name"><?php echo esc_html($actor['name']); ?></h4>
                                        <p class="character-name"><?php echo esc_html($actor['character']); ?></p>
                                        <?php if (isset($actor['episode_count'])) : ?>
                                            <p class="episode-count"><?php echo $actor['episode_count']; ?> <?php _e('episodes', 'moviedb-pro'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php 
                                $cast_count++;
                            endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            } ?>
            
            <!-- Episode Guide (Latest Episodes) -->
            <?php 
            $latest_episodes = get_post_meta(get_the_ID(), '_latest_episodes', true);
            if ($latest_episodes) {
                $episodes_data = json_decode($latest_episodes, true);
                if ($episodes_data && is_array($episodes_data)) : ?>
                    <div class="episodes-section">
                        <h3><?php _e('Latest Episodes', 'moviedb-pro'); ?></h3>
                        <div class="episodes-list">
                            <?php foreach ($episodes_data as $episode) : ?>
                                <div class="episode-item">
                                    <?php if (isset($episode['still_path']) && $episode['still_path']) : ?>
                                        <img src="https://image.tmdb.org/t/p/w300<?php echo $episode['still_path']; ?>" 
                                             alt="<?php echo esc_attr($episode['name']); ?>" 
                                             class="episode-still">
                                    <?php endif; ?>
                                    <div class="episode-info">
                                        <div class="episode-header">
                                            <h4 class="episode-title"><?php echo esc_html($episode['name']); ?></h4>
                                            <span class="episode-number">S<?php echo $episode['season_number']; ?>E<?php echo $episode['episode_number']; ?></span>
                                        </div>
                                        <?php if (isset($episode['air_date'])) : ?>
                                            <p class="episode-date"><?php echo date('M j, Y', strtotime($episode['air_date'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($episode['overview']) && $episode['overview']) : ?>
                                            <p class="episode-overview"><?php echo wp_trim_words($episode['overview'], 25); ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($episode['vote_average']) && $episode['vote_average'] > 0) : ?>
                                            <div class="episode-rating">
                                                <i class="fas fa-star"></i>
                                                <span><?php echo number_format($episode['vote_average'], 1); ?>/10</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            } ?>
            
            <!-- Creator/Producer Information -->
            <?php 
            $created_by = get_post_meta(get_the_ID(), '_created_by', true);
            if ($created_by) {
                $creators_data = json_decode($created_by, true);
                if ($creators_data && is_array($creators_data)) : ?>
                    <div class="creators-section">
                        <h3><?php _e('Created By', 'moviedb-pro'); ?></h3>
                        <div class="creators-grid">
                            <?php foreach ($creators_data as $creator) : ?>
                                <div class="creator-item">
                                    <?php if (isset($creator['profile_path']) && $creator['profile_path']) : ?>
                                        <img src="https://image.tmdb.org/t/p/w185<?php echo $creator['profile_path']; ?>" 
                                             alt="<?php echo esc_attr($creator['name']); ?>" 
                                             class="creator-photo">
                                    <?php endif; ?>
                                    <div class="creator-info">
                                        <h4><?php echo esc_html($creator['name']); ?></h4>
                                        <?php if (isset($creator['credit_id'])) : ?>
                                            <p class="creator-role"><?php _e('Creator', 'moviedb-pro'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            } ?>
            
            <!-- Download/Subtitle Links -->
            <?php 
            $subtitle_links = get_post_meta(get_the_ID(), '_subtitle_links', true);
            $download_links = get_post_meta(get_the_ID(), '_download_links', true);
            
            if ($subtitle_links || $download_links) : ?>
                <div class="links-section">
                    <?php if ($subtitle_links) : 
                        $subtitle_data = json_decode($subtitle_links, true);
                        if ($subtitle_data) : ?>
                            <div class="subtitle-links">
                                <h3><?php _e('Subtitles', 'moviedb-pro'); ?></h3>
                                <div class="links-grid">
                                    <?php foreach ($subtitle_data as $subtitle) : ?>
                                        <a href="<?php echo esc_url($subtitle['url']); ?>" 
                                           class="link-button subtitle-link" 
                                           target="_blank">
                                            <i class="fas fa-closed-captioning"></i>
                                            <?php echo esc_html($subtitle['language']); ?>
                                            <?php if (isset($subtitle['season'])) : ?>
                                                <small>(S<?php echo $subtitle['season']; ?>)</small>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; 
                    endif; ?>
                    
                    <?php if ($download_links) : 
                        $download_data = json_decode($download_links, true);
                        if ($download_data) : ?>
                            <div class="download-links">
                                <h3><?php _e('Download', 'moviedb-pro'); ?></h3>
                                <div class="links-grid">
                                    <?php foreach ($download_data as $download) : ?>
                                        <a href="<?php echo esc_url($download['url']); ?>" 
                                           class="link-button download-link" 
                                           target="_blank">
                                            <i class="fas fa-download"></i>
                                            <?php echo esc_html($download['quality']); ?>
                                            <?php if (isset($download['season'])) : ?>
                                                <small>(S<?php echo $download['season']; ?>)</small>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; 
                    endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related TV Shows -->
    <div class="related-movies-section">
        <h3><?php _e('Related TV Shows', 'moviedb-pro'); ?></h3>
        <?php
        $genres = get_the_terms(get_the_ID(), 'genre');
        if ($genres && !is_wp_error($genres)) {
            $genre_ids = array();
            foreach ($genres as $genre) {
                $genre_ids[] = $genre->term_id;
            }
            
            $related_query = new WP_Query(array(
                'post_type' => 'tvshow',
                'posts_per_page' => 8,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'genre',
                        'field' => 'term_id',
                        'terms' => $genre_ids,
                        'operator' => 'IN',
                    ),
                ),
                'orderby' => 'rand',
            ));
            
            if ($related_query->have_posts()) : ?>
                <div class="movie-grid">
                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                        <?php get_template_part('template-parts/movie-card'); ?>
                    <?php endwhile; ?>
                </div>
            <?php endif;
            wp_reset_postdata();
        }
        ?>
    </div>
    
</main>

<!-- TV Show Specific Styles -->
<style>
.tvshow-hero {
    border-left: 5px solid #667eea;
}

.tvshow-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.tvshow-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-ended {
    background: rgba(220, 53, 69, 0.8);
    color: white;
}

.status-returning {
    background: rgba(40, 167, 69, 0.8);
    color: white;
}

.status-canceled {
    background: rgba(108, 117, 125, 0.8);
    color: white;
}

.status-in-production {
    background: rgba(255, 193, 7, 0.8);
    color: #333;
}

.tvshow-stats {
    margin-top: 2rem;
    background: #1a1a1a;
    border-radius: 10px;
    padding: 1.5rem;
}

.tvshow-stats h3 {
    color: #667eea;
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.stats-grid {
    display: grid;
    gap: 1rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #333;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #888;
    font-weight: 500;
}

.stat-value {
    color: #fff;
    font-weight: 600;
}

.seasons-section {
    margin: 2rem 0;
}

.seasons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.season-card {
    background: #1a1a1a;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.season-card:hover {
    transform: translateY(-5px);
}

.season-poster {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.season-info {
    padding: 1rem;
}

.season-info h4 {
    color: #fff;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.episode-count,
.air-date {
    color: #888;
    font-size: 0.9rem;
    margin: 0.25rem 0;
}

.season-overview {
    color: #bbb;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-top: 0.5rem;
}

.episodes-section {
    margin: 2rem 0;
}

.episodes-list {
    display: grid;
    gap: 1.5rem;
    margin-top: 1rem;
}

.episode-item {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1rem;
    background: #1a1a1a;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.episode-item:hover {
    transform: translateY(-3px);
}

.episode-still {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.episode-info {
    padding: 1rem;
}

.episode-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.episode-title {
    color: #fff;
    font-size: 1.1rem;
    margin: 0;
    flex: 1;
    margin-right: 1rem;
}

.episode-number {
    background: #667eea;
    color: white;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.episode-date {
    color: #888;
    font-size: 0.9rem;
    margin: 0.5rem 0;
}

.episode-overview {
    color: #bbb;
    font-size: 0.9rem;
    line-height: 1.4;
    margin: 0.5rem 0;
}

.episode-rating {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #ffd700;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.creators-section {
    margin: 2rem 0;
}

.creators-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.creator-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #1a1a1a;
    padding: 1rem;
    border-radius: 10px;
}

.creator-photo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.creator-info h4 {
    color: #fff;
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
}

.creator-role {
    color: #888;
    font-size: 0.9rem;
    margin: 0;
}

/* Responsive adjustments for TV shows */
@media (max-width: 768px) {
    .tvshow-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .seasons-grid {
        grid-template-columns: 1fr;
    }
    
    .episode-item {
        grid-template-columns: 1fr;
    }
    
    .episode-still {
        height: 180px;
    }
    
    .episode-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .creators-grid {
        grid-template-columns: 1fr;
    }
    
    .creator-item {
        flex-direction: column;
        text-align: center;
    }
}

/* Enhanced link buttons for TV shows */
.link-button small {
    display: block;
    font-size: 0.7rem;
    opacity: 0.8;
    margin-top: 0.2rem;
}

/* Animation for status badge */
.status-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.status-ended {
    animation: none;
}
</style>

<script>
// TV Show specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Season selector functionality
    const seasonCards = document.querySelectorAll('.season-card');
    seasonCards.forEach(card => {
        card.addEventListener('click', function() {
            // Here you could implement season episode loading
            console.log('Season clicked:', this);
        });
    });
    
    // Episode tracking
    const episodeItems = document.querySelectorAll('.episode-item');
    episodeItems.forEach(item => {
        item.addEventListener('click', function() {
            // Mark episode as watched/track progress
            this.classList.toggle('watched');
        });
    });
});

// Share TV show function (override for TV shows)
function shareMovie() {
    const title = '<?php echo esc_js(get_the_title()); ?>';
    const url = '<?php echo esc_js(get_permalink()); ?>';
    const text = `Check out this TV show: ${title}`;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        });
    } else {
        navigator.clipboard.writeText(`${text} - ${url}`).then(function() {
            alert('<?php _e('TV show link copied to clipboard!', 'moviedb-pro'); ?>');
        });
    }
}
</script>

<?php endwhile; ?>

<?php get_footer(); ?>