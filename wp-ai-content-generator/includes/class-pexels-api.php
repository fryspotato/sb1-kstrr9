<?php
class Pexels_API {
    private $api_key;
    private $api_endpoint = 'https://api.pexels.com/videos/';

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function search_videos($query, $per_page = 1) {
        $response = wp_remote_get($this->api_endpoint . 'search', array(
            'headers' => array(
                'Authorization' => $this->api_key
            ),
            'body' => array(
                'query' => $query,
                'per_page' => $per_page
            )
        ));

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new Exception($body['error']);
        }

        return $body['videos'];
    }
}