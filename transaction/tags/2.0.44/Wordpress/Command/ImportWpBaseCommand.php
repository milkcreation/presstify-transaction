<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use tiFy\Console\Command;
use tiFy\Support\{MessagesBag, ParamsBag};
use tiFy\Plugins\Transaction\Proxy\Transaction;

abstract class ImportWpBaseCommand extends Command
{
    /**
     * Nombre d'éléments par portion de traitement.
     * @var int
     */
    protected $chunk = 100;

    /**
     * Données d'enregistrement de l'élément.
     * @var ParamsBag
     */
    protected $data;

    /**
     * Nom de classe du modèle de post d'origine (entrée).
     * @var string|null
     */
    protected $inModelClassname;

    /**
     * Données d'enregistrement de l'élément.
     * @var MessagesBag
     */
    protected $message;

    /**
     * Enregistrement de démarrage du traitement.
     * @var int
     */
    protected $offset = 0;

    /**
     * Nom de classe du modèle de post d'enregistrement (sortie).
     * @var string|null
     */
    protected $outModelClassname;

    /**
     * Liste des arguments de requête complémentaires de récupération des éléments.
     * @var array
     */
    protected $queryArgs = [];

    /**
     * CONSTRUCTEUR.
     *
     * @param string|null $name
     *
     * @return void
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, __('Url du site', 'tify'), '')
            ->addOption(
                'offset', null, InputOption::VALUE_OPTIONAL, __('Numéro d\'enregistrement de démarrage', 'tify'), 0
            )
            // @todo
            ->addOption(
                'length', null, InputOption::VALUE_OPTIONAL, __('Nombre d\'enregistrements à traiter', 'tify'), -1
            );
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOffset((int)($input->getOption('offset') ?: 0));
    }

    /**
     * Définition de donnée(s)|Récupération de donnée|Récupération de l'instance des données d'enregistrement.
     *
     * @param array|string|null $key Liste des définitions|Indice de qualification du paramètre (Syntaxe à point)|null.
     * @param mixed $default Valeur de retour par défaut lors de la récupération d'une donnée.
     *
     * @return mixed|ParamsBag
     */
    public function data($key = null, $default = null)
    {
        if (!$this->data instanceof ParamsBag) {
            $this->data = new ParamsBag();
        }

        if (is_string($key)) {
            return $this->data->get($key, $default);
        } elseif (is_array($key)) {
            return $this->data->set($key);
        } else {
            return $this->data;
        }
    }

    /**
     * Récupération de l'instance du modèle d'origine (entrée).
     *
     * @return BaseModel
     */
    public function getInModel(): ?BaseModel
    {
        $classname = $this->inModelClassname;

        return ($instance = new $classname()) instanceof BaseModel ? $instance : null;
    }

    /**
     * Récupération de l'enregistrement de démarrage.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Récupération de l'instance du modèle d'enregistrement (sortie).
     *
     * @return BaseModel
     */
    public function getOutModel(): ?BaseModel
    {
        $classname = $this->outModelClassname;

        return ($instance = new $classname()) instanceof BaseModel ? $instance : null;
    }

    /**
     * Récupération de l'identifiant de qualification d'un terme de taxonomie depuis son identifiant relationnem.
     *
     * @param int $rel_term_id Identifiant relationnel.
     *
     * @return int
     */
    public function getRelTermId(int $rel_term_id): int
    {
        return Transaction::import()->getWpTermId($rel_term_id);
    }

    /**
     * Récupération de l'identifiant de qualification d'une publication depuis son identifiant relationnel.
     *
     * @param int $rel_post_id Identifiant relationnel.
     *
     * @return int
     */
    public function getRelPostId(int $rel_post_id): int
    {
        return Transaction::import()->getWpPostId($rel_post_id);
    }

    /**
     * Récupération de l'identifiant de qualification d'un utilisateur depuis son identifiant relationnel.
     *
     * @param int $rel_user_id Identifiant relationnel.
     *
     * @return int
     */
    public function getRelUserId(int $rel_user_id): int
    {
        return Transaction::import()->getWpUserId($rel_user_id);
    }

    /**
     * Pré-traitement de la tâche d'import.
     *
     * @return static
     */
    public function handleBefore(): self
    {
        return $this;
    }

    /**
     * Post-traitement de tâche d'import.
     *
     * @return static
     */
    public function handleAfter(): self
    {
        return $this;
    }

    /**
     * Pré-traitement de l'import d'un élément.
     *
     * @param BaseModel $model Instance du modèle d'origine (entrée).
     *
     * @return static
     */
    public function handleItemBefore(BaseModel $model): self
    {
        return $this;
    }

    /**
     * Post-traitement de l'import d'un élément.
     *
     * @param int $id Valeur de la clé primaire de l'élément enregistré.
     * @param BaseModel $model Instance du modèle d'origine (entrée).
     *
     * @return static
     */
    public function handleItemAfter(int $id, BaseModel $model): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function message($level = null, string $message = null, ?array $data = [], ?string $code = null)
    {
        if(is_null($this->message)) {
            $this->message = new MessagesBag();
        }

        if (is_null($level)) {
            return $this->message;
        } else {
            return $this->message->add($level, $message, $data, $code);
        }
    }

    /**
     * @inheritDoc
     */
    public function outputMessages(OutputInterface $output, bool $forget = true)
    {
        foreach($this->message()->all() as $level => $messages) {
            $output->writeln($messages);
        }

        if ($forget = true) {
            $this->message()->flush();
        }
    }

    /**
     * Définition de la classe du modèle d'origine (entrée).
     *
     * @param string $classname
     *
     * @return static
     */
    public function setInModelClassname(string $classname): self
    {
        $this->inModelClassname = $classname;

        return $this;
    }

    /**
     * Définition de l'enregistrement de démarrage.
     *
     * @param int $offset
     *
     * @return static
     */
    public function setOffset(int $offset = 0): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Définition de la classe du modèle d'enregistrement (sortie).
     *
     * @param string $classname
     *
     * @return static
     */
    public function setOutModelClassname(string $classname): self
    {
        $this->outModelClassname = $classname;

        return $this;
    }
}