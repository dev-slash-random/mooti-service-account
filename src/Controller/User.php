<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Controller;

use Mooti\Xizlr\Core\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mooti\Xizlr\Core\Xizlr;

class User extends BaseController
{
    use Xizlr;

    public function getUsers(Request $request, Response $response)
    {
        $users = [
            [
                [
                    'firstName' => 'Ken',
                    'LastName' => 'Lalobo',
                ],
                [
                    'firstName' => 'Joe',
                    'LastName' => 'Bloggs',
                ]
            ]
        ];

        return $this->render($users, $response);
    }

    public function getUser($id, Request $request, Response $response)
    {
        $userMapper = $this->get('mooti.model.mapperFactory')->getMapper('user');
        $user = $userMapper->find($id);
        return $this->render($user, $response);
    }
}
