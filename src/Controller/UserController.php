<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Services\FormValidationService;
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
    private $formValidationService;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, FormValidationService $formValidationService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->formValidationService = $formValidationService;
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
            $formData = $form->getData();

            $userName = $formData->getName();
            $userSurname = $formData->getSurname();
            $userEmail = $formData->getEmail();
            $userPass1 = $formData->getPassword();
            $userPass2 = $formData->getPassword2();

            $bool = $this->formValidationService
                ->name($userName)
                ->surname($userSurname)
                ->email($userEmail)
                ->passwordLength($userPass1)
                ->passwordChars($userPass1)
                ->passwordMatch($userPass1, $userPass2)
                ->validate();

            $message = $this->formValidationService->getMessage();

            if ($bool) {
                $temp = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);
                if (empty($temp)) {
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                    $user->setRole(['ROLE_NONE']);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $message = "Používateľ bol vytvorený úspešne.";
                    return $this->redirectToRoute('user_index');
                } else {
                    $message = "Email sa už používa! Zadajte iný prosím.";
                }
            }

            if (!empty($message)) {
                $this->addFlash(
                    'info',
                    $message
                );
            }
//            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @return Response
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

        dump($form);
        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $userName = $formData->getName();
            $userSurname = $formData->getSurname();
            $userEmail = $formData->getEmail();
            $userPass1 = $formData->getPassword();
            $userPass2 = $formData->getPassword2();

            $bool = $this->formValidationService
                ->name($userName)
                ->surname($userSurname)
                ->email($userEmail)
                ->passwordLength($userPass1)
                ->passwordChars($userPass1)
                ->passwordMatch($userPass1, $userPass2)
                ->validate();

            $message = $this->formValidationService->getMessage();

            if ($bool) {
                $temp = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);
                if (empty($temp)) {
                    $user->setPassword($this->passwordEncoder->encodePassword($user, $userPass1));
                    $this->getDoctrine()->getManager()->flush();
                    $message = "Používateľ bol upravený úspešne.";
                    return $this->redirectToRoute('user_index');
                } else {
                    $message = "Email sa už používa! Zadajte iný prosím.";
                }
            }

            if (!empty($message)) {
                $this->addFlash(
                    'info',
                    $message
                );
            }
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="user_delete", methods={"DELETE"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'info',
                'Používateľ bol vymazaný úspešne!'
            );

        }
        return $this->redirectToRoute('user_index');
    }

    public function getTop3Earners()
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();



    }

    public function getTop3Spenders()
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
    }
}
