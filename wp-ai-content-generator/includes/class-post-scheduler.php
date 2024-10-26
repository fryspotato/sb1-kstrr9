<?php
class Post_Scheduler {
    private $default_schedule = [
        'interval' => 'daily',
        'time' => '09:00:00',
        'max_posts_per_day' => 1
    ];

    public function schedule_post($post_id, $schedule = null) {
        if (!$schedule) {
            $schedule = $this->default_schedule;
        }

        // Calculate next posting time
        $next_post_time = $this->calculate_next_post_time($schedule);
        
        // Schedule the post
        wp_schedule_single_event($next_post_time, 'publish_scheduled_post', [$post_id]);
        
        // Store scheduling metadata
        update_post_meta($post_id, '_scheduled_publish_time', $next_post_time);
        update_post_meta($post_id, '_post_schedule', $schedule);
    }

    private function calculate_next_post_time($schedule) {
        $current_time = current_time('timestamp');
        $time_parts = explode(':', $schedule['time']);
        
        // Set base time for today
        $next_time = strtotime(date('Y-m-d', $current_time) . ' ' . $schedule['time']);
        
        // If time has passed for today, move to next interval
        if ($next_time <= $current_time) {
            switch ($schedule['interval']) {
                case 'hourly':
                    $next_time = strtotime('+1 hour', $current_time);
                    break;
                case 'daily':
                    $next_time = strtotime('tomorrow ' . $schedule['time']);
                    break;
                case 'weekly':
                    $next_time = strtotime('next week ' . $schedule['time']);
                    break;
                case 'monthly':
                    $next_time = strtotime('first day of next month ' . $schedule['time']);
                    break;
            }
        }
        
        return $next_time;
    }

    public function get_available_slots($date) {
        global $wpdb;
        
        $start_of_day = strtotime('midnight', strtotime($date));
        $end_of_day = strtotime('tomorrow', $start_of_day) - 1;
        
        $scheduled_posts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->postmeta 
            WHERE meta_key = '_scheduled_publish_time' 
            AND meta_value BETWEEN %d AND %d",
            $start_of_day,
            $end_of_day
        ));
        
        $settings = get_option('ai_content_generator_schedule_settings', [
            'max_posts_per_day' => 1
        ]);
        
        return max(0, $settings['max_posts_per_day'] - $scheduled_posts);
    }
}