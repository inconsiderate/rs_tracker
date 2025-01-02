<?php

namespace App\Controller;

use Patreon\API;
use Patreon\OAuth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SecurityController extends AbstractController
{
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

    private HttpClientInterface $httpClient;
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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

            $patronStatus = $this->confirmPatreonMembership($accessToken);

            $user->setPreferences([
                'patreonAccessToken' => $accessToken,
                'patreonRefreshToken' => $refreshToken,
                'patreonTokenExpiry' => $expiresIn,
                'patreonSubscriber' => $patronStatus,
            ]);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('notice', 'Access verified.');

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->redirectToRoute('app_profile');
    }

    private function confirmPatreonMembership($accessToken)
    {
        $oauth_client = new OAuth($_ENV['PATREON_CLIENT_ID'], $_ENV['PATREON_CLIENT_SECRET']);
        $tokens = $oauth_client->get_tokens($_GET['code'], $_ENV['PATREON_REDIRECT_URI']);

        $api_client = new API($accessToken);
        $patron_response = $api_client->fetch_user();
        
        return $patron_response['included'][0]['attributes']['patron_status'] ?? null;
    }
}
