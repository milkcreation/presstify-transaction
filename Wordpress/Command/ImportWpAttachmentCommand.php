<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use GuzzleHttp\Client as HttpClient;
use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Database\Eloquent\Collection as BaseCollection;
use Symfony\Component\Console\Output\OutputInterface;
use tiFy\Wordpress\Database\Model\{Attachment as AttachmentModel, Post as PostModel};
use tiFy\Contracts\Filesystem\LocalFilesystem;
use tiFy\Filesystem\StorageManager;
use WP_Error;

class ImportWpAttachmentCommand extends ImportWpPostCommand
{
    /**
     * Séparateur de répertoire.
     * @return string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Activation du forçage de la copie si le fichier est existant dans le répertoire de destination.
     * @var bool
     */
    protected $forceCopy = false;

    /**
     * Indicateur de traitement hierarchique.
     * @var bool
     */
    protected $hierachical = false;

    /**
     * Identifiant de qualification du type de post d'origine (entrée).
     * @var string|null
     */
    protected $inPostType = 'attachment';

    /**
     * Identifiant de qualification du type de post d'enregistrement (sortie).
     * @var string|null
     */
    protected $outPostType = 'attachment';

    /**
     * Répertoire local de stockage des fichiers d'origine.
     * @var string|null
     */
    protected $inBaseDir;

    /**
     * Url de stockage des fichiers d'origine.
     * @var string|null
     */
    protected $inBaseUrl;

    /**
     * Instance du gestionnaire de stockage.
     * @var StorageManager
     */
    private $storage;

    /**
     * Répertoire de stockage des fichiers temporaires.
     * @var string|null
     */
    protected $tmpDir;

    /**
     * Répertoire de stockage des fichiers d'upload.
     * @var string|null
     */
    protected $uploadDir = WP_CONTENT_DIR . '/uploads';

    /**
     * Traitement des résultats de requête.
     *
     * @param BaseCollection|AttachmentModel[] $items
     * @param OutputInterface $output
     * @param int $parent
     *
     * @return void
     *
     * @throws Exception
     */
    protected function handleCollection(BaseCollection $items, OutputInterface $output, ?int $parent = null)
    {
        foreach ($items as $item) {
            $this->itemDatas()->clear();

            $this->counter++;

            $this->handleItemBefore($item);

            try {
                $id = $this->insertOrUpdate(
                    $item, is_null($parent) ? $this->getRelatedPostId($item->post_parent) : $parent
                );
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
     * @param AttachmentModel|PostModel $item
     * @param int $parent
     *
     * @return int
     *
     * @throws Exception
     */
    public function insertOrUpdate(PostModel $item, int $parent): int
    {
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        if (!$path = (string) $item->getMeta('_wp_attached_file')) {
            throw new Exception(sprintf(
                __('ERREUR: Aucun fichier média ne semble être associé à [%d >> %s].', 'tify'),
                $item->ID, basename($item->guid)
            ));
        } else {
            $subdir = ltrim(rtrim(dirname($path), '/'), '/');
        }

        if ($id = $this->getRelatedPostId($item->ID)) {
            if (!$this->isUpdatable()) {
                $this->message()->info(sprintf(
                    __('%s > INFO: Le fichier média a déjà été importé [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $id, basename($item->guid), $item->ID
                ));

                return $id;
            }

            if (! $file = $this->fetchTmpFile($path, $item)) {
                throw new Exception(sprintf(
                    __('ERREUR: Impossible de récupérer le fichier média associé depuis [#%d - %s].', 'tify'),
                    $item->ID, basename($item->guid)
                ));
            } elseif (!$upfile = $this->moveUploadedFile(basename($file['tmp_name']), "{$subdir}/{$file['name']}")) {
                throw new Exception(sprintf(
                    __('ERREUR: Impossible de déplacer le média dans le répertoire de destination [%d >> %s].', 'tify'),
                    $item->ID, basename($item->guid)
                ));
            }

            $this->parsePostdata($item, [
                'ID'             => $id,
                'post_mime_type' => $file['type'],
                'post_parent'    => $parent
            ]);

            $attached_id = wp_insert_attachment($this->itemDatas('postdata', []), $upfile, 0, true);

            if (!$attached_id instanceof WP_Error) {
                $this->generateAttachmentMetadata($attached_id);

                $this->importer()->addWpPost($attached_id, $item->ID, $this->withCache ? $item->toArray() : []);

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Mise à jour du média [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $attached_id, basename($item->guid), $item->ID
                ));

                return $attached_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour du média [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $item->ID, basename($item->guid), $attached_id->get_error_message(), $item->toJson()
                ));
            }
        } else {
            if (! $file = $this->fetchTmpFile($path, $item)) {
                throw new Exception(sprintf(
                    __('ERREUR: Impossible de récupérer le fichier média associé [%d >> %s].', 'tify'),
                    $item->ID, basename($item->guid)
                ));
            } elseif (!$upfile = $this->moveUploadedFile(basename($file['tmp_name']), "{$subdir}/{$file['name']}")) {
                throw new Exception(sprintf(
                    __('ERREUR: Impossible de déplacer le média dans le répertoire de destination [%d >> %s].', 'tify'),
                    $item->ID, basename($item->guid)
                ));
            }

            $this->parsePostdata($item, [
                'post_mime_type' => $file['type'],
                'post_parent'    => $parent
            ]);

            $attached_id = wp_insert_attachment($this->itemDatas('postdata', []), $upfile, 0, true);

            if (!$attached_id instanceof WP_Error) {
                $this->generateAttachmentMetadata($attached_id);

                $this->importer()->addWpPost($attached_id, $item->ID, $this->withCache ? $item->toArray() : []);

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Création du média [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $attached_id, basename($item->guid), $item->ID
                ));

                return $attached_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création du média depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $item->ID, basename($item->guid), $attached_id->get_error_message(), $item->toJson()
                ));
            }
        }
    }

    /**
     * Création du fichier temporaire.
     *
     * @param string $name
     * @param string $contents
     *
     * @return array|null
     */
    public function createTmpFile(string $name, string $contents): ?array
    {
        $tmpname = basename(tempnam($this->getTmpStorage()->path('/'), ''));

        try {
            $this->getTmpStorage()->update($tmpname, $contents);

            return [
                'name'     => $name,
                'type'     => $this->getTmpStorage()->getMimetype($tmpname),
                'tmp_name' => $this->getTmpStorage()->path($tmpname),
                'size'     => $this->getTmpStorage()->getSize($tmpname)
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Récupération des arguments du fichier temporaire associé au média.
     *
     * @param string $path Chemin relatif vers le fichier associé.
     * @param AttachmentModel $item
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function fetchTmpFile(string $path, AttachmentModel $item): ?array
    {
        $name = basename($path);

        $basedir = $this->getInBaseDir($item);
        $baseurl = $this->getInBaseUrl($item);

        if(!is_null($basedir)) {
            $filename = rtrim($basedir, self::DS) . self::DS . ltrim($path, self::DS);
            if (file_exists($filename) && ($contents = file_get_contents($filename))) {
                return $this->createTmpFile($name, $contents);
            }
        }

        if (!is_null($baseurl)) {
            $tmpfile = null;

            $client = new HttpClient([
                'allow_redirects' => false,
                'base_uri'        => rtrim($baseurl, '/') . '/',
                'verify'          => false
            ]);

            $client->getAsync(ltrim($path, '/'))->then(
                function (ResponseInterface $res) use ($name, &$tmpfile) {
                    if ($res->getStatusCode() === 200 && ($contents = $res->getBody()->getContents())) {
                        return $tmpfile = $this->createTmpFile($name, $contents);
                    } else {
                        return $tmpfile;
                    }
                }
            )->wait();

            return $tmpfile;
        }

        return null;
    }

    /**
     * Génération des metadonnées du fichier média.
     *
     * @param int $attached_id
     *
     * @return bool
     */
    public function generateAttachmentMetadata(int $attached_id): bool
    {
        if (!$file = get_attached_file($attached_id)) {
            return false;
        }

        $attached_data = wp_generate_attachment_metadata($attached_id, $file);

        return wp_update_attachment_metadata($attached_id, $attached_data);
    }

    /**
     * Récupération du répertoire local de stockage des fichiers médias d'origine (entrée).
     *
     * @param AttachmentModel $item
     *
     * @return string|null
     */
    public function getInBaseDir(AttachmentModel $item): ?string
    {
        return $this->inBaseDir;
    }

    /**
     * Récupération de l'url de stockage des fichiers médias d'origine (entrée).
     *
     * @param AttachmentModel $item
     *
     * @return string|null
     */
    public function getInBaseUrl(AttachmentModel $item): ?string
    {
        return $this->inBaseUrl;
    }

    /**
     * Récupération du gestionnaire de stockage.
     *
     * @return StorageManager
     */
    public function getStorage()
    {
        if (is_null($this->storage)) {
            $this->storage = new StorageManager();
            $this->storage->registerLocal('tmp', $this->tmpDir ?? sys_get_temp_dir());
            $this->storage->registerLocal('upload',
                $this->uploadDir ?? (wp_get_upload_dir()['basedir'] ? : WP_CONTENT_DIR . '/uploads')
            );
        }

        return $this->storage;
    }

    /**
     * Récupération de l'instance du disque de stockage des fichiers temporaires.
     *
     * @return LocalFilesystem
     */
    public function getTmpStorage(): FilesystemInterface
    {
        return $this->getStorage()->getFilesystem('tmp');
    }

    /**
     * Récupération de l'instance du disque de stockage des fichiers temporaires.
     *
     * @return LocalFilesystem
     */
    public function getUploadStorage(): FilesystemInterface
    {
        return $this->getStorage()->getFilesystem('upload');
    }

    /**
     * Vérification du forçage de la copie dans le répertoire de destination si le fichier est existant.
     *
     * @return bool
     */
    public function isForceCopy(): bool
    {
        return $this->forceCopy;
    }

    /**
     * Déplacement du fichier temporaire vers le répertoire de destination.
     *
     * @param string $tmp Chemin relatif du fichier temporaire.
     * @param string $dest Chemin relatif vers le fichier de destination.
     *
     * @return string Chemin absolu vers le fichier de destination
     */
    public function moveUploadedFile(string $tmp, string $dest): ?string
    {
        if ($this->isForceCopy() && $this->getUploadStorage()->has($dest)) {
            try {
                $this->getUploadStorage()->delete($dest);
            } catch (Exception $e) {

            }
        }

        try {
            if ($this->getStorage()->move("tmp://{$tmp}", "upload://{$dest}")) {
                return $this->getUploadStorage()->path($dest);
            }
        } catch (Exception $e) {

        }

        try {
            $this->getTmpStorage()->delete($tmp);
        } catch(Exception $e) {

        }

        return null;
    }

    /**
     * Définition de forçage de la copie dans le répertoire de destination si le fichier est existant.
     *
     * @param bool $force
     *
     * @return static
     */
    public function setForceCopy(bool $force = true): self
    {
        $this->forceCopy = $force;

        return $this;
    }

    /**
     * Récupération du répertoire local de stockage des fichiers d'origine.
     *
     * @param string $dir
     *
     * @return static
     */
    public function setInBaseDir(string $dir): self
    {
        $this->inBaseDir = $dir;

        return $this;
    }

    /**
     * Récupération de l'url de stockage des fichiers d'origine.
     *
     * @param string $url
     *
     * @return static
     */
    public function setInBaseUrl(string $url): self
    {
        $this->inBaseUrl = $url;

        return $this;
    }
}