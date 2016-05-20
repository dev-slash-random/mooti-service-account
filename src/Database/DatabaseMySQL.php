<?php
namespace Mooti\Service\Account\Database;

use Mooti\Framework\Framework;
use PDO;

class DatabaseMySQL
{
    use Framework;

    protected $pdo;

    public function getConnection()
    {
        if (empty($this->pdo)) {
            $mootiIniFile = '/etc/mooti/mooti.ini';

            if (!file_exists($mootiIniFile)) {
                throw new \Exception($mootiIniFile .' does not exixt'); 
            }

            $iniValues = parse_ini_file($mootiIniFile, true);
            $dbName = 'account';

            $dsn      = 'mysql:dbname='.$dbName.';host='.$iniValues['database']['host'];
            $user     = $iniValues['database']['user'];
            $password = $iniValues['database']['password'];

            $this->pdo = new PDO($dsn, $user, $password);
        }        
        return $this->pdo;
    }
}