<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
    
    <style>
        /* Additional responsive styles */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .main-navigation ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            
            .search-input {
                width: 200px;
            }
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .main-navigation {
                display: none;
                width: 100%;
            }
            
            .main-navigation.active {
                display: block;
            }
            
            .main-navigation ul {
                flex-direction: column;
                align-items: center;
                padding: 1rem 0;
            }
        }
    </style>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header id="masthead" class="site-header">
        <div class="header-container">
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <button class="mobile-menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>

            <nav id="site-navigation" class="main-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id' => 'primary-menu',
                    'fallback_cb' => 'moviedb_fallback_menu',
                ));
                ?>
            </nav>

            <div class="header-search">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" 
                           class="search-input" 
                           placeholder="<?php echo esc_attr_x('Search movies & TV shows...', 'placeholder', 'moviedb-pro'); ?>"
                           value="<?php echo get_search_query(); ?>" 
                           name="s" 
                           title="<?php echo esc_attr_x('Search for:', 'label', 'moviedb-pro'); ?>" />
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <?php if (is_user_logged_in()) : ?>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo get_avatar(get_current_user_id(), 40); ?>
                    </div>
                    <div class="user-dropdown">
                        <a href="<?php echo home_url('/watchlist'); ?>" class="user-link">
                            <i class="fas fa-heart"></i> <?php _e('My Watchlist', 'moviedb-pro'); ?>
                        </a>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="user-link">
                            <i class="fas fa-sign-out-alt"></i> <?php _e('Logout', 'moviedb-pro'); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="auth-links">
                    <a href="<?php echo wp_login_url(get_permalink()); ?>" class="auth-link">
                        <?php _e('Login', 'moviedb-pro'); ?>
                    </a>
                    <a href="<?php echo wp_registration_url(); ?>" class="auth-link">
                        <?php _e('Register', 'moviedb-pro'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Breadcrumbs -->
    <?php if (!is_home() && !is_front_page()) : ?>
        <div class="breadcrumbs-container">
            <div class="breadcrumbs">
                <?php moviedb_breadcrumbs(); ?>
            </div>
        </div>
    <?php endif; ?>

<?php
// Fallback menu function
function moviedb_fallback_menu() {
    echo '<ul id="primary-menu">';
    echo '<li><a href="' . home_url() . '">' . __('Home', 'moviedb-pro') . '</a></li>';
    echo '<li><a href="' . home_url('/movies') . '">' . __('Movies', 'moviedb-pro') . '</a></li>';
    echo '<li><a href="' . home_url('/tv-shows') . '">' . __('TV Shows', 'moviedb-pro') . '</a></li>';
    
    // Get genres for dropdown
    $genres = get_terms(array(
        'taxonomy' => 'genre',
        'hide_empty' => true,
        'number' => 10,
    ));
    
    if ($genres && !is_wp_error($genres)) {
        echo '<li class="menu-item-has-children">';
        echo '<a href="#">' . __('Genres', 'moviedb-pro') . '</a>';
        echo '<ul class="sub-menu">';
        foreach ($genres as $genre) {
            echo '<li><a href="' . get_term_link($genre) . '">' . esc_html($genre->name) . '</a></li>';
        }
        echo '</ul>';
        echo '</li>';
    }
    
    echo '</ul>';
}

// Breadcrumbs function
function moviedb_breadcrumbs() {
    if (!is_home() && !is_front_page()) {
        echo '<a href="' . home_url() . '">' . __('Home', 'moviedb-pro') . '</a>';
        
        if (is_category() || is_single()) {
            echo ' <i class="fas fa-chevron-right"></i> ';
            if (is_single()) {
                $categories = get_the_category();
                if ($categories) {
                    foreach ($categories as $category) {
                        echo '<a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a>';
                        echo ' <i class="fas fa-chevron-right"></i> ';
                        break;
                    }
                }
                echo '<span>' . get_the_title() . '</span>';
            } else {
                echo '<span>' . single_cat_title('', false) . '</span>';
            }
        } elseif (is_page()) {
            echo ' <i class="fas fa-chevron-right"></i> <span>' . get_the_title() . '</span>';
        } elseif (is_search()) {
            echo ' <i class="fas fa-chevron-right"></i> <span>' . __('Search Results', 'moviedb-pro') . '</span>';
        } elseif (is_tax()) {
            $term = get_queried_object();
            echo ' <i class="fas fa-chevron-right"></i> <span>' . $term->name . '</span>';
        } elseif (is_post_type_archive()) {
            echo ' <i class="fas fa-chevron-right"></i> <span>' . post_type_archive_title('', false) . '</span>';
        }
    }
}
?>

<style>
/* User Menu Styles */
.user-menu {
    position: relative;
    display: inline-block;
}

.user-avatar {
    cursor: pointer;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #e50914;
}

.user-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: #1a1a1a;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    z-index: 1000;
    border-radius: 8px;
    border: 1px solid #333;
    margin-top: 0.5rem;
}

.user-menu:hover .user-dropdown {
    display: block;
}

.user-link {
    color: #fff;
    padding: 0.75rem 1rem;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s ease;
}

.user-link:hover {
    background-color: #e50914;
}

.user-link i {
    margin-right: 0.5rem;
    width: 16px;
}

.auth-links {
    display: flex;
    gap: 1rem;
}

.auth-link {
    color: #fff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border: 1px solid #e50914;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.auth-link:hover {
    background-color: #e50914;
    color: #fff;
}

/* Breadcrumbs Styles */
.breadcrumbs-container {
    background: #1a1a1a;
    padding: 1rem 0;
    border-bottom: 1px solid #333;
}

.breadcrumbs {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    color: #888;
    font-size: 0.9rem;
}

.breadcrumbs a {
    color: #e50914;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumbs a:hover {
    color: #fff;
}

.breadcrumbs i {
    margin: 0 0.5rem;
    font-size: 0.8rem;
}

.breadcrumbs span {
    color: #fff;
}

/* Dropdown Menu Styles */
.menu-item-has-children {
    position: relative;
}

.sub-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #1a1a1a;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    z-index: 1000;
    border-radius: 8px;
    border: 1px solid #333;
    margin-top: 0.5rem;
    list-style: none;
    padding: 0;
}

.menu-item-has-children:hover .sub-menu {
    display: block;
}

.sub-menu li {
    margin: 0;
}

.sub-menu a {
    color: #fff;
    padding: 0.75rem 1rem;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s ease;
    border-radius: 0;
}

.sub-menu a:hover {
    background-color: #e50914;
}

/* Mobile Menu Styles */
@media (max-width: 768px) {
    .header-container {
        position: relative;
    }
    
    .user-menu .user-dropdown,
    .menu-item-has-children .sub-menu {
        position: static;
        display: block;
        box-shadow: none;
        border: none;
        margin: 0;
        background: transparent;
    }
    
    .auth-links {
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    
    .header-search {
        order: -1;
        width: 100%;
    }
    
    .search-form {
        width: 100%;
    }
    
    .search-input {
        flex: 1;
    }
    
    .breadcrumbs {
        font-size: 0.8rem;
        overflow-x: auto;
        white-space: nowrap;
    }
}

/* Loading States */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(229,9,20,0.3);
    border-top: 4px solid #e50914;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Accessibility improvements */
.screen-reader-text {
    border: 0;
    clip: rect(1px, 1px, 1px, 1px);
    clip-path: inset(50%);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute !important;
    width: 1px;
    word-wrap: normal !important;
}

.screen-reader-text:focus {
    background-color: #e50914;
    border-radius: 3px;
    box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
    clip: auto !important;
    clip-path: none;
    color: #fff;
    display: block;
    font-size: 14px;
    font-size: 0.875rem;
    font-weight: bold;
    height: auto;
    left: 5px;
    line-height: normal;
    padding: 15px 23px 14px;
    text-decoration: none;
    top: 5px;
    width: auto;
    z-index: 100000;
}

/* Skip link */
.skip-link {
    left: -9999px;
    position: absolute;
    top: -9999px;
}

.skip-link:focus {
    background-color: #e50914;
    color: #fff;
    left: 6px;
    padding: 8px 16px;
    position: absolute;
    top: 7px;
    text-decoration: none;
    z-index: 999999;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navigation = document.querySelector('.main-navigation');
    
    if (mobileToggle && navigation) {
        mobileToggle.addEventListener('click', function() {
            navigation.classList.toggle('active');
            const expanded = navigation.classList.contains('active');
            mobileToggle.setAttribute('aria-expanded', expanded);
            mobileToggle.querySelector('i').classList.toggle('fa-bars');
            mobileToggle.querySelector('i').classList.toggle('fa-times');
        });
    }
    
    // Search enhancement
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
});