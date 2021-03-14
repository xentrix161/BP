<?php

namespace App\Controller;

use App\Services\FormValidationService;
use App\Services\RoleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegisterController extends AbstractController
{
    private $roleTypes = [];
    private $passwordEncoder;
    private $formValidationService;
    private $roleService;


    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        FormValidationService $formValidationService,
        RoleService $roleService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->formValidationService = $formValidationService;
        $this->roleService = $roleService;

        $user = $this->roleService::ROLE_USER;
        $seller = $this->roleService::ROLE_SELLER;

        $this->roleTypes = [["['${$user}']", 'Nákupca'], ["['${$seller}']", 'Predajca']];
    }

    /**
     * @Route("/registerForm", name="register-form")
     * @param Request $request
     * @return Response
     */
    public function registerU(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(RegisterType::class, $user, [
            'action' => $this->generateUrl('register-form')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //ulozenie do DB
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
                    $user->setRole([$this->roleService::ROLE_NONE]);
                    $em->persist($user);
                    $em->flush();
                    $message = "Používateľ bol zaregistrovaný";

                    if (!empty($message)) {
                        $this->addFlash('info', $message);
                    }

                    return $this->redirectToRoute('app_login');
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

        return $this->render('form/index.html.twig', [
            'register_form' => $form->createView(),
            'user_roles' => $this->roleTypes
        ]);
    }
}