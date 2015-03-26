<?php
namespace StoreCore\FileSystem;

/**
 * Robots Controller
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright (c) 2015 StoreCore
 * @license   http://www.gnu.org/licenses/gpl.html
 * @version   0.0.1
 */
class Robots extends \StoreCore\AbstractController
{
    const VERSION = '0.0.1';

    private $Model;
    private $View = "User-agent: *\nDisallow:";

    public function __construct(\StoreCore\Registry $registry)
    {
        parent::__construct($registry);
        $this->loadModel();
        $this->renderView();
        $this->respond();
    }

    private function loadModel()
    {
        $this->Model = new \StoreCore\Database\Robots($this->Registry);
    }

    private function renderView()
    {
        $robots = $this->Model->getAllDisallows();

        if (is_array($robots)) {
            $view = (string)null;
            foreach ($robots as $user_agent => $paths) {
                $view .= 'User-agent: ' . $user_agent . "\n";
                foreach ($paths as $path) {
                    $view .= 'Disallow: ' . $path . "\n";
                }
                $view .= "\n";
            }
            $this->View = $view;
        }
    }

    private function respond()
    {
        $response = new \StoreCore\Response($this->Registry);
        $response->addHeader('Content-Type: text/plain');
        $response->setCompression(0);
        $response->setResponseBody($this->View);
        $response->output();
    }
}
