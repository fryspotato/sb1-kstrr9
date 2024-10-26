<?php
class SEO_Optimizer {
    public function optimize_content($content, $keywords) {
        // Add meta description
        $meta_description = $this->generate_meta_description($content);
        
        // Add alt tags to images
        $content = $this->optimize_images($content, $keywords);
        
        // Add video schema
        $content = $this->optimize_videos($content, $keywords);
        
        return [
            'content' => $content,
            'meta_description' => $meta_description
        ];
    }

    private function generate_meta_description($content) {
        // Extract first 155 characters of content, ending at a complete word
        $stripped_content = strip_tags($content);
        $meta = substr($stripped_content, 0, 155);
        $meta = substr($meta, 0, strrpos($meta, ' ')) . '...';
        return $meta;
    }

    private function optimize_images($content, $keywords) {
        // Add SEO-friendly alt tags and title attributes to images
        $content = preg_replace_callback(
            '/<img[^>]+>/',
            function($match) use ($keywords) {
                $img = $match[0];
                $keyword = $keywords[array_rand($keywords)];
                
                // Add alt and title if not present
                if (!strpos($img, 'alt=')) {
                    $img = str_replace('<img', '<img alt="' . esc_attr($keyword) . '"', $img);
                }
                if (!strpos($img, 'title=')) {
                    $img = str_replace('<img', '<img title="' . esc_attr($keyword) . '"', $img);
                }
                
                return $img;
            },
            $content
        );
        
        return $content;
    }

    private function optimize_videos($content, $keywords) {
        // Add schema.org markup for videos
        $content = preg_replace_callback(
            '/<iframe[^>]+>/',
            function($match) use ($keywords) {
                $iframe = $match[0];
                $keyword = $keywords[array_rand($keywords)];
                
                // Add schema.org video markup
                $schema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'VideoObject',
                    'name' => $keyword,
                    'description' => 'Video about ' . $keyword,
                    'thumbnailUrl' => '',
                    'uploadDate' => date('c')
                ];
                
                $schema_html = '<script type="application/ld+json">' . 
                              json_encode($schema) . 
                              '</script>';
                
                return $schema_html . $iframe;
            },
            $content
        );
        
        return $content;
    }
}