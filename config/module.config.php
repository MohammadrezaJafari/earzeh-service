<?php
namespace Service;

return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(

        )
    ),

    'controllers' => array(
        'factories' => array(
            'management' => 'Service\Factory\ManagementControllerFactory',
        ),
        'invokables' =>array(
            //without passing variable controlers
        )
    ),

    'controller_plugins' => array(
        'factories' => array(
            'ServiceQuery' => 'Service\Controller\Plugin\ServiceQuery\PluginFactory',
            'ServiceUiGenerator' => 'Service\Controller\Plugin\ServiceUiGenerator\PluginFactory',
        )
    ),

    'service_manager' => array(
        'factories' => array(
            'Ellie\Service\Log' => 'Ellie\Service\Log\LogServiceFactory',
           // 'Ellie\Service\Authentication' => 'Ellie\Service\Authentication\ServiceFactory',
        )
    ),

    // This lines opens the configuration for the RouteManager
    'router' => array(
        // Open configuration for all possible routes
        'routes' => array(
            // Define a new route called "post"
            'service' => array(
                // Define the routes type to be "Zend\Mvc\Router\Http\Literal", which is basically just a string
                'type' => 'segment',
                // Configure the route itself
                'options' => array(
                    'route'    => '[/:lang]/service[/:controller[/:action[/:id]]]',
                    'constraints' => array(
//                        'lang' => include __DIR__ . "/../../../config/language.config.php",
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    // Define default controller and action to be called when this route is matched
                    'defaults' => array(
                        'lang' => 'fa',
                        'controller' => 'management',
                        'action'     => 'create',
                    )
                ),
            )
        )
    ),

    'navigation_manager' => [
        "service"=>array(
            "label" => "Service Management",
            'route' => 'service',
            'inmenu'=>true,
            'icon'=>"fa fa-archive",
            'params' => array(
                'language'=>"fa",
                'icon'=>"fa fa-archive"
            ),
            'pages' => array(
                array(
                    'label' => 'Create New Service',
                    'route' => 'service',
                    'params'=>array(
                        'lang'=>'en',
                        'controller'=>'management',
                        'action'=>'create',
                    )
                ),
                array(
                    'label' => 'Service List',
                    'route' => 'service',
                    'params'=>array(
                        'lang'=>'en',
                        'controller'=>'manage',
                        'action'=>'list',
                    )
                ),
            ),
        )
    ],

    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),

    'controller_plugins' => array(
        'factories' => array(
            'ServiceQuery' => 'Service\Controller\Plugin\ServiceQuery\PluginFactory',
            'ServiceUiGenerator' => 'Service\Controller\Plugin\ServiceUiGenerator\PluginFactory',
        )
    ),
);