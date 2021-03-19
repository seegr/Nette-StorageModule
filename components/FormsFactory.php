<?php

namespace App\StorageModule\Components;

use Nette\Application\UI\Form;
use Nette\Utils\Finder;

use App\Managers\EventsManager;
use App\Components\Utils;

use App\Managers\ArticlesManager;
use App\Managers\SectionsManager;


class FormsFactory extends \App\Components\BaseFormsFactory
{

  public function folderForm()
  {
    $f = $this->newForm();

    $f->addText("title", "Název")->setRequired();
    $f->addTextArea("text", "Text");
    $f->addText("password", "Heslo");
    $f->addCheckBox("hide_all", "Skrýt všechny soubory");
    $f->addHidden("id");
    $f->addHidden("user");

    return $f;
  }

}