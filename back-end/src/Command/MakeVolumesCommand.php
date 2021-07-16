<?php

namespace App\Command;

use App\Repository\MangaRepository;
use App\Service\VolumesCreation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeVolumesCommand extends Command
{
    protected static $defaultName = 'makeVolumes';
    protected static $defaultDescription = 'Create volumes for each manga to initialise database';

    private $mangaRepository;
    private $volumesCreation;

    public function __construct(MangaRepository $mangaRepository, VolumesCreation $volumesCreation)
    {
        parent::__construct();
       $this->mangaRepository = $mangaRepository;
       $this->volumesCreation = $volumesCreation;
    }
    

    /**
     * method to fill DB using VolumesCreation service
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $mangas = $this->mangaRepository->findAll();
        foreach ($mangas as $manga) {
            $this->volumesCreation->createAll($manga->getId());
            
        }

        $io->success('Les tomes ont été correctement créés');

        return Command::SUCCESS;
    }
}
