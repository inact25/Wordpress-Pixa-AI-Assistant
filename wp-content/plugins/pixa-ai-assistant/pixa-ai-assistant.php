<?php
/**
 * Plugin Name: Pixa AI Assistant
 * Plugin URI: https://javapixa.com
 * Description: AI-powered writing assistant using Google Gemini to generate content and optimize articles for SEO
 * Version: 1.0.0
 * Author: Javapixa Creative Studio
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GWA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GWA_PLUGIN_URL', plugin_dir_url(__FILE__));

class Gemini_Writing_Assistant {

    private $option_name = 'gwa_gemini_api_key';
    private $model_option_name = 'gwa_gemini_model';

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
            add_settings_error('gwa_messages', 'gwa_message', 'Settings Saved', 'updated');
        }

        settings_errors('gwa_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('gwa_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $this->option_name; ?>">Gemini API Key</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="<?php echo $this->option_name; ?>"
                                   name="<?php echo $this->option_name; ?>"
                                   value="<?php echo esc_attr(get_option($this->option_name)); ?>"
                                   class="regular-text"
                                   placeholder="Enter your Gemini API key">
                            <p class="description">
                                Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $this->model_option_name; ?>">Gemini Model</label>
                        </th>
                        <td>
                            <select id="<?php echo $this->model_option_name; ?>"
                                    name="<?php echo $this->model_option_name; ?>"
                                    class="regular-text">
                                <?php
                                $models = array(
                                    'gemini-2.5-pro' => 'Gemini 2.5 Pro',
                                    'gemini-2.5-flash' => 'Gemini 2.5 Flash (Default)',
                                    'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite',
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
                            <p class="description">
                                Select which Gemini model to use for content generation
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        wp_enqueue_style('gwa-admin-style', GWA_PLUGIN_URL . 'assets/css/admin-style.css', array(), '1.1.2');
        wp_enqueue_script('gwa-admin-script', GWA_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), '1.1.2', true);

        wp_localize_script('gwa-admin-script', 'gwaData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gwa_nonce'),
            'hasApiKey' => !empty(get_option($this->option_name))
        ));
    }

    public function ajax_generate_content() {
        check_ajax_referer('gwa_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }

        $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';
        $tone = isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : 'professional';

        if (empty($prompt)) {
            wp_send_json_error('Prompt is required');
        }

        $api_key = get_option($this->option_name);

        if (empty($api_key)) {
            wp_send_json_error('API key not configured. Please add your Gemini API key in Settings > Pixa AI');
        }

        $content = $this->generate_content_with_gemini($api_key, $prompt, $tone);

        if (is_wp_error($content)) {
            wp_send_json_error($content->get_error_message());
        }

        wp_send_json_success(array('content' => $content));
    }

    public function ajax_analyze_content() {
        check_ajax_referer('gwa_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }

        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error('Content is required');
        }

        $api_key = get_option($this->option_name);

        if (empty($api_key)) {
            wp_send_json_error('API key not configured');
        }

        $analysis = $this->analyze_content_with_gemini($api_key, $content);

        if (is_wp_error($analysis)) {
            wp_send_json_error($analysis->get_error_message());
        }

        wp_send_json_success(array('analysis' => $analysis));
    }

    public function ajax_optimize_seo() {
        check_ajax_referer('gwa_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }

        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error('Content is required');
        }

        $api_key = get_option($this->option_name);

        if (empty($api_key)) {
            wp_send_json_error('API key not configured');
        }

        $optimized_content = $this->optimize_content_for_seo($api_key, $content);

        if (is_wp_error($optimized_content)) {
            wp_send_json_error($optimized_content->get_error_message());
        }

        wp_send_json_success(array('content' => $optimized_content));
    }

    private function generate_content_with_gemini($api_key, $prompt, $tone = 'professional') {
        $model = get_option($this->model_option_name, 'gemini-2.5-flash');
        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent?key=' . $api_key;

        $tone_instruction = "Write in a {$tone} tone. ";

        $body = json_encode(array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $tone_instruction . 'Write a blog post about: ' . $prompt . '\n\nIMPORTANT: Format the output in HTML with proper tags like <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em> etc. Do not use markdown. Return only the HTML content without any code blocks or backticks.')
                    )
                )
            )
        ));

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => $body,
            'timeout' => 30
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
        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent?key=' . $api_key;

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
                'Content-Type' => 'application/json'
            ),
            'body' => $body,
            'timeout' => 30
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
        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent?key=' . $api_key;

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
                'Content-Type' => 'application/json'
            ),
            'body' => $body,
            'timeout' => 30
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
}

new Gemini_Writing_Assistant();
