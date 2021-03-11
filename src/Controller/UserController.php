<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * @Route("/admin/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }


    /**
     * @Route("/ajax", name="ajax")
     * @return Response
     */
    public function ajaxAction(Request $request): Response
    {
        $usersFromDB = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            $jsonData = array();
            foreach ($usersFromDB as $user) {
                $temp = array(
                    'Meno' => $user->getName(),
                    'Email' => $user->getEmail()
                );
                array_push($jsonData, $temp);
            }
            return new JsonResponse($jsonData);
        } else {
            return $this->render('ajax.html.twig');
        }
    }

    /**
     * @Route("/admin/new", name="user_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

//            $pom = $this->getDoctrine()->getRepository(User::class)->findOneBy($email);
//            if ($pom == null) {
            $user->setRole(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'info',
                'Užívateľ bol vytvorený úspešne!'
            );

            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/{id}/edit", name="user_edit", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'info',
                'Užívateľ bol upravený úspešne!'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'info',
                'Užívatel bol vymazaný úspešne!'
            );

        }
        return $this->redirectToRoute('user_index');
    }
}
