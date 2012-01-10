<?php

namespace SimpleThings\DistributionBundle\Twig;

/**
 *
 * @author david badura <badura@simplethings.de>
 */
class ObjectAccessExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'get_attribute_value' => new \Twig_Function_Method($this, 'getAttributeValue'),
        );
    }

    public function getAttributeValue($object, $property)
    {
        $camelProp = ucfirst($property);
        $reflClass = new \ReflectionClass($object);
        $getter = 'get'.$camelProp;
        $isser = 'is'.$camelProp;

        if ($reflClass->hasMethod($getter)) {
            if (!$reflClass->getMethod($getter)->isPublic()) {
                throw new PropertyAccessDeniedException(sprintf('Method "%s()" is not public in class "%s"', $getter, $reflClass->getName()));
            }

            return $object->$getter();
        } else if ($reflClass->hasMethod($isser)) {
            if (!$reflClass->getMethod($isser)->isPublic()) {
                throw new PropertyAccessDeniedException(sprintf('Method "%s()" is not public in class "%s"', $isser, $reflClass->getName()));
            }

            return $object->$isser();
        } else if ($reflClass->hasMethod('__get')) {
            return $object->$property;
        } else if ($reflClass->hasProperty($property)) {
            if (!$reflClass->getProperty($property)->isPublic()) {
                throw new PropertyAccessDeniedException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "%s()" or "%s()"?', $property, $reflClass->getName(), $getter, $isser));
            }

            return $object->$property;
        } else if (property_exists($object, $property)) {
            return $object->$property;
        } else {
            throw new InvalidPropertyException(sprintf('Neither property "%s" nor method "%s()" nor method "%s()" exists in class "%s"', $property, $getter, $isser, $reflClass->getName()));
        }
    }

    public function getName()
    {
        return 'simplethings_distributionbundle_objectaccess';
    }
}