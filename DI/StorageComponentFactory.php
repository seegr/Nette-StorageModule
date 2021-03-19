<?php

namespace App\StorageModule\DI;

use App\StorageModule\Components\StorageComponent;
use App\StorageModule\Model\StorageModel;


class StorageComponentFactory
{

  private $StorageModel;
  private $publicPath;


  public function __construct(StorageModel $StorageModel, string $publicPath)
  {
    $this->StorageModel = $StorageModel;
    $this->publicPath = $publicPath;

    return $this;
  }

  public function newStorageComponent(): StorageComponent
  {
    return new StorageComponent($this->StorageModel, $this->publicPath);
  }

}