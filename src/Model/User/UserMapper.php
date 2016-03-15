<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Model\User;

use Mooti\Xizlr\Core\Xizlr;

class UserMapper
{
    use Xizlr;

    private $users = [
        1 => [
            'id'        => 1,
            'firstName' => 'Ken',
            'LastName'  => 'Lalobo',
        ],
        2 => [
            'id'        => 2,
            'firstName' => 'Joe',
            'LastName'  => 'Bloggs',
        ]
    ];

    public function findAll()
    {
        return $this->users;
    }

    public function find($id)
    {
        return $this->users[$id];
    }
}
