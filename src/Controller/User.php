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
}
