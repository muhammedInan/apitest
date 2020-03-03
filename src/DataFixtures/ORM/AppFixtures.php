<?php

namespace App\DataFixtures\ORM;

use Faker\Factory;
use App\Entity\Pret;
use App\Entity\Livre;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $manager;
    private $faker;
    private $repoLivre;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->faker=Factory::create('fr_FR');
        $this->passwordEncoder=$passwordEncoder;
    }
    public function load(EntityManagerInterface $manager)
    {
        $this->manager=$manager;
        $this->repoLivre=$this->manager->getRepository(Livre::class);
        $this->loadAdherent();
        $this->loadPret();

        $manager->flush();
    }

    /**
     * création des adhérents
     *
     * @return void
     */
    public function loadAdherent(){


        $genre=['male', 'female'];
        $commune=["75018", "75019", "75010"];
        for($i=0;$i<25;$i++){
            $adherent = new Adherent();
            $adherent ->setNom($this->faker->lastName())
                      ->setPrenom($this->faker->firstName($genre[mt_rand(0,1)]))
                      ->setAdresse($this->faker->streeetAddress()
                      ->setTel($this->faker->phoneNumber())
                      ->setCodeCommun($commune[mt_rand(0,sizeof($commune)-1)])
                      ->setMail(strtolower($adherent->getNom()) ."@gmail.com"))
                      ->setPassword($this->passwordEncoeder->encodePassword($adherent,$adherent->getNom()));
                $this->addReference("adherent". $i,$adherent);
                $this->manager->persist($adherent);
        }

        $adherent = new Adherent();
        $adherent ->setNom("Rolland")
                  ->setPrenom("Stephane")
                  ->setMail("admin@gmail.com")
                  ->setPassword("Rolland");
            $this->manager->persist($adherent);



        $this->manager->flush();
    }

    /**
     * creéation des prêtes
     * 
     * @return void
     */
    public function loadPret(){

        for($i=0;$i<25;$i++){//pour chaque adherent

            $max=mt_rand(1,5);
            for($j=0;$j<=$max;$j++){// creation des prets
                $pret = new Pret();
                $livre=$this->repoLivre->find(mt_rand(1,49));
                $pret   ->setLivre($livre)
                        ->setAdherent($this->getReference("adherent".$i))//recuperer de addreference
                        ->setDatePret($this->faker->dateTimeBetween('-6 months'));
                    $dateRetourPrevue=date('Y-m-d H:m:n',strtotime('15 days',$pret->getDatePret()->getTimestamp()));//15 jours apres la date pret, mettre un timestamp pour mettre les 15 jours
                    $dateRetourPrevue=\DateTime::createFromFormat('Y-m-d H:m:n', $dateRetourPrevue);// crée moi une date de format a partir dune chaine de caractere
                    $pret  ->setDateRetourPrevue($dateRetourPrevue);

                    // date de retour reelle
                    if(mt_rand(1,3)==1){
                        $pret->setDateRetourReelle($this->faker->dateTimeInInterval($pret->getDatePret(),"+30 days"));
                    }
                    $this->manager->persist($pret);
            }
        }

        $this->manager->flush();

    
    }
}
