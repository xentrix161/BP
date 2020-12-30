<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    //HOTOVO
    /**
     * @Route("/registerForm", name="register-form")
     */
    public function registerU(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(RegisterType::class, $user, [
            'action' => $this->generateUrl('register-form')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //ulozenie do DB

//            $user->setPassword(
//                $passwordEncoder
//            );

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'info',
                'Užívateľ bol zaregistrovaný!'
            );
        }

        return $this->render('form/index.html.twig', [
            'register_form' => $form->createView()
        ]);
    }

    /**
     * @Route("/formCout", name="formCout")
     */
    public function showUserTable(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        dump($users);
        exit;
    }


//    /**
//    //     * @Route("/formDel", name="formDel")
//    //     */
//    public function removeU(Request $request)
//    {
//        $user = new User();
//        $em = $this->getDoctrine()->getManager();
//        $form = $this->createForm(RegisterType::class, $user, [
//            'action' => $this->generateUrl('formDel')
//        ]);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $userToRemove = $em->getRepository(User::class)->findOneBy([
//                'email' => $user->getEmail()
//            ]);
//            $em->remove($userToRemove);
//            $em->flush();
//        }
//
//        return $this->render('form/index.html.twig', [
//            'post_form' => $form->createView()
//        ]);
//    }
//
//    /**
//     * @Route("/formUpd", name="formUpd")
//     */
//    public function updateU(Request $request)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $userToUpadate = $em->getRepository(User::class)->findOneBy([
//            'email' => 'update@gmail.com'
//        ]);
//
//        $form = $this->createForm(RegisterType::class, $userToUpadate, [
//            'action' => $this->generateUrl('formUpd')
//        ]);
//        $form->handleRequest($request);
//
//        $userToUpadate->setName("updatovany");
//        $em->persist($userToUpadate);
//        $em->flush();
//
//        return $this->render('form/index.html.twig', [
//            'post_form' => $form->createView()
//        ]);
//    }
}