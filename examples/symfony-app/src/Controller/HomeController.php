<?php

declare(strict_types=1);

namespace App\Controller;

use App\Portal\SummarizeSpell;
use App\Portal\Summary;
use Sourceability\Portal\Portal;
use Sourceability\Portal\Spell\StaticSpell;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function index(Request $request, Portal $portal, SummarizeSpell $summarizeSpell): Response
    {
        $form = $this->createFormBuilder()
            ->add('content', TextareaType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $summary = null;
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();

            // is_string is required because SummarizeSpell TInput is string
            if (is_string($content)) {
                $summary = $portal->cast($summarizeSpell, $content)->getValue();
            }
        }

        // renderHome exists to make sure phpstan sees $summary as null|Summary
        return $this->renderHome($form, $summary);
    }

    #[Route('/synonyms')]
    public function synonyms(Portal $portal): void
    {
        $spell = new StaticSpell(
            schema: [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                ],
            ],
            prompt: 'Synonyms of {{ input }}'
        );

        /** @var callable(string): array<string> $generateSynonyms */
        $generateSynonyms = $portal->callableFromSpell($spell);

        dd($generateSynonyms('car'));
    }

    private function renderHome(FormInterface $form, ?Summary $summary): Response
    {
        return $this->render('home.html.twig', [
            'form' => $form,
            'summary' => $summary,
        ]);
    }
}
