<?php
/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 10/11/15
 * Time: 11:02 PM
 */

namespace Service\Controller\Plugin\ServiceUiGenerator;

use Doctrine\DBAL\Connection;
use Zend\View\Model\ViewModel;
use Ellie\UI\Form;
use Ellie\UI\Element\TreeSelect;
use Ellie\UI\Element\Button;
use Ellie\UI\Element\Text;
use Ellie\UI\Element\Textarea;
use Ellie\UI\Element\CheckBox;
use Ellie\UI\Set\TabSet;
use Ellie\UI\Set\FieldSet;
use Application\Entity\Service;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
class Plugin extends AbstractPlugin
{
    protected $doctrineService;

    public function __construct($doctrineService)
    {
        $this->doctrineService = $doctrineService;
    }

    public function getDeleteServiceForm($currentService,$language)
    {
        $id = (isset($currentService))?$currentService[0]['id']:null;
        $langObj = $this->doctrineService->getRepository('Application\Entity\ServiceLang')->findOneBy(array("language"=>$language->getId(),"service"=>$id));
        $header = "Are you sure about deleting Service ".$langObj->getName()." ?";
        $form     = new Form(['header' => $header,'action' => $this->getController()->url()->fromRoute("service",array("controller"=>"management","action"=>"delete","id"=>$id,"lang"=>$language->getCode())),'name'=>'serviceDeleteForm']);
        $submit = new Button();
        $form->addChild($submit);

        return $form;
    }

    public function getCreateServiceForm($services ,$languageCode, $currentService= null){
        $header = (isset($currentService))?"Edit Service":"Create new Service";
        $action = (isset($currentService))?"edit":"create";
        $id = (isset($currentService))?$currentService[0]['id']:null;
        $serviceLangs = (isset($currentService))?(($currentService[0]["code"]=="fa")?array("fa"=>$currentService[0],"en"=>$currentService[1]):array("fa"=>$currentService[1],"en"=>$currentService[0])):array();
        // die(var_dump($languageCode));
        $form     = new Form(['header' => $header,'action' => $this->getController()->url()->fromRoute("service",array("controller"=>"management","action"=>$action,"id"=>$id,"lang"=>$languageCode)),'name'=>'serviceForm']);

        $tab = new TabSet();

        $fieldsetFa = new FieldSet(['name' => 'serviceFa','header' => 'Add A New Service' , 'label' => 'Fa']);
        $serviceNameFa = new Text([
            'name' => 'name[fa]',
            'placeholder' => 'Service Name',
            'type' => 'text',
            'value' => (isset($serviceLangs["fa"]["name"]))?$serviceLangs["fa"]["name"]:"",
            'label' => 'Service Name',
        ]);

        $descriptionFa = new Textarea([
            'name' => 'description[fa]',
            'placeholder' => 'Description ...',
            'label' => 'Description',
            'value'=>(isset($serviceLangs["fa"]["description"]))?$serviceLangs["fa"]["description"]:"",
        ]);

        $enablCheckboxFa = new CheckBox(['name' => 'enable[fa]', 'label' => 'Enable' ,'checked'=>(isset($serviceLangs["fa"]["enable"]))?$serviceLangs["fa"]["enable"]:"0",'option'=>'']);

        $fieldsetFa->addChild($serviceNameFa, 'serviceNameFa');
        $fieldsetFa->addChild($descriptionFa, 'username');
        $fieldsetFa->addChild($enablCheckboxFa);



        $fieldsetEn = new FieldSet(['name' => 'serviceEn','header' => 'Add A New Service' , 'label' => 'En']);
        $serviceNameEn = new Text([
            'name' => 'name[en]',
            'placeholder' => 'Service Name',
            'value' => (isset($serviceLangs["en"]["name"]))?$serviceLangs["en"]["name"]:"",
            'type' => 'text',
            'label' => 'Service Name',
        ]);
        $descriptionEn = new Textarea([
            'name' => 'description[en]',
            'placeholder' => 'Description ...',
            'label' => 'Description',
            'value' => (isset($serviceLangs["en"]["description"]))?$serviceLangs["en"]["description"]:"",
        ]);
        $enablCheckboxEn = new CheckBox(['name' => 'enable[en]', 'label' => 'Enable','checked'=>(isset($serviceLangs["en"]["enable"]))?$serviceLangs["en"]["enable"]:"0",'option'=>'']);

        $fieldsetEn->addChild($serviceNameEn);
        $fieldsetEn->addChild($descriptionEn, 'username');
        $fieldsetEn->addChild($enablCheckboxEn);


        $submit = new Button();

        $fieldsetCat = new FieldSet(['name' => 'parent','label' => 'Parent', 'header' => 'Choose Parent Service']);

        $treeSelect = new TreeSelect([
            "title"=>"choose category of your service",
            "services"=>$services,
            "selected"=>(isset($currentService[0]["parent"]))?$currentService[0]["parent"]:"",
            "name" => "parent",
        ]);
        //die(var_dump($currentService));
        $fieldsetCat->addChild($treeSelect);


        $tab->addChild($fieldsetFa, 'fieldsetFa');
        $tab->addChild($fieldsetEn, 'fieldsetEn');
        $tab->addChild($fieldsetCat,'fieldsetCat');
        $form->addChild($tab);
        $form->addChild($submit, 'submit');

        return $form;



        return $form;

    }

    public function getForTree($language_id = 1,$parent = null){
        $result = array();
        $childObjs = $this->doctrineService->getRepository('Application\Entity\Service')->findBy(array("parent"=>$parent));
       // die(var_dump($childObjs ));
        foreach($childObjs as $childObj){
            $childArray = $this->createArray($childObj,$language_id);
            array_push($result,$childArray);
        }

        return $result;
    }

    protected function createArray(Service $serviceObj,$language_id){
        $serviceLangObj = $this->doctrineService->getRepository('Application\Entity\ServiceLang')->findOneBy(array("language"=>$language_id,"service"=>$serviceObj->getId()));
        return array(
            "id"=>$serviceObj->getId(),
            "label"=> $serviceLangObj->getName(),
            "childList" => $this->getForTree($language_id,$serviceObj->getId())
        );
    }
}