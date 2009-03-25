<?php

require_once 'Resqee/Persistence.php';

class Resqee_Persistence_MySQL extends Resqee_Persistence_Item
{
    /**
     * A MySQL DB handle
     *
     * @var resouce
     */
    private static $db = null;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Get a MySQL DB resource
     *
     * @return resource
     */
    private static function getDb()
    {
        if (self::$db == null) {
            self::$db = mysql_connect('localhost', 'root', null);
            mysql_select_db('resqee', self::$db);
        }

        return self::$db;
    }

    /**
     * Save a job & it's args to a persistant store's queue
     *
     * A Resqee_Exception_Persistence is thrown on failure
     *
     * @param Resqee_Persistence_Item $item A Resqee_Persistence_Item
     *
     * @return bool TRUE on success
     */
    public function queue(Resqee_Persistence_Item $item)
    {
        $db  = self::getDb();
        $sql = 'INSERT
                INTO jobs(status, jobId, job, args, requestTime, jobClassId, jobParentClassId)
                VALUES(%d, \'%s\', \'%s\', \'%s\', FROM_UNIXTIME(%d), %d, %d)';

        $sql = vsprintf(
                    $sql,
                    array(
                        Resqee_Persistence_Item::STATUS_QUEUED,
                        mysql_real_escape_string($item->jobId),
                        mysql_real_escape_string($item->job),
                        mysql_real_escape_string($item->args),
                        $item->requestTime,
                        $this->getJobClassId($item->class),
                        $this->getJobClassId($item->parentClass)
                    )
                );

        if (! mysql_query($sql, $db)) {
            throw new Resqee_Exception_Persistence(
                mysql_error($db),
                mysql_errno($db)
            );
        }

        return true;
    }

    /**
     * Mark a job as completed and update the status
     *
     * @param string          $jobId    The jobId
     * @param Resqee_Response $response The response
     *
     * @return bool
     */
    public function completeJob($jobId, Resqee_Response $response)
    {
        $db = self::getDb();

        $data = array(
            'response'     => mysql_real_escape_string(serialize($response)),
            'hasErrors'    => count($response->getErrors()),
            'hasStdout'    => (int) ($response->getStdout() != null),
            'hasErrors'    => (int) ($response->getErrors() != null),
            'status'       => (int) ($response->getException() == null)
                ? Resqee_Persistence_Item::STATUS_COMPLETE_OK
                : Resqee_Persistence_Item::STATUS_COMPLETE_FAILED
        );

        $queryParts = array('responseTime = FROM_UNIXTIME(' . $response->getResponseTime() . ')');
        foreach ($data as $k => $v) {
            $queryParts[] = "$k = '$v'";
        }

        $sql = 'UPDATE jobs SET ' . implode(',', $queryParts);
        $res = mysql_query($sql, $db);
    }

    /**
     * Get the id of a jobClass
     *
     * @param string $className The name of the class
     *
     * @return int The id
     */
    public function getJobClassId($className)
    {
        $id  = null;
        $db  = self::getDb();
        $sql = 'SELECT id
                FROM jobClass
                WHERE class = \'' . mysql_real_escape_string($className, $db) . '\'';

        $res = mysql_query($sql, $db);

        if (mysql_numrows($res)) {
            $row = mysql_fetch_array($res);
            $id  = $row[0];
        } else {
            $sql = 'INSERT
                    INTO jobClass(class)
                    VALUES(\''. mysql_real_escape_string($className, $db) . '\')';

            $res = mysql_query($sql, $db);
            $id  = mysql_insert_id($db);
        }

        return $id;
    }

    /**
     * Remove a job from the persistemnt store's queue
     *
     * @param string $jobId The job's ID
     *
     * @return bool
     */
    public function dequeue($jobId)
    {
        $db  = self::getDb();
        $sql = 'DELETE FROM queuedJobs WHERE jobId = '
               . mysql_real_escape_string($jobId, $db);

        if (! mysql_query($sql, $db)) {
            throw new Resqee_Exception_Persistence(
                mysql_error($db),
                mysql_errno($db)
            );
        }

        return true;
    }

    public function findJobs(Resqee_Persistence_SearchParams $params)
    {

    }
}

?>