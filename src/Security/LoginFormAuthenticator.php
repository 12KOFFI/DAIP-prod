<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $roles = method_exists($token, 'getRoleNames') ? $token->getRoleNames() : $token->getUser()->getRoles();
        $isCandidat = in_array('ROLE_CANDIDAT', (array) $roles, true);
       $isUserCentre = in_array('ROLE_USER_CENTRE', (array) $roles, true);
      

        // Si une URL cible existe (ex: clic sur "Postule" puis login), y retourner en priorité
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            if ($isCandidat) {
                $path = parse_url($targetPath, PHP_URL_PATH);
                if ($path === '/' || $path === '/dashboard') {
                    return new RedirectResponse($this->urlGenerator->generate('app_candidature_index'));
                }
            }

             if ($isUserCentre) {
                $path = parse_url($targetPath, PHP_URL_PATH);
                if ($path === '/' || $path === '/dashboard') {
                    return new RedirectResponse($this->urlGenerator->generate('app_dashboard_centre'));
                }
            }

            return new RedirectResponse($targetPath);
        }

        // Sinon, redirection par défaut pour les candidats
        if ($isCandidat) {
            return new RedirectResponse($this->urlGenerator->generate('app_candidature_index'));
        }

          if ($isUserCentre) {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard_centre'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
