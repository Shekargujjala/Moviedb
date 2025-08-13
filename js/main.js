/**
 * MovieDB Pro Main JavaScript
 */

(function($) {
    'use strict';

    // Global variables
    let isLoading = false;
    let currentPage = 1;
    let maxPages = 1;

    // Initialize everything when DOM is ready
    $(document).ready(function() {
        initializeTheme();
        initializeSearch();
        initializeFilters();
        initializeLoadMore();
        initializeTMDBImport();
        initializeWatchlist();
        initializeLightbox();
        initializeTooltips();
        initializeAnimations();
    });

    /**
     * Initialize theme functionality
     */
    function initializeTheme() {
        // Mobile menu toggle
        $('.mobile-menu-toggle').on('click', function() {
            const nav = $('.main-navigation');
            const isExpanded = nav.hasClass('active');
            
            nav.toggleClass('active');
            $(this).attr('aria-expanded', !isExpanded);
            $(this).find('i').toggleClass('fa-bars fa-times');
        });

        // Smooth scrolling for anchor links
        $('a[href^="#"]').on('click', function(event) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                event.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 1000);
            }
        });

        // Back to top functionality
        const backToTop = $('#back-to-top');
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                backToTop.addClass('visible');
            } else {
                backToTop.removeClass('visible');
            }
        });

        backToTop.on('click', function() {
            $('html, body').animate({scrollTop: 0}, 600);
        });

        // Header scroll effect
        let lastScrollTop = 0;
        $(window).scroll(function() {
            const scrollTop = $(this).scrollTop();
            const header = $('.site-header');
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.addClass('header-hidden');
            } else {
                header.removeClass('header-hidden');
            }
            lastScrollTop = scrollTop;
        });
    }

    /**
     * Initialize search functionality
     */
    function initializeSearch() {
        const searchForm = $('.search-form');
        const searchInput = $('.search-input');
        let searchTimeout;

        // Live search suggestions (optional)
        searchInput.on('input', function() {
            const query = $(this).val();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(function() {
                    performLiveSearch(query);
                }, 300);
            } else {
                hideSuggestions();
            }
        });

        // Handle search form submission
        searchForm.on('submit', function(e) {
            const query = searchInput.val().trim();
            if (query.length < 2) {
                e.preventDefault();
                showNotification('Please enter at least 2 characters', 'warning');
            }
        });
    }

    /**
     * Perform live search
     */
    function performLiveSearch(query) {
        $.ajax({
            url: moviedb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'moviedb_live_search',
                query: query,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuggestions(response.data);
                }
            }
        });
    }

    /**
     * Show search suggestions
     */
    function showSuggestions(suggestions) {
        let suggestionsHtml = '<div class="search-suggestions">';
        
        suggestions.forEach(function(item) {
            suggestionsHtml += `
                <div class="suggestion-item">
                    <img src="${item.poster}" alt="${item.title}" class="suggestion-poster">
                    <div class="suggestion-info">
                        <h4>${item.title}</h4>
                        <span class="suggestion-year">${item.year}</span>
                        <span class="suggestion-type">${item.type}</span>
                    </div>
                </div>
            `;
        });
        
        suggestionsHtml += '</div>';
        
        $('.search-form').append(suggestionsHtml);
    }

    /**
     * Hide search suggestions
     */
    function hideSuggestions() {
        $('.search-suggestions').remove();
    }

    /**
     * Initialize filter functionality
     */
    function initializeFilters() {
        const filterForm = $('#filter-form');
        const filterSelects = $('.filter-select');

        // Auto-submit on filter change
        filterSelects.on('change', function() {
            if (!isLoading) {
                filterForm.submit();
            }
        });

        // AJAX filtering (optional)
        filterForm.on('submit', function(e) {
            if ($(this).data('ajax-enabled')) {
                e.preventDefault();
                performAjaxFilter();
            }
        });
    }

    /**
     * Perform AJAX filtering
     */
    function performAjaxFilter() {
        if (isLoading) return;

        isLoading = true;
        const formData = $('#filter-form').serialize();
        
        showLoadingState();

        $.ajax({
            url: moviedb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'moviedb_filter_movies',
                filters: formData,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#movie-grid').html(response.data.html);
                    updatePagination(response.data.pagination);
                    
                    // Update URL without page reload
                    const url = new URL(window.location);
                    const params = new URLSearchParams(formData);
                    params.forEach((value, key) => {
                        if (value) {
                            url.searchParams.set(key, value);
                        } else {
                            url.searchParams.delete(key);
                        }
                    });
                    window.history.pushState({}, '', url);
                }
            },
            error: function() {
                showNotification('Error loading results', 'error');
            },
            complete: function() {
                isLoading = false;
                hideLoadingState();
            }
        });
    }

    /**
     * Initialize load more functionality
     */
    function initializeLoadMore() {
        const loadMoreBtn = $('#load-more-btn');
        
        loadMoreBtn.on('click', function() {
            if (isLoading) return;
            
            const button = $(this);
            const nextPage = parseInt(button.data('page')) + 1;
            const maxPages = parseInt(button.data('max-pages'));
            
            if (nextPage > maxPages) return;
            
            loadMoreMovies(nextPage, button);
        });

        // Infinite scroll (optional)
        if ($('body').hasClass('infinite-scroll-enabled')) {
            $(window).scroll(function() {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 500) {
                    if (!isLoading && currentPage < maxPages) {
                        loadMoreMovies(currentPage + 1);
                    }
                }
            });
        }
    }

    /**
     * Load more movies via AJAX
     */
    function loadMoreMovies(page, button = null) {
        if (isLoading) return;
        
        isLoading = true;
        currentPage = page;
        
        const formData = $('#filter-form').length ? $('#filter-form').serialize() : '';
        
        if (button) {
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        }

        $.ajax({
            url: moviedb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'moviedb_load_more',
                page: page,
                filters: formData,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#movie-grid').append(response.data.html);
                    
                    if (button) {
                        button.data('page', page);
                        if (page >= maxPages) {
                            button.hide();
                        }
                    }
                    
                    // Trigger lazy loading for new images
                    initializeLazyLoading();
                }
            },
            error: function() {
                showNotification('Error loading more content', 'error');
            },
            complete: function() {
                isLoading = false;
                if (button) {
                    button.prop('disabled', false).html('Load More');
                }
            }
        });
    }

    /**
     * Initialize TMDB import functionality
     */
    function initializeTMDBImport() {
        const searchBtn = $('#search-tmdb');
        const importBtn = $('#import-tmdb');
        const searchInput = $('#tmdb-search');
        const resultsContainer = $('#tmdb-results');
        let selectedItem = null;

        searchBtn.on('click', function() {
            const query = searchInput.val().trim();
            if (!query) {
                showNotification('Please enter a search term', 'warning');
                return;
            }
            
            searchTMDB(query);
        });

        importBtn.on('click', function() {
            if (!selectedItem) {
                showNotification('Please select an item to import', 'warning');
                return;
            }
            
            importFromTMDB(selectedItem);
        });

        // Handle result selection
        resultsContainer.on('click', '.tmdb-result', function() {
            resultsContainer.find('.tmdb-result').removeClass('selected');
            $(this).addClass('selected');
            
            selectedItem = {
                id: $(this).data('id'),
                type: $(this).data('type')
            };
            
            importBtn.prop('disabled', false);
        });
    }

    /**
     * Search TMDB
     */
    function searchTMDB(query) {
        const searchBtn = $('#search-tmdb');
        const resultsContainer = $('#tmdb-results');
        const postType = $('#post_type').val() || 'movie';
        const type = postType === 'tvshow' ? 'tv' : 'movie';
        
        searchBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
        
        $.ajax({
            url: moviedb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'moviedb_search_tmdb',
                query: query,
                type: type,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.results && data.results.length > 0) {
                    displayTMDBResults(data.results, type);
                } else {
                    resultsContainer.html('<p>No results found.</p>');
                }
            },
            error: function() {
                showNotification('Error searching TMDB', 'error');
            },
            complete: function() {
                searchBtn.prop('disabled', false).html('Search');
            }
        });
    }

    /**
     * Display TMDB search results
     */
    function displayTMDBResults(results, type) {
        const resultsContainer = $('#tmdb-results');
        let html = '<div class="tmdb-results-grid">';
        
        results.slice(0, 10).forEach(function(item) {
            const title = item.title || item.name;
            const year = (item.release_date || item.first_air_date || '').substring(0, 4);
            const poster = item.poster_path ? 
                `https://image.tmdb.org/t/p/w185${item.poster_path}` : 
                'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="185" height="278"><rect width="100%" height="100%" fill="%23333"/><text x="50%" y="50%" text-anchor="middle" fill="%23666">No Image</text></svg>';
            
            html += `
                <div class="tmdb-result" data-id="${item.id}" data-type="${type}">
                    <img src="${poster}" alt="${title}" class="tmdb-poster">
                    <div class="tmdb-info">
                        <h4>${title}</h4>
                        <p>${year}</p>
                        <p>Rating: ${item.vote_average}/10</p>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        resultsContainer.html(html);
    }

    /**
     * Import from TMDB
     */
    function importFromTMDB(item) {
        const importBtn = $('#import-tmdb');
        
        importBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
        $.ajax({
            url: moviedb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'moviedb_import_tmdb',
                tmdb_id: item.id,
                type: item.type,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    showNotification('Successfully imported! Redirecting...', 'success');
                    setTimeout(function() {
                        window.location.href = `/wp-admin/post.php?post=${data.post_id}&action=edit`;
                    }, 2000);
                } else {
                    showNotification(data.error || 'Import failed', 'error');
                }
            },
            error: function() {
                showNotification('Error importing from TMDB', 'error');
            },
            complete: function() {
                importBtn.prop('disabled', false).html('Import Selected');
            }
        });
    }

    /**
     * Initialize watchlist functionality
     */
    function initializeWatchlist() {
        $(document).on('click', '.watchlist-button, .watchlist-action', function(e) {
            e.preventDefault();
            
            if (!moviedb_ajax.user_logged_in) {
                showNotification('Please log in to use the watchlist feature', 'warning');
                return;
            }
            
            const button = $(this);
            const postId = button.data('post-id');
            
            toggleWatchlist(postId, button);
        });
    }

    /**
     * Toggle watchlist status
     */
    function toggleWatchlist(postId, button) {
        const isAdded = button.hasClass('added');
        
        button.prop('disabled', true);
        
        $.ajax({
            url: moviedb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'moviedb_add_to_watchlist',
                post_id: postId,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.action === 'added') {
                        button.addClass('added');
                        button.find('span').text('Remove from Watchlist');
                        button.attr('title', 'Remove from Watchlist');
                        showNotification('Added to watchlist!', 'success');
                    } else {
                        button.removeClass('added');
                        button.find('span').text('Add to Watchlist');
                        button.attr('title', 'Add to Watchlist');
                        showNotification('Removed from watchlist!', 'info');
                    }
                } else {
                    showNotification('Error updating watchlist', 'error');
                }
            },
            error: function() {
                showNotification('Error updating watchlist', 'error');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    }

    /**
     * Initialize lightbox functionality
     */
    function initializeLightbox() {
        $(document).on('click', '.gallery-item img, [data-lightbox]', function(e) {
            e.preventDefault();
            const imageSrc = $(this).data('full-size') || $(this).attr('src');
            openLightbox(imageSrc);
        });

        $(document).on('click', '#lightbox-modal', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        $(document).keydown(function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
    }

    /**
     * Open lightbox
     */
    function openLightbox(imageSrc) {
        const modal = $('#lightbox-modal');
        const img = $('#lightbox-image');
        
        if (modal.length === 0) {
            $('body').append(`
                <div id="lightbox-modal" class="lightbox-modal">
                    <img id="lightbox-image" src="" alt="">
                    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
                </div>
            `);
        }
        
        $('#lightbox-modal').show();
        $('#lightbox-image').attr('src', imageSrc);
        $('body').addClass('lightbox-open');
    }

    /**
     * Close lightbox
     */
    window.closeLightbox = function() {
        $('#lightbox-modal').hide();
        $('body').removeClass('lightbox-open');
    };

    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        $('[title]').each(function() {
            $(this).on('mouseenter', function() {
                const title = $(this).attr('title');
                if (title) {
                    $(this).attr('data-original-title', title);
                    $(this).removeAttr('title');
                    
                    const tooltip = $(`<div class="tooltip">${title}</div>`);
                    $('body').append(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.css({
                        top: rect.top - tooltip.outerHeight() - 10,
                        left: rect.left + (rect.width / 2) - (tooltip.outerWidth() / 2)
                    });
                    
                    tooltip.fadeIn(200);
                }
            });
            
            $(this).on('mouseleave', function() {
                $('.tooltip').remove();
                const originalTitle = $(this).attr('data-original-title');
                if (originalTitle) {
                    $(this).attr('title', originalTitle);
                    $(this).removeAttr('data-original-title');
                }
            });
        });
    }

    /**
     * Initialize animations
     */
    function initializeAnimations() {
        // Intersection Observer for scroll animations
        if ('IntersectionObserver' in window) {
            const animationObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        animationObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            // Observe elements for animation
            $('.movie-card, .movie-details-grid, .cast-grid, .image-gallery').each(function() {
                animationObserver.observe(this);
            });
        }

        // Parallax effect for hero sections
        $(window).scroll(function() {
            const scrolled = $(this).scrollTop();
            const parallax = $('.movie-hero');
            const speed = 0.5;
            
            parallax.css('transform', `translateY(${scrolled * speed}px)`);
        });
    }

    /**
     * Initialize lazy loading
     */
    function initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.dataset.src;
                        
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                            img.classList.remove('lazy');
                            img.classList.add('loaded');
                        }
                        
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Show loading state
     */
    function showLoadingState() {
        const movieGrid = $('#movie-grid');
        movieGrid.addClass('loading');
        
        if ($('.loading-overlay').length === 0) {
            $('body').append(`
                <div class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
            `);
        }
    }

    /**
     * Hide loading state
     */
    function hideLoadingState() {
        $('#movie-grid').removeClass('loading');
        $('.loading-overlay').remove();
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notification notification-${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);
        
        $('#notifications-container').length === 0 && 
            $('body').append('<div id="notifications-container"></div>');
        
        $('#notifications-container').append(notification);
        
        notification.slideDown(300);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            notification.slideUp(300, () => notification.remove());
        }, 5000);
        
        // Manual close
        notification.find('.notification-close').on('click', () => {
            notification.slideUp(300, () => notification.remove());
        });
    }

    /**
     * Update pagination
     */
    function updatePagination(paginationData) {
        const paginationContainer = $('.pagination-container');
        if (paginationData) {
            paginationContainer.html(paginationData);
        }
    }

    /**
     * Utility functions
     */
    const Utils = {
        // Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        // Throttle function
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        // Format runtime
        formatRuntime: function(minutes) {
            if (!minutes) return 'N/A';
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
        },

        // Format rating
        formatRating: function(rating) {
            if (!rating) return 'N/A';
            return parseFloat(rating).toFixed(1);
        },

        // Get YouTube video ID from URL
        getYouTubeVideoId: function(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        },

        // Validate email
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Convert to slug
        toSlug: function(str) {
            return str
                .toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
        }
    };

    // Expose utilities globally
    window.MovieDBUtils = Utils;

    /**
     * Handle form submissions
     */
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        const formData = new FormData(this);
        
        // Add loading state
        submitBtn.prop('disabled', true);
        const originalText = submitBtn.text();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: form.attr('action') || moviedb_ajax.ajax_url,
            type: form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification(response.message || 'Success!', 'success');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    showNotification(response.message || 'Error occurred', 'error');
                }
            },
            error: function() {
                showNotification('Network error occurred', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    /**
     * Handle dynamic content loading
     */
    function loadDynamicContent(container, url, data = {}) {
        const $container = $(container);
        
        $container.addClass('loading');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                ...data,
                nonce: moviedb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data);
                    // Reinitialize components for new content
                    initializeLazyLoading();
                    initializeTooltips();
                } else {
                    $container.html('<p class="error">Error loading content</p>');
                }
            },
            error: function() {
                $container.html('<p class="error">Network error</p>');
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    }

    // Expose global functions
    window.loadDynamicContent = loadDynamicContent;
    window.showNotification = showNotification;

    /**
     * Performance optimizations
     */
    
    // Preload critical images
    function preloadImages() {
        const criticalImages = $('.movie-poster[data-preload]');
        criticalImages.each(function() {
            const img = new Image();
            img.src = $(this).attr('src');
        });
    }

    // Initialize on load
    $(window).on('load', function() {
        preloadImages();
        
        // Remove loading screens
        $('.page-loader').fadeOut(500);
        
        // Initialize lazy loading
        initializeLazyLoading();
    });

    /**
     * Error handling
     */
    window.addEventListener('error', function(e) {
        console.error('MovieDB Error:', e.error);
        // You can send errors to a logging service here
    });

    // AJAX error handling
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('AJAX Error:', error);
        if (xhr.status === 403) {
            showNotification('Access denied. Please refresh the page.', 'error');
        } else if (xhr.status === 500) {
            showNotification('Server error. Please try again later.', 'error');
        }
    });

})(jQuery);

/**
 * Additional CSS for JavaScript functionality
 */
const additionalCSS = `
<style>
/* Notifications */
#notifications-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
}

.notification {
    background: #1a1a1a;
    color: white;
    padding: 1rem;
    margin-bottom: 10px;
    border-radius: 8px;
    border-left: 4px solid #e50914;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: none;
    position: relative;
}

.notification-success {
    border-left-color: #28a745;
}

.notification-warning {
    border-left-color: #ffc107;
}

.notification-error {
    border-left-color: #dc3545;
}

.notification-info {
    border-left-color: #17a2b8;
}

.notification-close {
    position: absolute;
    top: 5px;
    right: 10px;
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

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

/* TMDB Results */
.tmdb-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.tmdb-result {
    background: #f9f9f9;
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tmdb-result:hover,
.tmdb-result.selected {
    border-color: #e50914;
    background: #fff;
}

.tmdb-poster {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.tmdb-info h4 {
    margin: 0 0 0.5rem 0;
    font-size: 0.9rem;
}

.tmdb-info p {
    margin: 0.25rem 0;
    font-size: 0.8rem;
    color: #666;
}

/* Tooltips */
.tooltip {
    position: absolute;
    background: rgba(0,0,0,0.9);
    color: white;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    z-index: 1000;
    white-space: nowrap;
    display: none;
}

/* Animations */
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

.animate-in {
    animation: fadeInUp 0.6s ease forwards;
}

/* Search suggestions */
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #1a1a1a;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    z-index: 1000;
    margin-top: 0.5rem;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #333;
    cursor: pointer;
    transition: background 0.3s ease;
}

.suggestion-item:hover {
    background: #2a2a2a;
}

.suggestion-poster {
    width: 40px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 1rem;
}

.suggestion-info h4 {
    margin: 0;
    color: white;
    font-size: 0.9rem;
}

.suggestion-year,
.suggestion-type {
    color: #888;
    font-size: 0.8rem;
    margin-right: 0.5rem;
}

/* Header hidden state */
.header-hidden {
    transform: translateY(-100%);
}

.site-header {
    transition: transform 0.3s ease;
}

/* Body states */
.lightbox-open {
    overflow: hidden;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    #notifications-container {
        left: 10px;
        right: 10px;
        max-width: none;
    }
    
    .tmdb-results-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .search-suggestions {
        max-height: 300px;
        overflow-y: auto;
    }
}
</style>
`;

// Inject additional CSS
document.head.insertAdjacentHTML('beforeend', additionalCSS);