<?php

namespace App\Services;

use http\Url;

class TrackingCodeAppender
{

    /**
     * @var string
     */
    private $source;
    /**
     * @var string
     */
    private $medium;
    /**
     * @var string
     */
    private $campaign;

    /**
     * @param string $source
     * @param string $medium
     * @param string $campaign
     */
    public function __construct($source, $medium, $campaign)
    {
        $this->source = $source;
        $this->medium = $medium;
        $this->campaign = $campaign;
    }

    /**
     * Appends the tracking codes to an existing URL.
     *
     * Merges any existing query parameters but will overwrite the values of utm_source, utm_medium or utm_campaign,
     * if they are already in the URL.
     *
     * @param string $url
     * @return string
     */
    public function addTracking($url)
    {
        $trackingParameters = [
            'query' => http_build_query([
                'utm_source' => $this->source,
                'utm_medium' => $this->medium,
                'utm_campaign' => $this->campaign,
            ])
        ];

        // The following replaces the unsupported method http_build_url($url, $newUrlParts, HTTP_URL_JOIN_QUERY);
        $parsedUrl = parse_url($url);
        $returnUrl = sprintf('%s://%s', $parsedUrl['scheme'], $parsedUrl['host']);
        if (isset($parsedUrl['path'])) {
            $returnUrl .= $parsedUrl['path'];
        }
        if (isset($parsedUrl['query'])) {
            $returnUrl = sprintf('%s?%s', $returnUrl, $parsedUrl['query']);
        }
        // Add tracking parameters
        $returnUrl = sprintf(
            (false !== strpos($returnUrl, '?')) ? '%s&%s' : '%s?%s',
            $returnUrl,
            $trackingParameters['query']
        );
        // Add # anchor fragments.
        if (isset($parsedUrl['fragment'])) {
            $returnUrl .= sprintf('#%s', $parsedUrl['fragment']);
        }

        return $returnUrl;
    }

}
