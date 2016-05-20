<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Controller;

use Mooti\Framework\Application\Rest\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mooti\Framework\Framework;

use Mooti\Service\Account\Model\User\UserMapper;

class User extends BaseController
{
    use Framework;

    public function getUsers(Request $request, Response $response)
    {
        $userMapper = $this->createNew(UserMapper::class);
        $users = $userMapper->findAll();
        return $this->render($users, $response);
    }

    public function getUser($uuid, Request $request, Response $response)
    {
        $userMapper = $this->createNew(UserMapper::class);
        $user = $userMapper->getUser($uuid);
        return $this->render($user, $response);
    }
}
