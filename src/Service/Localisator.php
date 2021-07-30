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
    public function gpsByAdress(string $adress, string $zipcode)
    {
        $response = $this->client->request(
            'GET',
            $this->apiUrl . $adress . "&postcode=" . $zipcode
        );
        $array = $response->toArray();
        return $this->getCoordinates($array);
    }

    /**
     * method to get a latitude and longitude from a zipcode
     *
     * @param string $zipcode
     * @return array [latitude, longitude]
     */
    public function gpsByZipcode(string $zipcode)
    {
        $response = $this->client->request(
            'GET',
            $this->apiUrl . $zipcode . "&type=municipality&&autocomplete=1"
        );
        $array = $response->toArray();
        return $this->getCoordinates($array);
    }
    /**
    * method to get a latitude and longitude from a city and zipcode
    *
    * @param string $adress
    * @param string $zipcode
    * @return array [latitude, longitude]
    */
    public function gpsByZipcodeAndCity(string $city, string $zipcode)
    {
        $response = $this->client->request(
            'GET',
            $this->apiUrl . $city . "&postcode=" . $zipcode . "&type=municipality"
        );
        $array = $response->toArray();
        return $this->getCoordinates($array);
    }

    /**
     * method to transform array response from API in coordinates
     *
     * @param [type] $array
     * @return void
     */
    protected function getCoordinates($array) {

        if (!$array['features']){
            return [
                'error' => 'Le code postal n\'existe pas'
            ];
        }

        $long = $array['features'][0]['geometry']['coordinates'][0];
        $lat = $array['features'][0]['geometry']['coordinates'][1];

        return [
            "latitude" => $lat,
            "longitude" =>$long
        ];
    }
}