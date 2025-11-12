<?php
/**
 * Plugin Name: Pixa AI Assistant
 * Plugin URI: https://javapixa.com
 * Description: AI-powered writing assistant using Google Gemini to generate content and optimize articles for SEO
 * Version: 2.4.0
 * Author: Javapixa Creative Studio
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PIXA_AI_VERSION', '2.4.0');
define('PIXA_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PIXA_AI_PLUGIN_URL', plugin_dir_url(__FILE__));

class Pixa_AI_Assistant {

    private $option_name = 'pixa_ai_api_key';
    private $model_option_name = 'pixa_ai_model';
    private $max_content_length = 50000; // 50KB limit
    private $rate_limit_seconds = 10; // Rate limit cooldown

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_gwa_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_gwa_analyze_content', array($this, 'ajax_analyze_content'));
        add_action('wp_ajax_gwa_optimize_seo', array($this, 'ajax_optimize_seo'));
    }

    public function add_settings_page() {
        add_options_page(
            'Pixa AI Assistant Settings',
            'Pixa AI',
            'manage_options',
            'gemini-writing-assistant',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('gwa_settings', $this->option_name, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('gwa_settings', $this->model_option_name, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'gemini-2.5-flash'
        ));
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('gwa_messages', 'gwa_message', 'Settings Saved Successfully!', 'updated');
        }

        // Get usage statistics
        $users = get_users(array('fields' => 'ID'));
        $total_generate = 0;
        $total_analyze = 0;
        $total_optimize = 0;
        $user_count = 0;

        foreach ($users as $user_id) {
            $usage = get_user_meta($user_id, 'pixa_ai_usage_' . $user_id, true);
            if (is_array($usage)) {
                $total_generate += isset($usage['generate']) ? $usage['generate'] : 0;
                $total_analyze += isset($usage['analyze']) ? $usage['analyze'] : 0;
                $total_optimize += isset($usage['optimize']) ? $usage['optimize'] : 0;
                if (!empty($usage['generate']) || !empty($usage['analyze']) || !empty($usage['optimize'])) {
                    $user_count++;
                }
            }
        }

        $total_requests = $total_generate + $total_analyze + $total_optimize;

        settings_errors('gwa_messages');
        ?>
        <div class="pixa-ai-settings-wrap">
            <!-- Header -->
            <div class="pixa-ai-header">
                <div class="pixa-ai-header-content">
                    <div class="pixa-ai-logo-section">
                        <img src="https://www.javapixa.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2Flogo_symbol.d3d80f6b.png&w=256&q=75" alt="Pixa AI" class="pixa-ai-logo">
                        <div>
                            <h1>Pixa AI Assistant</h1>
                            <p>AI-Powered Content Generation & Optimization</p>
                        </div>
                    </div>
                    <div class="pixa-ai-version">
                        <span class="version-badge">v<?php echo PIXA_AI_VERSION; ?></span>
                    </div>
                </div>
            </div>

            <div class="pixa-ai-container">
                <!-- Analytics Dashboard -->
                <div class="pixa-ai-section pixa-ai-analytics">
                    <h2>📊 Usage Analytics</h2>
                    <div class="pixa-ai-stats-grid">
                        <div class="pixa-ai-stat-card pixa-ai-stat-primary">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc143c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($total_requests); ?></div>
                                <div class="stat-label">Total Requests</div>
                            </div>
                        </div>
                        <div class="pixa-ai-stat-card pixa-ai-stat-success">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($total_generate); ?></div>
                                <div class="stat-label">Content Generated</div>
                            </div>
                        </div>
                        <div class="pixa-ai-stat-card pixa-ai-stat-info">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3d81f5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($total_analyze); ?></div>
                                <div class="stat-label">Articles Analyzed</div>
                            </div>
                        </div>
                        <div class="pixa-ai-stat-card pixa-ai-stat-warning">
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <circle cx="12" cy="12" r="6"/>
                                    <circle cx="12" cy="12" r="2"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($total_optimize); ?></div>
                                <div class="stat-label">SEO Optimized</div>
                            </div>
                        </div>
                    </div>

                    <?php if ($total_requests > 0):
                        // Calculate daily usage for the last 7 days
                        $daily_usage = array();
                        for ($i = 6; $i >= 0; $i--) {
                            $date = date('Y-m-d', strtotime("-$i days"));
                            $daily_usage[$date] = 0;
                        }

                        // Get all usage logs
                        foreach ($users as $user_id) {
                            $usage_log = get_user_meta($user_id, 'pixa_ai_usage_log_' . $user_id, true);
                            if (is_array($usage_log)) {
                                foreach ($usage_log as $log_entry) {
                                    if (isset($log_entry['date'])) {
                                        $log_date = date('Y-m-d', strtotime($log_entry['date']));
                                        if (isset($daily_usage[$log_date])) {
                                            $daily_usage[$log_date]++;
                                        }
                                    }
                                }
                            }
                        }

                        $usage_dates = array_keys($daily_usage);
                        $usage_counts = array_values($daily_usage);
                    ?>
                    <div class="pixa-ai-charts-grid">
                        <div class="pixa-ai-chart-container">
                            <h3>Request Distribution</h3>
                            <canvas id="pixaUsageChart" width="400" height="250"></canvas>
                        </div>
                        <div class="pixa-ai-chart-container">
                            <h3>Daily Usage Trend (Last 7 Days)</h3>
                            <canvas id="pixaDailyChart" width="400" height="250"></canvas>
                        </div>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Doughnut Chart - Request Distribution
                        const ctx1 = document.getElementById('pixaUsageChart');
                        if (ctx1) {
                            new Chart(ctx1, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Content Generation', 'Article Analysis', 'SEO Optimization'],
                                    datasets: [{
                                        data: [<?php echo $total_generate; ?>, <?php echo $total_analyze; ?>, <?php echo $total_optimize; ?>],
                                        backgroundColor: ['#dc143c', '#3d81f5', '#ffc107'],
                                        borderWidth: 0
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }
                            });
                        }

                        // Line Chart - Daily Usage Trend
                        const ctx2 = document.getElementById('pixaDailyChart');
                        if (ctx2) {
                            new Chart(ctx2, {
                                type: 'line',
                                data: {
                                    labels: [<?php echo '"' . implode('","', array_map(function($date) { return date('M d', strtotime($date)); }, $usage_dates)) . '"'; ?>],
                                    datasets: [{
                                        label: 'Requests',
                                        data: [<?php echo implode(',', $usage_counts); ?>],
                                        borderColor: '#dc143c',
                                        backgroundColor: 'rgba(220, 20, 60, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        borderWidth: 2,
                                        pointRadius: 4,
                                        pointBackgroundColor: '#dc143c',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointHoverRadius: 6
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 1
                                            },
                                            grid: {
                                                color: 'rgba(0, 0, 0, 0.05)'
                                            }
                                        },
                                        x: {
                                            grid: {
                                                display: false
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                    </script>
                    <?php endif; ?>

                    <div class="pixa-ai-info-box">
                        <p><strong>Active Users:</strong> <?php echo $user_count; ?> user(s) have used the AI assistant</p>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="pixa-ai-section pixa-ai-settings-form">
                    <h2>Configuration</h2>
                    <form action="options.php" method="post" class="pixa-ai-form">
                        <?php settings_fields('gwa_settings'); ?>

                        <div class="pixa-ai-form-group">
                            <label for="<?php echo $this->option_name; ?>">
                                <span class="label-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc143c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </span>
                                Gemini API Key
                            </label>
                            <input type="text"
                                   id="<?php echo $this->option_name; ?>"
                                   name="<?php echo $this->option_name; ?>"
                                   value="<?php echo esc_attr(get_option($this->option_name)); ?>"
                                   class="pixa-ai-input"
                                   placeholder="Enter your Gemini API key">
                            <p class="pixa-ai-help">
                                Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio →</a>
                            </p>
                        </div>

                        <div class="pixa-ai-form-group">
                            <label for="<?php echo $this->model_option_name; ?>">
                                <span class="label-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3d81f5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                    </svg>
                                </span>
                                AI Model
                            </label>
                            <select id="<?php echo $this->model_option_name; ?>"
                                    name="<?php echo $this->model_option_name; ?>"
                                    class="pixa-ai-select">
                                <?php
                                $models = array(
                                    'gemini-2.5-pro' => 'Gemini 2.5 Pro - Highest Quality',
                                    'gemini-2.5-flash' => 'Gemini 2.5 Flash - Recommended ⭐',
                                    'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite - Fastest',
                                    'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                                    'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite',
                                    'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                                    'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                                    'gemini-1.0-pro' => 'Gemini 1.0 Pro',
                                    'gemini-1.0-flash' => 'Gemini 1.0 Flash'
                                );
                                $selected_model = get_option($this->model_option_name, 'gemini-2.5-flash');
                                foreach ($models as $value => $label) {
                                    $selected = ($selected_model === $value) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="pixa-ai-help">
                                Choose the AI model for content generation. Flash models offer best speed/quality balance.
                            </p>
                        </div>

                        <div class="pixa-ai-form-actions">
                            <button type="submit" class="pixa-ai-btn pixa-ai-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                    <polyline points="17 21 17 13 7 13 7 21"/>
                                    <polyline points="7 3 7 8 15 8"/>
                                </svg>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick Guide -->
                <div class="pixa-ai-section pixa-ai-guide">
                    <h2>🚀 Quick Start Guide</h2>
                    <div class="pixa-ai-steps">
                        <div class="pixa-ai-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Get API Key</h4>
                                <p>Sign up at Google AI Studio and generate your API key</p>
                            </div>
                        </div>
                        <div class="pixa-ai-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Configure Settings</h4>
                                <p>Add your API key and select preferred AI model</p>
                            </div>
                        </div>
                        <div class="pixa-ai-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Start Creating</h4>
                                <p>Click the floating button in post editor to generate content</p>
                            </div>
                        </div>
                    </div>

                    <div class="pixa-ai-features-grid">
                        <div class="pixa-ai-feature">
                            <span class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc143c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </span>
                            <h4>Generate Content</h4>
                            <p>Create blog posts in Indonesian or English with various tones</p>
                        </div>
                        <div class="pixa-ai-feature">
                            <span class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3d81f5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>
                                    <path d="M22 12A10 10 0 0 0 12 2v10z"/>
                                </svg>
                            </span>
                            <h4>Analyze Articles</h4>
                            <p>Get comprehensive analysis and improvement suggestions</p>
                        </div>
                        <div class="pixa-ai-feature">
                            <span class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                </svg>
                            </span>
                            <h4>SEO Optimization</h4>
                            <p>Optimize content for better search engine rankings</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="pixa-ai-footer">
                <p>Made with ❤️ by <a href="https://javapixa.com" target="_blank">Javapixa Creative Studio</a></p>
            </div>
        </div>
        <?php
    }

    public function enqueue_admin_assets($hook) {
        // Enqueue for post editor
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_style('pixa-ai-admin-style', PIXA_AI_PLUGIN_URL . 'assets/css/admin-style.css', array(), PIXA_AI_VERSION);
            wp_enqueue_script('pixa-ai-admin-script', PIXA_AI_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), PIXA_AI_VERSION, true);
        }

        // Enqueue for settings page
        if ($hook === 'settings_page_gemini-writing-assistant') {
            wp_enqueue_style('pixa-ai-settings-style', PIXA_AI_PLUGIN_URL . 'assets/css/settings-style.css', array(), PIXA_AI_VERSION);
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
        }

        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        wp_enqueue_style('pixa-ai-admin-style', PIXA_AI_PLUGIN_URL . 'assets/css/admin-style.css', array(), PIXA_AI_VERSION);
        wp_enqueue_script('pixa-ai-admin-script', PIXA_AI_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), PIXA_AI_VERSION, true);

        wp_localize_script('pixa-ai-admin-script', 'pixaAiData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pixa_ai_nonce'),
            'hasApiKey' => !empty(get_option($this->option_name)),
            'maxContentLength' => $this->max_content_length,
            'strings' => array(
                'error_api_key' => __('API key not configured. Please add your Gemini API key in Settings > Pixa AI', 'pixa-ai'),
                'error_no_content' => __('No content found in the editor. Please write something first.', 'pixa-ai'),
                'error_prompt_required' => __('Please enter a description of what you want to write about.', 'pixa-ai'),
                'error_content_too_long' => __('Content is too long. Please reduce the article length.', 'pixa-ai'),
            )
        ));
    }

    public function ajax_generate_content() {
        check_ajax_referer('pixa_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'pixa-ai')));
            return;
        }

        // Rate limiting
        if (!$this->check_rate_limit()) {
            wp_send_json_error(array('message' => __('Please wait before making another request', 'pixa-ai')));
            return;
        }

        $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';
        $tone = isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : 'professional';
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : 'indonesian';

        // Validate prompt
        if (empty($prompt)) {
            wp_send_json_error(array('message' => __('Prompt is required', 'pixa-ai')));
            return;
        }

        if (strlen($prompt) > 5000) {
            wp_send_json_error(array('message' => __('Prompt is too long. Please keep it under 5000 characters.', 'pixa-ai')));
            return;
        }

        $api_key = get_option($this->option_name);

        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API key not configured. Please add your Gemini API key in Settings > Pixa AI', 'pixa-ai')));
            return;
        }

        $content = $this->generate_content_with_gemini($api_key, $prompt, $tone, $language);

        if (is_wp_error($content)) {
            $this->log_error('Generate Content Error', $content->get_error_message());
            wp_send_json_error(array('message' => $content->get_error_message()));
            return;
        }

        // Track usage
        $this->track_api_usage('generate');

        wp_send_json_success(array('content' => $content));
    }

    public function ajax_analyze_content() {
        check_ajax_referer('pixa_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'pixa-ai')));
            return;
        }

        if (!$this->check_rate_limit()) {
            wp_send_json_error(array('message' => __('Please wait before making another request', 'pixa-ai')));
            return;
        }

        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(array('message' => __('Content is required', 'pixa-ai')));
            return;
        }

        $validation = $this->validate_content_length($content);
        if (is_wp_error($validation)) {
            wp_send_json_error(array('message' => $validation->get_error_message()));
            return;
        }

        $api_key = get_option($this->option_name);

        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API key not configured', 'pixa-ai')));
            return;
        }

        $analysis = $this->analyze_content_with_gemini($api_key, $content);

        if (is_wp_error($analysis)) {
            $this->log_error('Analyze Content Error', $analysis->get_error_message());
            wp_send_json_error(array('message' => $analysis->get_error_message()));
            return;
        }

        $this->track_api_usage('analyze');

        wp_send_json_success(array('analysis' => $analysis));
    }

    public function ajax_optimize_seo() {
        check_ajax_referer('pixa_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'pixa-ai')));
            return;
        }

        if (!$this->check_rate_limit()) {
            wp_send_json_error(array('message' => __('Please wait before making another request', 'pixa-ai')));
            return;
        }

        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(array('message' => __('Content is required', 'pixa-ai')));
            return;
        }

        $validation = $this->validate_content_length($content);
        if (is_wp_error($validation)) {
            wp_send_json_error(array('message' => $validation->get_error_message()));
            return;
        }

        $api_key = get_option($this->option_name);

        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API key not configured', 'pixa-ai')));
            return;
        }

        $optimized_content = $this->optimize_content_for_seo($api_key, $content);

        if (is_wp_error($optimized_content)) {
            $this->log_error('Optimize SEO Error', $optimized_content->get_error_message());
            wp_send_json_error(array('message' => $optimized_content->get_error_message()));
            return;
        }

        $this->track_api_usage('optimize');

        wp_send_json_success(array('content' => $optimized_content));
    }

    private function generate_content_with_gemini($api_key, $prompt, $tone = 'professional', $language = 'indonesian') {
        $model = get_option($this->model_option_name, 'gemini-2.5-flash');
        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent';

        // Language instruction
        $language_map = array(
            'indonesian' => 'Write in Indonesian (Bahasa Indonesia). ',
            'english' => 'Write in English. '
        );
        $language_instruction = isset($language_map[$language]) ? $language_map[$language] : $language_map['indonesian'];

        $tone_instruction = "Write in a {$tone} tone. ";

        $body = json_encode(array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $language_instruction . $tone_instruction . 'Write a blog post about: ' . $prompt . '\n\nIMPORTANT: Format the output in HTML with proper tags like <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em> etc. Do not use markdown. Return only the HTML content without any code blocks or backticks.')
                    )
                )
            )
        ));

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $api_key
            ),
            'body' => $body,
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new WP_Error('api_error', 'Gemini API error: ' . $response_body);
        }

        $data = json_decode($response_body, true);

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        return new WP_Error('parse_error', 'Unable to parse API response');
    }

    private function analyze_content_with_gemini($api_key, $content) {
        $model = get_option($this->model_option_name, 'gemini-2.5-flash');
        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent';

        $prompt = "Analyze the following article and provide a comprehensive analysis including:\n\n1. Overall Quality Assessment: Rate the article's overall quality and effectiveness\n2. Strengths: What the article does well\n3. Areas for Improvement: Specific recommendations to enhance the content\n4. Missing Elements: Important topics or information that should be added\n5. SEO & Readability: Analysis of SEO optimization and readability\n6. Structure & Organization: Feedback on content flow and organization\n7. Target Audience: Whether it effectively reaches its intended audience\n\nIMPORTANT: Format the output in HTML with proper tags like <h3>, <h4>, <p>, <ul>, <li>, <strong>, <em> etc. Do not use markdown. Return only the HTML content without any code blocks or backticks.\n\nArticle to analyze:\n\n" . $content;

        $body = json_encode(array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            )
        ));

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $api_key
            ),
            'body' => $body,
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new WP_Error('api_error', 'Gemini API error: ' . $response_body);
        }

        $data = json_decode($response_body, true);

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        return new WP_Error('parse_error', 'Unable to parse API response');
    }

    private function optimize_content_for_seo($api_key, $content) {
        $model = get_option($this->model_option_name, 'gemini-2.5-flash');
        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent';

        $prompt = "Optimize the following article for SEO. Improve readability, add relevant keywords naturally, enhance structure with proper headings, and make it more engaging while maintaining the core message.\n\nIMPORTANT: Format the output in HTML with proper tags like <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em> etc. Do not use markdown. Return only the HTML content without any code blocks or backticks.\n\nArticle to optimize:\n\n" . $content;

        $body = json_encode(array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            )
        ));

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $api_key
            ),
            'body' => $body,
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new WP_Error('api_error', 'Gemini API error: ' . $response_body);
        }

        $data = json_decode($response_body, true);

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        return new WP_Error('parse_error', 'Unable to parse API response');
    }

    /**
     * Check rate limit for current user
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        $rate_key = 'pixa_ai_rate_limit_' . $user_id;

        if (get_transient($rate_key)) {
            return false;
        }

        set_transient($rate_key, true, $this->rate_limit_seconds);
        return true;
    }

    /**
     * Track API usage
     */
    private function track_api_usage($type) {
        $user_id = get_current_user_id();
        $usage_key = 'pixa_ai_usage_' . $user_id;
        $usage = get_user_meta($user_id, $usage_key, true);

        if (!is_array($usage)) {
            $usage = array(
                'generate' => 0,
                'analyze' => 0,
                'optimize' => 0
            );
        }

        if (isset($usage[$type])) {
            $usage[$type]++;
        }

        update_user_meta($user_id, $usage_key, $usage);

        // Also store usage log with timestamp for charts
        $log_key = 'pixa_ai_usage_log_' . $user_id;
        $usage_log = get_user_meta($user_id, $log_key, true);

        if (!is_array($usage_log)) {
            $usage_log = array();
        }

        $usage_log[] = array(
            'type' => $type,
            'date' => current_time('mysql'),
            'timestamp' => time()
        );

        // Keep only last 30 days of logs
        $thirty_days_ago = strtotime('-30 days');
        $usage_log = array_filter($usage_log, function($log) use ($thirty_days_ago) {
            return isset($log['timestamp']) && $log['timestamp'] >= $thirty_days_ago;
        });

        update_user_meta($user_id, $log_key, $usage_log);
    }

    /**
     * Log errors for debugging
     */
    private function log_error($title, $message) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log(sprintf('[Pixa AI] %s: %s', $title, $message));
        }
    }

    /**
     * Validate content length
     */
    private function validate_content_length($content) {
        if (strlen($content) > $this->max_content_length) {
            return new WP_Error('content_too_long', __('Content is too long. Please reduce the article length.', 'pixa-ai'));
        }
        return true;
    }
}

new Pixa_AI_Assistant();
