<?php

namespace App\StorageModule\DI;

use Nette;
use App\CoreModule\Router\RouterManager;
use App\CoreModule\Router\IRouterFactory;


class StorageExtension extends Nette\DI\CompilerExtension
{
	const ROUTER_TAG = "storage.router";


	public function loadConfiguration()
	{
		$config = $this->config;
		\Tracy\Debugger::barDump($config, "config");

		$builder = $this->getContainerBuilder();
		\Tracy\Debugger::barDump($builder, "builder");
		$pars = $builder->parameters;

		$builder->addDefinition($this->prefix('storageModel'))
			->setFactory(\App\StorageModule\Model\StorageModel::class);
		$builder->addDefinition($this->prefix('formsFactory'))
			->setFactory(\App\StorageModule\Components\FormsFactory::class);
			\Tracy\Debugger::barDump($builder, "builder");
		$builder->addDefinition($this->prefix('storageComponent'))
			->setFactory(\App\StorageModule\DI\StorageComponentFactory::class, ["@storage.storageModel", $pars["wwwDir"]]);
	}

	public function beforeCompile(): void
	{
		$config = $this->config;
		\Tracy\Debugger::barDump($config, "config");

		$builder = $this->getContainerBuilder();
		\Tracy\Debugger::barDump($builder, "builder");
		
		$builder->getDefinition("authorizator")
			->addSetup("addResource", ["Storage:Admin:Storage"]);
	}

}