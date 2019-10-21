<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportRecord as BaseImportRecordContract,
    ImportRecord as BaseImportRecord,
    Wordpress\Contracts\ImportWpUser as ImportWpUserContract
};
use WP_Error;
use WP_Roles;
use WP_User;
use WP_User_Query;

class ImportWpUser extends BaseImportRecord implements ImportWpUserContract
{
    /**
     * Identifiant de qualification du blog d'affectation.
     * @var int
     */
    protected $blog = 0;

    /**
     * Instance du post Wordpress associé.
     * @var WP_User|null
     */
    protected $exists;

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
     * @inheritDoc
     */
    public function execute(): BaseImportRecordContract
    {
        $this->prepare()->save()->saveInfos();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exists(): ?WP_User
    {
        return parent::exists();
    }

    /**
     * @inheritDoc
     */
    public function fetchBlogId(): ImportWpUserContract
    {
        if ($this->input('blog_id', 0)) {
            $this->blog = (int)$this->input('blog_id', 0);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchExists(): BaseImportRecordContract
    {
        if (is_null($this->exists)) {
            if ($exists = (new WP_User_Query([
                'blog_id' => 0,
                'include' => $this->input('ID', 0),
                'number'  => 1
            ]))->get_results()) {
                $this->setExists(current($exists));
                $this->output(['ID' => $this->exists()->ID]);
            } else {
                $this->setExists(false);
                $this->output(['user_id' => 0]);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchRole(): ImportWpUserContract
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
    public function fetchUserPass(): ImportWpUserContract
    {
        if ($user_pass = $this->input('user_pass', '')) {
            $this->output(['user_pass' => $user_pass]);
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
        return $this->exists ?: null;
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
    public function prepare(): BaseImportRecordContract
    {
        if (!$this->prepared) {
            $this
                ->fetchBlogId()
                ->fetchRole()
                ->fetchExists()
                ->fetchUserPass();

            $this->prepared = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(): BaseImportRecordContract
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
                    ->setExists(false);
            } else {
                if (!$user = get_userdata((int)$res)) {
                    $this
                        ->setSuccess(false)
                        ->setExists(false)
                        ->messages()->error(__('Impossible de récupérer l\'utilisateur importé', 'tify'));
                } else {
                    /** @todo
                     * if (is_multisite()) {
                        add_user_to_blog($this->getBlog())
                    } */

                    $this
                        ->setSuccess(true)
                        ->setExists($user)
                        ->messages()->success(
                            sprintf(
                                $update
                                    ? __('%s : "%s" - id : "%d" >> mis(e) à jour avec succès.', 'tify')
                                    : __('%s : "%s" - id : "%d" >> créé(e) avec succès.', 'tify'),
                                $this->records()->labels()->singular(),
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
    public function saveMetas(): ImportWpUserContract
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
    public function saveOptions(): ImportWpUserContract
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
     * @inheritDoc
     */
    public function setBlogId(int $blog_id): ImportWpUserContract
    {
        $this->blog = $blog_id;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRole(string $role): ImportWpUserContract
    {
        $this->role = $role;

        return $this;
    }
}