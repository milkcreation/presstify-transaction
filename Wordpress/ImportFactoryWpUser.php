<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportFactory as BaseImportFactoryContract,
    ImportFactory as BaseImportFactory,
    Wordpress\Contracts\ImportFactoryWpUser as ImportFactoryWpUserContract};
use WP_Error;
use WP_Roles;
use WP_User;
use WP_User_Query;

class ImportFactoryWpUser extends BaseImportFactory implements ImportFactoryWpUserContract
{
    /**
     * Identifiant de qualification du blog d'affectation.
     * @var int
     */
    protected $blog = 0;

    /**
     * Cartographie des clés de données de post.
     * @var array
     */
    protected $keys = [
        'ID',
        'user_pass',
        'user_login',
        'user_nicename',
        'user_url',
        'user_email',
        'display_name',
        'nickname',
        'first_name',
        'last_name',
        'description',
        'rich_editing',
        'comment_shortcuts',
        'admin_color',
        'use_ssl',
        'user_registered',
        'show_admin_bar_front',
        'role',
        'locale',
    ];

    /**
     * Nom de qualification du role associé.
     * @var string
     */
    protected $role = '';

    /**
     * Instance du post Wordpress associé.
     * @var WP_User|null
     */
    protected $user;

    /**
     * @inheritDoc
     */
    public function execute(): BaseImportFactoryContract
    {
        $this
            ->fetchBlogId()
            ->fetchRole()
            ->fetchID()
            ->fetchUserPass()
            ->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchBlogId(): ImportFactoryWpUserContract
    {
        if ($this->input('blog_id', 0)) {
            $this->blog = (int)$this->input('blog_id', 0);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchID(): ImportFactoryWpUserContract
    {
        if ($exists = (new WP_User_Query([
            'blog_id' => 0,
            'fields'  => 'ID',
            'include' => $this->input('ID', 0),
            'number'  => 1
        ]))->get_results()) {
            $user_id = (int)current($exists);
            $this->output(['ID' => $user_id]);
            $this->setPrimary($user_id);
        } else {
            $this->output(['user_id' => 0]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchRole(): ImportFactoryWpUserContract
    {
        if ($role = $this->input('role', '')) {
            $this->role = $role;
        }

        $this->output(['role' => $this->role]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchUserPass(): ImportFactoryWpUserContract
    {
        if ($user_pass = $this->input('user_pass', '')) {
            $this->output(['user_pass' => wp_hash_password($user_pass)]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBlogId(): int
    {
        return $this->blog ? : get_current_blog_id();
    }

    /**
     * @inheritDoc
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?WP_User
    {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function isRole(): bool
    {
        /** @var WP_Roles $wp_roles */
        global $wp_roles;

        return $wp_roles->is_role($this->getRole());
    }

    /**
     * @inheritDoc
     */
    public function save(): BaseImportFactoryContract
    {
        if (!$this->isRole()) {
            $this->messages()->error(sprintf(__('Le rôle "%s" n\'existe pas', 'tify'), $this->getRole()));
        } else {
            $disable = function() {
                return false;
            };

            add_filter('send_password_change_email', $disable, 99, 3);
            add_filter('send_email_change_email', $disable, 99, 3);

            $userdata = array_intersect_key($this->output->all(), array_flip($this->keys));

            if (!empty($userdata['ID'])) {
                $res = wp_update_user($userdata);
                $update = true;
            } else {
                $res = wp_insert_user($userdata);
                $update = false;
            }

            remove_filter('send_password_change_email', $disable, 99);
            remove_filter('send_email_change_email', $disable, 99);

            if ($res instanceof WP_Error) {
                $this->messages()->error($res->get_error_message(), (array)$res->get_error_data());

                $this
                    ->setSuccess(false)
                    ->setPrimary(0);
            } else {
                if (!$user = get_userdata((int)$res)) {
                    $this
                        ->setSuccess(false)
                        ->setPrimary(0)
                        ->messages()->error(__('Impossible de récupérer l\'utilisateur importé', 'tify'));
                } else {
                    /** @todo
                     * if (is_multisite()) {
                        add_user_to_blog($this->getBlog())
                    } */

                    $this
                        ->setSuccess(true)
                        ->setPrimary($user->ID)
                        ->messages()->success(
                            sprintf(
                                $update
                                    ? __('%s : "%s" - id : "%d" >> mis(e) à jour avec succès.', 'tify')
                                    : __('%s : "%s" - id : "%d" >> créé(e) avec succès.', 'tify'),
                                $this->getManager()->labels()->getSingular(),
                                html_entity_decode($user->display_name),
                                $user->ID
                            ),
                            ['user' => $user->to_array()]
                        );

                    $this->saveMetas()->saveOptions();
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function saveMetas(): ImportFactoryWpUserContract
    {
        if ($user = $this->getUser()) {
            foreach ($this->output('_meta', []) as $meta_key => $meta_value) {
                if (!update_user_meta($user->ID, $meta_key, $meta_value)) {
                    $this->messages()->info(
                        sprintf(__('La métadonnée "%s" n\'a pas été enregistrée.', 'tify'), $meta_key),
                        ['meta_key' => $meta_key, 'meta_value' => $meta_value, 'user' => $user->to_array()]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function saveOptions(): ImportFactoryWpUserContract
    {
        if ($user = $this->getUser()) {
            foreach ($this->output('_option', []) as $option_name => $newvalue) {
                if (!update_user_option($user->ID, $option_name, $newvalue)) {
                    $this->messages()->debug(
                        sprintf(__('L\'option "%s" n\'a pas été enregistrée.', 'tify'), $option_name),
                        ['option_name' => $option_name, 'option_value' => $newvalue, 'user' => $user->to_array()]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return ImportFactoryWpUserContract
     */
    public function setPrimary($primary): BaseImportFactoryContract
    {
        parent::setPrimary($primary);

        $this->user = ($user = get_userdata((int)$primary)) instanceof WP_User ? $user : null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBlogId(int $blog_id): ImportFactoryWpUserContract
    {
        $this->blog = $blog_id;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRole(string $role): ImportFactoryWpUserContract
    {
        $this->role = $role;

        return $this;
    }
}