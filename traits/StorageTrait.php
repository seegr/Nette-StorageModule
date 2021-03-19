<?php

namespace App\StorageModule\Traits;

use App\StorageModule\Exceptions\FolderNotEmptyException;
use Nette\Utils\ArrayHash;
use Monty\DataGrid;
use Monty\Html;

trait StorageTrait
{
  
  public function saveFolder(ArrayHash $vals): string
  {
    if (empty($vals->user)) {
      $vals->user = $this->getUser()->id;
    }
    
    $id = $this->StorageModel->saveFolder($vals, "hash");
    $folder = $this->StorageModel->getFolder($id);

    return $folder->hash;
  }

  public function saveFile($vals): ?int
  {
    return $this->StorageModel->saveFile($vals);
  }

  public function handleDeleteFolder($id, bool $force = false): void
  {
    try {
      $this->StorageModel->deleteFolder($id, $force);

      switch ($this->action) {
        case "foldersList":
          $this["foldersList"]->reload();
          break;
        case "folderForm":
          $this->redirect("foldersList");
          break;
      }
    } catch (FolderNotEmptyException $e) {
      $this->flashMessage("Máš tam nějaký soubory, mrkni pro jistotu dovnitř, smazat jí můžeš tam :)", "danger", true);
    }
  }

}