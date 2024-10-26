<?php
class OpenAI_Client {
    private $api_key;
    private $api_endpoint = 'https://api.openai.com/v1/chat/completions';

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function complete($params) {
        $response = wp_remote_post($this->api_endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($params),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new Exception($body['error']['message']);
        }

        return $body;
    }
}