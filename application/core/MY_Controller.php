<?php

/**
 * Main controller.
 *
 * @author    Tim Joosten   <Topairy@gmail.com>
 * @copyright Activisme-BE  <info@activisme.be>
 * @license:  MIT license
 * @since     2017
 * @package   Petitions
 */
class MY_Controller extends CI_Controller
{
    /**
     * Middlewares variable declaration.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * MY_Controller constructor.
     *
     * @return int|void|null
     */
    public function __construct()
    {
        parent::__construct();
        $this->_run_middlewares();
    }

    /**
     * The middleware assignment.
     *
     * @return array.
     */
    protected function middleware()
    {
        return [];
    }

    /**
     * Middleware implementation logic.
     *
     * @return int|void|null
     */
    public function _run_middlewares()
    {
        $this->load->helper('inflector');
        $middlewares = $this->middleware();

        foreach ($middlewares as $middleware) {
            $middlewareArray = explode('|', str_replace(' ', '', $middleware));
            $middlewareName  = $middlewareArray[0];
            $runMiddleware   = true;

            if (isset($middlewareArray[1])) {
                $options = explode(':', $middlewareArray[1]);
                $type    = $options[0];
                $methods = explode(',', $options[1]);

                if ($type == 'except') {
                    if (in_array($this->router->method, $methods)) {
                        $runMiddleware = false;
                    }
                } elseif ($type == 'only') {
                    if (! in_array($this->router->method, $methods)) {
                        $runMiddleware = false;
                    }
                }
            }

            $filename = ucfirst(camelize($middlewareName)) . 'Middleware';

            if ($runMiddleware == true) {
                if (file_exists(APPATH . 'middlewares/' . $filename . '.php')) {
                    require APPATH . 'middlewares/' . $filename . '.php';

                    $ci = &get_instance();

                    $object = new $filename($this, $ci);
                    $object->run();

                    $this->middlewares[$middlewareName] = $object;
                } else {
                    if (ENVIRONMENT == 'development') {
                        show_error('Unable to load middleware: ' . $filename . '.php');
                    } else {
                        show_error('Sorry something went wrong.');
                    }
                }
            }
        }
    }
}
