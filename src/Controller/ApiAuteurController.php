<?php

 namespace App\Controller;

 use App\Entity\Auteur;
 use Doctrine\ORM\EntityManager;
 use PhpParser\Builder\Interface_;
 use App\Repository\AuteurRepository;

 use Doctrine\Persistence\ObjectManager;
 use Doctrine\ORM\EntityManagerInterface;
 use App\Repository\NationaliteRepository;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\HttpFoundation\Response;
 use Symfony\Component\Routing\Annotation\Route;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\Serializer\SerializerInterface;
 use Symfony\Component\Validator\Validator\ValidatorInterface;
 use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 class ApiAuteurController extends AbstractController
 {
     /**
      * @Route("/api/auteurs", name="api_auteurs", methods={"GET"})
      */
     public function list(AuteurRepository $repo, SerializerInterface $serializer)
     {
         $auteurs = $repo->findAll();
         $resultat= $serializer->serialize(
             $auteurs,
             'json',
             [
                 'groups' =>['listAuteurFull']//pour eviter quil serialise un objet dans un objet pour eviter de perdre le temps dexecution
             ]
         );


         return new JsonResponse($resultat,200,[],true);
     }

     /**
      * @Route("/api/auteurs/{id}", name="api_auteurs_show", methods={"GET"})
      */
     public function show(Auteur $auteur , SerializerInterface $serializer)
     {
        
         $resultat= $serializer->serialize(
             $auteur,
             'json',
             [
                 'groups' =>['listAuteurSimple']//pour eviter quil serialise un objet dans un objet pour eviter de perdre le temps dexecution
             ]
         );


         return new JsonResponse($resultat,Response::HTTP_OK,[],true);
     }

     /**
      * @Route("/api/auteurs", name="api_auteurs_create", methods={"POST"})
      */
     public function create(Request $request , EntityManagerInterface $manager, NationaliteRepository $repoNation, SerializerInterface $serializer, ValidatorInterface $validator)
     {
         $data=$request->getContent();
         $dataTab=$serializer->decode($data,'json');
         $auteur=new Auteur();
         $nationalite=$repoNation->find($dataTab['nationalite']['id']); 
         $serializer->deserialize($data, Auteur::class,'json',['object_to_populate' => $auteur]);
        
        // $serializer->deserialize($data, Auteur::class,'json', ['object_to_populate' =>$auteur]);//rempli les informations que taura deserialiser dans la data
        // gestion des erreurs de validation
         $auteur= $serializer->deserialize($data, Auteur::class,'json');
         $errors = $validator->validate($auteur); //tableau d'erruer qui va contenir
         if(count($errors)){
             $errorsJson= $serializer->serialize($errors,'json'); //serializer les erreurs
             return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
         }
         $manager->persist($auteur);
         $manager->flush();
        
          return new JsonResponse(
             "Le auteur a bien etait crée",
         Response::HTTP_CREATED,
        // [
         //    "location" => "/api/auteurs/" .$auteur->getId()//renvoyer le hedear location pour renvoyer le id  , pour linstant on connais pas les id dans les bdd sauf si  on me les donnes
         //],true);
         ["location"=> $this->generateUrl(
             'api_auteurs_show',
              ["id"=>$auteur->getId()],
               UrlGeneratorInterface::ABSOLUTE_URL)],
               true);

     }

     /**
      * @Route("/api/auteurs/{id}", name="api_auteurs_update", methods={"PUT"})
      */
     public function edit(Auteur $auteur ,Request $request, NationaliteRepository $repoNation ,EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator)
     {
         //recuperer une varuable data grace aux donnee request
         $data=$request->getContent();
         $dataTab=$serializer->decode($data,'json');//contenir le format tableau  decode moi le data en forma json 
         $nationalite=$repoNation->find($dataTab['nationalite']['id']); // recuperrer la nationalite dont je vais te donner le numero qui se trouve dans la datatab et il est a la cle nationalite et tu le trouvera dans la cle id
         //solution1 objet l'un  dans lautre
         $serializer->deserialize($data, Auteur::class,'json',['object_to_populate' => $auteur]);
         $auteur->setNationalite($nationalite);// mon objet auteur je lui affecte la nationalite que je vien de recuperer de mon format json 
         //soltion 2 objet lun a coter de lautre
         //$serializer->denormalize($dataTab['auteur'], Auteur::class,null,['object_to_populate'=>$auteur]);//denormalize a partir de la dataTab ce qui est dans la cle auteur et tu me transform sa en objet auteur
         //gestion des erreurs
         $errors = $validator->validate($auteur); //tableau d'erruer qui va contenir
         if(count($errors)){
             $errorsJson= $serializer->serialize($errors,'json'); //serializer les erreurs
             return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
         }

         $manager->persist($auteur);
         $manager->flush();
         return new JsonResponse("le auteur a bien ete modifier",Response::HTTP_OK,[],true);
        
     }

     /**
      * @Route("/api/auteurs/{id}", name="api_auteurs_delete", methods={"DELETE"})
      */
     public function delete(Auteur $auteur , EntityManagerInterface $manager)
     {
        
        
         $manager->remove($auteur);
         $manager->flush();
         return new JsonResponse("le auteur a bien ete sup^prime",Response::HTTP_OK,[]);
        
     }
 }
