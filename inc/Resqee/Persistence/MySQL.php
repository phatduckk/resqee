<?php

require_once 'Resqee/Persistence.php';
require_once 'Resqee/Persistence/Item.php';
require_once 'Resqee/Exception/Persistence.php';
require_once 'Resqee/Persistence/SearchParams.php';
require_once 'Resqee/Persistence/SearchResults.php';

class Resqee_Persistence_MySQL extends Resqee_Persistence_Item
{
    /**
     * A MySQL DB handle
     *
     * @var resouce
     */
    private static $db = null;

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
        $sql = 'DELETE
                FROM jobs
                WHERE jobId = ' . mysql_real_escape_string($jobId, $db) .
               '    AND status = ' . Resqee_Persistence_Item::STATUS_QUEUED;

        if (! mysql_query($sql, $db)) {
            throw new Resqee_Exception_Persistence(
                mysql_error($db),
                mysql_errno($db)
            );
        }

        return true;
    }

    /**
     * Find jobs based on the $params that where passed in.
     *
     * This is a pretty naive & crappy function... just like the rest of this class
     *
     * @param Resqee_Persistence_SearchParams $params
     *
     * @return whatever
     */
    public function findJobs(Resqee_Persistence_SearchParams $params)
    {
        $db     = self::getDb();
        $where  = array();
        $fields = get_object_vars($params);

        foreach ($fields as $field => $value) {
            if ($value == null || $field == 'offset'
                || $field == 'limit' || $field == 'isSearchParentClass') {
                continue;
            } else if (preg_match('/\W/', $field)) {
                throw new Resqee_Exception_Persistence(
                    "Could not perform search: {$field} is not a value column name"
                );
            }

            $matches = array();
            if (preg_match('/^(min|max)(Request|Response)Time$/', $field, $matches)) {
                $value = (int) $value;
                if ($matches[1] == 'min') {
                    $where[] = strtolower($matches[2]) . " >= FROM_UNIXTIME($value) ";
                } else {
                    $where[] = strtolower($matches[2]) . " <= FROM_UNIXTIME($value) ";
                }
            } else {
                $where[] = (is_numeric($value))
                    ? "$field = $value"
                    : "$field = " . mysql_real_escape_string($value, $db);
            }
        }

        $sql      = 'SELECT * FROM jobs where ' . implode(' AND ', $where);
        $countSql = 'SELECT count(*) as num FROM jobs where ' . implode(' AND ', $where);

        if (isset($params->limit, $params->offset)) {
            $sql .= ' LIMIT ' . (int) $params->limit
                 .  ' OFFSET ' . (int) $params->offset;
        } else if (isset($params->limit)) {
            $sql .= ' LIMIT ' . (int) $params->limit;
        } else if (isset($params->offset)) {
            $sql .= ' LIMIT ' . Resqee_Persistence_SearchParams::MAX_RESULTS
                 .  ' OFFSET ' . (int) $params->offset;
        }

        $res   = mysql_query($countSql, $db);
        $row   = mysql_fetch_array($res);
        $num   = $row[0];
        $items = array();

        if ($num != 0) {
            $res = mysql_query($sql, $db);
            while ($row = mysql_fetch_object($res, 'Resqee_Persistence_Item')) {
                $items[] = $row;
            }
        }

        return new Resqee_Persistence_SearchResults($params, $num, $items);
    }
}

?>