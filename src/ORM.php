<?php

namespace Joomla3\Glue;

use JConfig;
use Joomla\CMS\Factory as JFactory;
use Joomla\DI\Container;
use Joomla\ORM\Service\RepositoryFactory;
use Joomla\ORM\Service\StorageServiceProvider;
use Joomla\ORM\Storage\Doctrine\PrefixedDoctrineDataMapper;
use Psr\Container\ContainerInterface;

defined('_JEXEC') or die;

class ORM
{
    protected static $configDirectrory = JPATH_ROOT . '/config/';

    public static function bootstrap()
    {
        self::createDatabaseConfiguration();
        $container         = self::createContainer();
        $repositoryFactory = self::createRepositoryFactory($container);
        $container->set('Repository', $repositoryFactory);
        $container->set('ConfigDirectory', self::$configDirectrory);

        return $container;
    }

    /**
     * Create RepositoryFactory
     *
     * @param ContainerInterface $container
     *
     * @return RepositoryFactory
     */
    protected static function createRepositoryFactory($container)
    {
        return (new StorageServiceProvider(self::$configDirectrory . 'database.ini'))->createRepositoryFactory($container);
    }

    /**
     * Ensure container exists
     *
     * @return Container
     */
    protected static function createContainer()
    {
        $app = JFactory::getApplication();

        if (is_null($app->get('container', null))) {
            $app->set('container', new Container());
        }

        $container = $app->get('container');

        if (!method_exists($container, 'set')) {
            $container = new Container($container);
        }

        return $container;
    }

    /**
     * Create DB configuration
     */
    protected static function createDatabaseConfiguration()
    {
        $config          = new JConfig();
        $dsn             = $config->dbtype . '://' . $config->user . ':' . $config->password . '@' . $config->host . '/' . $config->db;
        $dataMapperClass = PrefixedDoctrineDataMapper::class;

        $ini = <<<INI
databaseUrl="$dsn"
definitionPath="entities"
dataMapper="$dataMapperClass"
INI;
        file_put_contents(JPATH_ROOT . '/config/database.ini', $ini);
    }
}
