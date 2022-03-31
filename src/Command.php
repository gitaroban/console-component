<?php
declare(strict_types=1);

namespace Gitaroban\ConsoleComponent;

use DateTime;
use DateTimeInterface;
use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class Command extends SymfonyCommand
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param callable        $closure
     *
     * @return int
     */
    protected function traitement(InputInterface $input, OutputInterface $output, callable $closure): int
    {
        $stopwatchName = 'execution';
        $io = new SymfonyStyle($input, $output);
        $stopwatch = new Stopwatch();
        $stopwatch->start($stopwatchName);
        $dateDeDebut = new DateTime();

        try {
            $closure();

            $stopwatch->stop($stopwatchName);
            $stopwatchEvent = $stopwatch->getEvent($stopwatchName);

            $io->writeln($this->messageDeSucces('Terminé', $dateDeDebut, $stopwatchEvent));
            return parent::SUCCESS;
        } catch (Exception $exception) {
            $io->error($this->messageDErreur($dateDeDebut, $exception));
            return parent::FAILURE;
        }
    }

    /**
     * @param string            $message
     * @param DateTimeInterface $dateDeDebut
     * @param StopwatchEvent    $stopwatchEvent
     *
     * @return string
     */
    protected function messageDeSucces(
        string            $message,
        DateTimeInterface $dateDeDebut,
        StopwatchEvent    $stopwatchEvent
    ): string {
        return sprintf(
            '%s - %s en <comment>%s</comment> - Mémoire consommée <comment>%s</comment>.',
            $dateDeDebut->format('d/m/Y H:i:s'),
            $message,
            $this->tempsPasse($stopwatchEvent->getDuration()),
            Helper::formatMemory($stopwatchEvent->getMemory())
        );
    }

    /**
     * @param DateTimeInterface $dateDeDebut
     * @param Exception         $exception
     *
     * @return string
     */
    protected function messageDErreur(DateTimeInterface $dateDeDebut, Exception $exception): string
    {
        return sprintf(
            '%s - %s',
            $dateDeDebut->format('d/m/Y H:i:s'),
            $exception->getMessage()
        );
    }

    /**
     * @param int $millisecondes
     *
     * @return string
     */
    protected function tempsPasse(int $millisecondes): string
    {
        if ($millisecondes < 1000) {
            return $millisecondes . ' ms';
        }
        return Helper::formatTime($millisecondes / 1000);
    }
}
