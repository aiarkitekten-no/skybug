/**
 * SkyBug Modern JavaScript Framework
 * Handles dashboard interactivity, animations, and real-time updates
 */

// Global SkyBug namespace
window.SkyBug = window.SkyBug || {};

(function($) {
    'use strict';

    // Main application object
    SkyBug.App = {
        // Configuration
        config: {
            animationDuration: 300,
            chartUpdateInterval: 30000, // 30 seconds
            searchDelay: 300,
            fadeSpeed: 200
        },

        // Initialize the application
        init: function() {
            this.initComponents();
            this.bindEvents();
            if(typeof this.startAutoRefresh === 'function'){
                try { this.startAutoRefresh(); } catch(e){ console.warn('startAutoRefresh feilet', e); }
            }
            console.log('SkyBug Modern GUI initialized');
        },

        // Safe no-op auto refresh (placeholder for future live data refresh)
        startAutoRefresh: function(){ /* intentionally empty until live refresh implemented */ },

        // Initialize all components
        initComponents: function() {
            const modules = [
                ['Dashboard','Dashboard init feilet'],
                ['Programs','Programs init feilet'],
                ['Statistics','Statistics init feilet'],
                ['Search','Search init feilet'],
                ['Animations','Animations init feilet']
            ];
            modules.forEach(([key,msg])=>{
                const mod = SkyBug[key];
                if(mod && typeof mod.init === 'function'){
                    try { mod.init(); } catch(e){ console.warn(msg, e); }
                }
            });
        },

        // Bind global events
        bindEvents: function() {
            $(document).on('click', '.skybug-quick-action', this.handleQuickAction);
            $(document).on('click', '.skybug-program-action', this.handleProgramAction);
            if(this.handleResize){ $(window).on('resize', this.handleResize.bind(this)); }
        },

        // Handle quick actions
        handleQuickAction: function(e) {
            const $this = $(this);
            const action = $this.data('action');
            
            // Add loading state
            $this.addClass('loading');
            
            // Simulate action (replace with actual AJAX calls)
            setTimeout(() => {
                $this.removeClass('loading');
                SkyBug.Notifications.show('Action completed successfully', 'success');
            }, 1000);
        },

        // Handle program actions
        handleProgramAction: function(e) {
            e.preventDefault();
            const $this = $(this);
            const action = $this.data('action');
            const programId = $this.closest('.skybug-program-card').data('program-id');
            
            // Add visual feedback
            $this.addClass('loading');
            
            // Handle different actions
            switch(action) {
                case 'view':
                    window.location.href = $this.attr('href');
                    break;
                case 'edit':
                    if (SkyBug.Programs && typeof SkyBug.Programs.openEditModal === 'function') {
                        SkyBug.Programs.openEditModal(programId);
                    }
                    break;
                default:
                    console.log(`Action: ${action} for program: ${programId}`);
            }
            $this.removeClass('loading');
        },

        // Initialize status chart
        initStatusChart: function() {
            const ctx = document.getElementById('skyBugStatusChart');
            if (!ctx) return;

            this.charts.status = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Open', 'In Progress', 'Resolved'],
                    datasets: [{
                        data: [25, 15, 60],
                        backgroundColor: [
                            'rgb(239, 68, 68)',
                            'rgb(245, 158, 11)',
                            'rgb(16, 185, 129)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        // Initialize activity feed
        initActivityFeed: function() {
            this.loadRecentActivity();
            this.startActivityRefresh();
        },

        // Load recent activity
        loadRecentActivity: function() {
            // Simulate loading activity data
            const $activityList = $('.skybug-activity-list');
            if ($activityList.length === 0) return;

            // Add loading state
            $activityList.html('<div class="skybug-activity-loading">Loading recent activity...</div>');

            // Simulate API call
            setTimeout(() => {
                const activities = this.generateSampleActivity();
                this.renderActivityItems(activities);
            }, 1000);
        },

        // Generate sample activity data
        generateSampleActivity: function() {
            return [
                {
                    type: 'bug',
                    message: 'Bug #1234 was resolved in SkyAdmin',
                    time: '2 minutes ago',
                    user: 'Developer'
                },
                {
                    type: 'feature',
                    message: 'New feature request submitted for SkyHosting',
                    time: '15 minutes ago',
                    user: 'Client'
                },
                {
                    type: 'resolved',
                    message: 'Feature #5678 was completed and deployed',
                    time: '1 hour ago',
                    user: 'Team Lead'
                }
            ];
        },

        // Render activity items
        renderActivityItems: function(activities) {
            const $activityList = $('.skybug-activity-list');
            let html = '';

            activities.forEach(activity => {
                html += `
                    <div class="skybug-activity-item">
                        <div class="skybug-activity-icon ${activity.type}">
                            ${this.getActivityIcon(activity.type)}
                        </div>
                        <div class="skybug-activity-content">
                            <p class="skybug-activity-message">${activity.message}</p>
                            <div class="skybug-activity-meta">
                                <span>${activity.time}</span>
                                <span>•</span>
                                <span>${activity.user}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            $activityList.html(html);

            // Animate items in
            $('.skybug-activity-item').each(function(index) {
                const $item = $(this);
                setTimeout(() => {
                    $item.addClass('animate-in');
                }, index * 100);
            });
        },

        // Get activity icon
        getActivityIcon: function(type) {
            const icons = {
                bug: '🐛',
                feature: '✨',
                resolved: '✅'
            };
            return icons[type] || '•';
        },

        // Start activity refresh
        startActivityRefresh: function() {
            setInterval(() => {
                this.loadRecentActivity();
            }, 60000); // Refresh every minute
        },

        // Refresh metrics
        refreshMetrics: function() {
            $('.skybug-metric-value').each(function() {
                const $this = $(this);
                const currentValue = parseInt($this.text()) || 0;
                const newValue = currentValue + Math.floor(Math.random() * 3) - 1; // Random change
                
                if (newValue !== currentValue && newValue >= 0) {
                    $this.addClass('updating');
                    setTimeout(() => {
                        $this.text(newValue);
                        $this.removeClass('updating');
                    }, 200);
                }
            });
        }
    };

    // Programs functionality
    SkyBug.Programs = {
        init: function() {
            this.initSearch();
            this.initFilters();
            this.initCards();
            console.log('Programs initialized');
        },

        // Initialize search functionality
        initSearch: function() {
            const $searchInput = $('.skybug-search-input');
            let searchTimer;

            $searchInput.on('input', function() {
                clearTimeout(searchTimer);
                const query = $(this).val().toLowerCase();
                
                searchTimer = setTimeout(() => {
                    SkyBug.Programs.filterPrograms(query);
                }, SkyBug.App.config.searchDelay);
            });
        },

        // Initialize filters
        initFilters: function() {
            $('.skybug-filter-select').on('change', function() {
                const filter = $(this).val();
                SkyBug.Programs.applyFilter(filter);
            });
        },

        // Initialize program cards
        initCards: function() {
            $('.skybug-program-card').each(function(index) {
                const $card = $(this);
                
                // Animate cards on load
                setTimeout(() => {
                    $card.addClass('animate-in');
                }, index * 100);

                // Add interactive effects
                $card.on('mouseenter', function() {
                    $(this).addClass('hover-lift');
                }).on('mouseleave', function() {
                    $(this).removeClass('hover-lift');
                });
            });
        },

        // Filter programs by search query
        filterPrograms: function(query) {
            $('.skybug-program-card').each(function() {
                const $card = $(this);
                const title = $card.find('.skybug-program-title').text().toLowerCase();
                const description = $card.find('.skybug-program-description').text().toLowerCase();
                
                if (query === '' || title.includes(query) || description.includes(query)) {
                    $card.removeClass('hidden').addClass('visible');
                } else {
                    $card.removeClass('visible').addClass('hidden');
                }
            });

            this.updateResultsCount();
        },

        // Apply filter
        applyFilter: function(filter) {
            $('.skybug-program-card').each(function() {
                const $card = $(this);
                const health = $card.find('.skybug-health-text').text().toLowerCase();
                
                if (filter === 'all' || health === filter) {
                    $card.removeClass('filtered-out').addClass('filtered-in');
                } else {
                    $card.removeClass('filtered-in').addClass('filtered-out');
                }
            });

            this.updateResultsCount();
        },

        // Update results count
        updateResultsCount: function() {
            const visible = $('.skybug-program-card.visible:not(.filtered-out), .skybug-program-card:not(.hidden):not(.filtered-out)').length;
            const total = $('.skybug-program-card').length;
            
            const $counter = $('.skybug-results-counter');
            if ($counter.length === 0) {
                $('.skybug-programs-subtitle').after(`<div class="skybug-results-counter">Showing ${visible} of ${total} programs</div>`);
            } else {
                $counter.text(`Showing ${visible} of ${total} programs`);
            }
        },

        // Open edit modal
        openEditModal: function(programId) {
            // Create and show modal (simplified version)
            const modal = `
                <div class="skybug-modal-overlay">
                    <div class="skybug-modal">
                        <div class="skybug-modal-header">
                            <h3>Edit Program #${programId}</h3>
                            <button class="skybug-modal-close">&times;</button>
                        </div>
                        <div class="skybug-modal-body">
                            <p>Edit program functionality would go here...</p>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modal);
            
            // Bind close events
            $('.skybug-modal-overlay, .skybug-modal-close').on('click', function() {
                $('.skybug-modal-overlay').fadeOut(SkyBug.App.config.fadeSpeed, function() {
                    $(this).remove();
                });
            });
        }
    };

    // Statistics functionality
    SkyBug.Statistics = {
        charts: {},

        init: function() {
            this.initCharts();
            this.initControls();
            console.log('Statistics initialized');
        },

        // Initialize all charts
        initCharts: function() {
            this.initMainChart();
            this.initDistributionChart();
            this.initTrendChart();
        },

        // Initialize main statistics chart
        initMainChart: function() {
            const ctx = document.getElementById('skyBugMainChart');
            if (!ctx) return;

            // Fetch real data from server
            this.loadChartData('main', (data) => {
                this.charts.main = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        },

        // Initialize distribution chart
        initDistributionChart: function() {
            const ctx = document.getElementById('skyBugDistributionChart');
            if (!ctx) return;

            this.loadChartData('distribution', (data) => {
                this.charts.distribution = new Chart(ctx, {
                    type: 'pie',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            });
        },

        // Initialize trend chart
        initTrendChart: function() {
            const ctx = document.getElementById('skyBugTrendDetailChart');
            if (!ctx) return;

            this.loadChartData('trend', (data) => {
                this.charts.trend = new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        },

        // Load chart data from server
        loadChartData: function(chartType, callback, period) {
            if (typeof ajaxurl === 'undefined') {
                console.error('ajaxurl not defined');
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'skybug_get_statistics_data',
                    chart_type: chartType,
                    period: period || '30d'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        callback(response.data);
                    } else {
                        console.error('Failed to load chart data:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error loading chart data:', error);
                }
            });
        },

        // Initialize chart controls
        initControls: function() {
            $('.skybug-period-option').on('click', function() {
                const $this = $(this);
                const period = $this.data('period');
                
                $this.addClass('active').siblings().removeClass('active');
                SkyBug.Statistics.updateChartPeriod(period);
            });

            $('.skybug-chart-toggle').on('click', function() {
                const $this = $(this);
                const chartType = $this.data('chart-type');
                
                $this.addClass('active').siblings().removeClass('active');
                SkyBug.Statistics.toggleChartType(chartType);
            });
        },

        // Update chart period
        updateChartPeriod: function(period) {
            console.log(`Updating charts for period: ${period}`);
            // Update trend chart with new period
            if (this.charts.trend) {
                this.loadChartData('trend', (data) => {
                    this.charts.trend.data = data;
                    this.charts.trend.update();
                }, period);
            }
        },

        // Toggle chart type
        toggleChartType: function(type) {
            console.log(`Switching to chart type: ${type}`);
            // Implementation would change chart visualization
        },

        // Resize charts for responsive design
        resizeCharts: function() {
            Object.values(this.charts).forEach(chart => {
                if (chart && typeof chart.resize === 'function') {
                    chart.resize();
                }
            });
        },

        // Refresh charts with new data
        refreshCharts: function() {
            console.log('Refreshing statistics charts...');
            // Implementation would fetch new data and update charts
        }
    };

    // Search functionality
    SkyBug.Search = {
        init: function() {
            this.bindSearchEvents();
            console.log('Search initialized');
        },

        bindSearchEvents: function() {
            // Global search functionality
            $('.skybug-global-search').on('input', this.handleGlobalSearch);
        },

        handleGlobalSearch: function() {
            const query = $(this).val().toLowerCase();
            // Implementation for global search across all content
            console.log(`Global search query: ${query}`);
        }
    };

    // Animation system
    SkyBug.Animations = {
        init: function() {
            this.setupAnimations();
            this.bindAnimationEvents();
            console.log('Animations initialized');
        },

        setupAnimations: function() {
            // Add CSS classes for animations
            $('<style>').prop('type', 'text/css').html(`
                .animate-in {
                    opacity: 1;
                    transform: translateY(0);
                    transition: all 0.3s ease-out;
                }
                .animate-out {
                    opacity: 0;
                    transform: translateY(20px);
                }
                .hover-lift {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                }
                .loading {
                    opacity: 0.6;
                    pointer-events: none;
                }
                .updating {
                    color: var(--primary);
                    transform: scale(1.05);
                }
            `).appendTo('head');
        },

        bindAnimationEvents: function() {
            // Intersection Observer for scroll animations
            if ('IntersectionObserver' in window) {
                this.observeElements();
            }
        },

        observeElements: function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        $(entry.target).addClass('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            $('.skybug-card, .skybug-metric-card, .skybug-program-card').each(function() {
                observer.observe(this);
            });
        }
    };

    // Notification system
    SkyBug.Notifications = {
        show: function(message, type = 'info') {
            const notification = `
                <div class="skybug-notification skybug-notification-${type}">
                    <span>${message}</span>
                    <button class="skybug-notification-close">&times;</button>
                </div>
            `;
            
            const $notification = $(notification);
            $('body').append($notification);
            
            setTimeout(() => {
                $notification.addClass('show');
            }, 10);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                this.remove($notification);
            }, 5000);
            
            // Manual close
            $notification.find('.skybug-notification-close').on('click', () => {
                this.remove($notification);
            });
        },

        remove: function($notification) {
            $notification.removeClass('show');
            setTimeout(() => {
                $notification.remove();
            }, 300);
        }
    };

    // Settings functionality
    SkyBug.Settings = {
        init: function() {
            this.bindTestButtons();
            if (typeof wpAjax === 'undefined') {
                console.warn('SkyBug: wpAjax object mangler (nonces).');
            } else {
                console.log('SkyBug: Settings init med nonces', wpAjax);
            }
        },

        bindTestButtons: function() {
            // SMTP Test button
            $('#test-smtp').on('click', function(e) {
                e.preventDefault();
                SkyBug.Settings.testConnection('smtp', $(this));
            });

            // IMAP Test button
            $('#test-imap').on('click', function(e) {
                e.preventDefault();
                SkyBug.Settings.testConnection('imap', $(this));
            });
        },

        testConnection: function(type, $button) {
            const originalText = $button.text();
            $button.text(skyBugConfig.strings.loading || 'Testing...').addClass('loading');

            let nonce = '';
            if (typeof wpAjax !== 'undefined') {
                nonce = type === 'smtp' ? wpAjax.smtp : wpAjax.imap;
            }
            if (!nonce && $button.data('nonce')) {
                nonce = $button.data('nonce');
            }
            const ajaxData = {
                action: `skybug_test_${type}`,
                nonce: nonce
            };
                if(!ajaxData.nonce){
                    if ($result.length) {
                        $result.text('Mangler sikkerhetsnonce – last siden på nytt.').addClass('error').show();
                    }
                    $button.text(originalText).removeClass('loading');
                    return;
                }

            const $result = type === 'smtp' ? $('#smtp-test-result') : $('#imap-test-result');
            if ($result.length) {
                $result.removeClass('success error').text('Tester forbindelse...').show();
            }

            $.ajax({
                url: skyBugConfig.ajaxUrl,
                method: 'POST',
                data: ajaxData,
                dataType: 'json',
                timeout: 15000
            })
            .done(function(response) {
                const cls = response.success ? 'success' : 'error';
                if ($result.length) {
                    let html = `<div>${response.message || ''}</div>`;
                    if (type === 'imap' && response.attempts) {
                        html += '<details style="margin-top:8px"><summary>Forsøk detaljer</summary>';
                        html += '<table style="width:100%;margin-top:6px;font-size:12px;border-collapse:collapse">';
                        html += '<thead><tr><th style="text-align:left;padding:2px 4px">Label</th><th style="text-align:left;padding:2px 4px">Mappe</th><th style="text-align:left;padding:2px 4px">Flags</th><th style="text-align:left;padding:2px 4px">OK</th><th style="text-align:left;padding:2px 4px">Meldinger</th><th style="text-align:left;padding:2px 4px">Feil</th></tr></thead><tbody>';
                        response.attempts.forEach(a => {
                            html += `<tr>`+
                                `<td style="padding:2px 4px">${a.label||''}</td>`+
                                `<td style="padding:2px 4px">${a.folder||''}</td>`+
                                `<td style="padding:2px 4px">${a.flags||''}</td>`+
                                `<td style="padding:2px 4px">${a.success? '✅':'❌'}</td>`+
                                `<td style="padding:2px 4px">${typeof a.messages !== 'undefined' ? a.messages : ''}</td>`+
                                `<td style="padding:2px 4px;color:#b00">${a.error||''}</td>`+
                                `</tr>`;
                        });
                        html += '</tbody></table></details>';
                    }
                    $result.html(html).removeClass('success error').addClass(cls).show();
                } else {
                    SkyBug.Notifications.show(response.message, cls === 'success' ? 'success' : 'error');
                }
            })
            .fail(function(xhr) {
                let message = 'Connection test failed';
                try {
                    const response = JSON.parse(xhr.responseText);
                    message = response.message || message;
                } catch (e) {
                    message = `Error: ${xhr.status} ${xhr.statusText}`;
                }
                if ($result.length) {
                    $result.text(message).removeClass('success').addClass('error').show();
                } else {
                    SkyBug.Notifications.show(message, 'error');
                }
            })
            .always(function() {
                $button.text(originalText).removeClass('loading');
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        SkyBug.App.init();
    });

})(jQuery);