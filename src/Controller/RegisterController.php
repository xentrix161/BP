<?php

namespace App\Controller;

use App\Services\FormValidationService;
use App\Services\RoleService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegisterController extends AbstractController
{
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
    }

    /**
     * Zaregistruje nového používateľa, overí, či je heslo dostatočne bezpečné a pošle aktivačný email.
     * @Route("/registerForm", name="register-form")
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    public function registerNewUser(Request $request, MailerInterface $mailer)
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
                    $token = $this->generateActivateToken();

                    $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                    $user->setRole([$this->roleService::ROLE_NONE]);
                    $user->setExpense(0);
                    $user->setEarning(0);
                    $user->setRating(0);
                    $user->setToken($token);
                    $user->setTokenDate(new \DateTime());
                    $user->setActivate(0);
                    $em->persist($user);
                    $em->flush();
                    $this->sendEmail($mailer, $userEmail, $token);

                    $message = "Používateľ bol zaregistrovaný. Na Váš email bol zaslaný aktivačný email.";

                    if (!empty($message)) {
                        $this->addFlash('info', $message);
                    }

                    return $this->redirectToRoute('register-form');
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
        ]);
    }


    /**
     * @Route("/activate-account/{token}", name="activate_account")     *
     * @param $token
     * @return RedirectResponse|Response
     */
    public function accountActivation($token)
    {
        $em = $this->getDoctrine()->getManager();
        $tempUser = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['token' => $token]);

        if (empty($tempUser)) {
            return $this->redirectToRoute('register-form');
        }

        $actualDate = new \DateTime();

        //vydumpovat request, zobrat token, najast v DB podla tokenu, zobrat email, poslat nan novy token datum a link


        if ($tempUser->getTokenDate()->modify('+ 7 days') > $actualDate
            && $token == $tempUser->getToken()) {
            $status = 'success';

            $tempUser->setToken('activated');
            $tempUser->setActivate(true);
            $em->persist($tempUser);
            $em->flush();

            return $this->render('activate_acc.html.twig', [
                'status' => $status,
                'token' => $token
            ]);
        }

        if ($tempUser->getTokenDate()->modify('+ 7 days') < $actualDate) {
            $status = 'tokExp';
        } elseif ($tempUser->getTokenDate()->modify('+ 7 days') > $actualDate
            && $tempUser->getActivate() == false) {
            $status = 'notActivated';
        }

        return $this->render('activate_acc.html.twig', [
            'status' => $status,
            'token' => $token
//            'email' => $tempUser->getEmail(),
        ]);
    }

    /**
     * Vytvorí aktivačný email.
     * @param MailerInterface $mailer
     * @param $email
     * @param $token
     */
    public function sendEmail(MailerInterface $mailer, $email, $token)
    {
        $email = (new TemplatedEmail())
            ->from('filipkosmel@gmail.com')
            ->to($email)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Registrácia - aktivácia účtu')
            ->htmlTemplate('email/register.html.twig')
            ->context([
                'token' => $token
            ]);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
    }

    /**
     * Vytvorí nový aktivačný email.
     *
     * @Route("/resend-activate-email/{token}", name="resend_activate_mail")
     * @param MailerInterface $mailer
     * @param $token
     * @return RedirectResponse
     */
    public function resendEmail(MailerInterface $mailer, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $userForEmail = $this->getDoctrine()->getRepository(User::class)
            ->findOneBy(['token' => $token]);

        if (empty($userForEmail)) {
            return $this->redirectToRoute('activate_account');
        }

        $userEmail = $userForEmail->getEmail();
        $newToken = md5(uniqid());


        $email = (new TemplatedEmail())
            ->from('filipkosmel@gmail.com')
            ->to($userEmail)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Registrácia - aktivácia účtu')
            ->htmlTemplate('email/register.html.twig')
            ->context([
                'token' => $newToken
            ]);

        try {
            $userForEmail->setToken($newToken);
            $userForEmail->setTokenDate(new \DateTime());
            $em->persist($userForEmail);
            $em->flush();

            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
        return $this->redirectToRoute('app_login');
    }

    /**
     * Vygeneruje unikátny token.
     * @return string
     */
    private function generateActivateToken()
    {
        return md5(uniqid());
    }
}