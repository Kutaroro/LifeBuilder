<?php 

namespace App\Command;

use App\Repository\ModStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:clean-sanctions', description: 'Remet à zéro les sanctions expirées')]
class CleanSanctionsCommand extends Command
{
    private $em;
    private $statusRepository;

    public function __construct(EntityManagerInterface $em, ModStatusRepository $statusRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->statusRepository = $statusRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // On récupère tous les statuts dont la date est dépassée et qui ont encore une sanction
        $expiredStatuses = $this->statusRepository->findExpiredSanctions(new \DateTime());

        foreach ($expiredStatuses as $status) {
            $status->setStatus('Pas de sanction en cours');
            $status->setType("");
        }

        $this->em->flush();

        $output->writeln(count($expiredStatuses) . ' sanctions ont été nettoyées !');

        return Command::SUCCESS;
    }
}