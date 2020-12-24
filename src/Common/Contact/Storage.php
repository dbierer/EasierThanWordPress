<?php
namespace SimpleHtml\Common\Contact;

use PDO;
use PDOStatement;
use Throwable;
class Storage extends Base
{
    const DEFAULT_TABLE = 'contacts';
    public $pdo = NULL;
    public $config = [];
    /**
     * @param array $config : /src/config/config.php => 'STORAGE'
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $params = $config['STORAGE'];
        $dsn = 'mysql:host=' . $params['db_host'] . ';dbname=' . $params['db_name'];
        $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        try {
            $this->pdo = new PDO($dsn, $params['db_user'], $params['db_pwd'], $opts);
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . $t->getMessage());
        }
    }
    /**
     * Stores info into $table
     *
     * @param string $table : table name
     * @param array $post   : $_POST
     * @return bool
     */
    public function save(string $table, array $post)
    {
        $result = FALSE;
        $inputs = self::filter($table, $post, $this->config);
        $fields = array_keys($inputs);
        $sql = 'INSERT INTO ' . $table . ' (`'
             . implode('`,`', $fields)
             . '`) VALUES (:'
             . implode(',:', $fields)
             . ')';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($inputs);
            $result = $stmt->rowCount();
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . $t->getMessage());
            error_log(__METHOD__ . ':' . $sql);
            error_log(__METHOD__ . ':' . json_encode($inputs));
        }
        return $result;
    }
}
