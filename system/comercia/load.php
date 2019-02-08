<?php
namespace comercia;

class Load
{
    function library($library)
    {
        if (is_array($library)) {
            $libraries = $library;
            $result = [];
            foreach ($libraries as $library) {
                $result[$library] = $this->library($library);
            }
            return $result;
        };

        static $singletons = [];
        if (!isset($singletons[$library])) {
            $className = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $library))));
            $className = $className;
            $libDir = DIR_SYSTEM . "library/";
            $bestOption = $this->findBestOption($libDir, $library, "php");
            if (!class_exists($className)) {
                if (class_exists("VQMod")) {
                    @include_once(\VQMod::modCheck($this->modification($libDir . $bestOption["name"] . ".php"), $libDir . $bestOption["name"] . ".php"));
                } else {
                    @include_once($this->modification($libDir . $bestOption["name"] . ".php"));
                }
            }

            if (class_exists($className)) {
                $result = new $className(Util::registry());
                Util::registry()->set(Util::stringHelper()->ccToUnderline($className), $result);
                $singletons[$library] = $result;
            } else {
                $singletons[$library] = false;
            }

        }

        return $singletons[$library];
    }

    function findBestOption($dir, $name, $extension)
    {

        //fiend associated files
        $posibilities = glob($dir . "" . $name . "*." . $extension);
        $files = array();
        foreach ($posibilities as $file) {
            $file = str_replace(DIR_TEMPLATE, "", $file);
            $file = str_replace(".tpl", "", $file);
            $file = str_replace(".twig", "", $file);
            $expFile = str_replace(")", "", $file);
            $exp = explode("(", $expFile);
            $files[] = array(
                "name" => $file,
                "version" => isset($exp[1]) ? explode("_", $exp[1]) : false
            );
        }

        //find best option
        $bestOption = false;
        foreach ($files as $file) {
            if (
                ($file["version"]) && //check if this file has a version if no version its never the best option
                (
                    $file["version"][0] == "min" && Util::version()->isMinimal($file["version"][1]) ||//decide if is valid in case of minimal
                    $file["version"][0] == "max" && Util::version()->isMaximal($file["version"][1]) //decide if is valid in case of maximal
                ) &&
                (!$bestOption || $file["version"][0] == "max" || $bestOption["version"][0] == "min") && //prioritize max version over min version
                (
                    !$bestOption || // if there is no best option its always the best option
                    ($file["version"][0] == "min" && version_compare($file["version"][1], $bestOption["version"][1], ">")) ||//if priority is by minimal , find the highest version
                    $file["version"][0] == "max" && version_compare($file["version"][1], $bestOption["version"][1], "<") //if priority is by maximal , find the lowest version
                )
            ) {
                $bestOption = $file;
            }

        }

        if (!$bestOption) {
            $bestOption = array(
                "name" => $name,
                "version" => false,
            );
        }

        return $bestOption;

    }

    function model($model)
    {
        $model = $this->rewriteModel($model);
        if (is_array($model)) {
            $models = $model;
            $result = [];
            foreach ($models as $model) {
                $result[$model] = $this->model($model);
            }
            return $result;
        };

        $modelDir = DIR_APPLICATION . 'model/';
        $route = $this->getRouteInfo("model", $model, $modelDir);
        $className = $route["class"];
        if (!class_exists($className)) {
            if (class_exists("VQMod")) {
                @include_once(\VQMod::modCheck($this->modification($modelDir . $route["file"] . ".php"), $modelDir . $route["file"] . ".php"));
            } else {
                @include_once($this->modification($modelDir . $route["file"] . ".php"));
            }
        }

        if (class_exists($className)) {

            //Check for events
            if(Util::version()->isMinimal("2.2")) {
                $result = new \Proxy();

                $r = new \ReflectionMethod('Loader', 'callback');
                $r->setAccessible(true);

                foreach (get_class_methods($className) as $method) {
                    $result->{$method} = $r->invoke(new \Loader(Util::registry()), Util::registry(), $route["file"] . '/' . $method);
                }
            } else {
                $result = new $className(Util::registry());
            }

            Util::registry()->set(Util::stringHelper()->ccToUnderline($className), $result);
            return $result;
        }
        return false;
    }

    function getRouteInfo($prefix, $route, $dir)
    {
        $parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route));

        $fileRoute = "";
        $method = "";
        $params = [];
        while ($parts) {
            $file = $dir . implode('/', $parts) . '.php';

            if (is_file($file)) {
                $fileRoute = implode('/', $parts);
                break;
            } else {
                if ($method) {
                    $params[] = $method;
                }
                $method = array_pop($parts);
            }
        }

        $registry = Util::registry();

        $className = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fileRoute))));
        $className = lcfirst(str_replace(' ', '', ucwords(str_replace('/', ' ', $className))));
        $className = ucfirst($className);
        $className = ucfirst($prefix) . preg_replace('/[^a-zA-Z0-9]/', '', $className);

        $bestOption = $this->findBestOption($dir, $fileRoute, "php");

        return array(
            "file" => $bestOption["name"],
            "class" => $className,
            "method" => $method,
            "params" => $params
        );
    }

    function view($view, $data = array())
    {
        if (Util::version()->isMinimal("3.0")) {
            $extension = "twig";
            $view = str_replace(".tpl", ".twig", $view);
        } else {
            $extension = "tpl";
        }

        if (Util::info()->isInAdmin()) {
            $bestOption = $this->findBestOption(DIR_TEMPLATE, $view, $extension);
        } else {
            $bestOption1 = $this->findBestOption(DIR_TEMPLATE . "default/template/", $view, $extension);
            $bestOption2 = $this->findBestOption(DIR_TEMPLATE . Util::info()->theme() . "/template/", $view, $extension);
            if ($bestOption1["version"] && !$bestOption2["version"]) {
                $bestOption = $bestOption1;
            } else {
                $bestOption = $bestOption2;
            }
        }

        $view = $bestOption["name"];

        $registry = Util::registry();
        if (Util::version()->isMinimal(3.0)) {
            try {
                $view = str_replace(".twig", "", $view);
                return $registry->get("load")->view($view, $data);
            } catch (\Exception $ex) {
                return $this->tplFallback($view, $data);
            }
        } elseif (Util::version()->isMinimal(2.0)) {
            if (Util::version()->isMinimal("2.2") || Util::version()->isMinimal("2") && Util::info()->isInAdmin()) {
                if (Util::version()->isMaximal("2.1.0.2")) { // must be 2.0 or 2.1; both versions don't add tpl in the loader
                    $view .= "." . $extension;
                } else {
                    $view = str_replace("." . $extension, "", $view);
                }
                return $registry->get("load")->view($view, $data);
            } else {
                if (Util::version()->isMaximal("2.1.0.2")) {
                    $view .= "." . $extension;
                }

                if (file_exists(DIR_TEMPLATE . Util::info()->theme() . '/template/' . $view)) {
                    return $registry->get("load")->view(Util::info()->theme() . "/template/" . $view, $data);
                } else {
                    return $registry->get("load")->view('default/template/' . $view, $data);
                }
            }
        } elseif (Util::version()->isMinimal(1.5) && !Util::info()->isInAdmin()) {
            $view .= "." . $extension;
            if (file_exists(DIR_TEMPLATE . Util::info()->theme() . '/template/' . $view)) {
                $view = DIR_TEMPLATE . Util::info()->theme() . '/template/' . $view;
            } else {
                $view = 'default/template/' . $view;
            }
        }
        $fakeControllerFile = __DIR__ . "/fakeController.php";
        if (class_exists("VQMod")) {
            require_once(\VQMod::modCheck($this->modification($fakeControllerFile), $fakeControllerFile));
        } else {
            require_once($this->modification($fakeControllerFile));
        }
        $controller = new FakeController($registry);
        $result = $controller->getView($view, $data);
        return $result;
    }

    public function tplFallback($view, $_data = array())
    {
        $view = str_replace(".twig", ".tpl", $view);

        if (Util::info()->isInAdmin()) {
            $bestOption = $this->findBestOption(DIR_TEMPLATE, $view, "tpl");
        } else {
            $bestOption1 = $this->findBestOption(DIR_TEMPLATE . "default/template/", $view, "tpl");
            $bestOption2 = $this->findBestOption(DIR_TEMPLATE . Util::info()->theme() . "/template/", $view, "tpl");
            if ($bestOption1["version"] && !$bestOption2["version"]) {
                $bestOption = $bestOption1;
            } else {
                $bestOption = $bestOption2;
            }
        }

        $view = $bestOption["name"];

        $file = DIR_TEMPLATE . $view;

        if (!substr($file, -4) != ".tpl") {
            $file .= ".tpl";
        }

        if (is_file($file)) {
            extract($_data);
            ob_start();
            require($file);
            return ob_get_clean();
        }

    }

    function language($file, &$data = array(), $language = false)
    {
        if (is_array($file)) {
            $files = $file;
            $result = [];
            foreach ($files as $file) {
                $result = array_merge($result, $this->language($file, $data));
            }
            return $result;
        };

        $file = $this->rewriteLanguage($file);

        $registry = Util::registry();
        if ($language) {
            $language->load($file);
        } else {
            $result = $registry->get("load")->language($file);
        }
        foreach ($result as $key => $val) {
            $data[$key] = $val;
        }
        return $result;
    }

    function pageControllers(&$data)
    {
        $data['header'] = Util::load()->controller('common/header');
        $data['column_left'] = Util::load()->controller('common/column_left');
        $data['footer'] = Util::load()->controller('common/footer');
    }

    function controller($controller)
    {

        if (is_array($controller)) {
            $controllers = $controller;
            $result = [];
            foreach ($controllers as $controller) {
                $result[$controller] = $this->controller($controller);
            }
            return $result;
        };

        $controllerDir = DIR_APPLICATION . 'controller/';
        $route = $this->getRouteInfo("controller", $controller, $controllerDir);

        $className = $route["class"];
        if (!class_exists($className)) {
            if (class_exists("VQMod")) {
                @include_once(\VQMod::modCheck($this->modification($controllerDir . $route["file"] . ".php"), $controllerDir . $route["file"] . ".php"));
            } else {
                @include_once($this->modification($controllerDir . $route["file"] . ".php"));
            }
        }

        if (class_exists($className)) {
            $rc = new \ReflectionClass($className);
            if ($rc->isInstantiable()) {
                $method = $route["method"] ? $route["method"] : "index";
                $controller = new $className(Util::registry());
                $mr = new \ReflectionMethod($className, $method);
                $mr->setAccessible(true);
                if (!empty($route["params"])) {
                    $result = $mr->invokeArgs($controller, $route["params"]);
                } else {
                    $result = $mr->invoke($controller);
                }

                if (!$result) {
                    try {
                        $pr = new \ReflectionProperty($className, "output");
                        $pr->setAccessible(true);
                        $result = $pr->getValue($controller);
                    } catch (\Exception $ex) {
                    }
                }

                return $result ?: "";
            }
        }
        return "";
    }

    private function rewriteModel($model)
    {
        return Util::stringHelper()->rewriteForVersion($model,
            [
                [
                    "" => "sale/custom_field",
                    "2.1" => "customer/custom_field"
                ],
                [
                    "" => "sale/customer_group",
                    "2.1" => "customer/customer_group"
                ],
                [
                    "" => "setting/extension",
                    "2.0" => "extension/extension",
                    "3.0" => "setting/extension"
                ],
                [
                    "" => "extension/event",
                    "3.0" => "setting/event"
                ]
            ]
        );
    }


    private function rewriteLanguage($model)
    {
        return Util::stringHelper()->rewriteForVersion($model,
            [
                [
                    "" => "payment/",
                    "2.3" => "extension/payment/"
                ]
            ]
        );
    }

    // Modification Override
    function modification($filename) {
        if (Util::version()->isMinimal(2.0)) {
            if (defined('DIR_CATALOG')) {
                $file = DIR_MODIFICATION . 'admin/' . substr($filename, strlen(DIR_APPLICATION));
            } elseif (defined('DIR_OPENCART')) {
                $file = DIR_MODIFICATION . 'install/' . substr($filename, strlen(DIR_APPLICATION));
            } else {
                $file = DIR_MODIFICATION . 'catalog/' . substr($filename, strlen(DIR_APPLICATION));
            }

            if (substr($filename, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
                $file = DIR_MODIFICATION . 'system/' . substr($filename, strlen(DIR_SYSTEM));
            }

            if (is_file($file)) {
                return $file;
            }
        }

        return $filename;
    }
}

?>
