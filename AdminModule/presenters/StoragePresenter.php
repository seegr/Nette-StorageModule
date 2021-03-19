<?php

namespace App\StorageModule\AdminModule\Presenters;

use App\StorageModule\Traits\StorageTrait;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Monty\DataGrid;
use Monty\Dropzone;
use Monty\Html;


class StoragePresenter extends \App\AdminModule\Presenters\AdminPresenter
{

  use StorageTrait;

  /** @var \App\StorageModule\Model\StorageModel @inject */
  public $StorageModel;

  /** @var \App\Managers\UsersManager @inject */
  public $UsersManager;

  /** @var \App\StorageModule\Components\FormsFactory @inject */
  public $FormsFactory;

  protected $folderId;

  
  public function actionFolderForm(string $hash = null): void
  {
    if ($hash) {
      $folder = $this->StorageModel->getFolder($hash);
      bdump($folder, "folder");
      $this->folderId = $folder->id;
      $this->template->folder = $folder;

      $this["folderForm"]->setDefaults($folder);
      $this["folderFilesList"]->setDataSource($this->StorageModel->getFolderFiles($folder->id));
    }
  }


  public function createComponentFoldersList(): DataGrid
  {
    $list = new DataGrid;

    $superadmin = $this->getUser()->isInRole("superadmin") ? true : false;
    $folders = $this->StorageModel->getFolders();

    if (!$superadmin) {
      $folders->where("user", $this->getUser()->id);
    }

    $list->setDataSource($folders);

    $list->addColumnText("locked", "")->setRenderer(function($i) {
      if (!empty($i->password)) {
        return (Html::el("i class='fad fa-lock'"));
      }
    })->setFitContent();
    $list->addColumnLink("title", "Název", "folderForm", null, ["hash"]);
    if ($superadmin) {
      $list->addColumnText("user", "Vlastník")->setRenderer(function($i) {
        $user = $this->UsersManager->getUser($i->user);
        
        return $user->fullname;
      });
    }
    $list->addColumnDateTime("created", "Vytvořeno")->setFormat(self::DATETIME_FORMAT);
    $list->addAction("delete", "", "deleteFolder!")
      ->setConfirm("Jo?")
      ->setClass("fad fa-trash text-danger ajax");

    return $list;
  }

  public function createComponentFolderForm(): Form
  {
    $form = $this->FormsFactory->folderForm();

    $form["save"]->onClick[] = function($form, $vals) {
      $this->saveFolder($vals);
      $this->redirect("foldersList");
    };

    $form["save_stay"]->onClick[] = function($form, $vals) {
      $id = $this->saveFolder($vals);
      $this->redirect("this", $id);
    };

    $form["cancel"]->onClick[] = function($form, $vals) {
      $this->redirect("foldersList");
    };

    return $form;
  }

  public function createComponentFolderFilesList(): DataGrid
  {
    $list = new DataGrid;

    $list->addColumnText("locked", "")->setRenderer(function($i) {
      if (!empty($i->hidden)) {
        return (Html::el("i class='fad fa-lock'"));
      }
    })->setFitContent();
    $list->addColumnText("title", "Název");
    $list->addGroupAction("Skrýt soubory")->onSelect[] = function($ids) use ($list) {
      bdump($ids, "ids");
      $files = $this->StorageModel->getFiles()->where("id", $ids);
      foreach ($files as $file) {
        $file->update(["hidden" => !$file->hidden]);
      }
      $list->reload();
		};
    $list->addGroupAction("Smazat soubory")->onSelect[] = function($ids) use ($list) {
      bdump($ids);
      $this->StorageModel->getFiles()->where("id", $ids)->delete();
      $list->reload();
		};

    $list->setRefreshUrl(false);

    return $list;
  }

  public function createComponentFolderFilesDropzone(): Dropzone
  {
    $dz = new Dropzone;

    $dz->showThumbnails(false);

    $dz->onUpload[] = function($file) {
      bdump($file);
      $name = $file->getName();
      $fileId = $this->FilesManager->storeFile($file, $this->getUser()->id, $name);
      $this->StorageModel->saveFile(ArrayHash::from([
        "folder" => $this->folderId,
        "file" => $fileId,
        "title" => $name
      ]));
    };

    $dz->onUploadComplete[] = function() {
      bdump("a je to");
      $this->redrawControl("files");
      $this->flashMessage("A je to :)");
    };

    return $dz;
  }

}