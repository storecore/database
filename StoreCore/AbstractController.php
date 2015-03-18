<?php
namespace StoreCore;

/**
 * Abstract MVC Controller
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright (c) 2015 StoreCore
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.1.0
 */
abstract class AbstractController
{
    protected $Registry;

    public function __construct(\StoreCore\Registry $registry)
    {
        $this->Registry = $registry;
    }

    public function __get($key)
    {
        return $this->Registry->get($key);
    }

    public function __set($key, $value)
    {
        $this->Registry->set($key, $value);
    }
}
