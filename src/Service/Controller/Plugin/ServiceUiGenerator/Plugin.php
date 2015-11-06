<?php
/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 10/11/15
 * Time: 11:02 PM
 */

namespace Service\Controller\Plugin\ServiceUiGenerator;

use Doctrine\DBAL\Connection;
use Ellie\UI\Element\Assign;
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
    protected $translator;

    public function __construct($doctrineService,$translator)
    {
        $this->translator = $translator;
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

    public function getCreateServiceForm($services, $usersData ,$languageCode, $currentService= null){        $header = (isset($currentService))?"Edit Service":$this->translator->translate("Service Management");
        $action = (isset($currentService))?"edit":"create";
        $id     = (isset($currentService))?$currentService[0]['id']:null;
        $serviceLangs = (isset($currentService))?(($currentService[0]["code"]=="fa")?array("fa"=>$currentService[0],"en"=>$currentService[1]):array("fa"=>$currentService[1],"en"=>$currentService[0])):array();
        $form   = new Form(['header' => $header,'action' => $this->getController()->url()->fromRoute("service",array("controller"=>"management","action"=>$action,"id"=>$id,"lang"=>$languageCode)),'name'=>'serviceForm']);
        $tab    = new TabSet();

        $fieldsetFa = new FieldSet(['name' => 'serviceFa','header' => $this->translator->translate('Add A New Service') , 'label' => 'Fa']);
        $serviceNameFa = new Text([
            'name' => 'name[fa]',
            'placeholder' => $this->translator->translate('Service Name'),
            'type' => 'text',
            'value' => (isset($serviceLangs["fa"]["name"]))?$serviceLangs["fa"]["name"]:"",
            'label' => $this->translator->translate('Service Name'),
        ]);

        $descriptionFa = new Textarea([
            'name' => 'description[fa]',
            'placeholder' => $this->translator->translate('Description') . 'Description ...',
            'label' => $this->translator->translate('Description'),
            'value'=>(isset($serviceLangs["fa"]["description"]))?$serviceLangs["fa"]["description"]:"",
        ]);

        $enablCheckboxFa = new CheckBox(['name' => 'enable[fa]', 'label' => 'Enable' ,'checked'=>(isset($serviceLangs["fa"]["enable"]))?$serviceLangs["fa"]["enable"]:"0",'option'=>'']);

        $fieldsetFa->addChild($serviceNameFa, 'serviceNameFa');
        $fieldsetFa->addChild($descriptionFa, 'username');
        $fieldsetFa->addChild($enablCheckboxFa);



        $fieldsetEn = new FieldSet(['name' => 'serviceEn','header' => '' , 'label' => 'En']);
        $serviceNameEn = new Text([
            'name' => 'name[en]',
            'placeholder' => $this->translator->translate('Service Name'),
            'value' => (isset($serviceLangs["en"]["name"]))?$serviceLangs["en"]["name"]:"",
            'type' => 'text',
            'label' => $this->translator->translate('Service Name'),
        ]);
        $descriptionEn = new Textarea([
            'name' => 'description[en]',
            'placeholder' => $this->translator->translate('Description') . '...',
            'label' => $this->translator->translate('Description'),
            'value' => (isset($serviceLangs["en"]["description"]))?$serviceLangs["en"]["description"]:"",
        ]);
        $enableCheckboxEn = new CheckBox(['name' => 'enable[en]', 'label' => $this->translator->translate('Enable'),'checked'=>(isset($serviceLangs["en"]["enable"]))?$serviceLangs["en"]["enable"]:"0",'option'=>'']);

        $fieldsetEn->addChild($serviceNameEn);
        $fieldsetEn->addChild($descriptionEn);
        $fieldsetEn->addChild($enableCheckboxEn);


        $submit = new Button();

        $fieldsetCat = new FieldSet(['name' => 'parent','label' => 'Parent', 'header' => $this->translator->translate("Parent Service")]);

        $treeSelect = new TreeSelect([
            "title"=> $this->translator->translate("choose category of your service"),
            "services"=>$services,
            "selected"=>(isset($currentService[0]["parent"]))?$currentService[0]["parent"]:"",
            "name" => "parent",
        ]);
        $fieldsetCat->addChild($treeSelect);
        $fieldsetAssign = new FieldSet(["name" => 'assigned-users','label'=>'Assign Users', 'header' => 'assign users to Service']);
        $assign = new Assign([
            "selected" => $usersData["selected"],
            "unselected" => $usersData["unselected"],
            "title"=> "users"
        ]);
        $fieldsetAssign->addChild($assign);



        $tab->addChild($fieldsetFa, 'fieldsetFa');
        $tab->addChild($fieldsetEn, 'fieldsetEn');
        $tab->addChild($fieldsetCat,'fieldsetCat');
        $tab->addChild($fieldsetAssign , 'fieldsetAssign');
        $form->addChild($tab);
        $form->addChild($submit, 'submit');

        return $form;
    }

    public function getForTree($language_id = 1,$parent = null){
        $result = array();
        $childObjs = $this->doctrineService->getRepository('Application\Entity\Service')->findBy(array("parent"=>$parent));
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