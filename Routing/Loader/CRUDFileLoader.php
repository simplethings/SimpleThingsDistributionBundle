<?php

namespace SimpleThings\DistributionBundle\Routing\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Automatically finds and registers routes for the CRUDController
 */
class CRUDFileLoader extends FileLoader
{
    public function load($path, $type = null)
    {
        $dir = $this->locator->locate($path);

        $collection = new RouteCollection();
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file->getPathname())) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }

                if (is_subclass_of($class, 'SimpleThings\DistributionBundle\Controller\CRUDController')) {
                    $alias = $class::getControllerAlias();
                    $controller = $class::getControllerName();
                    $baseRouteName = $alias."_crud_".$controller;
                    $basePattern = '/admin/'.$controller;

                    $route = new Route($basePattern, array('_controller' => $class.'::indexAction'), array('_method' => 'GET'), array());
                    $collection->add($baseRouteName, $route);

                    $route = new Route($basePattern.'/new.html', array('_controller' => $class.'::newAction'), array('_method' => 'GET'), array());
                    $collection->add($baseRouteName.'_new', $route);

                    $route = new Route($basePattern, array('_controller' => $class.'::newAction'), array('_method' => 'POST'), array());
                    $collection->add($baseRouteName.'_create', $route);

                    $route = new Route($basePattern.'/{id}/edit', array('_controller' => $class.'::editAction'), array('_method' => 'GET'), array());
                    $collection->add($baseRouteName.'_edit', $route);

                    $route = new Route($basePattern.'/{id}/edit', array('_controller' => $class.'::updateAction'), array('_method' => 'POST'), array());
                    $collection->add($baseRouteName.'_update', $route);

                    $route = new Route($basePattern.'/{id}/delete', array('_controller' => $class.'::deleteAction'), array('_method' => 'POST'), array());
                    $collection->add($baseRouteName.'_delete', $route);

                    $collection->addResource(new FileResource($file->getPathname()));
                }
            }
        }

        return $collection;
    }
    public function supports($resource, $type = null)
    {
        try {
            $path = $this->locator->locate($resource);
        } catch (\Exception $e) {
            return false;
        }

        return is_string($resource) && is_dir($path) && ('simplethings_crud' === $type);
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && is_array($token) && in_array($token[0], array(T_NS_SEPARATOR, T_STRING)));
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
