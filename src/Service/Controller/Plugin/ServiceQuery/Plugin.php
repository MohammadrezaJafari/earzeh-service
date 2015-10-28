<?php
/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 10/11/15
 * Time: 11:02 PM
 */

namespace Service\Controller\Plugin\ServiceQuery;
use Application\Entity\WorkAt;
use Doctrine\DBAL\Connection;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
class Plugin extends AbstractPlugin
{
    protected $doctrineService;

    public function __construct($doctrineService)
    {
        $this->doctrineService = $doctrineService;
    }

    public function getLanguageBased($where=array())// add All for deletedAt
    {
        $queryBuilder = $this->doctrineService->createQueryBuilder();
        $queryBuilder
            ->select('s.id','IDENTITY(s.parent) AS parent','sl.enable','sl.name','sl.order','sl.description','sl.deletedAt','sl.createdAt','sl.updatedAt','l.id AS language_id','l.name AS language','l.attribute AS language_attribute','l.code AS code')
            ->from('Application\Entity\Service','s')
            ->join('Application\Entity\ServiceLang','sl','WITH','s.id = IDENTITY(sl.service)')
            ->join('Application\Entity\Language','l','WITH','l.id = IDENTITY(sl.language)');

        if(isset($where["id"]))
                {
                    $queryBuilder->where('s.id = :id')
                    ->setParameters(array('id'=>$where["id"]));
                }
            if(isset($where["languageId"]))
                {
                    $queryBuilder->where('l.id = :languageId')
                    ->setParameters(array('languageId'=>$where["languageId"]));
                }
        if($where["deletedAt"]==null || $where["deletedAt"] != "All" )
        {

            if($where["deletedAt"]!=null)
            {

                $queryBuilder->where('sl.deletedAt = :deletedAt')
                    ->setParameters(array('deletedAt'=>$where["deletedAt"]));
            }else{
                $queryBuilder->andWhere("sl.deletedAt is null");
            }

        }


        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }

    public function getUsers($service_id=null)
        {
            $allUsers = $this->getAllUsers();
            $selectedUsers = $this->getSelectedUsers($service_id);
            $unselectedUsers = $this->exceptUsers($allUsers,$selectedUsers);
            $result = ["selected"=>$selectedUsers,"unselected"=>$unselectedUsers];
            return $result;
        }
    public function exceptUsers($first,$second)
        {
            $result = array();
            foreach($first as $user)
                {
                    $userSerialized = serialize($user);
                    $exists = false;
                    foreach($second as $user2)
                        {
                            $user2Serialized = serialize($user2);
                            if($userSerialized == $user2Serialized)
                                $exists = true;
                        }
                    if(!$exists)
                        array_push($result,$user);
                }
            return $result;
        }
    public function getAllUsers(){
        $queryBuilder = $this->doctrineService->createQueryBuilder();
        $queryBuilder->
        select('u.id','IDENTITY(u.defaultLanguage) AS defaultLanguage','IDENTITY(u.role) AS role','u.username','u.password','u.country','u.email','u.avatar','u.deletedAt','u.updatedAt','u.createdAt')
            ->from('Application\Entity\User','u');

        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }
    public function getSelectedUsers($service_id=null){
        if($service_id != null)
        {
        $queryBuilder = $this->doctrineService->createQueryBuilder();
        $queryBuilder->
        select('u.id','IDENTITY(u.defaultLanguage) AS defaultLanguage','IDENTITY(u.role) AS role','u.username','u.password','u.country','u.email','u.avatar','u.deletedAt','u.updatedAt','u.createdAt')
            ->from('Application\Entity\User','u')
            ->join('Application\Entity\WorkAt','w','WITH','u.id = IDENTITY(w.user)');

            $queryBuilder->where('w.service = :service')
                ->setParameters(array('service'=>$service_id));


        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        }else{$results = array();}
        return $results;
    }

    public function updateLanguageEntities($submitedData,$id)
        {
            $rows = $this->doctrineService->getRepository('Application\Entity\ServiceLang')->findBy(array("service"=>$id));
            foreach($rows as $serviceTemp){
                $lang = $serviceTemp->getLanguage();
                $serviceTemp->setEnable((isset($submitedData["enable"][$lang->getCode()]))?1:0);
                $serviceTemp->setName($submitedData["name"][$lang->getCode()]);
                $serviceTemp->setDescription($submitedData["description"][$lang->getCode()]);
                $serviceTemp->setUpdatedAt(new \DateTime(date("Y-m-d H:i:s")));
            }
        }

    public  function deleteLanguageEntities($id)
        {
            $rows = $this->doctrineService->getRepository('Application\Entity\ServiceLang')->findBy(array("service"=>$id));
            foreach($rows as $serviceTemp){
                $serviceTemp->setDeletedAt(new \DateTime(date("Y-m-d H:i:s")));
                $this->doctrineService->flush();
            }
        }

    public function updateWorkAt($serviceEntity,$selected,$oldSelected){
        //selected is array of ids
        foreach($oldSelected as $oldUsers)
            {
                $key = array_search($oldUsers["id"],$selected);
                if($key == false)
                    {
                        $user = $this->doctrineService->getRepository('Application\Entity\WorkAt')->findOneBy(array("user"=>$oldUsers["id"]));
                        $this->doctrineService->remove($user);
                    }else{
                        unset($selected[$key]);
                    }
            }

        foreach($selected as $newUser)
            {
                $workAtEntity = new WorkAt();
                $userEntity = $this->doctrineService->find('Application\Entity\User',$newUser);
                $workAtEntity->setService($serviceEntity);
                $workAtEntity->setUser($userEntity);
                $this->doctrineService->persist($workAtEntity);
            }
        $this->doctrineService->flush();
    }
}