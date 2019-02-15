<?php

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Plugins\Transaction\Contracts\ImportItemWpUserInterface;

class ImportItemWpUserController extends ImportItemController implements ImportItemWpUserInterface
{
    /**
     * Cartographie des clés de données de sortie autorisées à être traitée.
     * @var array
     */
    protected $constraint = [
        'data' => [
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
            'locale'
        ]
    ];

    /**
     * Types de données pris en charge.
     * @var array {
     *  @var string $data Données principales.
     *  @var string $met Métadonnées.
     *  @var string $opt Options.
     * }
     */
    protected $types = [
        'data',
        'meta',
        /** @todo 'opt' */
    ];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Désactivation de l'expédition de mail aux utilisateurs.
        add_filter('send_password_change_email', '__return_false', 99, 3);
        add_filter('send_email_change_email', '__return_false', 99, 3);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessMessage($user_id = null)
    {
        return sprintf(
            __('L\'utilisateur "%s" a été importé avec succès', 'tify'),
            $user_id
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function getTypes()
    {
        return array_intersect($this->types, ['data', 'meta', 'opt']);
    }

    /**
     * {@inheritdoc}
     */
    public function outputFilterData($key, $value = null, $primary_id = null)
    {
        switch($key) :
            case 'user_pass' :
                if ($this->getOutputData('ID', 0) && $value) :
                    $value = wp_hash_password($value);
                endif;
                break;
            case 'role' :
                /** @var \WP_Roles $wp_roles */
                global $wp_roles;

                if (empty($value) || !$wp_roles->is_role($value)) :
                    $this->notices()->add(
                        'error',
                        __('Le rôle utilisateur d\'affectation n\'existe pas', 'tify')
                    );
                   $this->setOnBreak();
                endif;
                break;
        endswitch;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($userdata = [], $user_id = null)
    {
        $res = wp_insert_user($userdata);

        if (is_wp_error($res)) :
            $this->notices()->add(
                'error',
                $res->get_error_message(),
                $res->get_error_data() ? : []
            );

            $this->setSuccess(false);
            $user_id = 0;
        else :
            $user_id = (int)$res;

            $this->notices()->add(
                'success',
                $this->getSuccessMessage($user_id),
                [
                    'user_id' => $user_id
                ]
            );

            $this->setSuccess(true);
        endif;

        $this->setPrimaryId($user_id);
    }

    /**
     * {@inheritdoc}
     */
    public function insertMeta($meta_key, $meta_value, $user_id = null)
    {
        return update_user_meta($user_id, $meta_key, $meta_value);
    }

    /**
     * {@inheritdoc}
     */
    public function insertOption($option_name, $newvalue, $user_id = null)
    {
        return update_user_option($user_id, $option_name, $newvalue);
    }
}