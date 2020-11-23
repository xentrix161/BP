<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
//HOMEPAGE
    /**
     * @Route("/homepage")
     * @return Response
     */
    public function index(): Response
    {

        return $this->render('homepage.html.twig');
    }

//PRODUKTY
    /**
     * @Route(path="/produkt/{number}", name="article_moj")
     * @return Response
     */
    public function article($number): Response
    {
        return $this->render('article' . $number . '.html.twig');
    }

//LOGIN
    /**
     * @Route("/login")
     * @return Response
     */
    public function login(): Response
    {
        return $this->render('login.html.twig');
    }

//REGISTER
    /**
     * @Route("/register")
     * @return Response
     */
    public function register(): Response
    {
        return $this->render('register.html.twig');
    }

//ZABUDNUTE HESLO
    /**
     * @Route("/forgot")
     * @return Response
     */
    public function forgotPass(): Response
    {
        return $this->render('forgotPass.html.twig');
    }



//    /**
//     * @Route("/contact", name="page_contact")
//     */
//    public function contact(Request $request)
//    {
//        $defaultData = [''];
//        $form = $this->createFormBuilder($defaultData)
//            ->add('name', TextType::class)
//            ->add('surename', TextType::class)
//            ->add('email', EmailType::class)
//            ->add('password', TextType::class)
//            ->getForm();
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            // data is an array with "name", "email", and "message" keys
//            $data = $form->getData();
//            dump($data);
//        }
//
//        return $this->render('login.html.twig', [
//            'form' => $form
//        ]);
//    }

//
//    /**
//     * @Route("/message", name="message", methods="GET")
//     */
//    public function registerCon(Request $request): Response
//    {
//        $manager = $this->getDoctrine()->getManager('default');
//        $form = $request->get('form');
//
//        if (!empty($form)) {
//            $newUser = new User();
//            $newUser->setName();
//            $newUser->setSurename();
//            $newUser->setEmail();
//            $newUser->setPassword();
//            $manager->persist($newUser);
//            $manager->flush($newUser); //vlozi do tabulky
//            //$this->render(':message:index.html.twig');
//            $this->render('login.html.twig');
//        };
//        return $this->render('nepotrebne/register.html.twig');
//        exit;
//        //return $form;
//    }
//

//
//    /**
//     * @Route("/test/{id}")
//     * @return Response
//     */
//    public function test($id): Response
//    {
//        $manager = $this->getDoctrine()->getManager('default');
//        $userRepository = $manager->getRepository(User::class);
//        /** @var User $user */
//        $user = $userRepository->find($id);
////        VYTVORENIE A VLOZENIE
////        $newUser = new User();
////        $newUser->setName('');
////        $newUser->setSurenam('');
////        $newUser->setEmail('');
////        $newUser->setPassword('');
////        $manager->persist($newUser);
////        $manager->flush($newUser); //vlozi do tabulky
//
////        UPDATE
////        $user->setEmail('novymail');
////        $manager->flush()
//
////        DELETE
////        if (!$user) {
////            throw $this->createNotFoundException('No user found for id '.$id);
////        }
////        $manager->remove($user);
////        $manager->flush($user);
////        $manager->persist($user);
////
////        return $this->render('homepage.html.twig');
//        exit;
//    }
}