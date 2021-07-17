<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Localisator
{
    private $apiUrl = 'https://api-adresse.data.gouv.fr/search/?q=';
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * method to get a latitude and longitude from an adress and zipcode
     *
     * @param string $adress
     * @param string $zipcode
     * @return array [latitude, longitude]
     */
    public function gpsByAdress(string $adress, string $zipcode){

        $response = $this->client->request(
            'GET',   
            $this->apiUrl . $adress . "&postcode=" . $zipcode
        );
        $array = $response->toArray();
   
        $long = $array['features'][0]['geometry']['coordinates'][0];
        $lat = $array['features'][0]['geometry']['coordinates'][1];
        return [
            "latitude" => $lat,
            "longitude" =>$long
        ];
        
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