<?php
namespace CakeResque;

use Resque_Exception;

/**
 * Resque Job Creator Class
 *
 * Create a job instance
 *
 * This will find and instanciate a class from a classname.
 * Particulary important if the classname isn't the real classname,
 * like in CakePHP, where the classname can be prefixed with
 * a plugin name, and the classname doesn't give a clue about
 * the class file location.
 *
 * This class is optional, and if missing, Resque will handle the job
 * creation itself, with the standard method.
 *
 * @since 1.0
 * @author kamisama
 *
 */
class Resque_Job_Creator
{

    /**
     * Application Root Folder path
     *
     * @var String
     */
    public static $rootFolder = null;

    /**
     * Create and return a job instance
     *
     * @param string $className className of the job to instanciate
     * @param array $args Array of method name and arguments used to build the job
     * @return object $model a job class
     * @throws Resque_Exception when the class is not found, or does not follow the job file convention
     */
    public static function createJob($className, $args)
    {
        list($plugin, $model) = pluginSplit($className);

        if (self::$rootFolder === null) {
            self::$rootFolder = dirname(dirname(dirname(dirname(__DIR__)))) . DS;
        }

        $classpath = self::$rootFolder . (empty($plugin) ? '' : 'plugins' . DS . $plugin . DS) . 'src' . DS . 'Shell' . DS . $model . '.php';

        if (file_exists($classpath)) {
            require_once $classpath;
        } else {
            throw new Resque_Exception('Resque_Job_Creator could not find file '.$classpath.' for job class ' . $className . '.');
        }

        $model = $plugin . '\\Shell\\' . $model;

        if (!class_exists($model)) {
            throw new Resque_Exception('Resque_Job_Creator could not find job class ' . $className . ' in file '.$classpath.'.');
        }

        if (!method_exists($model, $args[0])) {
            throw new Resque_Exception('Resque_Job_Creator. Job class ' . $className . ' does not contain ' . $args[0] . ' method.');
        }

        return new $model();
    }
}