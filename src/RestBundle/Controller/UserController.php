<?php

namespace RestBundle\Controller;

use RestBundle\Entity\UserVisit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use RestBundle\Entity\User;
use Symfony\Component\Validator\Constraints\Date;

class UserController extends FOSRestController
{
    /**
     * @Rest\Get("/user")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('RestBundle:User')->findAll();
        if ($restresult === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Post("/user/")
     */
    public function postAction(Request $request)
    {
        $data = new User;
        $name = $request->get('name');
        $login = $request->get('login');
        if(empty($name) || empty($login))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setName($name);
        $data->setLogin($login);
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();

        return new View("User Added Successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/user/{id}")
     */
    public function updateAction($id, Request $request)
    {
        $data = $request->getContent();
        $data = json_decode($data);

        $login = $data->login;
        $name = $data->name;

        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('RestBundle:User')->find($id);
        if (empty($user)) {
            return new View("user not found", Response::HTTP_NOT_FOUND);
        }
        elseif(!empty($name) && !empty($login)){
            $user->setName($name);
            $user->setLogin($login);
            $em->flush();
            return new View("User Updated Successfully", Response::HTTP_OK);
        }

        elseif(empty($name) && !empty($login)){
            $user->setLogin($login);
            $em->flush();
            return new View("login Updated Successfully", Response::HTTP_OK);
        }
        elseif(!empty($name) && empty($role)){
            $user->setName($name);
            $em->flush();
            return new View("User Name Updated Successfully", Response::HTTP_OK);
        }
        else return new View("User name or login cannot be empty", Response::HTTP_NOT_ACCEPTABLE);
    }

    /**
     * @Rest\Delete("/user/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('RestBundle:User')->find($id);
        if (empty($user)) {
            return new View("user not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($user);
            $em->flush();
        }
        return new View("deleted successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/visit-user/")
     */
    public function registerVisitUser(Request $request)
    {
        $userVisit = new UserVisit();
        $em = $this->getDoctrine()->getManager();

        $userId = $request->get('userId');
        $userVisit->setUserId($userId);
        $userVisit->setVisitDate(new \DateTime);

        $em->persist($userVisit);
        $success = $em->flush();

        return new View("registered user successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/users/{from}/{to}")
     */
    public function getUniqueUserByDate($from, $to)
    {
        $dateInterval["from"] = $from;
        $dateInterval["to"] = $to;

        $users = $this->getDoctrine()->getRepository('RestBundle:UserVisit')->getUniqueUsersByDateInterval($dateInterval);

        if ($users === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }


        $usersId = [];
        foreach ($users as $user) {
            $usersId[] = $user->getUserId();
        }

        $uniqueUsers = array_unique($usersId);
        return $uniqueUsers;
    }
}
