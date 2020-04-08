<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\Collection as BaseCollection;
use Symfony\Component\Console\Output\OutputInterface;
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
     * Pré-traitement de la tâche.
     *
     * @return void
     */
    public function handleBefore(): void
    {
        parent::handleBefore();

        // Désactivation du mail de notification Wordpress.
        add_filter('send_password_change_email', '__return_false', 99, 3);
        add_filter('send_email_change_email', '__return_false', 99, 3);
    }

    /**
     * {@inheritDoc}
     *
     * @param BaseCollection|UserModel[] $items
     * @param OutputInterface $output
     */
    public function handleItems(BaseCollection $items, OutputInterface $output): void
    {
        foreach ($items as $item) {
            $this->itemDatas()->clear();

            $this->counter++;

            $this->handleItemBefore($item);

            try {
                $id = $this->insertOrUpdate($item);
            } catch (Exception $e) {
                $this->message()->error($e->getMessage());
            }

            $this->itemDatas()->set(['insert_id' => $id ?? 0]);

            $this->handleItemAfter($item);

            $this->handleMessages($output);
        }
    }

    /**
     * Création ou mise à jour.
     *
     * @param UserModel $item
     *
     * @return int
     *
     * @throws Exception
     */
    public function insertOrUpdate(UserModel $item): int
    {
        if ($id = $this->getRelatedUserId($item->ID)) {
            if (!$this->isUpdatable()) {
                throw new Exception(sprintf(
                    __('%s > INFO: L\'utilisateur a déjà été importé [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $id, $item->user_email, $item->ID
                ));
            }

            $this->parseUserdata($item, ['ID' => $id]);

            $user_id = wp_update_user($this->itemDatas('userdata', []));

            if (!$user_id instanceof WP_Error) {
                $this->saveHashedPassword($item->user_pass, $user_id);

                $this->importer()->addWpUser($user_id, $item->ID, $this->withCache ? $item->toArray() : []);

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Mise à jour de l\'utilisateur [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $user_id, $item->user_email, $item->ID
                ));

                return $user_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour l\'utilisateur [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $item->ID, $item->user_email, $user_id->get_error_message(), $item->toJson()
                ));
            }
        } else {
            $this->parseUserdata($item);

            $user_id = wp_insert_user($this->itemDatas('userdata', []));

            if (!$user_id instanceof WP_Error) {
                $this->saveHashedPassword($item->user_pass, $user_id);

                $this->importer()->addWpUser($user_id, $item->ID, $this->withCache ? $item->toArray() : []);

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Création de l\'utilisateur [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $user_id, $item->user_email, $item->ID
                ));

                return $user_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création l\'utilisateur depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $item->ID, $item->user_email, $user_id->get_error_message(), $item->toJson()
                ));
            }
        }
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
     * @param UserModel $item
     * @param array $attrs Liste des attributs personnalisés
     *
     * @return static
     */
    public function parseUserdata(UserModel $item, array $attrs = []): self
    {
        $this->itemDatas(['userdata' => array_merge([
            'user_pass'            => '',
            'user_login'           => $item->user_login,
            'user_nicename'        => $item->user_nicename,
            'user_url'             => $item->user_url,
            'user_email'           => $item->user_email,
            'display_name'         => $item->display_name,
            'nickname'             => $item->nickname,
            'first_name'           => $item->first_name,
            'last_name'            => $item->last_name,
            'description'          => $item->description,
            'rich_editing'         => $item->rich_editing ? 'true' : 'false',
            'syntax_highlighting'  => $item->syntax_highlighting ? 'true' : 'false',
            'comment_shortcuts'    => $item->comment_shortcuts ? 'true' : 'false',
            'admin_color'          => $item->admin_color,
            'use_ssl'              => $item->use_ssl,
            'user_registered'      => (string)$item->user_registered,
            'user_activation_key'  => $item->user_activation_key,
            'spam'                 => $item->spam,
            'show_admin_bar_front' => $item->show_admin_bar_front ? 'true' : 'false',
            'role'                 => $this->getOutRole() ?: $item->role,
            'locale'               => $item->locale,
        ], $attrs)]);

        return $this;
    }

    /**
     * Sauvegarde du mot de passe encrypté.
     *
     * @param string $hash Mot de passe encrypté.
     * @param int Identifiant de qualification de l'uitilisateur.
     *
     * @return void
     */
    public function saveHashedPassword(string $hash, int $user_id)
    {
        global $wpdb;

        $wpdb->update($wpdb->users, [
            'user_pass'           => $hash,
            'user_activation_key' => '',
        ], ['ID' => $user_id]);

        clean_user_cache($user_id);
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