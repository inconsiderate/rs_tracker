<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class GamesController extends AbstractController
{

    private array $words = ["MAGIC", "LEVEL", "MANA", "STATS", "ITEMS", "FORCE", "POWER", "ENERGY", "HEALTH", "BOSS", "GEMS", "GRIND", "BOOST", "LORE", "BOOST", "FOES", "CHARM", "SOUL", "VEINS", "CLIMB", "CLASH", "SNEAK", "KNIGHT", "BLOOD", "FAITH"];
    private string $wordFile = __DIR__ . '/../../var/used_words.json';

    private function getUsedWords(): array
    {
        if (!file_exists($this->wordFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->wordFile), true) ?? [];
    }

    private function saveUsedWord(string $word): void
    {
        $usedWords = $this->getUsedWords();
        $usedWords[] = $word;
        file_put_contents($this->wordFile, json_encode($usedWords));
    }

    #[Route('/wordle', name: 'wordle_game')]
    public function index(SessionInterface $session): JsonResponse
    {
        $today = (new \DateTime())->format('Y-m-d');
        $wordleDate = $session->get('wordle_date');
        $solved = $session->get('solved', false);
        $guesses = $session->get('guesses', []);

        // If it's a new day, reset everything
        if ($wordleDate !== $today) {
            $usedWords = $this->getUsedWords();
            $availableWords = array_diff($this->words, $usedWords);
    
            if (empty($availableWords)) {
                return $this->json(['error' => 'No words available.'], 400);
            }
    
            $wordKey = array_rand($availableWords);
            $word = $availableWords[$wordKey];
            $this->saveUsedWord($word);
    
            $session->set('word', $word);
            $session->set('wordle_date', $today);
            $session->set('guesses', []);
            $session->remove('solved'); // Reset solved state for new word
    
            return $this->json([
                'status' => 'ready',
                'wordLength' => strlen($word)
            ]);
        }
    
        $word = $session->get('word');
        $guessCount = count($guesses);
    
        // If the puzzle was solved or all guesses were used
        if ($solved || $guessCount >= 6) {
            return $this->json([
                'status' => 'done',
                'gameOver' => true,
                'word' => $word,
                'message' => $solved ? "You already solved today's puzzle!" : "Game over! The word was: " . $word
            ]);
        }
    
        // If the game is still in progress
        return $this->json([
            'status' => 'ongoing',
            'wordLength' => strlen($word),
            'remainingGuesses' => 6 - $guessCount
        ]);
    }
    

    #[Route('/wordle/guess', name: 'wordle_guess', methods: ['POST'])]
    public function guess(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $guess = strtoupper($data['guess'] ?? '');
        $word = $session->get('word');
    
        if (!$word) {
            return $this->json(['error' => 'Game not initialized.'], 400);
        }
    
        if (strlen($guess) !== strlen($word)) {
            return $this->json(['error' => 'Invalid guess. Must be ' . strlen($word) . ' letters.'], 400);
        }
    
        $guesses = $session->get('guesses', []);
    
        $result = [];
        for ($i = 0; $i < strlen($word); $i++) {
            if ($guess[$i] === $word[$i]) {
                $result[] = ['letter' => $guess[$i], 'status' => 'correct'];
            } elseif (str_contains($word, $guess[$i])) {
                $result[] = ['letter' => $guess[$i], 'status' => 'present'];
            } else {
                $result[] = ['letter' => $guess[$i], 'status' => 'absent'];
            }
        }
    
        $guesses[] = $result;
        $session->set('guesses', $guesses);
    
        // Check if the puzzle is solved
        if ($guess === $word) {
            $session->set('solved', true);
            return $this->json([
                'result' => $result,
                'gameOver' => true,
                'wordLength' => strlen($word),
                'message' => 'Congratulations! You solved today\'s puzzle.'
            ]);
        }
    
        // Check if max guesses are reached
        if (count($guesses) >= 6) {
            return $this->json([
                'result' => $result,
                'gameOver' => true,
                'message' => 'Game over! The correct word was: ' . $word
            ]);
        }
    
        return $this->json(['result' => $result, 'gameOver' => false]);
    }
    
    #[Route('/games', name: 'wordle_play')]
    public function play(SessionInterface $session): Response
    {
        $today = (new \DateTime())->format('Y-m-d');
    
        // Check if it's a new day and reset if needed
        if ($session->get('wordle_date') !== $today) {
            $session->set('wordle_date', $today);
            $session->remove('solved'); 
            $session->remove('previousGuesses'); 
        }
    
        // Determine game status
        $status = $session->get('solved') ? 'solved' : 'ready';
        $solvedMessage = $session->get('solved') ? 'You already solved today\'s puzzle!' : '';
    
        return $this->render('games/wordle.html.twig', [
            'message' => $solvedMessage,
            'status' => $status,
        ]);
    }
}
