<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Utilisateur;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

// 🛠️ On ajoute explicitement la méthode à appeler ici :
#[AsEventListener(event: CheckPassportEvent::class, method: 'onCheckPassport')]
class LoginListener
{
    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof Utilisateur) {
            return;
        }

        $status = $user->getStatus();

        if ($status && $status->getStatus() !== 'Pas de sanction en cours') {
            $now = new \DateTime();
            if ($status->getStatus() === 'Bannissement définitif' || ($status->getDateFin() && $status->getDateFin() > $now)) {
                
                $dateTexte = $status->getDateFin() ? 'jusqu\'au ' . $status->getDateFin()->format('d/m/Y') : 'définitivement';
                
                throw new CustomUserMessageAuthenticationException(
                    // sprintf('Votre compte est suspendu (%s) %s. Motif : %s', 
                    sprintf('Votre compte est suspendu  %s. Motif : %s', 
                        // $status->getStatus(), 
                        $dateTexte,
                        $status->getDescription() ?? 'Non spécifié'
                    )
                );
            }
        }
    }
}