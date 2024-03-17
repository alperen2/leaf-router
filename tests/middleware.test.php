<?php

use App\ExampleMiddleware;
use Leaf\Router;

class TMid
{
    static $callstack = '';
}


test('in-route middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_SERVER['REQUEST_URI'] = '/';

	$m = function () {
		echo '1';
	};

    $router = new Router;
	
	$router->post('/', ['middleware' => $m, function () {
		echo '2';
	}]);

	ob_start();
	$router->run();

	expect(ob_get_contents())->toBe('12');
	ob_end_clean();
});

test('before route middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'PUT';
	$_SERVER['REQUEST_URI'] = '/';

    $router = new Router;

	$router->before('PUT', '/', function () {
		echo '1';
	});
	$router->put('/', function () {
		echo '2';
	});

	ob_start();
	$router->run();

	expect(ob_get_contents())->toBe('12');
	ob_end_clean();
});

test('before router middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'PATCH';
	$_SERVER['REQUEST_URI'] = '/test';

    $router = new Router;

	$router->before('PATCH', '/.*', function () {
		echo '1';
	});
	$router->patch('/test', function () {
		echo '2';
	});

	ob_start();
	$router->run();

	expect(ob_get_contents())->toBe('12');
	ob_end_clean();
});

test('before router class middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/test';

	require __DIR__.'/setup/ExampleMiddleware.php';
	Router::setContainer(null);
	Router::before('GET', '/.*', [ExampleMiddleware::class, 'checkAuth']);
	Router::get('/test', function () {
		echo '2';
	});

	ob_start();
	Router::run();

	expect(ob_get_contents())->toBe('auth ok2');
	ob_end_clean();
});

test('after router middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'PUT';
	$_SERVER['REQUEST_URI'] = '/test';

    $router = new Router;

	$router->put('/test', function () {
		echo '1';
	});

	ob_start();
	$router->run(function () {
        echo '2';
    });

	expect(ob_get_contents())->toBe('12');
	ob_end_clean();

	// resets
	$router->hook('router.after', function () {});
});


test('middleware is only called for routes that run', function () {
	$_SERVER['REQUEST_METHOD'] = 'PUT';
	$_SERVER['REQUEST_URI'] = '/users/disable/5';

	$router = new Router;

	$router->group('/users', function () use ($router) {
		/**
		 * Disables a user
		 */
		$router->put('/disable/{id}', [
			'middleware' => function () {
				echo 'mid 1';
			},
			function ($id) {
				echo 'test 1';
			}
		]);

		$router->put('/enable/{id}', [
			'middleware' => function () {
				echo 'mid 2';
			},
			function ($id)  {
				echo 'test 2';
			}
		]);
		
		$router->put('/delete/{id}', [
			'middleware' => function () {
				echo 'mid 3';
			},
			function ($id)  {
				echo 'test 3';
			}
		]);
	});

	ob_start();
	$router->run();

	expect(ob_get_contents())->toBe('mid 1test 1');
	ob_end_clean();
});

