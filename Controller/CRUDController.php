<?php

namespace SimpleThings\DistributionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

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
abstract class CRUDController extends BaseController
{
    /**
     * Name of the Doctrine Entity class
     * @todo Refactor into abstract method
     *
     * @var string
     */
    protected $entityClassName;

    /**
     * Configuration for forms and such
     * @todo Refactor into abstract method
     *
     * @var array
     */
    protected $configs;

    public function __construct()
    {
        $defaults = array(
            'name' => $this->getControllerName(),
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
            ),
            'types' => array(),
            'labels' => array()
        );

        $this->configs = array_merge($defaults, $this->getConfiguration());
    }

    /**
     * @return Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine')->getEntityManager();
    }

    protected function getForm($fields)
    {
        $builder = $this->createFormBuilder();

        if($fields == '*') {
            $em = $this->getManager();
            $cm = $em->getClassMetadata($this->entityClassName);
            $fields = array();
            foreach ($cm->fieldMappings as $field) {
                if (!in_array($field['fieldName'], $cm->identifier)) {
                    $fields[] = $field['fieldName'];
                }
            }
        } elseif(!is_array($fields)) {
            throw new \Exception();
        }

        foreach ($fields as $field) {

            $required = true;

            if(isset($this->configs['types']) 
                && isset($this->configs['types'][$field])) {

                $type = $this->configs['types'][$field];
                if($type == 'boolean') {
                    $type = 'checkbox';
                    $required = false;
                }
            } else {
                $type = 'text';
            }

            if(isset($this->configs['labels'][$field])) {
                $label = $this->configs['labels'][$field];
            } else {
                $label == $field;
            }

            $builder->add($field, $type, array(
                'label' => $label,
                'required' => $required
            ));

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

    protected function getIdentifier()
    {
        $em = $this->getManager();
        $cm = $em->getClassMetadata($this->entityClassName);

        if ($cm->isIdentifierComposite) {
            throw new \RuntimeException("Entities with composite identifiers are not supported.");
        }

        return $cm->identifier[0];
    }

    protected function getConfiguration()
    {
        return array();
    }

    /**
     * @Extra\Template()
     */
    public function createAction()
    {
        if(isset($this->configs['actions'])
            && isset($this->configs['actions']['new'])
            && $this->configs['actions']['new'] == false) {

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        $class = $this->entityClassName;
        $entity = new $class();
        $request = $this->getRequest();

        $form = $this->getForm($this->configs['new']['fields']);
        $form->setData($entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        return $this->filterResponse(
            array(
                'name' => $this->configs['name'],
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
        if(isset($this->configs['actions']) 
            && isset($this->configs['actions']['delete'])
            && $this->configs['actions']['delete'] == false) {

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        $em = $this->getManager();
        $entity = $em->getRepository($this->entityClassName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ' . $this->entityClassName . ' entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Extra\Template()
     */
    public function editAction($id)
    {
        if(isset($this->configs['actions']) 
            && isset($this->configs['actions']['edit'])
            && $this->configs['actions']['edit'] == false) {

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        $em = $this->getManager();
        $entity = $em->getRepository($this->entityClassName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ' . $this->entityClassName . ' entity.');
        }

        $form = $this->getForm($this->configs['edit']['fields']);
        $form->setData($entity);

        return $this->filterResponse(
            array(
                'name' => $this->configs['name'],
                'entity' => $entity,
                'form' => $form->createView(),
                'identifier' => $this->getIdentifier(),
                'route_name_prefix' => $this->getRouteNamePrefix()
            ),
            array(
                'action' => 'edit'
            )
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

        $config = $this->getConfiguration();
        $fields = $this->configs['list']['fields'];

        if($fields == '*') {
            $fields = $this->getFields();
        }

        return $this->filterResponse(
            array(
                'name' => $this->configs['name'],
                'entities' => $entities,
                'fields' => $fields,
                'actions' => $this->configs['actions'],
                'labels' => $this->configs['labels'],
                'types' => $this->configs['types'],
                'identifier' => $this->getIdentifier(),
                'route_name_prefix' => $this->getRouteNamePrefix()
            ),
            array(
                'action' => 'index'
            )
        );
    }

    /**
     * Displays a form to create a new  entity.
     *
     * @Extra\Template()
     */
    public function newAction()
    {
        if(isset($this->configs['actions'])
            && isset($this->configs['actions']['new'])
            && $this->configs['actions']['new'] == false) {

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        $class = $this->entityClassName;
        $entity = new $class();
        $form = $this->getForm($this->configs['new']['fields']);
        $form->setData($entity);

        return $this->filterResponse(
            array(
                'name' => $this->configs['name'],
                'entity' => $entity,
                'form' => $form->createView(),
                'route_name_prefix' => $this->getRouteNamePrefix()
            ),
            array(
                'action' => 'new'
            )
        );
    }

    /*
     * Edits an existing  entity.
     *
     * @Extra\Template()
     */

    public function updateAction($id)
    {
        if(isset($this->configs['actions'])
            && isset($this->configs['actions']['edit'])
            && $this->configs['actions']['edit'] == false) {

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix()));
        }

        $em = $this->getManager();
        $entity = $em->getRepository($this->entityClassName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ' . $this->entityClassName . ' entity.');
        }

        $form = $this->getForm($this->configs['edit']['fields']);
        $form->setData($entity);

        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($this->getRouteNamePrefix() . '_edit', array('id' => $id)));
        }

        return $this->filterResponse(
            array(
                'name' => $this->configs['name'],
                'entity' => $entity,
                'form' => $form->createView(),
                'identifier' => $this->getIdentifier(),
                'route_name_prefix' => $this->getRouteNamePrefix()
            ),
            array(
                'action' => 'update'
            )
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

