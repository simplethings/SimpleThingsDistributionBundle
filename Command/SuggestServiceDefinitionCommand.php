<?php

namespace SimpleThings\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\Common\Util\Inflector;

class SuggestServiceDefinitionCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('output', InputArgument::REQUIRED, 'The output type, xml or yaml'),
            ))
            ->setHelp(<<<EOT
The <info>simplethings:services:suggest</info> command prints a list of services in a chosen
format that are suggested to be created by convention but are not yet.

<info>./app/console simplethings:services:suggest --type=yaml</info>


EOT
            )
            ->setName('simplethings:services:suggest')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = new \Symfony\Component\DependencyInjection\ContainerBuilder();

        $container = $this->getContainer();
        if ($container->has('doctrine')) {
            $doctrineRegistry = $container->get('doctrine');
            /* @var $doctrineRegistry \Symfony\Bridge\Doctrine\RegistryInterface */
            foreach ($doctrineRegistry->getEntityManagerNames() AS $entityManagerName) {
                $entityManager = $doctrineRegistry->getEntityManager($entityManagerName);
                
                foreach ($entityManager->getMetadataFactory()->getAllMetadata() AS $classMetadata) {
                    /* @var $classMetadata ClassMetadata */
                    $repositoryClassName = "Doctrine\ORM\EntityRepository";
                    if ($classMetadata->customRepositoryClassName) {
                        $repositoryClassName = $classMetadata->customRepositoryClassName;
                    }

                    $def = new \Symfony\Component\DependencyInjection\Definition($repositoryClassName);
                    $def->setFactoryService($entityManagerName);
                    $def->setFactoryMethod("getRepository");
                    $def->setArguments(array($classMetadata->name));

                    $id = str_replace("_bundle.entity.", ".repository.", Inflector::tableize(str_replace("\\", ".", $classMetadata->name)));
                    if (!$container->has($id) && !$builder->hasDefinition($id)) {
                        $builder->setDefinition($id, $def);
                    }
                }
            }
        }

        if ($container->hasParameter('doctrine_couchdb.document_managers')) {
            foreach ($container->getParameter('doctrine_couchdb.document_managers') AS $documentManagerName) {
                $documentManager = $container->get($documentManagerName);

                foreach ($documentManager->getMetadataFactory()->getAllMetadata() AS $classMetadata) {
                    /* @var $classMetadata ClassMetadata */
                    $repositoryClassName = "Doctrine\ODM\CouchDB\DocumentRepository";
                    if ($classMetadata->customRepositoryClassName) {
                        $repositoryClassName = $classMetadata->customRepositoryClassName;
                    }

                    $def = new \Symfony\Component\DependencyInjection\Definition($repositoryClassName);
                    $def->setFactoryService($documentManagerName);
                    $def->setFactoryMethod("getRepository");
                    $def->setArguments(array($classMetadata->name));

                    $id = str_replace("_bundle.document.", ".repository.", Inflector::tableize(str_replace("\\", ".", $classMetadata->name)));
                    if (!$container->has($id) && !$builder->hasDefinition($id)) {
                        $builder->setDefinition($id, $def);
                    }
                }
            }
        }

        if ($input->getArgument('output') == "yaml") {
            $dumper = new \Symfony\Component\DependencyInjection\Dumper\YamlDumper($builder);
        } else {
            $dumper = new \Symfony\Component\DependencyInjection\Dumper\XmlDumper($builder);
        }
        echo $dumper->dump() . PHP_EOL;
    }
}