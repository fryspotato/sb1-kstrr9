<div class="wrap">
    <h1>AI Content Generator</h1>

    <div class="card">
        <h2>Generate New Content</h2>
        <div class="inside">
            <form id="ai-content-generator-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="content-prompt">Content Prompt</label>
                        </th>
                        <td>
                            <textarea 
                                id="content-prompt" 
                                name="prompt" 
                                rows="4" 
                                class="large-text"
                                placeholder="Enter your content prompt here..."
                                required
                            ></textarea>
                            <p class="description">
                                Describe the content you want to generate. Be specific about the topic, tone, and target audience.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="schedule-post">Schedule Post</label>
                        </th>
                        <td>
                            <select id="schedule-post" name="schedule[interval]">
                                <option value="">Publish Immediately</option>
                                <option value="hourly">Every Hour</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            <input 
                                type="time" 
                                name="schedule[time]" 
                                value="09:00"
                                class="schedule-time"
                            />
                            <p class="description">
                                Choose when to publish this post.
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="submit-container">
                    <button type="submit" class="button button-primary" id="generate-content">
                        Generate Content
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h2>Scheduling Settings</h2>
        <div class="inside">
            <form id="ai-content-settings-form" method="post" action="options.php">
                <?php settings_fields('ai_content_generator_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="max-posts">Maximum Posts per Day</label>
                        </th>
                        <td>
                            <input 
                                type="number" 
                                id="max-posts" 
                                name="ai_content_generator_schedule_settings[max_posts_per_day]" 
                                value="<?php echo esc_attr(get_option('ai_content_generator_schedule_settings')['max_posts_per_day']); ?>" 
                                min="1" 
                                max="24"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="continuous-generation">Continuous Generation</label>
                        </th>
                        <td>
                            <input 
                                type="checkbox" 
                                id="continuous-generation" 
                                name="ai_content_generator_schedule_settings[continuous_generation]" 
                                value="1"
                                <?php checked(get_option('ai_content_generator_schedule_settings')['continuous_generation']); ?>
                            />
                            <p class="description">
                                Automatically generate new posts when scheduled posts are published.
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
    </div>

    <!-- Previous settings sections remain the same -->
</div>