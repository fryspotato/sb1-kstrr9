<?php
/**
 * Plugin Name: AI Content Generator
 * Description: Automatically generates and schedules SEO-optimized blog posts with videos and images using AI
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-openai-client.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-unsplash-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-pexels-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-seo-optimizer.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-post-scheduler.php';

class WP_AI_Content_Generator {
    private static $instance = null;
    private $seo_optimizer;
    private $post_scheduler;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->seo_optimizer = new SEO_Optimizer();
        $this->post_scheduler = new Post_Scheduler();

        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_generate_content', array($this, 'generate_content'));
        add_action('publish_scheduled_post', array($this, 'publish_scheduled_post'));
        
        // Add settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('ai_content_generator_options', 'ai_content_generator_api_key');
        register_setting('ai_content_generator_options', 'ai_content_generator_unsplash_key');
        register_setting('ai_content_generator_options', 'ai_content_generator_pexels_key');
        register_setting('ai_content_generator_options', 'ai_content_generator_schedule_settings');
    }

    public function generate_content() {
        check_ajax_referer('ai_content_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $prompt = sanitize_text_field($_POST['prompt']);
        $schedule = isset($_POST['schedule']) ? $_POST['schedule'] : null;
        
        try {
            // Generate content
            $content = $this->generate_ai_content($prompt);
            
            // Fetch media
            $media = $this->fetch_media($content['keywords']);
            
            // Optimize content for SEO
            $optimized = $this->seo_optimizer->optimize_content(
                $this->format_content($content['content'], $media),
                $content['keywords']
            );
            
            // Create post
            $post_id = $this->create_post($content['title'], $optimized['content']);
            
            // Add SEO metadata
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $optimized['meta_description']);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', implode(',', $content['keywords']));
            
            // Schedule post if requested
            if ($schedule) {
                $this->post_scheduler->schedule_post($post_id, $schedule);
            }
            
            wp_send_json_success(array(
                'post_id' => $post_id,
                'edit_url' => get_edit_post_link($post_id, 'raw')
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function publish_scheduled_post($post_id) {
        wp_publish_post($post_id);
        
        // Generate next post if continuous generation is enabled
        $settings = get_option('ai_content_generator_schedule_settings');
        if ($settings['continuous_generation']) {
            $prompt = $this->generate_prompt_from_keywords(
                get_post_meta($post_id, '_yoast_wpseo_focuskw', true)
            );
            $this->generate_content($prompt);
        }
    }

    private function generate_prompt_from_keywords($keywords) {
        $api_key = get_option('ai_content_generator_api_key');
        $client = new OpenAI_Client($api_key);
        
        $response = $client->complete([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Generate a blog post prompt based on these keywords: ' . $keywords
                ]
            ]
        ]);
        
        return $response['choices'][0]['message']['content'];
    }

    // ... [Previous methods remain the same]
}

// Initialize the plugin
add_action('plugins_loaded', array('WP_AI_Content_Generator', 'get_instance'));

// Activation hook
register_activation_hook(__FILE__, 'ai_content_generator_activate');
function ai_content_generator_activate() {
    // Add default options
    add_option('ai_content_generator_api_key', '');
    add_option('ai_content_generator_unsplash_key', '');
    add_option('ai_content_generator_pexels_key', '');
    add_option('ai_content_generator_schedule_settings', array(
        'interval' => 'daily',
        'time' => '09:00:00',
        'max_posts_per_day' => 1,
        'continuous_generation' => false
    ));
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ai_content_generator_deactivate');
function ai_content_generator_deactivate() {
    // Clear scheduled events
    wp_clear_scheduled_hook('publish_scheduled_post');
}