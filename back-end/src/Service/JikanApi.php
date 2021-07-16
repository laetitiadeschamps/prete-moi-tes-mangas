<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class JikanApi
{
    private $apiUrl = 'https://api.jikan.moe/v3/search/manga?';
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Method allowing to fetch informations from jikan API about a manga using its title
     *
     * @param string manga title
     * @return Array
     */
    public function fetch($title)
    {
        $response = $this->client->request(
            'GET',   
            $this->apiUrl . 'q=' . $title
        );

        
        return $response->toArray();
    }
}