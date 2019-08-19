<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Import;

use DateTimeZone;
use Exception;
use Symfony\Component\Console\{
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface
};
use tiFy\Console\Command as BaseCommand;
use tiFy\Plugins\Transaction\Contracts\{
    ImportCommand,
    ImportFactory,
    ImportManager
};
use tiFy\Support\{
    DateTime,
    ParamsBag
};

abstract class Command extends BaseCommand implements ImportCommand
{
    /**
     * Instance des messages de notification.
     * @var ParamsBag
     */
    private $messagesBag;

    /**
     * Instance du controleur d'import.
     * @var ImportManager
     */
    private $manager;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->messagesBag = ParamsBag::createFromAttrs([
            'start'               => [
                '====================================',
                __('Import des élèments.', 'tify'),
                '====================================',
            ],
            'start_datetime'      => __('Démarrage des opérations : %s', 'tify'),
            'total_count'         => [
                __('Total des élèments à traiter : %d', 'tify'),
                '',
            ],
            'item_before'         => [
                '------------------------------------',
                __('Import de l\'élèment %d/%d', 'tify'),
                '------------------------------------'
            ],
            'item_start_datetime' => [
                __('Démarrage de l\'opération : %s', 'tify'),
                ''
            ],
            'item_end_datetime'   => [
                '',
                __('Fin de l\'opération : %s', 'tify'),
            ],
            'item_after'          => [
                '',
            ],
            'end_datetime'        => [
                '',
                'Import des éléments terminé.',
                __('Fin des opérations : %s', 'tify')
            ],
            'end'                 => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
        ]);

        $this
            ->addOption(
                'offset', null, InputOption::VALUE_OPTIONAL, __('Numéro d\'enregistrement de démarrage', 'theme'), 0
            )
            ->addOption(
                'length', null, InputOption::VALUE_OPTIONAL, __('Nombre d\'enregistrements à traiter', 'theme'), null
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->onStart();

        $output->writeln($this->message('start'));

        $output->writeln(array_map(function ($message) {
            return sprintf($message, $this->getDatetime());
        }, $this->messageBag('start_datetime')));

        $items  = $this->getManager()->collect()->slice((int)$input->getOption('offset'), $input->getOption('length'));
        $count  = $items->count();

        $output->writeln(array_map(function ($message) use ($count) {
            return sprintf($message, $count);
        }, $this->messageBag('total_count')));

        $results = [];
        foreach ($items as $i => $item) {
            $this->onItemStart($item, $i);

            $output->writeln(array_map(function ($message) use ($i, $count) {
                return sprintf($message, $i, $count);
            }, $this->messageBag('item_before')));

            $output->writeln(array_map(function ($message) {
                return sprintf($message, $this->getDatetime());
            }, $this->messageBag('item_start_datetime')));

            $results[] = $res = $this->getManager()->executeItem($item);

            foreach ($res['data']['notices'] as $type => $notices) {
                foreach ($notices as $id => $notice) {
                    $output->writeln($notice['message']);
                }
            }

            $output->writeln(array_map(function ($message) {
                return sprintf($message, $this->getDatetime());
            }, $this->messageBag('item_end_datetime')));

            $output->writeln(array_map(function ($message) use ($i, $count) {
                return sprintf($message, $i, $count);
            }, $this->messageBag('item_after')));

            $this->onItemEnd($item, $i);
        }

        $output->writeln(array_map(function ($message) {
            return sprintf($message, $this->getDatetime());
        }, $this->messageBag('end_datetime')));

        $output->writeln($this->message('end'));

        $this->onEnd($results);

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getDatetime(?string $time = null, ?DateTimeZone $tz = null): string
    {
        try{
            return (new DateTime($time, $tz))->toDateTimeString();
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }

    /**
     * @inheritDoc
     */
    public function getManager(): ?ImportManager
    {
        return $this->manager;
    }

    /**
     * @inheritDoc
     */
    public function messageBag($key = null, $default = '')
    {
        if (is_string($key)) {
            return $this->messagesBag->get($key, $default);
        } elseif (is_array($key)) {
            return $this->messagesBag->set($key);
        } else {
            return $this->messagesBag;
        }
    }

    /**
     * @inheritDoc
     */
    public function onEnd(array $results): void {}

    /**
     * @inheritDoc
     */
    public function onItemEnd(ImportFactory $item, $key): void {}

    /**
     * @inheritDoc
     */
    public function onItemStart(ImportFactory $item, $key): void {}

    /**
     * @inheritDoc
     */
    public function onStart(): void {}
}