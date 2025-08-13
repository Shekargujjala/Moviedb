<?php
/**
 * Template part for displaying movie cards
 */
?>

<article class="movie-card" data-post-id="<?php echo get_the_ID(); ?>">
    <div class="movie-card-inner">
        
        <!-- Movie Poster -->
        <div class="movie-poster-container">
            <a href="<?php the_permalink(); ?>" class="poster-link">
                <?php if (has_post_thumbnail()) : ?>
                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'movie-poster'); ?>" 
                         alt="<?php the_title_attribute(); ?>" 
                         class="movie-poster"
                         loading="lazy">
                <?php else : ?>
                    <div class="no-poster">
                        <i class="fas fa-film"></i>
                        <span><?php _e('No Image', 'moviedb-pro'); ?></span>
                    </div>
                <?php endif; ?>
            </a>
            
            <!-- Overlay with quick actions -->
            <div class="movie-overlay">
                <div class="overlay-actions">
                    <?php if (is_user_logged_in()) : ?>
                        <button class="quick-action watchlist-action <?php echo moviedb_is_in_watchlist(get_the_ID()) ? 'added' : ''; ?>" 
                                data-post-id="<?php echo get_the_ID(); ?>"
                                title="<?php echo moviedb_is_in_watchlist(get_the_ID()) ? __('Remove from Watchlist', 'moviedb-pro') : __('Add to Watchlist', 'moviedb-pro'); ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    <?php endif; ?>
                    
                    <?php 
                    $trailer_url = get_post_meta(get_the_ID(), '_trailer_url', true);
                    if ($trailer_url) : ?>
                        <a href="<?php echo esc_url($trailer_url); ?>" 
                           class="quick-action trailer-action" 
                           target="_blank"
                           title="<?php _e('Watch Trailer', 'moviedb-pro'); ?>">
                            <i class="fas fa-play"></i>
                        </a>
                    <?php endif; ?>
                    
                    <button class="quick-action share-action" 
                            data-url="<?php echo esc_url(get_permalink()); ?>"
                            data-title="<?php echo esc_attr(get_the_title()); ?>"
                            title="<?php _e('Share', 'moviedb-pro'); ?>">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
                
                <!-- Quick info -->
                <div class="overlay-info">
                    <div class="movie-rating">
                        <i class="fas fa-star"></i>
                        <span class="rating-value"><?php echo moviedb_get_movie_rating(get_the_ID()); ?></span>
                    </div>
                    
                    <?php 
                    $platforms = moviedb_get_streaming_platforms(get_the_ID());
                    if ($platforms) : ?>
                        <div class="streaming-indicators">
                            <?php 
                            $platform_count = 0;
                            foreach ($platforms as $platform) : 
                                if ($platform_count >= 3) break; ?>
                                <span class="platform-indicator" title="<?php echo esc_attr($platform->name); ?>">
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/platforms/<?php echo $platform->slug; ?>.png" 
                                         alt="<?php echo esc_attr($platform->name); ?>">
                                </span>
                                <?php 
                                $platform_count++;
                            endforeach; ?>
                            
                            <?php if (count($platforms) > 3) : ?>
                                <span class="platform-indicator more">
                                    +<?php echo count($platforms) - 3; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Type indicator -->
            <div class="type-indicator">
                <?php if (get_post_type() == 'tvshow') : ?>
                    <span class="type-badge tv"><?php _e('TV', 'moviedb-pro'); ?></span>
                <?php else : ?>
                    <span class="type-badge movie"><?php _e('Movie', 'moviedb-pro'); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Featured indicator -->
            <?php if (get_post_meta(get_the_ID(), '_featured', true) == 'yes') : ?>
                <div class="featured-indicator">
                    <i class="fas fa-crown"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Movie Info -->
        <div class="movie-info">
            <h3 class="movie-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            
            <div class="movie-meta">
                <span class="movie-year"><?php echo moviedb_get_release_year(get_the_ID()); ?></span>
                <span class="movie-runtime"><?php echo moviedb_get_runtime(get_the_ID()); ?></span>
            </div>
            
            <!-- Genres -->
            <div class="movie-genres">
                <?php
                $genres = get_the_terms(get_the_ID(), 'genre');
                if ($genres && !is_wp_error($genres)) {
                    $genre_count = 0;
                    foreach ($genres as $genre) {
                        if ($genre_count >= 3) break;
                        echo '<span class="genre-tag">' . esc_html($genre->name) . '</span>';
                        $genre_count++;
                    }
                    if (count($genres) > 3) {
                        echo '<span class="genre-tag more">+' . (count($genres) - 3) . '</span>';
                    }
                }
                ?>
            </div>
            
            <!-- Synopsis excerpt -->
            <?php if (has_excerpt()) : ?>
                <div class="movie-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                </div>
            <?php endif; ?>
            
            <!-- View more button -->
            <div class="card-actions">
                <a href="<?php the_permalink(); ?>" class="view-more-btn">
                    <?php _e('View Details', 'moviedb-pro'); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</article>

<style>
.movie-card {
    background: #1a1a1a;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid #333;
}

.movie-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(229,9,20,0.3);
    border-color: #e50914;
}

.movie-card-inner {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.movie-poster-container {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
}

.poster-link {
    display: block;
    width: 100%;
    height: 100%;
}

.movie-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.movie-card:hover .movie-poster {
    transform: scale(1.05);
}

.no-poster {
    width: 100%;
    height: 100%;
    background: #333;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 2rem;
}

.no-poster span {
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.movie-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.8) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1rem;
}

.movie-card:hover .movie-overlay {
    opacity: 1;
}

.overlay-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.quick-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    text-decoration: none;
}

.quick-action:hover {
    background: rgba(229,9,20,0.8);
    transform: scale(1.1);
}

.watchlist-action.added {
    background: rgba(40,167,69,0.8);
}

.trailer-action:hover {
    background: rgba(255,215,0,0.8);
    color: #333;
}

.share-action:hover {
    background: rgba(102,126,234,0.8);
}

.overlay-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.movie-rating {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    background: rgba(0,0,0,0.6);
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

.movie-rating i {
    color: #ffd700;
    font-size: 0.9rem;
}

.rating-value {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.streaming-indicators {
    display: flex;
    gap: 0.3rem;
    align-items: center;
}

.platform-indicator {
    width: 30px;
    height: 20px;
    border-radius: 4px;
    overflow: hidden;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.platform-indicator img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.platform-indicator.more {
    background: rgba(229,9,20,0.8);
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
}

.type-indicator {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
}

.type-badge {
    background: rgba(229,9,20,0.9);
    color: white;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.type-badge.tv {
    background: rgba(102,126,234,0.9);
}

.featured-indicator {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    color: #ffd700;
    font-size: 1.2rem;
    filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.8));
}

.movie-info {
    padding: 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.movie-title {
    margin: 0 0 0.75rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.3;
}

.movie-title a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.movie-title a:hover {
    color: #e50914;
}

.movie-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: #888;
}

.movie-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.movie-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-bottom: 0.75rem;
}

.genre-tag {
    background: rgba(229,9,20,0.15);
    color: #e50914;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    border: 1px solid rgba(229,9,20,0.3);
    font-weight: 500;
}

.genre-tag.more {
    background: rgba(255,255,255,0.1);
    color: #888;
    border-color: rgba(255,255,255,0.2);
}

.movie-excerpt {
    color: #bbb;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 1rem;
    flex: 1;
}

.card-actions {
    margin-top: auto;
}

.view-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #e50914;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    padding: 0.5rem 0;
}

.view-more-btn:hover {
    color: #fff;
    gap: 0.75rem;
}

.view-more-btn i {
    transition: transform 0.3s ease;
}

.view-more-btn:hover i {
    transform: translateX(3px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .movie-poster-container {
        height: 250px;
    }
    
    .movie-info {
        padding: 1rem;
    }
    
    .movie-title {
        font-size: 1rem;
    }
    
    .overlay-actions {
        justify-content: center;
    }
    
    .overlay-info {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .streaming-indicators {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .movie-poster-container {
        height: 200px;
    }
    
    .movie-meta {
        flex-direction: column;
        gap: 0.3rem;
    }
    
    .quick-action {
        width: 35px;
        height: 35px;
    }
}

/* Loading state */
.movie-card.loading {
    opacity: 0.6;
    pointer-events: none;
}

.movie-card.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30px;
    height: 30px;
    border: 3px solid rgba(229,9,20,0.3);
    border-top: 3px solid #e50914;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    transform: translate(-50%, -50%);
    z-index: 10;
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.movie-card {
    animation: fadeInUp 0.6s ease forwards;
}

/* Accessibility improvements */
.movie-card:focus-within {
    outline: 2px solid #e50914;
    outline-offset: 2px;
}

.quick-action:focus {
    outline: 2px solid #fff;
    outline-offset: 2px;
}

.view-more-btn:focus {
    outline: 2px solid #e50914;
    outline-offset: 2px;
    border-radius: 4px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .movie-card {
        border: 2px solid #fff;
    }
    
    .movie-overlay {
        background: rgba(0,0,0,0.9);
    }
    
    .genre-tag {
        border-width: 2px;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .movie-card,
    .movie-poster,
    .quick-action,
    .view-more-btn,
    .view-more-btn i {
        transition: none;
    }
    
    .movie-card:hover {
        transform: none;
    }
    
    .movie-card:hover .movie-poster {
        transform: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Watchlist functionality for cards
    document.querySelectorAll('.watchlist-action').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const postId = this.dataset.postId;
            const button = this;
            const card = button.closest('.movie-card');
            
            // Add loading state
            card.classList.add('loading');
            
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
                        button.title = '<?php _e('Remove from Watchlist', 'moviedb-pro'); ?>';
                    } else {
                        button.classList.remove('added');
                        button.title = '<?php _e('Add to Watchlist', 'moviedb-pro'); ?>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                card.classList.remove('loading');
            });
        });
    });
    
    // Share functionality for cards
    document.querySelectorAll('.share-action').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const url = this.dataset.url;
            const title = this.dataset.title;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                });
            } else {
                // Fallback to copy to clipboard
                navigator.clipboard.writeText(url).then(function() {
                    // Show temporary tooltip
                    const button = e.target.closest('.share-action');
                    const originalTitle = button.title;
                    button.title = '<?php _e('Link copied!', 'moviedb-pro'); ?>';
                    
                    setTimeout(() => {
                        button.title = originalTitle;
                    }, 2000);
                });
            }
        });
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Card animation on scroll
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = Math.random() * 0.3 + 's';
                entry.target.classList.add('animate-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.movie-card').forEach(card => {
        cardObserver.observe(card);
    });
});
</script>