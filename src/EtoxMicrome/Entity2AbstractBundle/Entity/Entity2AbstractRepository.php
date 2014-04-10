<?php

namespace EtoxMicrome\Entity2AbstractBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Entity2AbstractRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Entity2AbstractRepository extends EntityRepository
{
    public function getValToSearch($field)
    {
        switch ($field) {
            case "hepatotoxicity":
                $valToSearch="hepval";
                break;
            case "cardiotoxicity":
                $valToSearch="cardval";
                break;
            case "nephrotoxicity":
                $valToSearch="nephval";
                break;
            case "phospholipidosis":
                $valToSearch="phosval";
                break;
        }
        return $valToSearch;
    }

    public function getOrderBy($orderBy, $valToSearch)
    {
        switch ($orderBy) {
            case "score":
                $orderBy=$valToSearch;
                break;
            case "pattern":
                $orderBy="patternCount";
                break;
            case "rule":
                $orderBy="ruleScore";
                break;
            case "term":
                $orderBy="hepTermVarScore";
                break;
        }
        return $orderBy;
    }

    public function getEntity2AbstractFromField($field, $typeOfEntity, $arrayEntityName, $orderBy)
    {
        return $this->getEntity2AbstractFromFieldDQL($field, $typeOfEntity, $arrayEntityName, $orderBy)->getResult();
    }

    public function getEntity2AbstractFromFieldDQL($field, $entityType, $arrayEntityName, $orderBy)
    {//("hepatotoxicity","CompoundDict",arrayEntityId)
        $message="inside getEntity2AbstractFromFieldDQL";
        ld($orderBy);
        $valToSearch=$this->getValToSearch($field);//"i.e hepval, embval... etc"
        if ($orderBy=="hepval" or $orderBy=="score"){
            $orderBy="hepval desc";
        }elseif($orderBy=="svmConfidence"){
            $orderBy="svmConfidence desc";
        }elseif($orderBy=="pattern"){
            $orderBy="patternCount asc";
        }elseif($orderBy=="term"){
            $orderBy="hepTermVarScore asc";
        }elseif($orderBy=="rule"){
            $orderBy="ruleScore asc";
        }elseif($orderBy=="curation"){
            $orderBy="curation desc";
        }
        /*$sql="SELECT e2a,a
            FROM EtoxMicromeEntity2AbstractBundle:Entity2Abstract e2a
            JOIN e2a.abstracts a
            WHERE e2a.name IN (:arrayEntityName)
            AND e2a.qualifier = :entityType
            AND a.$valToSearch is not NULL
            ORDER BY a.$valToSearch desc
            ";
        */
        $sql="SELECT e2a
            FROM EtoxMicromeEntity2AbstractBundle:Entity2Abstract e2a
            WHERE e2a.name IN (:arrayEntityName)
            AND e2a.qualifier = :entityType
            AND e2a.$valToSearch is not NULL
            ORDER BY e2a.$orderBy
            ";
        $query = $this->_em->createQuery($sql);
        $query->setParameter("arrayEntityName", $arrayEntityName);
        $query->setParameter('entityType', $entityType);
        //ldd($query->getSql());
        /*
        $rawSql = $query->getSql();
        print_r(array(
            'parameters' => $query->getParameters(),
        ));
        ldd($rawSql);
        */
        return $query;

    }

    public function getCompound2Term2DocumentRelations($field, $typeOfEntity, $arrayEntityName)
    {
        return $this->getCompound2Term2DocumentRelationsDQL($field, $typeOfEntity, $arrayEntityName)->getResult();
    }

    public function getCompound2Term2DocumentRelationsDQL($field, $entityType, $arrayEntityName)
    {//("hepatotoxicity","pubmed","CompoundDict",arrayEntityId)
        $valToSearch=$this->getValToSearch($field);//"i.e hepval, embval... etc"
        //We have to create a query that searchs all over the entityIds inside the $arrayEntityId
        $sql="SELECT c2t2d
            FROM EtoxMicromeEntity2DocumentBundle:Compound2Term2Document c2t2d
            WHERE c2t2d.compoundName IN (:arrayEntityName)
            ORDER BY c2t2d.relationScore desc, c2t2d.hepval desc
            ";

        //ld($sql);
        $query = $this->_em->createQuery($sql);
        $query->setParameter("arrayEntityName", $arrayEntityName);
        return $query;

    }

    public function getCompound2Cytochrome2DocumentFromField($field, $typeOfEntity, $arrayEntityName)
    {
        return $this->getCompound2Cytochrome2DocumentFromFieldDQL($field, $typeOfEntity, $arrayEntityName)->getResult();
    }

    public function getCompound2Cytochrome2DocumentFromFieldDQL($field, $entityType, $arrayEntityName)
    {//("hepatotoxicity","pubmed","CompoundDict",arrayEntityId)
        $valToSearch=$this->getValToSearch($field);//"i.e hepval, embval... etc"
        //We have to create a query that searchs all over the entityIds inside the $arrayEntityId
        $sql="SELECT c2t2d
            FROM EtoxMicromeEntity2DocumentBundle:Compound2Cyp2Document c2t2d
            WHERE c2t2d.cyp IN (:arrayEntityName)
            ORDER BY c2t2d.relationScore desc
            ";

        //ld($sql);
        $query = $this->_em->createQuery($sql);
        $query->setParameter("arrayEntityName", $arrayEntityName);
        return $query;

    }

    public function getCompound2Marker2DocumentFromField($field, $typeOfEntity, $arrayEntityName)
    {
        return $this->getCompound2Marker2DocumentFromFieldDQL($field, $typeOfEntity, $arrayEntityName)->getResult();
    }

    public function getCompound2Marker2DocumentFromFieldDQL($field, $entityType, $arrayEntityName)
    {//("hepatotoxicity","pubmed","CompoundDict",arrayEntityId)
        $valToSearch=$this->getValToSearch($field);//"i.e hepval, embval... etc"
        //We have to create a query that searchs all over the entityIds inside the $arrayEntityId
        $sql="SELECT c2m2d
            FROM EtoxMicromeEntity2DocumentBundle:Compound2Marker2Document c2m2d
            WHERE c2m2d.liverMarkerName IN (:arrayEntityName)
            ORDER BY c2m2d.relationScore desc
            ";

        //ld($sql);
        $query = $this->_em->createQuery($sql);
        $query->setParameter("arrayEntityName", $arrayEntityName);
        return $query;

    }

    public function findEntity2AbstractFromAbstract($abstract)
    {
        //Function to search all the entities involved in a particular sentence in order to highlight them
        $abstractId=$abstract->getId();

        $em = $this->getEntityManager();
        $consulta = $em->createQuery('
            SELECT e2a
            FROM EtoxMicromeEntity2AbstractBundle:Entity2Abstract e2a
            WHERE e2a.abstracts = :abstractId
        ');
        $consulta->setParameter('abstractId', $abstractId);
        return $consulta->execute();
    }

    public function updateEntity2AbstractCuration($entity2AbstractId, $action)
    {
        /*Here we get the entity2Abstract and the action to take for the curation value.
        $action can be check or cross.
        If $action==check, then we have to add one to the curation field of the Entity2Abstract register
        If $action==cross, then we have to substract one to the curation field of the Entity2Abstract register

        After that, taking into account the curation value, we have to generate the html to render inside the curation
        */

        //ld($entity2AbstractId);
        //ldd($action);

        $em = $this->getEntityManager();
        $entity2Abstract=$em->getRepository('EtoxMicromeEntity2AbstractBundle:Entity2Abstract')->findOneById($entity2AbstractId);
        if (!$entity2Abstract) {
            throw $this->createNotFoundException(
                "Cannot curate this Entity2Abstract $entity2AbstractId"
            );
        }
        else{
            $curation=$entity2Abstract->getCuration();
            if ($action=="check"){
                $entity2Abstract->setCuration($curation + 1);
            }elseif($action=="cross"){
                $entity2Abstract->setCuration($curation - 1);
            }
            $em->flush();
            $curationReturn=$entity2Abstract->getCuration();
            return($curationReturn);
        }
        return ($curationReturn);
    }

    public function getEntitySummary($entity2AbstractId, $qualifier){
        $em = $this->getEntityManager();
        $dictionary=array();
        $stringOutput="";

        if ($qualifier=="CompoundDict"){
            $entity2Document=$em->getRepository('EtoxMicromeEntity2AbstractBundle:Entity2Abstract')->findOneById($entity2AbstractId);
            $nameEntity=$entity2Document->getName();
            $entity=$em->getRepository("EtoxMicromeEntityBundle:CompoundDict")->findOneByName($nameEntity);
            if($entity==null){
                $stringOutput="";
                return $stringOutput;
            }
            //Once we have the entity itself we have to create a dictionary to save with key=field, value=field_value which can be processed to create the string to the mouseover
            $name=$entity->getName();

            if($name!=""){
                $dictionary["name"]="<a href='http://www.ncbi.nlm.nih.gov/pubmed/?term=\"$name\"[MeSH+Terms]' target='_blank'>$name</a>";
            }
            $chemIdPlus=$entity->getChemIdPlus();
            if($chemIdPlus!=""){
                $dictionary["chemIdPlus"]=$chemIdPlus;
            }
            $chebi=$entity->getChebi();
            if($chebi!=""){
                $dictionary["chebi"]=$chebi;
            }
            $inChi=$entity->getInChi();
            if($inChi!=""){
                $dictionary["inChi"]=$inChi;
            }
            $drugBank=$entity->getDrugBank();
            if($drugBank!=""){
                $dictionary["drugBank"]=$drugBank;
            }
            $humanMetabolome=$entity->getHumanMetabolome();
            if($humanMetabolome!=""){
                $dictionary["humanMetabolome"]=$humanMetabolome;
            }
            $keggCompound=$entity->getKeggCompound();
            if($keggCompound!=""){
                $dictionary["keggCompound"]=$keggCompound;
            }
            $keggDrug=$entity->getKeggDrug();
            if($keggDrug!=""){
                $dictionary["keggDrug"]=$keggDrug;
            }
            $mesh=$entity->getMesh();
            if($mesh!=""){
                $dictionary["mesh"]=$mesh;
            }
            $nrDbIds=$entity->getNrDbIds();
            if($nrDbIds!=""){
                $dictionary["nrDbIds"]=$nrDbIds;
            }
            $smile=$entity->getSmile();
            if($smile!=""){
                $dictionary["smile"]=$smile;
            }
        }

        if($qualifier=="Marker"){
            $entity2Document=$em->getRepository('EtoxMicromeEntity2AbstractBundle:Entity2Abstract')->findOneById($entity2AbstractId);
            $nameEntity=$entity2Document->getName();
            $entity=$em->getRepository("EtoxMicromeEntityBundle:Marker")->findOneByName($nameEntity);
            //Once we have the entity itself we have to create a dictionary to save with key=field, value=field_value which can be processed to create the string to the mouseover
            $name=$entity->getName();
            if($name!=""){
                $dictionary["name"]=$name;
            }
            $tax=$entity->getTax();
            if($tax!=""){
                $dictionary["tax"]=$tax;
            }
            $markerType=$entity->getMarkerType();
            if($markerType!=""){
                $dictionary["markerType"]=$markerType;
            }
        }

        if($qualifier=="Specie"){
            $entity2Document=$em->getRepository('EtoxMicromeEntity2AbstractBundle:Entity2Abstract')->findOneById($entity2AbstractId);
            $nameEntity=$entity2Document->getName();
            $entity=$em->getRepository("EtoxMicromeEntityBundle:Specie")->findOneByName($nameEntity);
            //Once we have the entity itself we have to create a dictionary to save with key=field, value=field_value which can be processed to create the string to the mouseover
            $name=$entity->getName();
            if($name!=""){
                $dictionary["name"]=$name;
            }
            $nameClass=$entity->getNameClass();
            if($nameClass!=""){
                $dictionary["nameClass"]=$nameClass;
            }
            $ncbiTaxId=$entity->getNcbiTaxId();
            if($ncbiTaxId!=""){
                $dictionary["NCBItaxId"]=$ncbiTaxId;
            }
            $specieCategory=$entity->getSpecieCategory();
            if($specieCategory!=""){
                $dictionary["specieCategory"]=$specieCategory;
            }
            $specieTox=$entity->getSpecieTox();
            if($specieTox!=""){
                $dictionary["specieTox"]=$specieTox;
            }
        }

        foreach($dictionary as $key => $value){
            if ($value!=""){
                $stringOutput=$stringOutput."$key: $value<br/>";
            }
        }
        return ($stringOutput);
    }
}