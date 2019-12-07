<?php

namespace App\Controller;

use App\Exceptions\ApiException;
use App\Security\Voter\PersonVoter;
use App\Repository\PersonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeletePersonController
{
    private $manager;
    private $security;
    private $personRepository;
    private $personVoter;

    public function __construct(
        ObjectManager $manager,
        Security $security,
        PersonRepository $personRepository,
        PersonVoter $personVoter
    )
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->personRepository = $personRepository;
        $this->personVoter = $personVoter;
    }
    /**
     * @Route("/deletePerson/{id}", methods={"DELETE"})
     */
    public function deletePerson($id, Request $request)
    {
        $person = $this->personRepository->findOneById($id);
        
        if(null == $person){
            throw new ApiException('This person not exist.', 404);
        }

        $vote = $this->personVoter->vote($this->security->getToken(), $person, ['delete']);
        if($vote < 1){
            throw new ApiException('You are not authorized to access this resource.', 403);
        }

        $this->manager->remove($person);
        $this->manager->flush();
        return new JsonResponse('{"code" : 200}', $status = 200, $headers = [], true);

    }
}
