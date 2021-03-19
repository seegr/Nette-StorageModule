<?php

namespace App\StorageModule\DI;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\IRouter;


class RouterFactory implements \App\Router\IRouterFactory
{

	public function createRouter(): IRouter
	{
		$router = new RouteList;

		$router[] = new Route("admin/uloziste/slozky", "Storage:Admin:Storage:foldersList");
		$router[] = new Route("admin/uloziste/slozka", "Storage:Admin:Storage:folderForm");

		return $router;
	}

}