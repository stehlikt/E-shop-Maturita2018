<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = $adminRouter = new RouteList('Admin');
        $adminRouter[] = new Route("/admin", 'Page:default');
        $adminRouter[] = new Route("/admin/sprava-uzivatelu", 'Page:userManagment');
        $routet[] = new Route('Registrace','Register:default');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = new Route( 'Košík', 'Shoppingcart:default');
		$router[] = new Route('Fakturace', 'Shoppingcart:productSend');
		$router[] = new Route('Dokonceni-Objednavky','ShoppingCart:thankYou');
		return $router;
	}
}
