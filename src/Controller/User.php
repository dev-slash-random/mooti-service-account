<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Controller;

use Mooti\Framework\Rest\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mooti\Framework\Framework;

use Mooti\Service\Account\Model\User\UserRepository;

class User extends BaseController
{
    use Framework;

    public function getUsers(Request $request, Response $response)
    {
        $userRepository = $this->createNew(UserRepository::class);
        $users = $userRepository->findAll();
        return $this->render($users, $response);
    }

    public function getUser($id, Request $request, Response $response)
    {
        $userRepository = $this->createNew(UserRepository::class);
        $user = $userRepository->find($id);
        return $this->render($user, $response);
    }
}
