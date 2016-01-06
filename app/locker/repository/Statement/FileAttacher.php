<?php namespace Locker\Repository\Statement;
use Locker\Repository\Document\FileTypes as FileTypes;
use Locker\Helpers\Helpers as Helpers;
use Locker\Helpers\Exceptions as Exceptions;
use Locker\Repository\File\Factory as FileFactory;

class FileAttacher {

  /**
   * Stores attachments with the given options.
   * @param [\stdClass] $attachments
   * @param [String] $hashes
   * @param StoreOptions $opts
   */
  public function store(array $attachments, array $hashes, StoreOptions $opts) {
    $dir = $this->getDir($opts);

    foreach ($attachments as $attachment) {
      if (!in_array($attachment->hash, $hashes)) throw new Exceptions\Exception(
        'Attachment hash does not exist in given statements'
      );

      $ext = $this->getExt($attachment->content_type);
      if ($ext === false) throw new Exceptions\Exception(
        'This file type cannot be supported'
      );

      $filename = $attachment->hash.'.'.$ext;
      FileFactory::create()->update($dir.$filename, ['content' => $attachment->content], []);
    }
  }

  /**
   * Gets all of the attachments for the given statements.
   * @param [\stdClass] $statements
   * @param IndexOptions $opts
   * @return [\stdClass]
   */
  public function index(array $statements, IndexOptions $opts) {
    $dir = $this->getDir($opts);

    $attachments = [];
    foreach ($statements as $statement) {
      $attachments = array_merge($attachments, array_map(function ($attachment) use ($dir) {
        $ext = $this->getExt($attachment->contentType);
        $filename = $attachment->sha2.'.'.$ext;
        return (object) [
          'content_type' => $attachment->contentType,
          'hash' => $attachment->sha2,
          'content' => FileFactory::create()->stream($dir.$filename, [])
        ];
      }, isset($statement->attachments) ? $statement->attachments : []));
    }

    return $attachments;
  }

  /**
   * Gets the extension from the given content type.
   * @param String $content_type
   * @return String
   */
  private function getExt($content_type) {
    return array_search($content_type, FileTypes::getMap());
  }

  /**
   * Gets the directory for attachments with the given options.
   * @param Options $opts
   * @return String
   */
  private function getDir(Options $opts) {
    return $opts->getOpt('lrs_id').'/attachments/';
  }
}
