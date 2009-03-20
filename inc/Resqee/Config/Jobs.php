<?php

require_once 'Resqee/Config.php';

class Resqee_Config_Jobs extends Resqee_Config
{
    /**
     * Name of the config file that says which servers can handle which jobs
     *
     */
    const CONFIG_FILE = 'resqee-jobs.ini';

    /**
     * Key in the config array that holds the array of hosts that
     * can service all jobs
     */
    const ALL = '.all';

    /**
     * const used to fetch/add the config array in APC
     *
     */
    const APC_KEY_CONFIG = 'RESQEE_CONFIG_JOB_APC_KEY_CONFIG';

    /**
     * const to determine how long we store the config in apc
     */
    // TODO: maybe make this configurable
    const APC_CONFIG_TTL = 300;

    /**
     * Whether or not apc extension is enabled
     *
     * @var bool
     */
    private $isAPCEnabled = false;

    /**
     * Constructor
     *
     * If the server has APC enabled we'll check to see if the config is there.
     * If so we'll use it. If not we'll shove the config in APC for
     * self::APC_CONFIG_TTL seconds
     *
     * @return void
     */
    protected function __construct()
    {
        $this->isAPCEnabled = ini_get('apc.enabled');

        if ($this->isAPCEnabled) {
            $config = apc_fetch(self::APC_KEY_CONFIG);

            if ($config !== false) {
                $this->config = $config;
            }
        }

        if (empty($this->config)) {
            $this->parseConfig($this->getConfigFile());
            apc_add(self::APC_KEY_CONFIG, $this->config, self::APC_CONFIG_TTL);
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
     * Get the info for a server which is able to handle this job
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
    public function getServer(Resqee_Job $job)
    {
        $config  = Resqee_Config_Jobs::getInstance();
        $allJobs = $config->getConfig();
        $thisJob = $config->getConfig(get_class($job));

        $all = array_merge($allJobs, $thisJob);

        if (!empty($all)) {
            shuffle($all);
            return array_shift($all);
        } else {
            return null;
        }
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
                foreach ($rawConfig[$k] as $host => $enabled) {
                    $this->addJob($k, $host, $enabled);
                }
            } else if ($v) {
                $this->addJob(self::ALL, $k, $v);
            }
        }
    }

    /**
     * Set stuff
     *
     * @param array  $jobName     The name of the job
     * @param string $host        The name of the host/server
     * @param string $configValue Value for this entry in the ini
     *
     * @return void
     */
    private function addJob($jobName, $host, $configValue)
    {
        if (((int) $configValue)) {
            $info    = array();
            $matches = array();

            // check to see if we have a port
            if (preg_match('/^([\w\._-]+)\:(\d+)$/', $host, $matches)) {
                $info = array(
                    'host' => $matches[1],
                    'port' => $matches[2]
                );
            } else {
                $info = array(
                    'host' => $host,
                    'port' => 80
                );
            }

            $key = "{$info['host']}:{$info['port']}";

            $this->config['job'][$jobName][$key] = $info;

            if ($jobName !== self::ALL) {
                $this->config['server'][$key][]      = $jobName;
            }
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