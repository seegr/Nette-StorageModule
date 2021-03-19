<?php

namespace App\StorageModule\Model;

use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\ForeignKeyConstraintViolationException;
use Nette\Utils\ArrayHash;
use App\Managers\FilesManager;
use App\StorageModule\Exceptions\FolderNotEmptyException;

class StorageModel extends \App\Managers\BaseManager
{

  const TABLE_PREFIX = "storage_";
  const TABLE_FOLDERS = self::TABLE_PREFIX . "folders";
  const TABLE_FILES = self::TABLE_PREFIX . "folders_files";


  public function getFolders(): Selection
  {
    return $this->db->table(self::TABLE_FOLDERS);
  }

  public function getFolder($id): ?ActiveRow
  {
    $folder = $this->getFolders()->whereOr([
      "hash" => $id,
      "id" => $id
    ])->fetch();

    return $folder ? $folder : null;
  }

  public function getUserFolders(int $id): Selection
  {
    return $this->getFolders()->where("user", $id);
  }

  public function getFiles(): Selection
  {
    return $this->db->table(self::TABLE_FILES);
  }

  /**
   * @type string|int $id get files by folder id or hash
   */
  public function getFolderFiles($id): Selection
  {
    $folder = $this->getFolder($id);

    return $this->getFiles()->where("folder", $folder->id);
  }

  public function getFileByHash(string $hash): ?ActiveRow
  {
    return $this->getFiles()->where("file.key", $hash)->fetch();
  }

  public function getFile($id): ?ActiveRow
  {
    return $this->getFiles()->get($id);
  }

  public function getFileData(string $id): ?ActiveRow
  {
    $file = $this->getFiles()->where("file.key", $id);

    return $file ? $file->fetch()->ref("file") : null;
  }

  public function saveFolder(ArrayHash $vals): int
  {
    bdump($vals);

    $data = [
      "title" => $vals->title,
      "text" => $vals->text,
      "password" => $vals->password,
      "user" => $vals->user
    ];
    
    bdump($data, "data");

    if (empty($vals->id)) {
      $data["hash"] = $this->generateUniqueHashId(self::TABLE_FOLDERS, "hash", 40);

      $folder = $this->getFolders()->insert($data);
      $id = $folder->id;
    } else {
      $id = $vals->id;
      $this->getFolder($id)->update($data);
    }

    return $id;
  }

  /**
   * @param string|int $id get file by id or hash key
   */
  public function deleteFolder($id, bool $forceDeleteFiles = false): void
  {
    bdump($id, "delete folder id");
    if ($forceDeleteFiles) {
      $this->getFolderFiles($id)->delete();
    }
    
    try {
      $this->getFolder($id)->delete();
    } catch (ForeignKeyConstraintViolationException $e) {
      throw new FolderNotEmptyException("Folder is not empty");
    }
  }

  public function getFolderOwner(int $id): ?ActiveRow
  {
    $user = $this->getFolder($id);

    return $user ? $user->ref("user") : null;
  }

  public function saveFile($vals): int
  {
    $data = [
      "title" => $vals->title ?? null
    ];

    if (empty($vals->id)) {
      $data = $data + [
        "folder" => $vals->folder,
        "file" => $vals->file
      ];

      $file = $this->getFiles()->insert($data);
      $id = $file->id;
    } else {
      $id = $vals->id;
      $this->getFile($vals->id)->update($data);
    }

    return $id;
  }

}