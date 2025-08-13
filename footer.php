<?php
/**
 * The template for displaying the footer
 */
?>

</div><!-- #page -->

<footer id="colophon" class="site-footer">
    <div class="footer-container">
        
        <!-- Footer Widgets -->
        <?php if (is_active_sidebar('footer-1')) : ?>
            <div class="footer-widgets">
                <div class="footer-widget-area">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Footer Content -->
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php _e('Quick Links', 'moviedb-pro'); ?></h3>
                <ul class="footer-links">
                    <li><a href="<?php echo home_url(); ?>"><?php _e('Home', 'moviedb-pro'); ?></a></li>
                    <li><a href="<?php echo home_url('/movies'); ?>"><?php _e('Movies', 'moviedb-pro'); ?></a></li>
                    <li><a href="<?php echo home_url('/tv-shows'); ?>"><?php _e('TV Shows', 'moviedb-pro'); ?></a></li>
                    <?php if (is_user_logged_in()) : ?>
                        <li><a href="<?php echo home_url('/watchlist'); ?>"><?php _e('My Watchlist', 'moviedb-pro'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3><?php _e('Popular Genres', 'moviedb-pro'); ?></h3>
                <ul class="footer-links">
                    <?php
                    $popular_genres = get_terms(array(
                        'taxonomy' => 'genre',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 6,
                        'hide_empty' => true,
                    ));
                    
                    foreach ($popular_genres as $genre) {
                        echo '<li><a href="' . get_term_link($genre) . '">' . esc_html($genre->name) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3><?php _e('Streaming Platforms', 'moviedb-pro'); ?></h3>
                <ul class="footer-links">
                    <?php
                    $platforms = get_terms(array(
                        'taxonomy' => 'streaming_platform',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 6,
                        'hide_empty' => true,
                    ));
                    
                    foreach ($platforms as $platform) {
                        echo '<li><a href="' . get_term_link($platform) . '">' . esc_html($platform->name) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3><?php _e('About MovieDB Pro', 'moviedb-pro'); ?></h3>
                <p><?php _e('Your ultimate destination for movie and TV show information. Discover new content, track your favorites, and find where to watch.', 'moviedb-pro'); ?></p>
                
                <div class="social-links">
                    <h4><?php _e('Follow Us', 'moviedb-pro'); ?></h4>
                    <div class="social-icons">
                        <a href="#" class="social-icon facebook" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon twitter" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon instagram" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-icon youtube" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'moviedb-pro'); ?></p>
                    <p class="tmdb-attribution">
                        <?php _e('Movie data provided by', 'moviedb-pro'); ?> 
                        <a href="https://www.themoviedb.org/" target="_blank" rel="noopener">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/tmdb-logo.svg" alt="TMDB" style="height: 20px; vertical-align: middle; margin-left: 5px;">
                        </a>
                    </p>
                </div>
                
                <div class="footer-menu">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_id' => 'footer-menu',
                        'container' => false,
                        'fallback_cb' => 'moviedb_footer_fallback_menu',
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" aria-label="<?php _e('Back to top', 'moviedb-pro'); ?>">
        <i class="fas fa-chevron-up"></i>
    </button>
</footer>

<!-- Footer Styles -->
<style>
.site-footer {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
    color: #888;
    margin-top: 4rem;
    border-top: 3px solid #e50914;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 20px 1rem;
}

.footer-widgets {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #333;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-section h3 {
    color: #e50914;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.footer-section h4 {
    color: #fff;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-links a {
    color: #888;
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.9rem;
}

.footer-links a:hover {
    color: #e50914;
}

.footer-section p {
    line-height: 1.6;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.social-links {
    margin-top: 1rem;
}

.social-icons {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.social-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(229, 9, 20, 0.1);
    color: #e50914;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(229, 9, 20, 0.3);
}

.social-icon:hover {
    background: #e50914;
    color: #fff;
    transform: translateY(-2px);
}

.footer-bottom {
    border-top: 1px solid #333;
    padding-top: 2rem;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.copyright p {
    margin: 0;
    font-size: 0.85rem;
    color: #666;
}

.tmdb-attribution {
    margin-top: 0.5rem;
}

.tmdb-attribution a {
    color: #e50914;
    text-decoration: none;
}

.footer-menu ul {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.footer-menu a {
    color: #888;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-menu a:hover {
    color: #e50914;
}

.back-to-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 50px;
    height: 50px;
    background: #e50914;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: #b8070f;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-menu ul {
        justify-content: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .social-icons {
        justify-content: center;
    }
    
    .back-to-top {
        bottom: 1rem;
        right: 1rem;
        width: 45px;
        height: 45px;
    }
}

@media (max-width: 480px) {
    .footer-container {
        padding: 2rem 15px 1rem;
    }
    
    .footer-content {
        gap: 1.5rem;
    }
    
    .footer-section h3 {
        font-size: 1.1rem;
    }
}

/* Widget Styles */
.footer-widget-area .widget {
    margin-bottom: 2rem;
}

.footer-widget-area .widget-title {
    color: #e50914;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.footer-widget-area .widget ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-widget-area .widget li {
    margin-bottom: 0.5rem;
}

.footer-widget-area .widget a {
    color: #888;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-widget-area .widget a:hover {
    color: #e50914;
}

/* Loading animation for footer */
.footer-loading {
    text-align: center;
    padding: 2rem;
}

.footer-loading .spinner {
    width: 30px;
    height: 30px;
    border: 3px solid rgba(229,9,20,0.3);
    border-top: 3px solid #e50914;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}
</style>

<!-- Footer JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to top functionality
    const backToTopButton = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('visible');
        } else {
            backToTopButton.classList.remove('visible');
        }
    });
    
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Smooth scrolling for footer links
    document.querySelectorAll('.footer-links a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Footer animation on scroll
    const footer = document.querySelector('.site-footer');
    const observerOptions = {
        threshold: 0.1
    };
    
    const footerObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    if (footer) {
        footer.style.opacity = '0';
        footer.style.transform = 'translateY(50px)';
        footer.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        footerObserver.observe(footer);
    }
});
</script>

<?php
// Footer fallback menu
function moviedb_footer_fallback_menu() {
    echo '<ul id="footer-menu">';
    echo '<li><a href="' . get_privacy_policy_url() . '">' . __('Privacy Policy', 'moviedb-pro') . '</a></li>';
    echo '<li><a href="' . home_url('/terms') . '">' . __('Terms of Service', 'moviedb-pro') . '</a></li>';
    echo '<li><a href="' . home_url('/contact') . '">' . __('Contact', 'moviedb-pro') . '</a></li>';
    echo '<li><a href="' . home_url('/about') . '">' . __('About', 'moviedb-pro') . '</a></li>';
    echo '</ul>';
}

wp_footer(); ?>

</body>
</html>