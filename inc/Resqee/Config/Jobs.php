<?php

require_once 'Resqee/Config.php';

class Resqee_Config_Jobs extends Resqee_Config
{
    /**
     * Name of the config file that says which servers can handle which jobs
     *
     * @var string
     */
    const CONFIG_FILE = 'resqee-jobs.ini';

    /**
     * Key in the config array that holds the array of hosts that
     * can service all jobs
     *
     * @var string
     */
    const ALL = 'Resqee_Job';

    /**
     * const used to fetch/add the config array in APC
     *
     * @var string
     */
    const APC_KEY_CONFIG = 'RESQEE_CONFIG_JOB_APC_KEY_CONFIG';

    /**
     * Base key for use in APC for a disabled server flag
     *
     * @var string
     */
    const APC_KEY_SERVER_DISABLED = 'APC_KEY_SERVER_DISABLED.';

    /**
     * const to determine how long we store the config in apc
     *
     * @var string
     */
    // TODO: maybe make this configurable
    const APC_TTL_CONFIG = 300;

    /**
     * How long we want to disable a server for in APC
     *
     * @var string
     */
    const APC_TTL_DISABLE_SERVER = self::APC_TTL_CONFIG;

    /**
     * Whether or not apc extension is enabled
     *
     * @var bool
     */
    private static $isAPCEnabled = false;

    /**
     * A list of disabled servers
     *
     * @var array
     */
    private static $disabledServers = array();

    /**
     * Constructor
     *
     * If the server has APC enabled we'll check to see if the config is there.
     * If so we'll use it. If not we'll shove the config in APC for
     * self::APC_TTL_CONFIG seconds
     *
     * @return void
     */
    protected function __construct()
    {
        $keySuffix          = (PHP_SAPI == 'cli') ? '_cli' : '';
        self::$isAPCEnabled = ini_get('apc.enabled' . $keySuffix);

        if (self::$isAPCEnabled) {
            $config = apc_fetch(self::APC_KEY_CONFIG);

            if ($config !== false) {
                $this->config = $config;
            }
        }

        if (empty($this->config)) {
            $this->parseConfig($this->getConfigFile());
            
            if (self::$isAPCEnabled) {
                apc_add(self::APC_KEY_CONFIG, $this->config, self::APC_TTL_CONFIG);
            }
        }
    }

    /**
     * Get the name of the config file
     *
     * @return string The name of the config file
     */
    public function getConfigFile()
    {
        return self::CONFIG_FILE;
    }

    /**
     * Get the info for a server which is able to handle this job.
     *
     * We check local cache and APC to make sure the server isn't marked as
     * disabled
     *
     * The returned array looks like this
     * array(
     *  'scheme' => 'http',
     *  'host'   => 'example.com'
     *  'post'   => 80
     * )
     *
     * @param Resqee_Job $job
     *
     * @return array The server's info
     */
    public static function getServer(Resqee_Job $job)
    {
        $server  = null;
        $config  = Resqee_Config_Jobs::getInstance();
        $allJobs = $config->getConfig();
        $thisJob = $config->getConfig(get_class($job));
        $parent  = $config->getConfig(get_parent_class($job));
        $all     = array_merge($allJobs, $thisJob);

        if (! empty($all)) {
            $avail = array();
            while ($info = array_shift($all)) {
                if (! $config->isServerDisabled($info)) {
                    $avail[] = $info;
                }
            }

            // if we've got any available servers we'll weight em
            if (! empty($avail)) {
                $weighted = array();
                foreach ($avail as $info) {
                    $a = array_fill(0, $info['weight'], $info);
                    $weighted = array_merge($weighted, $a);
                }

                shuffle($weighted);
                $server = $weighted[0];
            }
        }

        return $server;
    }

    /**
     * Disable a server
     *
     * If a server doesn't respond we want to disable it.
     *
     * If you have APC enabled then we'll set a disabled flag there for
     * APC_TTL_DISABLE_SERVER seconds. We don't store the flag in APC forever
     * because we don't want to clear APC on your client/web-server in order to
     * unset that flag in the event the server has come back up.
     *
     * Either way - we store the flag in a local cache as well
     *
     * @param string|array $hostAndPort localhost:80
     *  or array('host' => localhost, 'port' => 80)
     *
     * @return void
     */
    public static function disableServer($hostAndPort)
    {
        Resqee_Config_Jobs::getInstance();

        if (is_array($hostAndPort)) {
            $hostAndPort = "{$hostAndPort['host']}:{$hostAndPort['port']}";
        }

        self::$disabledServers[$hostAndPort] = true;

        if (self::$isAPCEnabled) {
            apc_add(
                self::APC_KEY_SERVER_DISABLED . $hostAndPort,
                true,
                self::APC_TTL_DISABLE_SERVER
            );
        }
    }

    /**
     * Check whether a server is disabled
     *
     * We check local cache and APC
     *
     * @param string|array $hostAndPort localhost:80
     *  or array('host' => localhost, 'port' => 80)
     *
     * @return bool
     */
    public function isServerDisabled($hostAndPort)
    {
        if (is_array($hostAndPort)) {
            $hostAndPort = "{$hostAndPort['host']}:{$hostAndPort['port']}";
        }

        return (
            isset(self::$disabledServers[$hostAndPort])
            || (self::$isAPCEnabled && apc_fetch(self::APC_KEY_SERVER_DISABLED . $hostAndPort))
        );
    }

    /**
     * Get the array of server info from $this->config
     *
     * An empty array is returned if an invalid key is specified
     *
     * @param string $configKey The key in $this->config
     *
     * @return array()
     */
    private function getConfig($configKey = self::ALL)
    {
        return (isset($this->config['job'][$configKey]))
            ? $this->config['job'][$configKey]
            : array();
    }

    /**
     * Method to parse the config file
     *
     * @param string $file The name of the ini file
     */
    protected function parseConfig($file)
    {
        $config    = array();
        $rawConfig = parse_ini_file($file, true);

        $this->config = array(
            'job'    => array(),    // what server's can run this job?
            'server' => array(),    // what jobs can this server run?
        );

        $this->config['job'][self::ALL] = array();

        foreach ($rawConfig as $k => $v) {
            if (is_array($v)) {
                foreach ($rawConfig[$k] as $host => $weight) {
                    $this->addJob($k, $host, $weight);
                }
            } else if ($v) {
                $this->addJob(self::ALL, $k, $v);
            }
        }
    }

    /**
     * Set stuff
     *
     * @param array  $jobName The name of the job
     * @param string $host    The name of the host/server
     * @param string $weight  Value for this entry in the ini
     *
     * @return void
     */
    private function addJob($jobName, $host, $weight)
    {
        $weight = (int) $weight;

        if ($weight !== 0) {
            $info    = array();
            $matches = array();

            // check to see if we have a port
            if (preg_match('/^([\w\._-]+)(\:|_port_|\*)(\d+)$/', $host, $matches)) {
                $info = array(
                    'host' => $matches[1],
                    'port' => $matches[3]
                );
            } else {
                $info = array(
                    'host' => $host,
                    'port' => 80
                );
            }

            $info['weight'] = $weight;
            $key            = "{$info['host']}:{$info['port']}";

            $this->config['job'][$jobName][$key] = $info;
            $this->config['server'][$key][]      = $jobName;
        }
    }

    /**
     * Get an instance
     *
     * @return Resqee_Config_Jobs
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Resqee_Config_Jobs();
        }

        return self::$instance;
    }
}

?>