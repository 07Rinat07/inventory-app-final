<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Контроллер для аутентификации пользователей.
 */
final class SecurityController extends AbstractController
{
    /**
     * Отображает форму входа и обрабатывает ошибки входа.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Выход из системы.
     *
     * Метод перехватывается фаерволом Symfony, поэтому тело метода никогда не выполняется.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony перехватит
        throw new \LogicException('Logout handled by firewall.');
    }
}
