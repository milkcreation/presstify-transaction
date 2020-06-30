<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use tiFy\Plugins\Transaction\Contracts\{
    ImportCommand,
    ImportCommandStack,
    ImportManager as ImportManagerContract,
    ImportRecorder,
    Transaction
};
use tiFy\Support\{Arr, DateTime, Str};
use tiFy\Support\Proxy\{Database, Schema};
use Symfony\Component\Console\Command\Command as SfCommand;
use WP_Post;
use WP_Term;
use WP_User;

class ImportManager implements ImportManagerContract
{
    /**
     * Instances des commandes déclarées.
     * @var ImportCommand[]|array
     */
    protected $command = [];

    /**
     * Instances des jeux de commandes déclarés.
     * @var ImportCommandStack[]|array
     */
    protected $commandStack = [];

    /**
     * Instances des gestionnaire d'enregistrement déclarés.
     * @var ImportRecorder[]|array
     */
    protected $recorder = [];

    /**
     * Nom de qualification de la table d'enregistrement des donnés d'import
     * @var string
     */
    protected $table = 'tify_transaction_import';

    /**
     * Instance du gestionnaire de transaction.
     * @var Transaction
     */
    private $transaction;

    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('deleted_post', function ($post_id) {
            if ($res = $this->getFromObjectId((int)$post_id, 'wp:post')) {
                Database::table($this->table)->delete($res->id);
            }
        });

        add_action('delete_term', function ($term_id) {
            if ($res = $this->getFromObjectId((int)$term_id, 'wp:term')) {
                Database::table($this->table)->delete($res->id);
            }
        });

        add_action('deleted_user', function ($user_id) {
            if ($res = $this->getFromObjectId((int)$user_id, 'wp:user')) {
                Database::table($this->table)->delete($res->id);
            }
        });

        Database::addConnection(
            array_merge(Database::getConnection()->getConfig(), ['strict' => false]),
            'transaction.import'
        );

        if (is_multisite()) {
            global $wpdb;

            Database::getConnection('transaction.import')->setTablePrefix($wpdb->prefix);
        }

        $schema = Schema::connexion('transaction.import');

        if (!$schema->hasTable($this->table)) {
            $schema->create($this->table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('object_type', 255);
                $table->bigInteger('object_id', false, true);
                $table->string('relation', 255);
                $table->timestamps();
                $table->longText('data');
            });
        }
    }

    /**
     * @inheritDoc
     */
    public function add(string $object_type, int $object_id, $relation, array $data = []): bool
    {
        $data = Arr::serialize($data);

        if ($res = $this->getFromRelation($relation, $object_type)) {
            $updated_at = DateTime::now();

            return !!Database::table($this->table)->where('id', $res->id)->update(compact(
                'object_type', 'object_id', 'relation', 'updated_at', 'data'
            ));
        } else {
            $created_at = DateTime::now();
            $updated_at = DateTime::now();

            return Database::table($this->table)->insert(compact(
                'object_type', 'object_id', 'relation', 'created_at', 'updated_at', 'data'
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function addWpPost(int $object_id, $relation, array $data = []): bool
    {
        return $this->add('wp:post', $object_id, $relation, $data);
    }

    /**
     * @inheritDoc
     */
    public function addWpTerm(int $object_id, $relation, array $data = []): bool
    {
        return $this->add('wp:term', $object_id, $relation, $data);
    }

    /**
     * @inheritDoc
     */
    public function addWpUser(int $object_id, $relation, array $data = []): bool
    {
        return $this->add('wp:user', $object_id, $relation, $data);
    }

    /**
     * @inheritDoc
     */
    public function getFromRelation($relation, string $object_type): ?object
    {
        if ($res = Database::table($this->table)->where(compact('object_type', 'relation'))->first()) {
            $res->data = Str::unserialize((string)$res->data);

            return $res;
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getFromObjectId(int $object_id, string $object_type): ?object
    {
        if ($res = Database::table($this->table)->where(compact('object_type', 'object_id'))->first()) {
            $res->data = Str::unserialize((string)$res->data);

            return $res;
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getFromWpObject(object $object): ?object
    {
        if ($object instanceof WP_Post) {
            return $this->getFromObjectId((int)$object->ID, 'wp:post');
        } elseif ($object instanceof WP_Term) {
            return $this->getFromObjectId((int)$object->term_id, 'wp:term');
        } elseif ($object instanceof WP_User) {
            return $this->getFromObjectId((int)$object->ID, 'wp:user');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getObjectId($relation, string $object_type): int
    {
        return ($res = $this->getFromRelation($relation, $object_type)) ? $res->object_id : 0;
    }

    /**
     * @inheritDoc
     */
    public function getWpPostId($relation): int
    {
        return $this->getObjectId($relation, 'wp:post');
    }

    /**
     * @inheritDoc
     */
    public function getWpTermId($relation): int
    {
        return $this->getObjectId($relation, 'wp:term');
    }

    /**
     * @inheritDoc
     */
    public function getWpUserId($relation): int
    {
        return $this->getObjectId($relation, 'wp:user');
    }

    /**
     * @inheritDoc
     */
    public function getCommand(string $name): ?ImportCommand
    {
        return $this->command[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getCommandStack(string $name): ?ImportCommandStack
    {
        return $this->commandStack[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getRecorder(string $name): ?ImportRecorder
    {
        return $this->recorder[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function registerImportCommand(?string $name = null, ?ImportRecorder $recorder = null, array $params = []): ?ImportCommand {
        try {
            /** @var ImportCommand $concrete */
            $concrete = $this->transaction->resolve('import.command');
        } catch (Exception $e) {
            return null;
        }

        if ($name) {
            $concrete->setName($name);
        }

        /** @var SfCommand|ImportCommand $command */
        $command = $concrete->setRecorder($recorder)->setParams($params);
        $command = $this->transaction->getConsole()->add($command);

        return $this->command[$command->getName()] = $command;
    }

    /**
     * @inheritDoc
     */
    public function registerCommandStack(?string $name = null, array $stack = []): ?ImportCommandStack
    {
        try {
            /** @var ImportCommandStack $concrete */
            $concrete = $this->transaction->resolve('import.command-stack');
        } catch (Exception $e) {
            return null;
        }

        if ($name) {
            $concrete->setName($name);
        }

        /** @var SfCommand|ImportCommandStack $command */
        $command = $concrete->setStack($stack);
        $command = $this->transaction->getConsole()->add($command);

        return $this->commandStack[$command->getName()] = $command;
    }

    /**
     * @inheritDoc
     */
    public function registerRecorder(string $name, array $params = []): ?ImportRecorder
    {
        try {
            /** @var ImportRecorder $concrete */
            $concrete = $this->transaction->resolve('import.recorder');
        } catch (Exception $e) {
            return null;
        }

        try {
            return $this->recorder[$name] = $concrete->setManager($this->transaction)->setParams($params);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function setTransaction(Transaction $transaction): ImportManagerContract
    {
        $this->transaction = $transaction;

        return $this;
    }
}