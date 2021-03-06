<?php

namespace Service\Controller;

use Application\Controller\BaseController;
use Zend\Mvc\Controller\AbstractActionController;// for run in zend's MVC
use Ellie\Interfaces\ControllerInterface;
use Application\Entity\Service;
use Application\Entity\ServiceLang;
use Zend\View\Model\ViewModel;

class ManagementController extends  BaseController
    implements ControllerInterface
{
    //***Services
    protected $doctrineService;
    protected $authService;
    protected $aclService;
    //***Other Vars
    protected $request;
    protected $language;
    //*** Events
    protected $eventHandler;
    //**** Controller Plugins
    protected $serviceQueryPlugin;
    protected $serviceUiGeneratorPlugin;


    public function __construct($services,$eventHandler)
    {
        $this->doctrineService = $services["doctrine"];
        $this->authService = $services["auth"];
        $this->aclService = $services["acl"];

        $this->request = $this->getRequest();
        $this->eventHandler = $eventHandler;

    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $layout = $this->layout();
        $layout->setTemplate('layout/master');
        $this->serviceQueryPlugin = $this->ServiceQuery();
        $this->serviceUiGeneratorPlugin = $this->ServiceUiGenerator();
        $languageCode = $this->params()->fromRoute('lang', 'fa');
        $this->language = $this->doctrineService->getRepository('Application\Entity\Language')->findOneBy(array("code"=> $languageCode));
        if(!isset($this->language))
            die("cant find language");
        return parent::onDispatch($e);
    }

    public function createAction()
    {
        $usersData = $this->serviceQueryPlugin->getUsers();
        if($this->request->isPost())
            {
                $submitedData = (array) $this->request->getPost();
                $serviceEntity = new Service();
                if(!empty($submitedData["parent"])){

                    $parent = $this->doctrineService->find('Application\Entity\Service', $submitedData["parent"]);
                    $serviceEntity->setParent($parent);
                }
                $this->doctrineService->persist($serviceEntity);

                $languages = $this->doctrineService->getRepository('Application\Entity\Language')->findAll();
                foreach($languages as $lang){

                    $serviceTemp =  new ServiceLang();
                    $serviceTemp->setEnable((isset($submitedData["enable"][$lang->getCode()]))?1:0);
                    $serviceTemp->setName($submitedData["name"][$lang->getCode()]);
                    $serviceTemp->setDescription($submitedData["description"][$lang->getCode()]);
                    $serviceTemp->setOrder(0);
                    $serviceTemp->setLanguage($lang);
                    $serviceTemp->setService($serviceEntity);
                    $this->doctrineService->persist($serviceTemp);
                }

                $this->serviceQueryPlugin->updateWorkAt($serviceEntity,isset($submitedData["selectedUsers"])?$submitedData["selectedUsers"]:array(),$usersData["selected"]);
                $this->doctrineService->flush();

                $this->layout()->message = [
                    'type' => 'success',
                    'text' => 'new user  created successfully.'
                ];

            }

        $usersData = $this->serviceQueryPlugin->getUsers();
        $services = $this->serviceUiGeneratorPlugin->getForTree($this->language->getId());

        return $this->serviceUiGeneratorPlugin->getCreateServiceForm($services,$usersData,$this->language->getCode());

    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $usersData = $this->serviceQueryPlugin->getUsers($id);
        if($this->request->isPost())
            {
                $submitedData = (array) $this->request->getPost();
                $serviceEntity = $this->doctrineService->find('Application\Entity\Service',$id);
                if(!empty($submitedData["parent"])){
                    $parent = $this->doctrineService->find('Application\Entity\Service', $submitedData["parent"]);
                    $serviceEntity->setParent($parent);
                }else{
                    $serviceEntity->setParent(null);
                }
                $this->serviceQueryPlugin->updateLanguageEntities($submitedData,$id);
                $this->serviceQueryPlugin->updateWorkAt($serviceEntity,isset($submitedData["selectedUsers"])?$submitedData["selectedUsers"]:array(),$usersData["selected"]);
                $this->doctrineService->flush();
                $this->layout()->message = [
                    'type' => 'success',
                    'text' => ' service updated successfully.'
                ];
            }
        $currentService = $this->serviceQueryPlugin->getLanguageBased(array("id"=>$id,"deletedAt"=>"All"));
        $usersData = $this->serviceQueryPlugin->getUsers($id);
        $services = $this->serviceUiGeneratorPlugin->getForTree($this->language->getId());
        return $this->serviceUiGeneratorPlugin->getCreateServiceForm($services,$usersData,$this->language->getCode(),$currentService);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $currentService = $this->serviceQueryPlugin->getLanguageBased(array("id"=>$id,"deletedAt"=>"All"));
        if($this->request->isPost())
            {
                $this->serviceQueryPlugin->deleteLanguageEntities($id);
                $this->layout()->message = [
                    'type' => 'success',
                    'text' => ' service deleted successfully.'
                ];
            }
        return $this->serviceUiGeneratorPlugin->getDeleteServiceForm($currentService,$this->language);
    }

    public function listAction()
    {
        if($this->authService->hasIdentity())
            {
                $user = $this->authService->getIdentity();
                $availableServices =  $this->serviceQueryPlugin->getAvailableServices($user);
                $result = array();
                foreach ($availableServices as $service ) {
                    $serviceTemp = $this->serviceQueryPlugin->getLanguageBased(array("languageId"=>$this->language->getId(),"deletedAt"=>"","id"=>$service->getId()));
                    array_push($result,$serviceTemp[0]);
                }
                $usersRole = $user->getRole();


                $view = new ViewModel();
                $view->setTemplate('service/datatable');
                $view->setVariables(array("services"=>$result,"canCreate"=>$this->aclService->isAllowed($usersRole->getName(),"createService"),"canManage"=>$this->aclService->isAllowed($usersRole->getName(),"manageService")));
                return $view;

            }
    }

}