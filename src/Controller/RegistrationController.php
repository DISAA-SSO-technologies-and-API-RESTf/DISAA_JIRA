<?php

namespace App\Controller;

use App\Subscribers\LocalSubscriber;
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
use App\Subscribers;

#[Route('/{_locale}')]
class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
		public static $no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		public static $permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");


	public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
		public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response {
			$url_code = $request->get('id');
			$iv = $this->decrypt($_ENV["KEY"], $url_code);
			//dd($request->getQueryString());
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
				//str_replace: elimina los espacios en blanco entre las palabras
				//mb_substr: corta las palabras
				str_replace(RegistrationController::$no_permitidas, RegistrationController::$permitidas, str_replace(' ', '', $user_name . "_" . $user_last_name)),
				str_replace(RegistrationController::$no_permitidas, RegistrationController::$permitidas, str_replace(' ', '', $user_last_name . "_" . $user_name)),
				str_replace(RegistrationController::$no_permitidas, RegistrationController::$permitidas, str_replace(' ', '', mb_substr($user_name, 0, 1, 'UTF-8') . "_" . $user_last_name)),
				str_replace(RegistrationController::$no_permitidas, RegistrationController::$permitidas, str_replace(' ', '', mb_substr($user_last_name, 0, 1, 'UTF-8') . "_" . $user_name)),
				str_replace(RegistrationController::$no_permitidas, RegistrationController::$permitidas, str_replace(' ', '', mb_substr($user_name, 0, -2, 'UTF-8') . "_" . mb_substr($user_last_name, 0, -2, 'UTF-8'))),
				str_replace(RegistrationController::$no_permitidas, RegistrationController::$permitidas, str_replace(' ', '', mb_substr($user_last_name, 0, -2, 'UTF-8') . "_" . mb_substr($user_name, 0, -2, 'UTF-8'))),
			];

			/*print "<pre>" . print_r("URL_CODE: " . $url_code, true) . "</pre>" . PHP_EOL;
			print "<pre>" . print_r("DECODE: " . $iv, true) . "</pre>" . PHP_EOL;
			echo "<pre>" . print_r("USER: " . $user, true) . "</pre>" . PHP_EOL;*/

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
				$user->setLocale('en_US');
				// encode the plain password
				$user->setPassword(
					$userPasswordHasher->hashPassword(
						$user,
						$form->get('plainPassword')->getData()
					)
				);

				if (strcmp($user_IdentificationNumber, $data->getIdentificationNumber()) == 0) {
					$account->setEmail($data->getUsername() . "@disaa.com");
					$account->setUser($user);

					$entityManager->persist($user);
					$entityManager->persist($account);
					$entityManager->flush();

					// generate a signed url and email it to the user
					$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
							(new TemplatedEmail())
									->from(new Address('transferencia.email.20@gmail.com', 'DISAA MAIL'))
									->to($user->getUsername())
									->subject('Please Confirm your Email')
									->htmlTemplate('registration/confirmation_email.html.twig')
					);
					// do anything else you need here, like send an email

					$this->addFlash('success', "La cuenta ha sido creada satisfactoriamente, ingresa con tus nuevas credenciales.");
					//return $this->redirectToRoute('app_user_edit', ['id' => $user_Id]);
					return $this->redirectToRoute('app_login');
				}
				else {
					$this->addFlash('error', "Los datos ingresados para crear la cuenta no son correctos");
					return $this->render('registration/register.html.twig', [
						'registrationForm' => $form->createView(),
						'usernames' => $usernames,
					]);
				}
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

	#[Route('/changelanguaje', name: 'change_language')]
	public function changeLanguage(Request $request){

		//$Sub= new LocalSubscriber('en_US');
		//dd($Sub->setDefaultLocale('en_US'));
		//$request->setDefaultLocale('fr_FR');
		//$request->setDefaultLocale('en_US');
		//var_dump($request->attributes->get('_locale'));
		dd($request->getLocale());
		return $request->getLocale();
	}

	public function generateCodeForURL(){
		echo  print_r("enCODE: "."1212qw ". $this->encrypt($_ENV["KEY"], '1212qw'), true).PHP_EOL;
		echo  print_r("enCODE: "."qwe444 ". $this->encrypt($_ENV["KEY"], 'qwe444'), true).PHP_EOL;
		echo  print_r("enCODE: "."121333 ". $this->encrypt($_ENV["KEY"], '121333'), true).PHP_EOL;
		echo  print_r("enCODE: "."sde343 ". $this->encrypt($_ENV["KEY"], 'sde343'), true).PHP_EOL;
		echo  print_r("enCODE: "."asqwqw ". $this->encrypt($_ENV["KEY"], 'asqwqw'), true).PHP_EOL;
		echo  print_r("enCODE: "."67hnhn ". $this->encrypt($_ENV["KEY"], '67hnhn'), true).PHP_EOL;
		echo  print_r("enCODE: "."98hjhj ". $this->encrypt($_ENV["KEY"], '98hjhj'), true).PHP_EOL;
		echo  print_r("enCODE: "."67hjrr ". $this->encrypt($_ENV["KEY"], '67hjrr'), true).PHP_EOL;
		echo  print_r("enCODE: "."mnvbdd ". $this->encrypt($_ENV["KEY"], 'mnvbdd'), true).PHP_EOL;
		echo  print_r("enCODE: "."2rsdcn ". $this->encrypt($_ENV["KEY"], '2rsdcn'), true).PHP_EOL;
	}

}
