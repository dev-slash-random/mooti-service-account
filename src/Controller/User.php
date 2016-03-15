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

use Mooti\Service\Account\Model\User\UserMapper;

class User extends BaseController
{
    use Xizlr;

    public function getUsers(Request $request, Response $response)
    {
        $userMapper = $this->createNew(UserMapper::class);
        $users = $userMapper->findAll();
        return $this->render($users, $response);
    }

    public function getUser($id, Request $request, Response $response)
    {
        $userMapper = $this->createNew(UserMapper::class);
        $user = $userMapper->find($id);
        return $this->render($user, $response);
    }
}
