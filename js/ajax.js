/**
 * MovieDB Pro AJAX Handlers
 */

(function($) {
    'use strict';

    // AJAX Configuration
    const AjaxHandler = {
        init: function() {
            this.setupAjaxDefaults();
            this.bindEvents();
        },

        setupAjaxDefaults: function() {
            // Set default AJAX settings
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    // Add nonce to all requests
                    if (settings.data && typeof settings.data === 'string') {
                        settings.data += '&nonce=' + moviedb_ajax.nonce;
                    } else if (settings.data && typeof settings.data === 'object') {
                        settings.data.nonce = moviedb_ajax.nonce;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error
                    });
                }
            });
        },

        bindEvents: function() {
            // Live search
            this.initLiveSearch();
            
            // Filter handling
            this.initFilterHandling();
            
            // Load more content
            this.initLoadMore();
            
            // Rating submission
            this.initRatingSystem();
            
            // Comment handling
            this.initCommentHandling();
        },

        /**
         * Initialize live search functionality
         */
        initLiveSearch: function() {
            let searchTimeout;
            const searchInput = $('.search-input');
            const searchForm = $('.search-form');
            let currentRequest = null;

            searchInput.on('input', function() {
                const query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                // Cancel previous request
                if (currentRequest) {
                    currentRequest.abort();
                }
                
                if (query.length >= 3) {
                    searchTimeout = setTimeout(() => {
                        currentRequest = AjaxHandler.performLiveSearch(query);
                    }, 300);
                } else {
                    AjaxHandler.hideSuggestions();
                }
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-form').length) {
                    AjaxHandler.hideSuggestions();
                }
            });

            // Handle suggestion clicks
            $(document).on('click', '.suggestion-item', function() {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });
        },

        /**
         * Perform live search
         */
        performLiveSearch: function(query) {
            return $.ajax({
                url: moviedb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'moviedb_live_search',
                    query: query
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        AjaxHandler.showSuggestions(response.data);
                    } else {
                        AjaxHandler.hideSuggestions();
                    }
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        console.error('Live search error:', xhr);
                    }
                }
            });
        },

        /**
         * Show search suggestions
         */
        showSuggestions: function(suggestions) {
            AjaxHandler.hideSuggestions();
            
            let suggestionsHtml = '<div class="search-suggestions">';
            
            suggestions.forEach(function(item) {
                suggestionsHtml += `
                    <div class="suggestion-item" data-url="${item.url}">
                        <img src="${item.poster}" alt="${item.title}" class="suggestion-poster" loading="lazy">
                        <div class="suggestion-info">
                            <h4>${item.title}</h4>
                            <div class="suggestion-meta">
                                <span class="suggestion-year">${item.year}</span>
                                <span class="suggestion-type">${item.type}</span>
                                ${item.rating ? `<span class="suggestion-rating">★ ${item.rating}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            suggestionsHtml += '</div>';
            
            $('.search-form').append(suggestionsHtml);
        },

        /**
         * Hide search suggestions
         */
        hideSuggestions: function() {
            $('.search-suggestions').remove();
        },

        /**
         * Initialize filter handling
         */
        initFilterHandling: function() {
            const filterForm = $('#filter-form');
            let filterTimeout;

            // Auto-submit on filter change
            filterForm.on('change', '.filter-select', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    AjaxHandler.performFilter();
                }, 500);
            });

            // Handle manual filter submission
            filterForm.on('submit', function(e) {
                e.preventDefault();
                AjaxHandler.performFilter();
            });

            // Reset filters
            $('.filter-reset').on('click', function(e) {
                e.preventDefault();
                filterForm[0].reset();
                AjaxHandler.performFilter();
            });
        },

        /**
         * Perform AJAX filtering
         */
        performFilter: function() {
            const filterForm = $('#filter-form');
            const movieGrid = $('#movie-grid');
            const loadMoreBtn = $('#load-more-btn');
            
            const formData = filterForm.serialize();
            
            // Show loading state
            movieGrid.addClass('loading');
            
            $.ajax({
                url: moviedb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'moviedb_filter_movies',
                    filters: formData
                },
                success: function(response) {
                    if (response.success) {
                        movieGrid.html(response.data.html);
                        
                        // Update pagination
                        if (response.data.pagination) {
                            $('.pagination-container').html(response.data.pagination);
                        }
                        
                        // Update load more button
                        if (response.data.has_more) {
                            loadMoreBtn.show().data({
                                'page': 1,
                                'max-pages': response.data.max_pages
                            });
                        } else {
                            loadMoreBtn.hide();
                        }
                        
                        // Update URL without page reload
                        AjaxHandler.updateURL(formData);
                        
                        // Trigger events for new content
                        $(document).trigger('moviesLoaded', [response.data]);
                        
                    } else {
                        AjaxHandler.showError('Error loading filtered results');
                    }
                },
                error: function() {
                    AjaxHandler.showError('Network error occurred');
                },
                complete: function() {
                    movieGrid.removeClass('loading');
                }
            });
        },

        /**
         * Initialize load more functionality
         */
        initLoadMore: function() {
            $(document).on('click', '#load-more-btn', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const currentPage = parseInt(button.data('page')) || 1;
                const maxPages = parseInt(button.data('max-pages')) || 1;
                const nextPage = currentPage + 1;
                
                if (nextPage > maxPages) return;
                
                AjaxHandler.loadMoreContent(nextPage, button);
            });

            // Infinite scroll (optional)
            if ($('body').hasClass('infinite-scroll')) {
                let isLoadingMore = false;
                
                $(window).scroll(function() {
                    if (isLoadingMore) return;
                    
                    const scrollTop = $(window).scrollTop();
                    const windowHeight = $(window).height();
                    const documentHeight = $(document).height();
                    
                    if (scrollTop + windowHeight >= documentHeight - 500) {
                        const loadMoreBtn = $('#load-more-btn');
                        if (loadMoreBtn.is(':visible')) {
                            isLoadingMore = true;
                            loadMoreBtn.trigger('click');
                            setTimeout(() => {
                                isLoadingMore = false;
                            }, 1000);
                        }
                    }
                });
            }
        },

        /**
         * Load more content
         */
        loadMoreContent: function(page, button) {
            const movieGrid = $('#movie-grid');
            const filterForm = $('#filter-form');
            const formData = filterForm.length ? filterForm.serialize() : '';
            
            // Update button state
            const originalText = button.text();
            button.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            
            $.ajax({
                url: moviedb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'moviedb_load_more',
                    page: page,
                    filters: formData
                },
                success: function(response) {
                    if (response.success) {
                        movieGrid.append(response.data.html);
                        button.data('page', page);
                        
                        // Hide button if no more pages
                        if (!response.data.has_more || page >= response.data.max_pages) {
                            button.hide();
                        }
                        
                        // Trigger events for new content
                        $(document).trigger('moreMoviesLoaded', [response.data]);
                        
                    } else {
                        AjaxHandler.showError('Error loading more content');
                    }
                },
                error: function() {
                    AjaxHandler.showError('Network error occurred');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Initialize rating system
         */
        initRatingSystem: function() {
            $(document).on('click', '.rating-stars .star', function() {
                if (!moviedb_ajax.user_logged_in) {
                    AjaxHandler.showError('Please log in to rate movies');
                    return;
                }
                
                const postId = $(this).closest('.rating-container').data('post-id');
                const rating = $(this).data('rating');
                
                AjaxHandler.submitRating(postId, rating);
            });

            // Hover effects for rating stars
            $(document).on('mouseenter', '.rating-stars .star', function() {
                const rating = $(this).data('rating');
                const container = $(this).closest('.rating-stars');
                
                container.find('.star').each(function(index) {
                    if (index < rating) {
                        $(this).addClass('hover');
                    } else {
                        $(this).removeClass('hover');
                    }
                });
            });

            $(document).on('mouseleave', '.rating-stars', function() {
                $(this).find('.star').removeClass('hover');
            });
        },

        /**
         * Submit rating
         */
        submitRating: function(postId, rating) {
            $.ajax({
                url: moviedb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'moviedb_submit_rating',
                    post_id: postId,
                    rating: rating
                },