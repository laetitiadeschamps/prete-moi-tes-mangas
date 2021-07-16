<?php

namespace App\Service;

use App\Entity\Volume;
use App\Repository\MangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VolumesCreation
{
  private $mangaRepository;
  private $em;

  public function __construct(HttpClientInterface $client, MangaRepository $mangaRepository, EntityManagerInterface $em)
  {
    $this->client = $client;
    $this->em = $em;
    $this->mangaRepository = $mangaRepository;
  }
  /**
   *Method allowing to create volumes in database for a manga using its volume number info
   * @param int id
   */
  public function createAll($id)
  {
    $manga = $this->mangaRepository->find($id);
    $number = $manga->getVolumeNumber();

    for ($i = 1; $i <= $number; $i++) {
      $volume = new Volume();
      $volume->setNumber($i);
      $volume->setManga($manga);
      $this->em->persist($volume);
    }
    $this->em->flush();
  }
}
