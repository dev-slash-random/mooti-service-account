<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Model\User;

use Mooti\Framework\Framework;
use Mooti\Service\Account\ServiceProvider\ServiceProvider as AccountServiceProvider;
use NilPortugues\Sql\QueryBuilder\Builder\MySqlBuilder;
use PDO;
use Redis;
use DateTime;
use GearmanClient;

class UserMapper
{
    use Framework;

    private $users = [
        1 => [
            'id'        => 1,
            'firstName' => 'Ken',
            'lastName'  => 'Lalobo',
        ],
        2 => [
            'id'        => 2,
            'firstName' => 'Joe',
            'lastName'  => 'Bloggs',
        ]
    ];

    public function findOne(array $fields)
    {
        $result = $this->find($fields);
        if (count($result['result']['count'] == 0)) {
            throw new Exception("NOT FOUND!!");    
        }

        return $result['result']['items'][0];
    }

    public function find(array $fields)
    {
        /*$fields = [
            'id' => ['=', 1]
        ];*/
        /*$query = $this->createNew(Query);
        
        $query->setCollection('users');
        $query->setFields($fields);
        $query->setPage(1);
        $query->setPerPage(10);
        $query->setMaxPages(5);
        $query->setSort('firstName', Query::SORT_ASC);
        $result = $query->search();
        $items = $result->getItems();

        $returnUsers = [];
        foreach ($items as $item) {
            $returnUsers[] = $this->getUser($item['id']);
        }

        $result = [
            'search' => [
                'fields'   => $fields,
                'page'     => $page,
                'perPage'  => $perPage,
                'maxPages' => $maxPages,
                'sort'     => $sort
            ],
            'result' => [
                'count' => $result->count(),
                'items' => $returnUsers
            ]
        ];
        return $returnUsers;*/
        return [];
    }

    public function getUserFromDatabase($uuid)
    {
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
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return new User($row);
    }

    public function getItemFromCache($uuid)
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP); 
        $cacheItem = json_decode($redis->get($uuid), true);
        $timeNow = new DateTime();
        if (!$cacheItem) {
            $cacheItem = [       
                'item'     => null,
                'updating' => false,
                'expire'   => $timeNow->format('r'),
                'status'   => 'MISS'
            ];  
        } elseif ($cacheItem['updating'] == true) {
            $cacheItem['status'] = 'EXPIRED';
        } elseif ($this->createNew(DateTime::class, $cacheItem['expire']) > $timeNow) {
            $cacheItem['status'] = 'HIT';
        } else {
            $cacheItem['status'] = 'EXPIRED';
        }
        $cacheItem['time'] = $timeNow->format('r');
        return $cacheItem;
    }

    public function scheduleCacheItem($name, $uuid)
    {
        $gmclient= new GearmanClient();
        $gmclient->addServer();
        $jobHandle = $gmclient->doBackground($name.'.cache', $uuid);
    }

    public function cacheUser($uuid)
    {
        $user = $this->getUserFromDatabase($uuid);
        $payload = [
            'item'   => $user,
            'updating' => false,
            'expire' => $this->createNew(DateTime::class, '+60 seconds')->format('r')
        ];
        $cacheItem = json_encode($payload);
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP); 
        $redis->set($uuid, $cacheItem);
        return $cacheItem;
    }

    public function getUser($uuid)
    {
        $cacheItem = $this->getItemFromCache($uuid);

        $user = $cacheItem['item'];
        if (empty($user)) {
            $user = $this->getUserFromDatabase($uuid);
        }
        if (in_array($cacheItem['status'], ['MISS', 'EXPIRED'], true)) {
            $this->scheduleCacheItem('mooti.account.user', $uuid);
        }
        return $user;
    }    


}
