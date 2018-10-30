<?php
namespace comercia;
class ModuleSettings
{
    var $fields = array();
    var $prepare;
    var $postFinish;
    var $name;

    function __construct($name)
    {
        $this->setName($name);
        $this->prepare = function () {
        };
        $this->postFinish=function(){};

    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setFields($first)
    {
        if (!is_array($first)) {
            $first = func_get_args();
        }
        $this->fields = $first;
    }

    function prepare($func)
    {
        $this->prepare = $func;
    }


    function postFinish($func){
        $this->postFinish=$func;
    }
    function run($forceRedirect = false)
    {
        //load the language data
        $data = array();
        $name = $this->name;
        $form = Util::form($data);
        Util::load()->language("module/" . $name, $data);

        if ($forceRedirect) {
            $data['redirect'] = $forceRedirect;
        }

        $form->finish(function ($data) {
            Util::config()->set($this->name, Util::request()->post()->all());
            Util::session()->success = $data['msg_settings_saved'];
            $postFinish = $this->postFinish;
            if (is_callable($postFinish)) {
                $postFinish($data);
            }
            Util::response()->redirect(@$data['redirect'] ?: Util::route()->extension());
        });

        //handle the form when finished
        $formFields = $this->fields;
        $prepare = $this->prepare;
        if (is_callable($prepare)) {
            $prepare($data);
        }

        //place the prepared data into the form
        $form
            ->fillFromSessionClear("error_warning", "success")
            ->fillFromPost($formFields)
            ->fillFromConfig($formFields);

        Util::breadcrumb($data)
            ->add("text_home", "common/home")
            ->add("settings_title", "module/" . $name);


        //handle document related things
        Util::document()->setTitle(Util::language()->heading_title);

        //create links
        $data['action'] = Util::version()->isMinimal("2.3")?Util::url()->link('extension/module/' . $name):Util::url()->link('module/' . $name);
        $data['cancel'] = Util::url()->link(Util::route()->extension());

        //create a response
        Util::response()->view("module/" . $name . ".tpl", $data);
    }

}

?>