<?php

namespace App\Services;

class UrlGenerator
{

    private $mappings = array(
        'Main' => '',
        'Contact owner' => 'contact-owner',
        'Contact' => 'contact-us',
    );

    /**
     * Dwelling ID.
     *
     * @var int
     */
    private $id;

    /**
     * @param int $id Dwelling ID
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function generateUrls()
    {
        $urls = array();

        foreach ($this->mappings as $type => $identifier) {
            $url = sprintf(
                'http://www.oliverstravels.com/otbs?%s',
                http_build_query(array('id' => $this->id))
            );

            if ($identifier) {
                $url = sprintf('%s#%s', $url, $identifier);
            }

            $urls[$type] = $url;
        }

        return $urls;
    }

}
