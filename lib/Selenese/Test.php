<?php

namespace Selenese;

use Selenese\Command;

class Test {

    /**
     * @var Command\Command[]
     */
    public $commands = array();

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @param string $file
     * @return Command\Command[]
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function loadFromSeleneseHtml($file) {

        if (!file_exists($file)) {
            throw new \InvalidArgumentException("$file does not exist");
        }

        if (!is_readable($file)) {
            throw new \InvalidArgumentException("$file is not readable");
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $loaded = $dom->loadHTMLFile($file);
        // todo: deal with badly loading HTML if needed
//        if (!$loaded) {
//            foreach (libxml_get_errors() as $error) {
//                // handle errors here
//            }
//            libxml_clear_errors();
//        }

        // get the base url
        $this->baseUrl = $dom->getElementsByTagName('link')->item(0)->getAttribute('href');
        $this->baseUrl = rtrim($this->baseUrl, '/');

        //<link rel="selenium.base" href="https://www.creditkarma.com/" />

        // todo: catch loading of things NOT selenese
        $rows = $dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');

        // extract the commands
        foreach ($rows as $row) {
            /** @var \DOMElement $row */
            $tds = $row->getElementsByTagName('td');
            $command = $tds->item(0)->nodeValue;
            $target  = $tds->item(1)->nodeValue;
            $value   = $tds->item(2)->nodeValue;
            $commandClass = '\\Selenese\\Command\\' . $tds->item(0)->nodeValue;
            if (class_exists($commandClass)) {
                /** @var Command\Command $command */
                $commandObj = new $commandClass();
                $commandObj->command = $command;
                $commandObj->target  = ($command == 'open' ? $this->baseUrl : '') . $target;
                $commandObj->value   = $value;
                $this->commands[] = $commandObj;
            }
            else {
//                throw new \Exception("Unknown command: ");
            }
        }

    }

}