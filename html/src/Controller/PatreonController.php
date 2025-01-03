<?php

namespace App\Controller;

use Patreon\API;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PatreonService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class PatreonController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/webhook/patreon', name: 'patreon_webhook', methods: ['POST'])]
    public function handlePatreonWebhook(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!$payload) {
            return new JsonResponse(['error' => 'Invalid payload'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            // Verify the webhook signature (optional but recommended)
            $signature = $request->headers->get('X-Patreon-Signature');
            $this->logger->info("test patreon inside try");
            $this->verifyWebhookSignature($request->getContent(), $signature);

            // Process the event
            $eventType = $payload['type'] ?? null;

            if ($eventType === 'members:create' || $eventType === 'members:update') {
                $patronData = $payload['data'] ?? [];
                $includedData = $payload['included'][0]['attributes'] ?? [];

                $patreonId = $patronData['id'] ?? null;
                $patronStatus = $includedData['patron_status'] ?? null;
                $entitledCents = $includedData['currently_entitled_amount_cents'] ?? 0;

                $this->logger->info("patron update or create");
                $this->logger->info($patreonId);
                $this->logger->info($patronStatus);
                $this->logger->info($entitledCents);

                // Update user in the database
                $this->updateUserSubscription($patreonId, $patronStatus, $entitledCents);
            } elseif ($eventType === 'members:delete') {
                $patreonId = $payload['data']['id'] ?? null;

                $this->logger->info("patron delete");
                $this->logger->info($patreonId);
                // Mark user as unsubscribed
                $this->cancelUserSubscription($patreonId);
            }

            return new JsonResponse(['message' => 'Webhook handled successfully'], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function verifyWebhookSignature(string $rawPayload, ?string $signature): void
    {
        $secret = $_ENV['PATREON_WEBHOOK_SECRET'];
    
        if (!$secret) {
            throw new \RuntimeException('Patreon webhook secret is not set.');
        }
        $computedHash = hash_hmac('md5', $rawPayload, $secret);
        if (!hash_equals($computedHash, $signature)) {
            throw new \Exception('Invalid webhook signature');
        }
    }

    private function updateUserSubscription(string $patreonId, ?string $patronStatus, int $entitledCents): void
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['patreonId' => $patreonId]);

        if ($user) {
            $user->setPatreonData([
                'patron_status' => $patronStatus,
                'currently_entitled_amount_cents' => $entitledCents,
            ]);
            $entityManager->flush();
        }
    }

    private function cancelUserSubscription(string $patreonId): void
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['patreonId' => $patreonId]);

        if ($user) {
            $user->setPatreonData([
                'patron_status' => 'former_patron',
                'currently_entitled_amount_cents' => 0,
            ]);
            $entityManager->flush();
        }
    }
}