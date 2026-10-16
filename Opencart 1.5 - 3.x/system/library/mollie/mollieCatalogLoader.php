<?php
/**
 * Minimal loader used by the credit order code on OC 2.0 - 2.2: loads catalog models from
 * the catalog directory and hands every other call to the original loader.
 */
class MollieCatalogLoader {
    private $registry;
    private $loader;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->loader = $registry->get('load');
    }

    public function model($route) {
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
        $key = 'model_' . str_replace('/', '_', $route);

        if ($this->registry->has($key)) {
            return;
        }

        $file = DIR_CATALOG . 'model/' . $route . '.php';
        $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);

        if (!is_file($file)) {
            return $this->loader->model($route);
        }

        if (defined('DIR_MODIFICATION') && is_file(DIR_MODIFICATION . 'catalog/model/' . $route . '.php')) {
            $file = DIR_MODIFICATION . 'catalog/model/' . $route . '.php';
        }

        require_once($file);

        $this->registry->set($key, new $class($this->registry));
    }

    public function __call($name, $arguments) {
        return call_user_func_array(array($this->loader, $name), $arguments);
    }
}
