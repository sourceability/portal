<?php

declare(strict_types=1);

namespace App\Controller;

use App\Portal\SummarizeSpell;
use App\Portal\Summary;
use Sourceability\Portal\Portal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

            if (is_string($content)) {
                $summary = $this->getSummary($portal, $summarizeSpell, $content);
            }
        }

        return $this->render('home.html.twig', [
            'form' => $form,
            'summary' => $summary,
        ]);
    }

    // Make sure phpstan validates generics
    private function getSummary(Portal $portal, SummarizeSpell $spell, string $content): Summary
    {
        return $portal->cast($spell, $content)->value;
    }
}
