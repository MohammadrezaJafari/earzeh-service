<?php

/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 10/11/15
 * Time: 10:59 PM
 */
namespace Service\Controller\Plugin\ServiceUiGenerator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class PluginFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $doctrineService = $realServiceLocator->get('Doctrine\ORM\EntityManager');
        $translator = $realServiceLocator->get('translator');

        return new Plugin($doctrineService,$translator);
    }
}