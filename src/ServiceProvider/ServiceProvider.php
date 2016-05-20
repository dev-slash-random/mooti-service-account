<?php
namespace Mooti\Service\Account\ServiceProvider;

use Mooti\Service\Account\Database\DatabaseMySQL;
use Mooti\Framework\ServiceProvider\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    const DATABASE_MYSQL = 'mooti.service.account.databse.mysql';

    /**
     * Get the details of the services we are providing     
     *
     * @return array
     */
    public function getServices()
    {
        return [
            self::DATABASE_MYSQL => function () { return new DatabaseMySQL();}
        ];
    }
}