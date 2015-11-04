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
            'Ellie\Service\Authentication' => 'Ellie\Service\Authentication\ServiceFactory',
        )
    ),

    'service_manager' => array(
        'factories' => array(
            'Ellie\Service\Log' => 'Ellie\Service\Log\LogServiceFactory',
            'Ellie\Service\Acl' => 'Ellie\Service\Acl\ServiceFactory',
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
                        'lang' => include __DIR__ . "/../../../config/language.config.php",
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

//    'menu'  => [
//        'User Management' => [
//            'Create New User' => 'manage/create',
//            'Company List' => 'manage/list',
//            'Manager List' => 'user/manage/',
//            'Operator List' => 'user/manage/',
//            'Unregisted List' => 'user/manage/',
//        ]
//    ]
);