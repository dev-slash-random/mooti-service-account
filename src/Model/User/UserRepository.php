<?php
/**
 *
 * @author Ken Lalobo
 *
 */
namespace Mooti\Service\Account\Model\User;

use Mooti\Framework\Framework;
use Mooti\Service\Account\ServiceProvider\ServiceProvider as AccountServiceProvider;
use NilPortugues\Sql\QueryBuilder\Builder\MySqlBuilder;
use PDO;

class UserMapper
{
    use Framework;

    private $users = [
        1 => [
            'id'        => 1,
            'uuid'      => 'foo',
            'firstName' => 'Ken',
            'lastName'  => 'Lalobo',
        ],
        2 => [
            'id'        => 2,
            'uuid'      => 'bar',
            'firstName' => 'Joe',
            'lastName'  => 'Bloggs',
        ]
    ];

    public function getUser($uuid)
    {
        if (empty($this->users[$uuid])) {
            return null;
        }
        return (object) $this->users[$uuid];

        $database = $this->get(AccountServiceProvider::DATABASE_MYSQL);
        $pdo = $database->getConnection();

        $builder = $this->createNew(MySqlBuilder::class); 

        $query = $builder->select()->setTable('user');

        $query->setColumns(['uuid' => 'uuid', 'firstName' => 'first_name', 'lastName' => 'last_name'])
            ->where()
            ->equals('uuid', $uuid);
    
        $query->limit(0, 1);

        $sql = $builder->write($query);
        $values = $builder->getValues();

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false );
        $statement = $pdo->prepare($sql);
        $statement->execute($values);
        $row = $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

}
