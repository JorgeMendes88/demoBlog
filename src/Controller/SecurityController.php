<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        dump($request);

        $user = new User;

        $formRegistration = $this->createForm(RegistrationType::class, $user);

        $formRegistration->handleRequest($request);

        dump($user);

        if($formRegistration->isSubmitted() && $formRegistration->isValid())
        {
            // Encodage du mot de passe
            $hash = $encoder->encodePassword($user, $user->getPassword());

            dump($hash);

            // on renvoie le mot de passe dans l'entité donc la BDD
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            // On stock en session un message de validation après la validation de l'inscription
            $this->addFlash('success', "Felicitations ! Vous êtes maintenant inscrit, vous pouvez dès à present vous connecter !!");

            // on Redirige l'internaute vers la connexion après la validation de l'inscription
            return $this->redirectToRoute('security_login');
        }        

        return $this->render('security/registration.html.twig', [
            'formRegistration' => $formRegistration->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupération du message d'erreur en cas de mauvaise connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier username (email) saisi par l'internaute en cas d'erreur de connexion
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'lastEmail' => $lastEmail
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        // cette méthode ne retourne rien, il nous suffit d'avoir une route pour se deconnecter
    }
}
