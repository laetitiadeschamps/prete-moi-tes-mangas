<?php

namespace App\Command;

use App\Entity\Manga;
use App\Service\JikanApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMangaCommand extends Command
{
    protected static $defaultName = 'makeManga';
    protected static $defaultDescription = 'Fill out database with mangas';

    private $jikanApi;
    private $em;
    private $mangaNames = [
        
        // [
        //     "title"  => "One piece",
        //     "author" =>"ODA Eiichirô",
        // ],
        // [
        //     "title"  => "Dragon ball",
        //     "author" => "TORIYAMA Akira"
        // ],
        // [
        //     "title"  => "Death note",
        //     "author" => "OBATA Takeshi"
        // ],
        // [
        //     "title"  => "One punch man",
        //     "author" => "MURATA Yûsuke"
        // ],
        // [
        //     "title"  =>"The promised neverland",
        //     "author" => "DEMIZU Posuka"
        // ],
        // [
        //     "title"  =>"Demon slayer",
        //     "author" => "GOTÔGE Koyoharu"
        // ],
        // [
        //     "title"  => "My hero academia",
        //     "author" => "HORIKOSHI Kôhei"
        // ],
        // [
        //     "title"  => "Fairy tail",
        //     "author" => "MASHIMA Hiro"
        // ],
        // [
        //     "title"  => "Naruto",
        //     "author" => "KISHIMOTO Masashi"
        // ],
        // [
        //     "title"  => "L'attaque des titans",
        //     "author" => "ISAYAMA Hajime"
        // ],
        // [
        //     "title"  => "Dragon ball Z",
        //     "author" => "TORIYAMA Akira"
        // ],
        // [
        //     "title"  => "Dragon ball super",
        //     "author" => "TORIYAMA Akira"
        // ],
        // [
        //     "title"  => "Dragon ball super",
        //     "author" => "TORIYAMA Akira"
        // ],
        // [
        //     "title"  => "Fullmetal alchemist",
        //     "author" => "ARAKAWA Hiromu"
        // ],
        
        // [
        //     "title"  => "Fire force",
        //     "author" => "OHKUBO Atsushi"
        // ], 
        // [
        //     "title"  => "Blue exorcist",
        //     "author" => "KATO Kazue"
        // ], 
        // [
        //     "title"  => "Black torch",
        //     "author" => "TAKAKI Tsuyoshi"
        // ], 
        // [
        //     "title"  => "Boruto",
        //     "author" => "IKEMOTO Mikio"
        // ], 
        // [
        //     "title"  => "Tokyo ghoul",
        //     "author" => "ISHIDA Sui"
        // ],
        // [
        //     "title"  => "Noragami",
        //     "author" => "ADACHITOKA"
        // ],
        // [
        //     "title"  => "Soul eater",
        //     "author" => "OHKUBO Atsushi"
        // ],
        // [
        //     "title"  => "Seven deadly sins",
        //     "author" => "SUZUKI Nakaba"
        // ],
        // [
        //     "title"  => "DARLING in the FRANXX",
        //     "author" => "HAYASHI Naotaka"
        // ],
        // [
        //     "title"  => "GTO",
        //     "author" => "FUJISAWA Tohru"
        // ],
        // [
        //     "title"  => "Hunter X Hunter",
        //     "author" => "TOGASHI Yoshihiro"
        // ],
        // [
        //     "title"  => "Bleach",
        //     "author" => "TITE Kubo"
        // ],
        // [
        //     "title"  => "A silent voice",
        //     "author" => "OHIMA Yoshitoki"
        // ],
        // [
        //     "title"  => "Jujutsu kaisen",
        //     "author" => "AKUTAMI Gege"
        // ],
        // [
        //     "title"  => "Food wars",
        //     "author" => "SAEKI Shun"
        // ],
        // [
        //     "title"  => "Dr. Stone",
        //     "author" => "BOICHI - INAGAKI Riichiro"
        // ],
        // [
        //     "title"  => "Black clover",
        //     "author" => "TABATA Yûki"
        // ],
        // [
        //     "title"  => "Haikyu!!",
        //     "author" => "FURUDATE Haruichi"
        // ],
        // [
        //     "title"  => "Banana fish",
        //     "author" => "YOSHIDA Akimi"
        // ],
        // [
        //     "title"  => "Seraph of the end",
        //     "author" => "YAMAMOTO Yamato"
        // ],
        // [
        //     "title"  => "Bungo stray dogs",
        //     "author" => "Harukawa Sango"
        // ],
        // [
        //     "title"  => "Edens zero",
        //     "author" => "MASHIMA Hiro"
        // ],
        // [
        //     "title"  => "Another",
        //     "author" => "KIYOHARA Hiro"
        // ],
        // [
        //     "title"  => "Berserk",
        //     "author" => "MIURA Kentarô"
        // ],
        // [
        //     "title"  => "Akira",
        //     "author" => "OTOMO Katsuhiro"
        // ],
        // [
        //     "title"  => "Detective conan",
        //     "author" => "AOYAMA Gosho"
        // ],
        // [
        //     "title"  => "Kuroko's basket",
        //     "author" => "TAKAHASHI Ichirô"
        // ],
        // [
        //     "title"  => "Beastars",
        //     "author" => "ITAGAKI Paru"
        // ],
        // [
        //     "title"  => "Ano hana",
        //     "author" => "IZUMI Mitsu"
        // ],
        // [
        //      "title"  => "Monster",
        //      "author" => "URASAWA Naoki"
        //  ],
        //  [
        //      "title"  => "Gunnm",
        //      "author" => "KISHIRO Yukito"
        //  ],
        //  [
        //      "title"  => "20th century boys",
        //      "author" => "URASAWA Naoki"
        //  ],
        //  [
        //      "title"  => "20th century boys",
        //      "author" => "URASAWA Naoki"
        //  ],
         
        // [
        //     'title'=>'Last quarter',
        //     'author'=>'YAZAWA Ai'     
        // ],
        // [
        //     'title' => 'Pluto',
        //     'author'=>'URASAWA Naoki'
        // ],
        // [
        //     'title'=>"Vagabond",
        //     'author'=>'INOUE Takehiko'
        // ],
        // [
        //     'title'=> "Jojo's bizarre adventure",
        //     'author'=>'ARAKI Hirohiko'
        // ],
        // [
        //     'title'=> "Gintama",
        //     'author'=>'SORACHI Hideaki'
        // ],
        //  [
        //     'title'=> "Kingdom",
        //     'author'=>'HARA Yasuhisa'
        //  ],
        // [
        //     'title'=>"World trigger",
        //     'author'=>'ASHIHARA Daisuke'
        // ],
        //  [
        //     'title'=>"Doraemon",
        //     'author'=>'FUJIKO F. Fujio'
        // ],
        // [
        //    'title'=> "Yu yu hakusho",
        //     'author'=>'TOGASHI Yoshihiro'
        // ],
        // [
        //     'title'=>"Black jack",
        //     'author'=>'TEZUKA Osamu'
        //  ],
        //  [
        //     'title'=> "Le pacte des yokai",
        //     'author'=>'MIDORIKAWA Yuki'
        // ],
        //  [
        //     'title'=>"Ken le survivant",
        //     'author'=>'HARA Tetsuo'
        // ],
        //  [
        //     'title'=>"Touch",
        //     'author'=>'ADACHI Mitsuru'
        // ],
        //  [
        //     'title'=>"Phénix",
        //     'author'=>'HASEGAWA Keiichi' 
        // ],
        //  [
        //     'title'=>"La rose de versailles",
        //     'author'=>'IKEDA Riyoko'
        // ],
        //  [
        //     'title'=>"Inu-yasha",
        //     'author'=>'TAKAHASHI Rumiko'
        // ],
        //  [
        //     'title'=>"Ashita no joe",
        //     'author'=>'CHIBA Tetsuya'
        // ],
        // [
        //     'title'=>"Dragon quest",
        //     'author'=>'INADA Kôji'
        //  ],
        //  [
        //     'title'=> "Violet Evergarden",
        //     'author'=>'YOSHIDA Reiko'
        // ],
        //  [
        //     'title'=>"Arte",
        //     'author'=>'OHKUBO kei / ÔKUBO Kei'
        // ],
        //  [
        //     'title'=>"The asterisk war",
        //     'author'=>'MIYAZAKI Yuu'
        // ],
        //  [
        //     'title'=>"No game no life",
        //     'author'=>'HIIRAGI Mashiro'
        // ],
        // [
        //     'title'=>"High school fleet",
        //     'author'=>'AIS'
        // ],
        //  [
        //     'title'=>"Alice in borderland",
        //     'author'=>'ASÔ Haro'
        // ],
        //  [
        //     'title'=>"Fruits basket",
        //     'author'=>'TAKAYA Natsuki'
        // ],
        //  [
        //     'title'=>"Toilet-bound hanako-kun",
        //     'author'=>' AIDAIRO / AIDA Iro'
        // ],
        //  [
        //     'title'=>"Parasite",
        //     'author'=>'IWAAKI Hitoshi'
        // ],
        //  [
        //     'title'=>"Assassination classroom",
        //     'author'=>'MATSUI Yûsei'
        // ],
        //  [
        //     'title'=>"Rurouni Kenshin",
        //     'author'=>'WATSUKI Nobuhiro'
        // ],
        // [
        //     'title'=>"Chainsaw man",
        //     'author'=>'FUJIMOTO Tatsuki'
        // ],
        //  [
        //     'title'=>"Golgo 13",
        //     'author'=>'SAITÔ Takao'
        // ],
        //  [
        //     'title'=>"Vinland Saga",
        //     'author'=>'YUKIMURA Makoto'
        // ],
        //  [
        //     'title'=>"Nausicaä de la vallée du vent",
        //     'author'=>'MIYAZAKI Hayao'
        // ],
        //  [
        //     'title'=>"City hunter",
        //     'author'=>'HOJO Tsukasa / HÔJÔ Tsukasa'
        // ],
        //  [
        //     'title'=>"Eyeshield 21",
        //     'author'=>'MURATA Yûsuke'
        // ],
        //  [
        //     'title'=>"Bakuman",
        //     'author'=>'OBATA Takeshi'
        // ],
        //  [
        //     'title'=>"Nana",
        //     'author'=>'YAZAWA Ai'
        // ],
        //  [
        //     'title'=>"Gantz",
        //     'author'=>'OKU Hiroya'
        // ],
        //  [
        //     'title'=>"Ippo",
        //     'author'=>'MORIKAWA George'
        // ],
        //  [
        //     'title'=>"Les chevaliers du zodiaque",
        //     'author'=>'KURUMADA Masami'
        // ],
        //  [
        //     'title'=>"Tokyo revengers",
        //     'author'=>'WAKUI Ken'
        // ],
        //  [
        //     'title'=>"Moriarty",
        //     'author'=>'MIYOSHI Hikaru'
        // ],
        //  [
        //     'title'=>"Erased",
        //     'author'=>'SANBE Kei'
        // ],
        //  [
        //     'title'=>"gegege no kitaro",
        //     'author'=>'MIZUKI Shigeru'
        // ],
        //  [
        //     'title'=>"Saiki kusuo",
        //     'author'=>'YOKOTE Michiko'
        // ],
        //  [
        //     'title'=>"Kaguya sama love is war",
        //     'author'=>' AKASAKA Aka'
        // ],
        //  [
        //     'title'=>"spy x family",
        //     'author'=>'ENDO Tatsuya'
        // ],
        //  [
        //     'title'=>"shadows house",
        //     'author'=>'SÔMATÔ / So-ma-to'
        // ],
        //  [
        //      "title"  => "the quintessential quintuplets",
        //      "author" => "NAOMURA Tôru"
        //  ],
        //  [
        //      "title"  => "gambling school",
        //      "author" => "NAOMURA Tôru"
        //  ],
        //  [
        //      "title"  => "Your lie in april",
        //      "author" => "ARAKAWA Naoshi"
        //  ],
        //  [
        //      "title"  => "Made in abyss",
        //      "author" => "TSUKUSHI Akihito"
        //  ],

    ];
    public function __construct(JikanApi $jikanApi, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->jikanApi = $jikanApi;
        $this->em = $em;
    }
    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1',
        // InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach($this->mangaNames as $mangaName) {
            $result = $this->jikanApi->fetch($mangaName['title']);
            dump($result["results"][0]["title"]);
            $manga = new Manga();
            $manga->setTitle($mangaName['title']);
            $manga->setPicture($result["results"][0]["image_url"]);
            $manga->setVolumeNumber($result["results"][0]["volumes"]);
            $manga->setSynopsis($result["results"][0]["synopsis"]);
            $manga->setAuthor($mangaName['author']);
            $this->em->persist($manga);    
        }
        $this->em->flush();

        
     
        //$arg1 = $input->getArgument('arg1');
       
        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
