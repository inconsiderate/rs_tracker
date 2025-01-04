<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\PatreonService;

class SecurityController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private $patreonService;

    public function __construct(HttpClientInterface $httpClient, PatreonService $patreonService)
    {
        $this->httpClient = $httpClient;
        $this->patreonService = $patreonService;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    #[Route('/patreon', name: 'app_patreon')]
    public function app_patreon(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        $code = $request->get('code');

        if (!$code) {
            return new JsonResponse(['error' => 'Authorization code is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $clientId = $_ENV['PATREON_CLIENT_ID'];
        $clientSecret = $_ENV['PATREON_CLIENT_SECRET'];
        $redirectUri = $_ENV['PATREON_REDIRECT_URI'];

        try {
            // Exchange the authorization code for an access token
            $httpClient = $this->httpClient; // Ensure this is injected
            $response = $httpClient->request('POST', 'https://www.patreon.com/api/oauth2/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                ],
            ]);
    
            $data = $response->toArray();
    
            if (!isset($data['access_token'])) {
                return new JsonResponse(['error' => 'Failed to exchange token'], JsonResponse::HTTP_BAD_REQUEST);
            }
    
            // Store the tokens and user-specific information to the user
            $accessToken = $data['access_token'];
            $refreshToken = $data['refresh_token'];
            $expiresIn = $data['expires_in'];

            $patronData = $this->patreonService->fetchPatreonData($accessToken);
            $user->setPatreonData($patronData);
            $user->setPatreonId($patronData['id']);
            $user->setPatreonData([
                'patreonAccessToken' => $accessToken,
                'patreonRefreshToken' => $refreshToken,
                'patreonTokenExpiry' => $expiresIn
            ]);

            $entityManager->persist($user);
            $entityManager->flush();


            $this->addFlash('notice', 'Access verified.');

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->redirectToRoute('app_profile');
    }
}
