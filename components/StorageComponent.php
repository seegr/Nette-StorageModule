<?php

namespace App\StorageModule\Components;

use App\StorageModule\Model\StorageModel;
use Monty\FileSystem;
use Monty\Form;
use Nette\Application\Responses\FileResponse;


class StorageComponent extends \Monty\BaseControl
{

  protected $folderId;
  private $StorageModel;
  private $allow;
  private $filesPath;


  public function __construct(
    StorageModel $StorageModel,
    $filesPath)
  {
    $this->StorageModel = $StorageModel;
    $this->filesPath = $filesPath;

    bdump($filesPath, "filesPath");

    return $this;
  }

  public function render(): void
  {
    $template = $this->template;
    $template->setFile(__DIR__ . "./templates/storage.latte");

    $template->id = $this->getId();
    $template->folder = $this->StorageModel->getFolder($this->folderId);
    $template->files = $this->StorageModel->getFolderFiles($this->folderId);

    $this->template->allow = $this->allow ? true : false;

    $template->render();
  }

  public function setFolderId(int $id): self
  {
    $this->folderId = $id;

    return $this;
  }

  public function createComponentPasswordForm(): Form
  {
    $form = new Form;

    $form->addText("password", "Heslo")->setRequired();
    $form->addSubmit("submit", "Odemknout");

    $form->onSuccess[] = function($f, $v) {
      $folder = $this->StorageModel->getFolder($this->folderId);
      $presenter = $this->getPresenter();

      if ($v->password === $folder->password) {
        $this->allow = true;
        $this->redrawControl("files");
        $presenter->flashMessage("Heslo přijato");
      } else {
        $presenter->flashMessage("Špatné heslo", "warning");
      }
    };

    return $form;
  }
 

  public function handleDownloadFile(string $hash): void
  {
    bdump($hash, "hash");
    $file = $this->StorageModel->getFileByHash($hash);
    $f = $this->StorageModel->getFileData($hash);
    bdump($file, "file");
    bdump($f, "f");

    $response = new FileResponse($this->filesPath . "/" . $f->url, "$file->title", FileSystem::getMime($f->ext));
    $this->getPresenter()->sendResponse($response);
  }

}