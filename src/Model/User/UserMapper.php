<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Model\User;

use Mooti\Framework\Framework;

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
        $query = $this->createNew(Query);
        
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
        return $returnUsers;
    }

    public function getUser($id)
    {
        //nilportugues/php-sql-query-builder
        $db = $this->get(ServiceProvider::MYSQL_DATABASE);
        
        $builder = createNew(MySqlBuilder::class); 

        $query = $builder->select()
            ->setTable('user')
            ->setColumns(['id' => 'id', 'firstName' => 'first_name', 'lastName' => 'last_name'])
            ->where()
            ->equals('user_id', 1)
            ->limit(1);

        $this->log($builder->writeFormatted($query));

        $results = $db->query($builder->write($query));

        $userData = $results[0];

        return new User($userData);
    }
}
