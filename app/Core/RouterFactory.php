<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        //$router->addRoute('pet/<action>[/<id>]', 'Pet:default');
        //$router->addRoute('attribute/<action>[/<id>]', 'Attribute:default');
        //$router->addRoute('pet', 'Pet:default');

        $router->addRoute('api/pet/create', 'Pet:create');
        $router->addRoute('api/pet/update', 'Pet:update');
        $router->addRoute('api/pet[/<id>]', 'Pet:find');
        $router->addRoute('api/pet/delete/<id>', 'Pet:delete');
        $router->addRoute('api/attributes', 'Pet:getAttributes');

        // Add more routes here...

        // Default route with <presenter>
        //$router->addRoute('<presenter>/<action>[/<id>]', 'Attribute:default');

        return $router;
    }
}
