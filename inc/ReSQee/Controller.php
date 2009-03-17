<?php

abstract class ReSQee_Controller
{
    /**
     * URI this controller will be dealing with
     *
     * @var string
     */
    protected $uri = null;

    /**
     * The first part of the uri
     *
     * ex: /status/job
     * $this->basePath = 'status'
     *
     * @var string
     */
    protected $basePath = null;

    /**
     * The action that will run. AKA the function we'll be calling.
     * 2nd part of the uri
     *
     * @var string
     */
    protected $action = null;

    /**
     * The 3rd part of the uri
     *
     * ex: /status/jobs/completed
     * $this->detail = 'completed'
     *
     * @var string
     */
    protected $detail = null;

    /**
     * Output format of the controller.
     *
     * ex: /status/jobs/completed.json
     *  $this->outputFormat = 'json'
     *
     * @var string
     */
    protected $outputFormat = null;

    /**
     * Path to the template that will render this request.
     *
     * The value is:
     * $this->basePath/$this->outputFormat/$this->action.php
     *
     * @var string
     */
    protected $templatePath = null;

    /*
     * output formats
     */
    const OUTPUT_PHP     = 'php';
    const OUTPUT_JSON    = 'json';
    const OUTPUT_RSS     = 'rss';
    const OUTPUT_DEFAULT = self::OUTPUT_PHP;

    /**
     * Constructor
     *
     * @param string $uri The URI we're dealing with
     */
    public function __construct($uri)
    {
        $this->uri = rtrim($uri);
        $this->parseUri($this->uri);
    }

    /**
     * Parse the passed in uri and set member variables based on the uri.
     *
     * These member variables that you set will determine:
     *  what public function is called ($this->action)
     *  what output format & templates will be used ($this->outputFormat)
     *
     * So at the very least you want to parse those out. But you're going
     * to want to override this in order to create a custom controller.
     *
     * @param string $uri A URI
     */
    protected function parseUri($uri)
    {
        $this->outputFormat = self::OUTPUT_DEFAULT;

        $matches = array();
        if (preg_match('/(.*)\.(\w+))/', $uri, $matches)) {
            $uri             = $matches[1];
            $potentialFormat = $matches[2];

            switch ($potentialFormat) {
                case self::OUTPUT_JSON :
                    $this->outputFormat = self::OUTPUT_JSON;
                    break;
                case self::OUTPUT_RSS :
                    $this->outputFormat = self::OUTPUT_RSS;
                    break;
                default:
                    throw new ReSQee_Exception(
                        "$potentialFormat is not a valid output format"
                    );
            }
        }

        $parts          = explode('/', trim($uri, '/'));
        $numParts       = count($parts);
        $this->basePath = $parts[0];

        switch ($numParts) {
            case 1 :
                $this->action = $this->basePath;
                break;
            case 2 :
                $this->action = $parts[1];
                break;
            case 3 :
                $this->action = $parts[1];
                $this->detail = $parts[2];
                break;
        }

        $this->templatePath = "{$this->basePath}/{$this->outputFormat}/{$this->action}.php";
    }

    /**
     * Factory method to get an instance of a controller
     *
     * @param string $uri The URI we want a controller for
     */
    public static function factory($uri)
    {
        $pathParts = explode('/', trim($uri, '/'));

        $controllerBaseName = (isset($pathParts[0]))
            ? ucfirst($pathParts[0])
            : 'Index';

        // TODO: need a loader of some type
        $controllerName = "ReSQee_Controller_{$controllerBaseName}";

        return new $controllerName($uri);
    }
}
?>