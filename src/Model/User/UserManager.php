<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Model\User;

use Mooti\Framework\Framework;

class UserManager
{
    use Framework;

    public function findAll()
    {
        $userSearch = new UserSearch();
        $userIds = $userSearch->search();

        return $this->getUsers($userIds);
    }

    public function find($id)
    {
        $users = $this->getUsers([$id]);
        return $users[0];
    }

    public function getUsers(array $userIds)
    {
        $userRepository = new UserRepository();
        $users = $userRepository->find($userIds);

        $uuids = array_keys($users);
        $userDocumentStore = new UserDocumentStore();
        $noSQLUsers = $userDocumentStore->find($uuids);

        $returnUsers = [];
        foreach ($users as $uuid => $sqlData) {
            $userData = [
                'sqlData' => $sqlData,
                'documentStore' => $noSQLUsers[$uuid]
            ];
            $user = $userMapper->createModelFromData($userData);
            $returnUsers[] = $user;
        }
        return $returnUsers;
    }

    public function addUser(array $data)
    {
        list($relationalData, $jsonData, $searchData) = $userMapper->extractData($data);

        $userEntity = $userRepository->add($relationalData);
        $userDocumentStore->add($userEntity->uuid, $jsonData);
        $userSearch->index($userEntity->uuid);
        $userCache->warm($userEntity->uuid);
    }
}
