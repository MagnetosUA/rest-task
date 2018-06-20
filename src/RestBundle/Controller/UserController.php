<?php

namespace RestBundle\Controller;

use RestBundle\Entity\UserVisit;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use RestBundle\Form\Type\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends FOSRestController
{
    /**
     * This is the method for getting users from database
     *
     * @ApiDoc(
     *  description="Get users",
     *
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
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(UserType::class);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
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
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(UserType::class);
        $form->submit($data);
        $user = $this->getDoctrine()->getRepository('RestBundle:User')->find($id);
        if (empty($user)) {
            return new View("user not found", Response::HTTP_NOT_FOUND);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $updatedUser = $form->getData();
            $user->setName($updatedUser->getName());
            $user->setLogin($updatedUser->getLogin());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return new View("User name or login cannot be empty", Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     *
     * * @ApiDoc(
     *  description="Delete User",
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="user id"},
     *  },
     * statusCodes={
     *         204="Returned when user deleted successfully",
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
        return new View("deleted successfully", Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *  description="Register User's visiting",
     *  parameters={
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="user id"},
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
        $login = $request->get('login');
        $user = $this->getDoctrine()->getRepository('RestBundle:User')->getUserByLogin($login);
        $user = $user[0];
        $userVisit->setUser($user);
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
     * @Rest\Get("/unique-users/{from}/{to}")
     */
    public function getUniqueUserByDateInterval($from, $to)
    {
        $userVisits = $this->getDoctrine()->getRepository('RestBundle:UserVisit')->getUsersByDateInterval($from, $to);

        if ($userVisits === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }

        foreach ($userVisits as $userVisit) {
            $date = $userVisit->getVisitDate();
            $date = $date->format('Y-m-d');
            $dates[] = $date;
        }
        $uniqueDates = array_unique($dates);

        foreach ($uniqueDates as $uniqueDate) {
            foreach ($userVisits as $userVisit) {
                $date = $userVisit->getVisitDate();
                $date = $date->format('Y-m-d');
                if ($date == $uniqueDate) {
                    $allUsers["$date"][] = $userVisit->getUser()->getId();
                }
            }
        }

        foreach ($allUsers as $kay => $value) {
            $results["$kay"] = count(array_unique($value));
        }
        if (empty($results)) {
            return new View("users is not found", Response::HTTP_NOT_FOUND);
        } else {
            return $results;
        }
    }

}

