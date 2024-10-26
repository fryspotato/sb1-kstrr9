<?php
class Unsplash_API {
    private $api_key;
    private $api_endpoint = 'https://api.unsplash.com/';

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function search($query, $per_page = 1) {
        $response = wp_remote_get($this->api_endpoint . 'search/photos', array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $this->api_key
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

        if (isset($body['errors'])) {
            throw new Exception(implode(', ', $body['errors']));
        }

        return $body['results'];
    }
}