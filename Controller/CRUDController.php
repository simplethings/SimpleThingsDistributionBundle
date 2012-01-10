<?php

namespace SimpleThings\DistributionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration AS Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Abstract CRUD Controller that simplifies creation of very simple entities
 * that don't justify using a full-fledged admin bundle. This controller
 * specifically tries to avoid code-generation, however it implements
 * almost the same logic as the Sensio generated CRUD Controller.
 *
 * Assumptions:
 * - no show method. Implement this yourself.
 * - No hooks (yet), just for very simple entities.
 * - Requires to register a route loader.
 * - Requires FrameworkExtraBundle, every action is @Template tagged.
 * - No paginator yet, but will be based on pagerfanta.
 * - Route prefix is generated based on pattern "{bundle_alias}_crud_{entityshortname}"
 */
abstract class CRUDController extends Controller
{

    protected $entityClassName;

    /**
     * @return Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine')->getEntityManager();
    }

    protected function getCreateForm()
    {
        $config = $this->getConfiguration();
        $fields = $config['new']['fields'];
        $builder = $this->createFormBuilder();
        
        if($fields == '*') {
            $em = $this->getManager();
            $cm = $em->getClassMetadata($this->entityClassName);
            foreach ($cm->fieldMappings as $fieldName => $mapping) {
                if (!in_array($fieldName, $cm->identifier)) {
                    $builder->add($fieldName);
                }
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field => $type) {
                $builder->add($fieldName, $type);
            }
        } else {
            throw new \Exception();
        }
        
        return $builder->getForm();
    }

    protected function getEditForm()
    {
        $config = $this->getConfiguration();
        $fields = $config['edit']['fields'];
        $builder = $this->createFormBuilder();
        
        if($fields == '*') {
            $em = $this->getManager();
            $cm = $em->getClassMetadata($this->entityClassName);
            foreach ($cm->fieldMappings as $fieldName => $mapping) {
                if (!in_array($fieldName, $cm->identifier)) {
                    $builder->add($fieldName);
                }
            }
        } elseif(is_array($fields)) {
            foreach($fields as $field => $type) {
                $builder->add($fieldName, $type);
            }
        } else {
            throw new \Exception();
        }
        
        return $builder->getForm();
    }

    protected function getFields()
    {
        $em = $this->getManager();
        $cm = $em->getClassMetadata($this->entityClassName);

        $fields = array();
        foreach ($cm->fieldMappings as $fieldName => $mapping) {
            $fields[] = $fieldName;
        }
        return $fields;
    }

    protected function getConfiguration()
    {

        return array(
            'list' => array(
                'fields' => '*',
            ),
            'new' => array(
                'fields' => '*'
            ),
            'edit' => array(
                'fields' => '*'
            ),
            'actions' => array(
                'edit' => true,
                'new' => true,
                'delete' => true
            )
        );
        
    }

    /**
     * @Extra\Template()
     */
    public function createAction()
    {
        $class = $this->entityClassName;
        $entity = new $class();
        $request = $this->getRequest();

        $form = $this->getCreateForm();
        $form->setData($entity);
        $form->bindRequest($request);

        if ($form->isValid()) {

            // TODO: remove!
            $entity->SDEACODEID = rand(1, 10000);

            $em = $this->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        return $this->filterResponse(
            array(
                'entity' => $entity,
                'form' => $form->createView(),
                'route_name_prefix' => $this->getRouteNamePrefix()
            ), 
            array(
            'action' => 'create'
            )
        );
    }

    /**
     * @Extra\Template()
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getManager();
            $entity = $em->getRepository($this->entityClassName)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ' . $this->entityClassName . ' entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                ->add('id', 'hidden')
                ->getForm()
        ;
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Extra\Template()
     */
    public function editAction($id)
    {
        $em = $this->getManager();

        $entity = $em->getRepository($this->entityClassName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ' . $this->entityClassName . ' entity.');
        }

        $editForm = $this->createForm($this->getEditFormType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'route_name_prefix' => $this->getRouteNamePrefix()
        );
    }

    /**
     * Lists all entities.
     *
     * @Extra\Template()
     */
    public function indexAction()
    {
        $em = $this->getManager();

        $entities = $em->getRepository($this->entityClassName)->findAll();

        return $this->filterResponse(array(
                'entities' => $entities,
                'fields' => $this->getFields()
                ), array(
                'action' => 'index'
            ));
    }

    /**
     * Displays a form to create a new  entity.
     *
     * @Extra\Template()
     */
    public function newAction()
    {
        $class = $this->entityClassName;
        $entity = new $class();
        $form = $this->getCreateForm();
        $form->setData($entity);

        return $this->filterResponse(array(
                'entity' => $entity,
                'form' => $form->createView(),
                'route_name_prefix' => $this->getRouteNamePrefix()
                ), array(
                'action' => 'new'
            ));
    }

    /*
     * Edits an existing  entity.
     *
     * @Extra\Template()
     */

    public function updateAction($id)
    {
        $em = $this->getManager();

        $entity = $em->getRepository($this->entityClassName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ' . $this->entityClassName . ' entity.');
        }

        $editForm = $this->createForm($this->getEditFormType(), $entity);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix() . '_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'route_name_prefix' => $this->getRouteNamePrefix()
        );
    }

    protected function getRouteNamePrefix()
    {
        return self::getControllerAlias() . '_crud_' . self::getControllerName();
    }

    protected function filterResponse($data, $params = array())
    {
        return $data;
    }

    static public function getControllerAlias()
    {
        $class = get_called_class();
        if (preg_match('(([a-zA-Z0-9\\\\]+Bundle))', $class, $match)) {
            $alias = \Symfony\Component\DependencyInjection\Container::underscore(str_replace(array("\\", "Bundle"), "", $match[1]));
            return $alias;
        } else {
            throw new \RuntimeException("Controller is not inside a bundle");
        }
    }

    static public function getControllerName()
    {
        $class = get_called_class();
        return strtolower(str_replace('Controller', '', substr($class, strrpos($class, '\\') + 1)));
    }

}

