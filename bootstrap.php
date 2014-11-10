<?php

////////////////////////////
//  INCLUDES
require_once "../../../twig/twig/lib/Twig/Autoloader.php";  Twig_Autoloader::register();
require_once "php/objects/Twig.php";
require_once "php/objects/Twig/Environment.php";
require_once "php/objects/Twig/CoreExtension.php";
require_once "php/objects/Twig/Nodes/Assets.php";
require_once "php/objects/Twig/TokenParsers/Assets.php";
require_once 'php/functions/functions.php';


////////////////////////////
//  SERVER
define('SERVER', 'dev');
// define('SERVER', 'live');


////////////////////////////
//  DEFINITIONS
define('ABS_TO_HOST_PATH',  '__core__/philmprice/block-host');
define('HOST_ROOT',         str_replace('\\', '/', realpath(dirname(__FILE__))));
define('PROJ_HOST_ROOT',    str_replace('\\', '/', str_replace('__core__', 'project', HOST_ROOT)));
define('ABS_ROOT',          str_replace(ABS_TO_HOST_PATH,'',HOST_ROOT));
define('HOST_VENDOR',       'philmprice');
define('HOST_BLOCK',        'block-host');


////////////////////////////
//  LOADER SETUP
$blockAuthor    = 'philmprice';
$blockFolder    = 'block-host';
$loaderDirArray = array(
    '../models/'
);
$loaderNamespaceArray = array(
    'Host\Object'   => '../php/objects/',
    'Host\Model'    => '../models/'
);
$loaderClassArray = array(
    'Host\Controller\BaseControllerCore'       => ABS_ROOT.'__core__/'.$blockAuthor.'/'.$blockFolder.'/controllers/BaseControllerCore.php',
    'Host\Controller\BaseController'           => ABS_ROOT.'project/' .$blockAuthor.'/'.$blockFolder.'/controllers/BaseController.php',
    
    'Host\Controller\IndexControllerCore'      => ABS_ROOT.'__core__/'.$blockAuthor.'/'.$blockFolder.'/controllers/IndexControllerCore.php',
    'Host\Controller\IndexController'          => ABS_ROOT.'project/' .$blockAuthor.'/'.$blockFolder.'/controllers/IndexController.php',
    
    'Host\Controller\ExtenderControllerCore'   => ABS_ROOT.'__core__/'.$blockAuthor.'/'.$blockFolder.'/controllers/ExtenderControllerCore.php',
    'Host\Controller\ExtenderController'       => ABS_ROOT.'project/' .$blockAuthor.'/'.$blockFolder.'/controllers/ExtenderController.php',
    
    'Model\NounCore'                           => ABS_ROOT.'__core__/'.$blockAuthor.'/'.$blockFolder.'/models/NounCore.php',
    'Model\Noun'                               => ABS_ROOT.'project/' .$blockAuthor.'/'.$blockFolder.'/models/Noun.php'
);

$loader = new \Phalcon\Loader();
$loader->registerDirs($loaderDirArray)->registerNamespaces($loaderNamespaceArray)->register();


////////////////////////////
//  DEPENDENCY INJECTOR
$di = new Phalcon\DI();
$di->set('dispatcher',  $dispatcher = new \Phalcon\Mvc\Dispatcher());
$di->set('response',    $response   = new \Phalcon\Http\Response());
$di->set('request',     $request    = new \Phalcon\Http\Request());
$di->set('router',      $router     = new \Phalcon\Mvc\Router());
$di->set('assets',      $assets     = new \Phalcon\Assets\Manager());
$di->set('escaper',     $escaper    = new \Phalcon\Escaper());


////////////////////////////
//  BOOTSTRAP other blocks
$blockFolderArray = Host\Object\AppBlockCore::getBlockFolderArray();
foreach($blockFolderArray AS $blockFolder)
{
    //  IF block folder's bootstrap file exists
    if(file_exists($blockFolder.'bootstrap.php'))
    {
        //  RUN boostrap for block
        require_once($blockFolder.'bootstrap.php');
    }
}


////////////////////////////
//  BIND LOADER
$loader = new \Phalcon\Loader();
$loader->registerDirs(      $loaderDirArray);
$loader->registerClasses(   $loaderClassArray);
$loader->registerNamespaces($loaderNamespaceArray);
$loader->register();


////////////////////////////
//  SYNC PROJECT FOLDER
if(SERVER == 'dev')
{
    Host\Object\ExtenderCore::SyncProjectFolder();

    //  gather up and minify CSS
    /*
    $assetManager   = new \Phalcon\Assets\Manager();
    $assetManager
        ->collection('headerCss')
        ->addCss('../css/style.css')
        ->setTargetPath('css/minified.css')
        ->setTargetUri('css/minified.css')
        ->join(true);

    $assetManager->outputCss('headerCss');
    */

    //  gather up and minify JS
}


////////////////////////////
//  VIEW SETUP
$viewDirArray = array();


////////////////////////////
//  ROOT
$di->set('url', function(){
    $url = new \Phalcon\Mvc\Url();
    $url->setBaseUri('/');
    return $url;
});


////////////////////////////
//  ROUTER
$router->setDefaults( 
    array(
        'controller'    => 'index',
        'action'        => 'index',
        'namespace'     => 'Host\Controller'
    )
);
$router->add("/extender", 
    array(
        'controller'    => 'extender',
        'action'        => 'index',
        'path'          => $_GET['path'],
        'namespace'     => 'Host'
    )
);
$router->add("/extender/extend", 
    array(
        'controller'    => 'extender',
        'action'        => 'index',
        'path'          => $_GET['path'],
        'namespace'     => 'Host'
    )
);


////////////////////////////
//  BIND DATABASE
$di->set('db', function(){
    return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host"      => "localhost",
        "username"  => "root",
        "password"  => "",
        "dbname"    => "blocks-dev"
    ));
});


////////////////////////////
//  BIND VIEW
Twig_Autoloader::register();

$di['twigService'] = function($view, $di) {
    Phalcon\Mvc\View\Engine\Twig::setOptions(array(
        'debug'               => true,
        'charset'             => 'UTF-8',
        'base_template_class' => 'Twig_Template',
        'strict_variables'    => false,
        'autoescape'          => false,
        'cache'               => __DIR__.'/../../cache/twig/',
        'auto_reload'         => null,
        'optimizations'       => -1,
    ));
 
    $twig = new View($view, $di);
    return $twig;
};

$di->set('twigService', function($view, $di) use ($config) {

    //  create twig object
    $option     = array('cache' => '../cache/');
    $arrFolder  = array('../views/');
    $twig       = new \Phalcon\Mvc\View\Engine\Twig($view, $di, $options, $arrFolder);

    return $twig;

}, true);

$di->set('view', function (){

    //  create view
    $view = new \Phalcon\Mvc\View();

    //  set directories
    $view->setViewsDir('../views/');

    $view->registerEngines(array(
        '.twig' => 'twigService'

        /*
        ,".volt" => function($view, $di) {
            $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
            $volt->setOptions(array('compileAlways' => true));
            return $volt;
        }
        */
    ));

    return $view;
});

//////////////////
//  MODELS
$di->setShared('modelsManager', function() {

    $eventsManager = new \Phalcon\Events\Manager();

    //Attach an anonymous function as a listener for "model" events
    $eventsManager->attach('model', function($event, $model){

        //  listeners go here
        
        return true;
    });

    //Setting a default EventsManager
    $modelsManager = new Phalcon\Mvc\Model\Manager();
    $modelsManager->setEventsManager($eventsManager);
    return $modelsManager;
});

$di->setShared('modelsMetadata', new Phalcon\Mvc\Model\Metadata\Memory());