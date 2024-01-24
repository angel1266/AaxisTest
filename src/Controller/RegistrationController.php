<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormTypes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
class RegistrationController extends AbstractController
{
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFormTypes::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
           
              $user = $form->getData();

                // Encode the user's password
                $hashedPassword = $this->passwordEncoder->hashPassword(
                $user,
                $form->get('password')->getData()
                );

                $user->setPassword($hashedPassword );
                $roles=array("ROLE_USER","ROLE_EDITOR");
            
            try{
                $entityManager->persist($user);
                $entityManager->flush();
           } catch (\Exception $e) {
                return $this->render('register/index.html.twig', [
                    'error' => ["Usuario ya existe"],
                    'registrationForm' => $form->createView(),
                ]);
                
           }
           


            // do anything else you need here, like send an email
            $this->addFlash('success', '¡Registro exitoso!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'error' => [],
            'registrationForm' => $form->createView(),
        ]);
    }
}