<?php
/**
 * The template for displaying single movie posts
 */

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<!-- Movie Hero Section -->
<div class="movie-hero" style="background-image: url('<?php echo esc_url(get_post_meta(get_the_ID(), '_backdrop_url', true)); ?>');">
    <div class="movie-hero-content">
        <div class="movie-hero-info">
            <h1 class="movie-title"><?php the_title(); ?></h1>
            <div class="movie-meta">
                <span class="release-year"><?php echo moviedb_get_release_year(get_the_ID()); ?></span>
                <span class="runtime"><?php echo moviedb_get_runtime(get_the_ID()); ?></span>
                <span class="rating">
                    <i class="fas fa-star"></i> <?php echo moviedb_get_movie_rating(get_the_ID()); ?>
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
        
        <!-- Movie Poster -->
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
        </div>
        
        <!-- Movie Details -->
        <div class="movie-info-section">
            
            <!-- Synopsis -->
            <div class="movie-synopsis">
                <h2><?php _e('Synopsis', 'moviedb-pro'); ?></h2>
                <div class="synopsis-content">
                    <?php the_content(); ?>
                </div>
            </div>
            
            <!-- Movie Details Table -->
            <div class="movie-details-table">
                <h3><?php _e('Details', 'moviedb-pro'); ?></h3>
                <table class="details-table">
                    <tr>
                        <td><strong><?php _e('Release Date', 'moviedb-pro'); ?>:</strong></td>
                        <td><?php echo date('F j, Y', strtotime(get_post_meta(get_the_ID(), '_release_date', true))); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Runtime', 'moviedb-pro'); ?>:</strong></td>
                        <td><?php echo moviedb_get_runtime(get_the_ID()); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('IMDB Rating', 'moviedb-pro'); ?>:</strong></td>
                        <td>
                            <?php 
                            $imdb_rating = get_post_meta(get_the_ID(), '_imdb_rating', true);
                            if ($imdb_rating) {
                                echo '<span class="rating-value">' . $imdb_rating . '/10</span>';
                                echo '<div class="rating-stars">';
                                for ($i = 1; $i <= 5; $i++) {
                                    $star_rating = ($imdb_rating / 2);
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
                                echo '<a href="https://www.themoviedb.org/movie/' . $tmdb_id . '" target="_blank">' . $tmdb_id . '</a>';
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
                                    </div>
                                </div>
                                <?php 
                                $cast_count++;
                            endforeach; ?>
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
    
    <!-- Image Gallery -->
    <?php 
    $image_gallery = get_post_meta(get_the_ID(), '_image_gallery', true);
    if ($image_gallery) {
        $gallery_data = json_decode($image_gallery, true);
        if ($gallery_data && isset($gallery_data['backdrops']) && count($gallery_data['backdrops']) > 0) : ?>
            <div class="image-gallery-section">
                <h3><?php _e('Gallery', 'moviedb-pro'); ?></h3>
                <div class="image-gallery">
                    <?php 
                    $gallery_limit = 8;
                    $gallery_count = 0;
                    foreach ($gallery_data['backdrops'] as $image) : 
                        if ($gallery_count >= $gallery_limit) break;
                        ?>
                        <div class="gallery-item">
                            <img src="https://image.tmdb.org/t/p/w500<?php echo $image['file_path']; ?>" 
                                 alt="<?php the_title_attribute(); ?> - Image <?php echo $gallery_count + 1; ?>"
                                 loading="lazy"
                                 onclick="openLightbox('https://image.tmdb.org/t/p/w1280<?php echo $image['file_path']; ?>')">
                        </div>
                        <?php 
                        $gallery_count++;
                    endforeach; ?>
                </div>
            </div>
        <?php endif;
    } ?>
    
    <!-- Related Movies -->
    <div class="related-movies-section">
        <h3><?php _e('Related Movies', 'moviedb-pro'); ?></h3>
        <?php
        $genres = get_the_terms(get_the_ID(), 'genre');
        if ($genres && !is_wp_error($genres)) {
            $genre_ids = array();
            foreach ($genres as $genre) {
                $genre_ids[] = $genre->term_id;
            }
            
            $related_query = new WP_Query(array(
                'post_type' => array('movie', 'tvshow'),
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

<!-- Lightbox Modal -->
<div id="lightbox-modal" class="lightbox-modal" onclick="closeLightbox()">
    <img id="lightbox-image" src="" alt="">
    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
</div>

<!-- Single Movie Styles -->
<style>
.movie-hero {
    position: relative;
    height: 70vh;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: end;
    margin-bottom: 2rem;
    border-radius: 15px;
    overflow: hidden;
}

.movie-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.9) 100%);
}

.movie-hero-content {
    position: relative;
    z-index: 2;
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    color: white;
}

.movie-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
}

.movie-meta {
    display: flex;
    gap: 2rem;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.movie-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.movie-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.movie-details-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.movie-poster-large {
    width: 100%;
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
}

.movie-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1.5rem;
}

.watchlist-button,
.trailer-button,
.share-button {
    padding: 1rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.watchlist-button {
    background: linear-gradient(45deg, #e50914, #b8070f);
    color: white;
}

.watchlist-button.added {
    background: linear-gradient(45deg, #28a745, #20b147);
}

.trailer-button {
    background: linear-gradient(45deg, #ffd700, #ffed4e);
    color: #333;
}

.share-button {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
}

.streaming-section {
    margin-top: 2rem;
}

.streaming-section h3 {
    color: #e50914;
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.streaming-platforms {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}

.platform-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    background: #1a1a1a;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.platform-item:hover {
    transform: translateY(-5px);
}

.platform-logo {
    width: 60px;
    height: 40px;
    object-fit: contain;
    margin-bottom: 0.5rem;
}

.platform-item span {
    color: #fff;
    font-size: 0.9rem;
    text-align: center;
}

.movie-info-section h2,
.movie-info-section h3 {
    color: #e50914;
    margin-bottom: 1rem;
    border-bottom: 2px solid #e50914;
    padding-bottom: 0.5rem;
}

.synopsis-content {
    color: #fff;
    line-height: 1.8;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
}

.details-table td {
    padding: 0.75rem 0;
    border-bottom: 1px solid #333;
    color: #fff;
}

.details-table td:first-child {
    width: 150px;
    color: #888;
}

.rating-stars {
    display: inline-flex;
    gap: 0.2rem;
    margin-left: 0.5rem;
    color: #ffd700;
}

.cast-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cast-member {
    background: #1a1a1a;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.cast-member:hover {
    transform: translateY(-5px);
}

.cast-photo {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.cast-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-photo {
    width: 100%;
    height: 100%;
    background: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 3rem;
}

.cast-info {
    padding: 1rem;
}

.actor-name {
    color: #fff;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.character-name {
    color: #888;
    font-size: 0.9rem;
    margin: 0;
}

.links-section {
    margin: 2rem 0;
}

.links-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.link-button {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.subtitle-link {
    background: linear-gradient(45deg, #6c5ce7, #a29bfe);
    color: white;
}

.download-link {
    background: linear-gradient(45deg, #00b894, #00cec9);
    color: white;
}

.link-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.gallery-item {
    cursor: pointer;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.05);
}

.gallery-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.lightbox-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    cursor: pointer;
}

.lightbox-modal img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    background: none;
    border: none;
    cursor: pointer;
}

/* Responsive Design */
@media (max-width: 768px) {
    .movie-hero {
        height: 50vh;
    }
    
    .movie-title {
        font-size: 2rem;
    }
    
    .movie-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .movie-details-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .cast-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
    }
    
    .image-gallery {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .streaming-platforms {
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    }
}
</style>

<script>
// Watchlist functionality
document.addEventListener('DOMContentLoaded', function() {
    const watchlistBtn = document.querySelector('.watchlist-button');
    if (watchlistBtn) {
        watchlistBtn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const button = this;
            
            fetch(moviedb_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'moviedb_add_to_watchlist',
                    post_id: postId,
                    nonce: moviedb_ajax.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        button.classList.add('added');
                        button.querySelector('span').textContent = '<?php _e('Remove from Watchlist', 'moviedb-pro'); ?>';
                    } else {
                        button.classList.remove('added');
                        button.querySelector('span').textContent = '<?php _e('Add to Watchlist', 'moviedb-pro'); ?>';
                    }
                }
            });
        });
    }
});

// Share functionality
function shareMovie() {
    const title = '<?php echo esc_js(get_the_title()); ?>';
    const url = '<?php echo esc_js(get_permalink()); ?>';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback to copy to clipboard
        navigator.clipboard.writeText(url).then(function() {
            alert('<?php _e('Link copied to clipboard!', 'moviedb-pro'); ?>');
        });
    }
}

// Lightbox functionality
function openLightbox(imageSrc) {
    const modal = document.getElementById('lightbox-modal');
    const img = document.getElementById('lightbox-image');
    modal.style.display = 'block';
    img.src = imageSrc;
}

function closeLightbox() {
    const modal = document.getElementById('lightbox-modal');
    modal.style.display = 'none';
}

// Close lightbox with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});
</script>

<?php endwhile; ?>

<?php get_footer(); ?>