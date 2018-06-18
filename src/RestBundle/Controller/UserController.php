<?php

namespace RestBundle\Controller;

use RestBundle\Entity\UserVisit;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use RestBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class UserController extends FOSRestController
{
    /**
     * This is the method for getting users from database
     *
     * @ApiDoc(
     *  description="Get users",
     *  parameters={
     *      {"name"="name", "dataType"="string", "format" = "json", "required"=true, "description"="user name"},
     *      {"name"="login", "dataType"="string", "format" = "json", "required"=true, "description"="user login"},
     *  },
     * statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the users is not found"
     *     }
     * )
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
     * @ApiDoc(
     *  description="Create a new User",
     *  parameters={
     *      {"name"="name", "dataType"="string", "format" = "json", "required"=true, "description"="user name"},
     *      {"name"="login", "dataType"="string", "format" = "json", "required"=true, "description"="user login"},
     *  },
     * statusCodes={
     *         200="Returned when user added successfully",
     *         406="Returned when NULL values"
     *     }
     * )
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
     * @ApiDoc(
     *  description="Update User",
     *  parameters={
     *      {"name"="name", "dataType"="string", "format" = "json", "required"=true, "description"="user name"},
     *      {"name"="login", "dataType"="string", "format" = "json", "required"=true, "description"="user login"},
     *  },
     * statusCodes={
     *         200="Returned when user updated successfully",
     *         404="Returned when user is not found",
     *         406="Returned when NULL values",
     *     }
     * )
     *
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
     *
     * * @ApiDoc(
     *  description="Delete User",
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="user id"},
     *  },
     * statusCodes={
     *         200="Returned when user deleted successfully",
     *         404="Returned when user is not found"
     *     }
     * )
     *
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
     * @ApiDoc(
     *  description="Register User's visiting",
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="user id"},
     *      {"name"="date", "dataType"="date", "required"=true, "description"="visit data"},
     *  },
     * statusCodes={
     *         200="Returned when successfully"
     *     }
     * )
     *
     * @Rest\Post("/visit-user/")
     */
    public function registerVisitUser(Request $request)
    {
        $userVisit = new UserVisit();
        $em = $this->getDoctrine()->getManager();
        $userId = $request->get('userId');
        $userVisit->setUserId($userId);
        $userVisit->setVisitDate();
        $em->persist($userVisit);
        $em->flush();

        return new View("registered user successfully", Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  description="Get unique users by date interval",
     *
     * requirements={
     *      {"name"="from", "dataType"="date", "required"=true, "description"="visit data from"},
     *      {"name"="to", "dataType"="date", "required"=true, "description"="visit data to"},
     *  },
     * statusCodes={
     *         404="Returned when user is not found",
     *     }
     * )
     *
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

