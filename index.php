<?php 

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

// use Loader() to autoload our model
$loader = new Loader();

$loader->registerDirs(
    array(
        __DIR__ . '/models/'
    )
)->register();

$di = new FactoryDefault();

// Set up the database service
$di->set('db', function() {
    return new PdoMysql(
        array(
            "host"      => "localhost",
            "username"  => "root",
            "password"  => "0000",
            "dbname"    => "phalcapi"
        )
    );
});

// Create and bind the DI to the application
$app = new Micro($di);

// Define the routes

// Retrieves all robots
$app->get('/api/robots', function() use ($app) {

    $phql = "SELECT * FROM Robots ORDER BY name";
    $robots = $app->modelsManager->executeQuery($phql);

    $data = array();

    foreach ($robots as $robot) {
        $data[] = array(
            'id'    => $robot->id,
            'name'  => $robot->name
        );
    }

    echo json_encode($data);
});

// Searches for robots with $name in their name
$app->get('/api/robots/search/{name}', function($name) use ($app) {

    $phql = "SELECT * FROM Robots WHERE name LIKE :name: ORDER BY name";
    $robots = $app->modelsManager->executeQuery(
        $phql,
        array(
            'name'  => '%' . $name . '%'
        )
    );

    $data = array();

    foreach ($robots as $robot) {
        $data[] = array(
            'id'   => $robot->id,
            'name' => $robot->name
        );
    }

    echo json_encode($data);
});

// Retrieves robots based on primary key
$app->get('/api/robots/{id:[0-9]+}', function($id) use ($app) {

    $phql = "SELECT * FROM Robots WHERE id = :id:";
    $robot = $app->modelsManager->executeQuery($phql, array(
        'id'    => $id
    ))->getFirst();

    // Create a response
    $response = new Response();

    if ($robot == false) {
        $response->setJsonContent(
            array(
                'status'    => 'NOT-FOUND'
            )
        );
    }
    else {
        $response->setJsonContent(
            array(
                'status'    => 'FOUND',
                'data'      => array
                    (
                        'id'    => $robot->id,
                        'name'  => $robot->name
                    )
            )
        );
    }

    return $response;
});

// Add a new robot
$app->post('/api/robots', function() use ($app) {

    $robot = $app->request->getJsonRawBody();

    $phql = "INSERT INTO Robots (name, type, year) VALUES (:name:, :type:, :year:)";

    $status = $app->modelsManager->executeQuery($phql, array(
        'name'  => $robot->name,
        'type'  => $robot->type,
        'year'  => $robot->year
    ));

    // Create a response
    $response = new Response();

    // Check if the insertion was succesful
    if ($status->success == true) {

        // Change the HTTP status
        $response->setStatusCode(201, "Created");

        $robot->id = $status->getModel()->id;

        $response->setJsonContent(
            array(
                'status'    => 'OK',
                'data'      => $robot
            )
        );
    }
    else {

        // Change the HTTP status
        $response->setStatusCode(409, "Conflict");

        // Send errors to the client
        $errors = array();

        foreach ($status->getMessage() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'    => 'ERROR',
                'messages'  => $errors
            )
        );
    }

    return $response;
});

// Updates robots based on primary key
$app->put('/api/robots/{id:[0-9]+}', function() {

});

// Deletes robots based on primary key
$app->delete('/api/robots/{id:[0-9]+}', function() {

});

$app->handle();