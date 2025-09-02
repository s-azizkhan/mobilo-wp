<?php
namespace Mobilo\WpTheme\Services;

use const Mobilo\WpTheme\Utils\CACHE_TYPE_TRANSIENT;
use Mobilo\WpTheme\Utils\FlexibleCache;

defined('ABSPATH') || exit;

class BackendApiManager
{
    use FlexibleCache;

    private string $base_url;
    private string $gateway_id;
    private array $headers = [];

    /**
     * Initiate the API manager, by default it points to APK gateway
     *
     * @param string $gateway_id The gateway ID, either 'APK' or 'API'
     */
    public function __construct($gateway_id = 'APK')
    {
        // TODO: add below to env file
        $this->gateway_id = $gateway_id;
        $this->base_url = ($gateway_id === 'API') ? MOBILO_API_GATEWAY : MOBILO_APK_GATEWAY;
        $this->set_cache_type(CACHE_TYPE_TRANSIENT);

    }

    public function getSuccessCodes()
    {
        return [200, 201];
    }

    /**
     * Get the API key headers
     *
     * @return array
     */
    private function get_apk_key_headers(): array
    {
        return [
            'Content-Type' => 'application/json', // Set the content type to JSON
            'X-API-KEY' => MOBILO_X_API_KEY,
        ];
    }

    /**
     * Prepare headers for the API request
     *
     * @param array $additional_headers Additional headers to merge with the default headers
     */
    public function prepare_headers(array $additional_headers = []): void
    {
        $headers = ($this->gateway_id === 'APK') ? $this->get_apk_key_headers() : [];
        $this->headers = array_merge($additional_headers, $headers);
    }

    /**
     * Perform a GET request to the API
     *
     * @param string $endPoint The API endpoint
     * @param array $params Query parameters
     * @return mixed|null
     */
    public function getData(string $endPoint, array $params = [])
    {
        try {

            if (!$endPoint) {
                return null;
            }

            $url = $this->base_url . $endPoint;
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            // $cache_data = $this->get_cache($url);
            // if ($cache_data) {
            //     return json_decode($cache_data, true);
            // }

            if (empty($this->headers)) {
                $this->prepare_headers();
            }

            // log the req
//             mobilo_log(__METHOD__, "GET:$url", 'debug');

            $response = wp_remote_get($url, [
                'timeout' => 60,
                'headers' => $this->headers,
            ]);

            if (is_wp_error($response)) {
                return null;
            }

            $body = wp_remote_retrieve_body($response);
            // set the data in cache for 2sec
            // $this->set_cache($url, $body, 5);
            return json_decode($body, true);

        } catch (\Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }

    /**
     * Perform a POST request to the API
     *
     * @param string $path The API path
     * @param array $params Query parameters
     * @param array $body Request body
     * @return mixed|null
     */
    public function postData(string $path, array $params = [], array $body = [])
    {
        try {
            if (!$path) {
                return null;
            }

            $url = $this->base_url . $path;
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            if (empty($this->headers)) {
                $this->prepare_headers();
            }

            mobilo_log(__METHOD__, "POST:$url", 'info');

            $response = wp_remote_post($url, [
                'timeout' => 60,
                'headers' => $this->headers,
                'body' => json_encode($body),
            ]);

            if (is_wp_error($response)) {
                return null;
            }

            $responseBody = wp_remote_retrieve_body($response);
            return json_decode($responseBody, true);
        } catch (\Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }
}
