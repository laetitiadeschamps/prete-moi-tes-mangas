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
     * Méthode permettant de retourner les informations (issues d'une API)
     * d'un manga en fonction de son title
     *
     * @param string $title
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