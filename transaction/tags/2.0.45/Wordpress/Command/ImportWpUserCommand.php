<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\{Builder, Collection as BaseCollection, Model as BaseModel};
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface};
use tiFy\Wordpress\Database\Model\User as UserModel;
use WP_Error;

class ImportWpUserCommand extends ImportWpBaseCommand
{
    /**
     * Identifiant de qualification du rôle d'origine (entrée).
     * @var string|null
     */
    protected $inRole;

    /**
     * Identifiant de qualification du rôle d'enregistrement (sortie).
     * @var string|null
     */
    protected $outRole;

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleBefore();

        parent::execute($input, $output);

        // Désactivation du mail de notification Wordpress.
        add_filter('send_password_change_email', '__return_false', 99, 3);
        add_filter('send_email_change_email', '__return_false', 99, 3);

        $this->buildQuery()->chunkById($this->chunk, function (BaseCollection $collect) use ($output) {
            $this->handleCollection($collect, $output);
        });

        $this->handleAfter();
    }

    /**
     * Traitement des résultats de requête.
     *
     * @param BaseCollection $collect
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws Exception
     */
    protected function handleCollection(BaseCollection $collect, OutputInterface $output)
    {
        foreach ($collect as $model) {
            $this->counter++;

            $this->handleItemBefore($model);

            try {
                $id = $this->insertOrUpdate($model);

                $this->handleItemAfter($id, $model);
            } catch (Exception $e) {
                $this->message()->error($e->getMessage());
            }

            $this->outputMessages($output);
        }
    }

    /**
     * Création ou mise à jour.
     *
     * @param UserModel $model
     *
     * @return int
     *
     * @throws Exception
     */
    protected function insertOrUpdate(UserModel $model): int
    {
        $this->data()->clear();

        $this->parseUserdata($model);

        if ($id = $this->getRelatedUserId($model->ID)) {
            $this->data(['ID' => $id]);

            $user_id = wp_update_user($this->data()->all());

            if (!$user_id instanceof WP_Error) {
                $this->importer()->addWpUser($user_id, $model->ID, $this->withCache ? $model->toArray() : []);

                $this->message()->success(sprintf(
                    __('%d -- SUCCES: Mise à jour de l\'utilisateur [#%d - %s] depuis [#%d].', 'tify'),
                    $this->counter, $user_id, $model->user_email, $model->ID
                ));

                return $user_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour l\'utilisateur [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $model->ID, $model->user_email, $user_id->get_error_message(), $model->toJson()
                ));
            }
        } else {
            $user_id = wp_insert_user($this->data()->all());

            if (!$user_id instanceof WP_Error) {
                $this->importer()->addWpUser($user_id, $model->ID, $this->withCache ? $model->toArray() : []);

                $this->message()->success(sprintf(
                    __('%d -- SUCCES: Création de l\'utilisateur [#%d - %s] depuis [#%d].', 'tify'),
                    $this->counter, $user_id, $model->user_email, $model->ID
                ));

                return $user_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création l\'utilisateur depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $model->ID, $model->user_email, $user_id->get_error_message(), $model->toJson()
                ));
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return UserModel|Builder
     */
    public function getInModel(): ?BaseModel
    {
        $classname = $this->inModelClassname;

        return ($instance = new $classname()) instanceof UserModel ? $instance : null;
    }

    /**
     * Récupération du nom de qualification du rôle d'origine (entrée).
     *
     * @return string
     */
    public function getInRole(): ?string
    {
        return $this->inRole;
    }

    /**
     * {@inheritDoc}
     *
     * @return UserModel|Builder
     */
    public function getOutModel(): ?BaseModel
    {
        $classname = $this->outModelClassname;

        return ($instance = new $classname()) instanceof UserModel ? $instance : null;
    }

    /**
     * Récupération du nom de qualification du rôle d'enregistrement (sortie).
     *
     * @return string
     */
    public function getOutRole(): ?string
    {
        return $this->outRole;
    }

    /**
     * Traitement des données utilisateur à enregistrer selon le modèle d'entrée et données personnalisées.
     *
     * @param UserModel $model
     * @param array $attrs Liste des attributs personnalisés
     *
     * @return static
     */
    public function parseUserdata(UserModel $model, array $attrs = []): self
    {
        $this->data(array_merge([
            'user_pass'            => $model->user_pass,
            'user_login'           => $model->user_login,
            'user_nicename'        => $model->user_nicename,
            'user_url'             => $model->user_url,
            'user_email'           => $model->user_email,
            'display_name'         => $model->display_name,
            'nickname'             => $model->nickname,
            'first_name'           => $model->first_name,
            'last_name'            => $model->last_name,
            'description'          => $model->description,
            'rich_editing'         => $model->rich_editing,
            'syntax_highlighting'  => $model->syntax_highlighting,
            'comment_shortcuts'    => $model->comment_shortcuts,
            'admin_color'          => $model->admin_color,
            'use_ssl'              => $model->use_ssl,
            'user_registered'      => (string)$model->user_registered,
            'user_activation_key'  => $model->user_activation_key,
            'spam'                 => $model->spam,
            'show_admin_bar_front' => $model->show_admin_bar_front,
            'role'                 => $this->getOutRole() ?: $model->role,
            'locale'               => $model->locale,
        ], $attrs));

        return $this;
    }

    /**
     * Définition de l'identifiant de qualification du rôle d'origine (entrée).
     *
     * @param string $role
     *
     * @return static
     */
    public function setInRole(string $role): self
    {
        $this->inRole = $role;

        return $this;
    }

    /**
     * Définition de l'identifiant de qualification du rôle d'enregistrement (sortie).
     *
     * @param string $role
     *
     * @return static
     */
    public function setOutRole(string $role): self
    {
        $this->outRole = $role;

        return $this;
    }
}