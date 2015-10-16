<?php
/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 10/11/15
 * Time: 11:02 PM
 */

namespace Service\Controller\Plugin\ServiceQuery;
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
}