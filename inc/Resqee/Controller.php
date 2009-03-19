<?php
/**
 * Pretty simple controller
 *
 */
abstract class Resqee_Controller
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
     * $_SERVER should be passed in to __construct. We store that here
     *
     * @var array
     */
    protected $serverGlobal = array();

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
    public function __construct($serverGlobal)
    {
        $this->uri          = rtrim($serverGlobal['REDIRECT_URL']);
        $this->serverGlobal = $serverGlobal;

        $this->setOutputFormat();
        $this->parseUri($this->uri);
    }

    /**
     * Set the output format
     *
     * @return void
     */
    public function setOutputFormat()
    {
        $this->outputFormat = self::OUTPUT_DEFAULT;

        $matches = array();
        if (preg_match('/(.*)\.(\w+)/', $this->uri, $matches)) {
            $this->uri       = $matches[1];
            $potentialFormat = $matches[2];

            switch ($potentialFormat) {
                case self::OUTPUT_JSON :
                    $this->outputFormat = self::OUTPUT_JSON;
                    break;
                case self::OUTPUT_RSS :
                    $this->outputFormat = self::OUTPUT_RSS;
                    break;
                default:
                    throw new Resqee_Exception(
                        "$potentialFormat is not a valid output format"
                    );
            }
        }
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
    }

    /**
     * Get the path to the template that will render this request
     *
     * @return string
     */
    protected function getTemplatePath()
    {
        return "action/{$this->basePath}/{$this->outputFormat}/{$this->action}.php";
    }

    /**
     * GHet the name of the action that needs to run for this request
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Forward work over to another action
     *
     * @param string $newAction The action to forward the control to
     *
     * @return mixed
     */
    protected function forward($newAction)
    {
        $this->action = $newAction;
        return call_user_func_array(array($this, $newAction), func_get_args());
    }

    /**
     * Render this request
     *
     * The rendering is performed by instnciating a new Resqee_Template.
     * All member variables of this class will be passed over to the template.
     * The template is determined by the value of $this->getTemplatePath();
     *
     * @return void
     */
    protected function render()
    {
        // this is ghetto but whatever
        if ($this->outputFormat == self::OUTPUT_PHP) {
            require_once 'chrome/header.php';
        }

        echo new Resqee_Template($this->getTemplatePath(), get_object_vars($this));

        if ($this->outputFormat == self::OUTPUT_PHP) {
            require_once 'chrome/footer.php';
        }
    }

    /**
     * Factory method to get an instance of a controller
     *
     * @param string $uri The URI we want a controller for
     *
     * @return Resqee_Controller
     */
    public static function factory($serverGlobal)
    {
        $uri        = $serverGlobal['REDIRECT_URL'];
        $controller = null;
        $trimmed    = trim($uri, '/');

        if ($trimmed == '') {
            $controller = 'Index';
        } else {
            $pathParts  = explode('/', $trimmed);
            $controller = ucfirst($pathParts[0]);
        }

        // TODO: need a loader of some type
        $className = "Resqee_Controller_{$controller}";

        return new $className($serverGlobal);
    }
}
?>