<?php

 namespace App\Controller;

 use App\Entity\Genre;
 use Doctrine\ORM\EntityManager;
 use PhpParser\Builder\Interface_;
 use App\Repository\GenreRepository;

 use Doctrine\Persistence\ObjectManager;
 use Doctrine\ORM\EntityManagerInterface;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\HttpFoundation\Response;
 use Symfony\Component\Routing\Annotation\Route;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\Serializer\SerializerInterface;
 use Symfony\Component\Validator\Validator\ValidatorInterface;
 use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

 class ApiGenreController extends AbstractController
 {
     /**
      * @Route("/api/genres", name="api_genres", methods={"GET"})
      */
     public function list(GenreRepository $repo, SerializerInterface $serializer)
     {
         $genres = $repo->findAll();
         $resultat= $serializer->serialize(
             $genres,
             'json',
             [
                 'groups' =>['listGenreSimple']//pour eviter quil serialise un objet dans un objet pour eviter de perdre le temps dexecution
             ]
         );


         return new JsonResponse($resultat,200,[],true);
     }

     /**
      * @Route("/api/genres/{id}", name="api_genres_show", methods={"GET"})
      */
     public function show(Genre $genre , SerializerInterface $serializer)
     {
        
         $resultat= $serializer->serialize(
             $genre,
             'json',
             [
                 'groups' =>['listGenreFull']//pour eviter quil serialise un objet dans un objet pour eviter de perdre le temps dexecution
             ]
         );


         return new JsonResponse($resultat,Response::HTTP_OK,[],true);
     }

     /**
      * @Route("/api/genres", name="api_genres_create", methods={"POST"})
      */
     public function create(Request $request , EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator)
     {
         $data=$request->getContent();
         //$genre=new Genre();
        // $serializer->deserialize($data, Genre::class,'json', ['object_to_populate' =>$genre]);//rempli les informations que taura deserialiser dans la data
        // gestion des erreurs de validation
         $genre= $serializer->deserialize($data, Genre::class,'json');
         $errors = $validator->validate($genre); //tableau d'erruer qui va contenir
         if(count($errors)){
             $errorsJson= $serializer->serialize($errors,'json'); //serializer les erreurs
             return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
         }
         $manager->persist($genre);
         $manager->flush();
        
          return new JsonResponse(
             "Le genre a bien etait crée",
         Response::HTTP_CREATED,
        // [
         //    "location" => "/api/genres/" .$genre->getId()//renvoyer le hedear location pour renvoyer le id  , pour linstant on connais pas les id dans les bdd sauf si  on me les donnes
        //],true);
          ["location"=> $this->generateUrl(
             'api_genres_show',
              ["id"=>$genre->getId()],
               UrlGeneratorInterface::ABSOLUTE_URL)],
               true);

     }

     /**
      * @Route("/api/genres/{id}", name="api_genres_update", methods={"PUT"})
      */
     public function edit(Genre $genre ,Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator)
     {
         //recuperer une varuable data grace aux donnee request
         $data=$request->getContent();
         $serializer->deserialize($data, Genre::class,'json',['object_to_populate' => $genre]);

         $genre= $serializer->deserialize($data, Genre::class,'json');
         $errors = $validator->validate($genre); //tableau d'erruer qui va contenir
         if(count($errors)){
             $errorsJson= $serializer->serialize($errors,'json'); //serializer les erreurs
             return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
         }

         $manager->persist($genre);
         $manager->flush();
         return new JsonResponse("le genre a bien ete modifier",Response::HTTP_OK,[],true);
        
     }

     /**
      * @Route("/api/genres/{id}", name="api_genres_delete", methods={"DELETE"})
      */
     public function delete(Genre $genre , EntityManagerInterface $manager)
     {
        
        
         $manager->remove($genre);
         $manager->flush();
         return new JsonResponse("le genre a bien ete sup^prime",Response::HTTP_OK,[]);
        
     }
 }
