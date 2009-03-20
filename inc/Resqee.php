<?php

require_once 'Resqee/Exception.php';

class Resqee
{
    /**
     * Value used as the POST param for the serialized job
     */
    const KEY_POST_JOB_PARAM = 'REQSEE_JOB';

    /**
     * Value used as the POST param for the # of tried
     */
    const KEY_POST_JOB_NUM_TRIES = 'NUM_TRIES';

    /**
     * Value used as the POST param for the job's class name
     */
    const KEY_POST_JOB_CLASS_PARAM = 'REQSEE_JOB_CLASS';

    /**
     * Load a class by name
     *
     * This loader assumes that a class's path is determined by the name.
     * _ is replaced with DIRECTORY_SEPARATOR and .php is tacked on to the end
     *
     * Note that the generated path must be in your include_path
     *
     * @param string $className Name of the class to load
     *
     * @return bool TRUE upon success
     */
    public static function loadClass($className)
    {
        if (! class_exists($className)) {
            $path = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            $inc  = @include_once $path;

            if (! $inc) {
                throw new Resqee_Exception(
                    "Could not load class: {$className}. I look in "
                    . '<include_path>' . DIRECTORY_SEPARATOR
                    . "{$path} but couldn't find it. Your include_path is: "
                    . get_include_path()
                );
            }
        }

        return true;
    }
}

?>