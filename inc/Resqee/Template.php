<?php
/**
 * Quick templating class
 *
 */
class Resqee_Template
{
    /**
     * Array holding the data for the template
     *
     * @var array
     */
    private $data = array();

    /**
     * Path to the template we're gonna render
     *
     * @var string
     */
    private $template = null;

    /**
     * Construct
     *
     * @param string $template Path to a template to render. $template msut be
     *  available via the include_path
     * @param array  $data     The data for the template as an assoc array
     */
    public function __construct($template, array $data = array())
    {
        $this->template = $template;
        $this->data     = $data;
    }

    /**
     * Render the template
     *
     * If $returnOutput if set to true then the method will return the output
     * else it will just dump the result onto the screen.
     *
     * @param bool $returnOutput Whether to return the output or not
     *
     * @return mixed The output if $returnOutput else void
     */
    public function render($returnOutput = false)
    {
        if ($returnOutput) {
            ob_start();
            extract($this->data);
            require $this->template;
            return ob_get_clean();
        } else {
            extract($this->data);
            require $this->template;
        }
    }

    /**
     * Include another template from within this one
     *
     * @param array $extraData
     */
    public function inc($template, array $extraData = array())
    {
        $data = (! empty($extraData))
            ? array_merge($this->data, $extraData)
            : $this->data;

        echo new Resqee_Template($template, $data);
    }

    /**
     * __toString method that will return the output of this template
     *
     * <pre>
     * echo new Template($path);
     * </pre>
     *
     * @return string
     */
    public function __toString() {
        return $this->render(true);
    }
}

?>