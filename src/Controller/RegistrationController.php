<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
		public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response {
			$url_code = $request->get('id');
			$iv = $this->decrypt($_ENV["KEY"], $url_code);

			$user = $doctrine->getRepository(User::class)->findOneBy(['code' => $iv]);

			$user_Id = $user->getId();
			$user_IdentificationNumber = $user->getIdentificationNumber();
			$user_Code = $user->getCode();
			$user->setIdentificationNumber("");
			$user->setCode("");
			$user->setUsername("");

			$user_name = $user->getName();
			$user_last_name = $user->getLastName();
			$usernames = [
				$user_name . "." . $user_last_name,
				$user_last_name . "." . $user_name,
				mb_substr($user_name, 0, 1, 'UTF-8') . "." . $user_last_name,
				mb_substr($user_last_name, 0, 1, 'UTF-8') . "." . $user_name,
				mb_substr($user_name, 0, -2, 'UTF-8') . "." . mb_substr($user_last_name, 0, -2, 'UTF-8'),
				mb_substr($user_last_name, 0, -2, 'UTF-8') . "." . mb_substr($user_name, 0, -2, 'UTF-8'),
			];

			print "<pre>" . print_r("DECODE: " . $iv, true) . "</pre>" . PHP_EOL;
			echo "<pre>" . print_r("USER: " . $user, true) . "</pre>" . PHP_EOL;

			$account = new Account();
			$form = $this->createForm(RegistrationFormType::class, $user);
			$form->handleRequest($request);

			$data = $form->getData();
			//dd($data);
			//dd($data->getUsername());
			$user->setUsername($data->getUsername());
			//$form->get('username')->submit('Fabien');

			//Verificar identificationNumber and code
			//$user->setIdentificationNumber("");
			//$user->setCode("");

			if ($form->isSubmitted() && $form->isValid()) {
				$user->setIsVerified(true);
				// encode the plain password
				$user->setPassword(
					$userPasswordHasher->hashPassword(
						$user,
						$form->get('plainPassword')->getData()
					)
				);

				$account->setEmail($data->getUsername() . "@disaa.com");
				$account->setUser($user);

				$entityManager->persist($user);
				$entityManager->persist($account);
				$entityManager->flush();
				// generate a signed url and email it to the user
				/*$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
						(new TemplatedEmail())
								->from(new Address('transferencia.email.20@gmail.com', 'DISAA MAIL'))
								->to($user->getUsername())
								->subject('Please Confirm your Email')
								->htmlTemplate('registration/confirmation_email.html.twig')
				);*/
				// do anything else you need here, like send an email

				$this->addFlash('success', "La cuenta ha sido creada");
				return $this->redirectToRoute('app_user_edit', ['id' => $user_Id]);
			}

			return $this->render('registration/register.html.twig', [
				'registrationForm' => $form->createView(),
				'usernames' => $usernames,
			]);
		}

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

		function encrypt($key, $payload) {
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			$encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
			return base64_encode($encrypted . '::' . $iv);
		}

		function decrypt($key, $garble) {
			list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
			return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
		}
}
